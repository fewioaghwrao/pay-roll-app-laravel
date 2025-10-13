# Payroll App

給与明細アプリ（Laravel 12 / PHP 8.2）。このリポジトリはデモ用ポートフォリオです。

## 概要
- 管理者（admin）と一般ユーザー（web）のガード分離
- 支払明細（Payslip）と源泉徴収票の作成・ダウンロード（PDF）
- 検索・ページング・チャート向けデータ整形

## 主要技術
- PHP 8.2, Laravel 12
- Vite, TailwindCSS（フロントエンド資産）
- barryvdh/laravel-dompdf（PDF 出力）
- PHPUnit を用いたテスト（in-memory sqlite 設定）

---

## 必須環境（開発用 / Windows PowerShell）
- PHP 8.2
- Composer
- Node.js + npm
- SQLite または MySQL（サンプル手順は SQLite を前提）

---

## 速攻セットアップ（PowerShell）
プロジェクトルートで以下を実行してください。

```powershell
# 依存インストール
composer install
npm install

# 環境ファイル作成
copy .env.example .env
php artisan key:generate

# sqlite を使う場合（database/database.sqlite ファイル作成）
if (-Not (Test-Path database\database.sqlite)) { New-Item database\database.sqlite -ItemType File }

# マイグレーション & シーダ（開発用）
php artisan migrate --seed

# ビルド（開発ビルド）
npm run build

# ローカルサーバ起動
php artisan serve
```

ブラウザで http://127.0.0.1:8000 を開きます。

---

## サンプルアカウント
（デフォルトで seed が用意されている場合）
- 管理者: admin@example.com / password
- ユーザ: user@example.com / password

※ もしシーダがない場合は、`php artisan tinker` で手動作成してください。

---

## テスト
ローカルでテストを実行するには：

```powershell
php artisan test
```

テストは `phpunit.xml` で `:memory:` SQLite を使う設定になっています。

---

## よくある問題と対処
- 「Trait Tests\\CreatesApplication not found」などのエラーが出る場合は、`tests/CreatesApplication.php` が存在することを確認してください（このリポジトリでは追加済み）。
- 依存関係エラーは `composer install` を再実行してキャッシュをクリアしてください。

---

## セキュリティ上の注意（面接でのアピールポイント）
- パスワードは `Hash::make()` で保存しています。モデルの `$casts` と `$fillable` を見直し、マスアサインメントを最小化してあります。
- しかし、本番公開前には更に依存ライブラリの脆弱性スキャンとロギング、レート制限（ログイン）を追加することを推奨します。

---

## 追加で私が入れた修正一覧（面接で説明できる変更）
- `User` モデルの `$casts` を正しく修正（`password` のハッシュと `email_verified_at` の cast）
- `Payslip` モデルの relation を `user_id` ベースに統一
- テスト用の `CreatesApplication` トレイトを追加し、テストを通るよう修正
- `Authenticate` ミドルウェアの admin 判定を堅牢化

---

## 次の改善候補（履歴書で触れるとよい）
- CI の追加（GitHub Actions で tests + pint を実行）
- Docker compose でワンコマンド起動
- 主要ロジックのサービス化と単体テストの強化

---

## Screenshots

以下はアプリケーションのスクリーンショット例です。

<p align="center">
	<img src="docs/screenshots/screenshot-1.png" alt="User payslips list" style="max-width:900px; width:100%; height:auto;" />
</p>

<p align="center">
	<img src="docs/screenshots/screenshot-2.png" alt="Admin user list" style="max-width:900px; width:100%; height:auto;" />
</p>

<p align="center">
	<img src="docs/screenshots/screenshot-3.png" alt="Payslip chart" style="max-width:900px; width:100%; height:auto;" />
</p>

---
### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
