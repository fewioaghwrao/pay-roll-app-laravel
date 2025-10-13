<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AiExplanationService
{
public function explain(array $payload): string
{
    // 6か月の系列があれば「傾向モード」
    $isTrend = isset($payload['series']['labels'], $payload['series']['net']);

    // キャッシュキー（明細の系列で変わるように）
    $keyBasis = $isTrend ? $payload['series'] : $payload;
    $cacheKey = 'ai_explain:' . md5(json_encode($keyBasis, JSON_UNESCAPED_UNICODE));
    if ($hit = Cache::get($cacheKey)) return $hit;

    // DEMO_MODE はローカル生成（課金ゼロ）
    if (config('app.demo') || env('DEMO_MODE', false)) {
        $text = $isTrend
            ? $this->demoTrendText($payload['series'])
            : $this->demoSnapshotText($payload); // 互換用（単月）
        Cache::put($cacheKey, $text, now()->addMinutes(5)); // デモは短めキャッシュ
        return $text;
    }

    // 実API：傾向モード用プロンプト
    $prompt = $isTrend
        ? $this->buildTrendPrompt($payload['series'])
        : $this->buildPrompt($payload);

    $apiKey = env('OPENAI_API_KEY');
    $model  = env('OPENAI_MODEL', 'gpt-4.1-mini');

    $resp = Http::timeout(20)->withToken($apiKey)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' =>
                    'あなたは日本の給与明細に詳しいアシスタントです。事実のみ簡潔に、箇条書きで説明します。誇張や断定は避けます。'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
            'max_tokens'  => 600,
        ])->throw();

    $text = data_get($resp->json(), 'choices.0.message.content', '説明を生成できませんでした。');
    Cache::put($cacheKey, $text, now()->addDays(30));
    return $text;
}

/** 旧：単月スナップショット（互換） */
private function demoSnapshotText(array $p): string
{
    return "【AI説明（デモ）】\n"
        ."{$p['year']}年{$p['month']}月度の明細です。\n"
        ."総支給は「".number_format($p['gross_amount'])."円」。\n"
        ."税額は「".number_format($p['tax_amount'])."円」。\n"
        ."差引の手取りは「".number_format($p['net_amount'])."円」です。\n"
        ."内訳では「基本給」「残業手当」「社会保険料」等の増減が影響しています。";
}

/** 新：6か月傾向のデモ文をローカル生成 */
private function demoTrendText(array $s): string
{
    $labels = $s['labels'] ?? [];
    $net    = $s['net'] ?? [];
    if (count($net) < 2) {
        return "【AI説明（デモ/傾向）】データが不足しているため、傾向を算出できません。";
    }

    $fmt = fn($n) => '¥'.number_format((int)$n);
    $first = (int)reset($net);
    $last  = (int)end($net);
    $diff  = $last - $first;
    $pct   = $first !== 0 ? round($diff / $first * 100, 1) : 0.0;

    // MoM（直近）
    $prev  = (int)$net[count($net)-2];
    $mom   = $last - $prev;
    $momPct= $prev !== 0 ? round($mom / $prev * 100, 1) : 0.0;

    // 最大/最小
    $maxV = max($net); $maxI = array_search($maxV, $net);
    $minV = min($net); $minI = array_search($minV, $net);
    $maxL = $labels[$maxI] ?? '';
    $minL = $labels[$minI] ?? '';

    $bonusNote = !empty($s['has_bonus']) ? "（※賞与月が含まれるため、変動が大きくなっています）\n" : "";

    return "【AI説明（デモ/6か月の傾向）】\n"
        ."期間：".($labels[0] ?? '?')." 〜 ".(end($labels) ?: '?')."\n"
        ."• 手取りの推移：{$fmt($first)} → {$fmt($last)}（{$pct}%）\n"
        ."• 直近の前月比：".($mom>=0?'+':'')."{$fmt($mom)}（{$momPct}%）\n"
        ."• 最も高い月：{$maxL}（{$fmt($maxV)}）\n"
        ."• 最も低い月：{$minL}（{$fmt($minV)}）\n"
        .$bonusNote
        ."※ 本文はデモ用の自動要約です。詳細は各月の明細をご確認ください。";
}

/** OpenAI用：傾向プロンプト */
private function buildTrendPrompt(array $s): string
{
    // JSONをそのまま渡さず、必要最小限の数値だけを列挙
    $labels = $s['labels'] ?? [];
    $gross  = $s['gross']  ?? [];
    $tax    = $s['tax']    ?? [];
    $net    = $s['net']    ?? [];
    $hasBonus = !empty($s['has_bonus']) ? 'あり' : 'なし';

    $lines = [];
    for ($i=0; $i<count($labels); $i++) {
        $L = $labels[$i] ?? '';
        $g = (int)($gross[$i] ?? 0);
        $t = (int)($tax[$i]   ?? 0);
        $n = (int)($net[$i]   ?? 0);
        $lines[] = "{$L} : 総支給 {$g} / 税額 {$t} / 手取り {$n}";
    }
    $table = implode("\n", $lines);

    return <<<TXT
以下はある従業員の「過去6か月」の給与明細の要約（メタ情報）です。個人特定情報は含みません。
データ:
{$table}
備考: 賞与の有無={$hasBonus}

要件:
1) 6か月の「手取り」の傾向を、冒頭1行で要約（増加/減少/横ばい）。
2) 次に 箇条書きで 3〜6行: 全体の増減率、直近の前月比、最も高い/低い月、賞与の影響（あれば）を短く。
3) 表現は事実ベースで丁寧に。断定や推測は避け、「可能性がある」等の緩め表現を使う。
4) 合計で7行以内、日本語、数値はカンマ区切り（円表記は不要）。
TXT;
}

}
