<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_login_page_uses_the_existing_database_and_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Selamat datang kembali')
            ->assertSee('assets/css/app.css');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_crm_analytics_requires_crm_authentication(): void
    {
        $this->get('/crm/analytics')->assertRedirect('/crm/login');
    }
}
