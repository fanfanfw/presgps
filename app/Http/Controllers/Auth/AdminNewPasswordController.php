<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AdminNewPasswordController extends Controller
{
    /**
     * Display the password reset view for admin/HRD.
     */
    public function create(Request $request): View
    {
        return view('auth.admin.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request for admin/HRD.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::query()
            ->where('email', $request->email)
            ->first();

        if (! $user || ! $this->isAdminAccount($user)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email ini tidak terdaftar sebagai admin/HRD.']);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status == Password::PASSWORD_RESET
            ? redirect()->route('loginuser')->with('status', __($status))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
    }

    private function isAdminAccount(User $user): bool
    {
        return $user->roles()->where('name', '!=', 'karyawan')->exists();
    }
}

