<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    /**
     * Show the "enter your email" form.
     */
    public function showLinkRequestForm(): View
    {
        return view('LoginPage.forgot-password');
    }

    /**
     * Send a reset link to the given email, if an account exists for it.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'A password reset link has been sent to that email address.')
            : back()->withErrors(['email' => 'We could not find an account with that email address.']);
    }

    /**
     * Show the "set a new password" form.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('LoginPage.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Handle the actual password reset.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('home')->with('status', 'Your password has been reset. You can now log in.')
            : back()->withErrors(['email' => 'This password reset link is invalid or has expired.']);
    }
}
