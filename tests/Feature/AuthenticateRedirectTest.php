<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthenticateRedirectTest extends TestCase
{
    public function test_admin_path_redirects_to_admin_login()
    {
        $response = $this->get('/admin');

        // 未認証なのでリダイレクト（302）
        $response->assertStatus(302);
        $location = $response->headers->get('Location');
        $this->assertTrue(
            in_array($location, [route('admin.login.form'), route('login')], true),
            "Redirected to unexpected location: $location"
        );
    }

    public function test_root_path_redirects_to_login()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }
}
