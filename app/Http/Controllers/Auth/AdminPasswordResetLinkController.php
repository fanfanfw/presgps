<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class AdminPasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view for admin/HRD.
     */
    public function create(): View
    {
        return view('auth.admin.forgot-password');
    }

    /**
     * Handle an incoming password reset link request for admin/HRD.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::query()
            ->where('email', $request->email)
            ->first();

        if (! $user || ! $this->isAdminAccount($user)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email ini tidak terdaftar sebagai admin/HRD.']);
        }

        $status = Password::sendResetLink(
            $request->only('email'),
            function ($user, string $token) {
                $user->notify(new AdminResetPasswordNotification($token));
            }
        );

        return $status == Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    private function isAdminAccount(User $user): bool
    {
        return $user->roles()->where('name', '!=', 'karyawan')->exists();
    }
}

