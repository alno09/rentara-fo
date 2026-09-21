<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Panel;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_homepage_and_admin_access(): void
    {
        $this->get('/')->assertRedirect('/admin');
        $this->get('/admin')->assertRedirect('/admin/login');

        $admin = new User(['email' => 'admin@example.com']);
        $other = new User(['email' => 'other@example.com']);
        $panel = Panel::make()->id('admin');

        config(['auth.filament_admin_email' => null]);
        $this->assertFalse($admin->canAccessPanel($panel));

        config(['auth.filament_admin_email' => 'admin@example.com']);
        $this->assertTrue($admin->canAccessPanel($panel));
        $this->assertFalse($other->canAccessPanel($panel));
        $this->assertFalse($admin->canAccessPanel(Panel::make()->id('other')));
    }
}
