<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        // 未認証環境のテスト実行では / はログインへリダイレクトされる想定
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
