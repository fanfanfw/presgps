<?php

namespace Tests\Feature\Auth;

use App\Notifications\AdminResetPasswordNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/admin/forgot-password');

        $response->assertStatus(200);
    }

    public function test_admin_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->post('/admin/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, AdminResetPasswordNotification::class);
    }

    public function test_admin_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->post('/admin/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, AdminResetPasswordNotification::class, function ($notification) {
            $response = $this->get('/admin/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_admin_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $user->assignRole('admin');

        $this->post('/admin/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, AdminResetPasswordNotification::class, function ($notification) use ($user) {
            $response = $this->post('/admin/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response->assertSessionHasNoErrors();

            return true;
        });
    }
}
