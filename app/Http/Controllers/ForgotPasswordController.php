<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use App\Services\SmsService;

class ForgotPasswordController extends Controller
{
    public function __construct(protected SmsService $smsService)
    {
    }
    public function showLinkRequestForm(): View
    {
        return view('LoginPage.forgot-password');
    }

    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $method = $request->input('method', 'email');

        if ($method === 'mobile') {
            $request->validate([
                'mobile' => ['required', 'string'],
                'login_id' => ['required', 'string'],
            ]);

            $mobile = trim($request->input('mobile'));
            $loginId = trim($request->input('login_id'));
            $user = User::where('mobile_number', $mobile)
                ->where('login_id', $loginId)
                ->first();

            if (! $user) {
                return back()->withErrors([
                    'mobile' => 'We could not find an account matching that mobile number and ID.',
                ])->withInput($request->only('mobile', 'login_id', 'method'));
            }

            return $this->sendOtpAndRedirect($mobile, $loginId);
        }

        if ($method === 'both') {
            $request->validate([
                'email' => ['required', 'email'],
                'login_id' => ['required', 'string'],
            ]);

            $email = trim($request->input('email'));
            $loginId = trim($request->input('login_id'));

            $user = User::where('email', $email)
                ->where('login_id', $loginId)
                ->first();

            if (! $user) {
                return back()->withErrors([
                    'email' => 'We could not find an account matching that email address and ID.',
                ])->withInput($request->only('email', 'login_id', 'method'));
            }

            // Always attempt the email link — this doesn't depend on a mobile
            // number being on file, so it should still go out even if the
            // mobile side below can't run.
            Password::sendResetLink(['email' => $email]);

            if (blank($user->mobile_number)) {
                return back()->with(
                    'status',
                    'A password reset link has been sent to your email. This account has no mobile number on file, so no OTP could be sent.'
                )->withInput($request->only('email', 'login_id', 'method'));
            }

            return $this->sendOtpAndRedirect($user->mobile_number, $loginId, true);
        }

        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', 'A password reset link has been sent to that email address.')
            : back()->withErrors(['email' => 'We could not find an account with that email address.']);
    }

    protected function sendOtpAndRedirect(string $mobile, string $loginId, bool $alsoEmailed = false): RedirectResponse
    {
        $otp = random_int(100000, 999999);
        Cache::put('password_otp:' . $mobile . ':' . $loginId, $otp, now()->addMinutes(10));

        $demoMode = config('services.otp.demo_mode', false);

        if (! $demoMode) {
            $message = "Your ISU Library reset code is {$otp}. It expires in 10 minutes.";
            if (! $this->smsService->send($mobile, $message)) {
                return back()->withErrors(['mobile' => 'We were unable to send an OTP to that number. Please try again or use email reset.']);
            }
        }

        $redirect = redirect()->route('password.otp.form', [
            'mobile' => $mobile,
            'login_id' => $loginId,
        ]);

        if ($demoMode) {
            $status = $alsoEmailed
                ? 'A password reset link was emailed to you.'
                : 'An OTP has been sent to your mobile number.';

            return $redirect->with('status', $status)->with('demo_otp', $otp);
        }

        $status = $alsoEmailed
            ? 'A password reset link has been sent to your email, and an OTP has been sent to your mobile number.'
            : 'An OTP has been sent to your mobile number.';

        return $redirect->with('status', $status);
    }

    public function showOtpForm(Request $request): View
    {
        $mobile = $request->query('mobile');
        return view('LoginPage.verify-otp', [
            'mobile' => $mobile,
            'login_id' => $request->query('login_id'),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'mobile' => ['required', 'string'],
            'login_id' => ['required', 'string'],
            'otp' => ['required', 'string'],
        ]);

        $mobile = trim($request->input('mobile'));
        $loginId = trim($request->input('login_id'));
        $otp = trim($request->input('otp'));

        $cached = Cache::get('password_otp:' . $mobile . ':' . $loginId);
        if (! $cached || (string) $cached !== (string) $otp) {
            return back()->withErrors(['otp' => 'The provided OTP is invalid or expired.']);
        }

        $user = User::where('mobile_number', $mobile)
            ->where('login_id', $loginId)
            ->first();

        if (! $user) {
            return back()->withErrors(['mobile' => 'We could not find an account matching that mobile number and ID.']);
        }

        Cache::forget('password_otp:' . $mobile . ':' . $loginId);

        $token = Str::random(64);
        Cache::put('password_phone_token:' . $token, [
            'mobile' => $mobile,
            'login_id' => $loginId,
        ], now()->addMinutes(30));

        return redirect()->route('password.reset', [
            'token' => $token,
            'phone' => $mobile,
            'login_id' => $loginId,
            'phone_token' => $token,
        ]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('LoginPage.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
            'phone' => $request->query('phone'),
            'login_id' => $request->query('login_id'),
            'phone_token' => $request->query('phone_token'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        // Support both email (password broker) and phone (OTP-verified token)
        if ($request->filled('phone') && $request->filled('phone_token')) {
            $request->validate([
                'phone' => ['required', 'string'],
                'login_id' => ['required', 'string'],
                'phone_token' => ['required', 'string'],
                'password' => ['required', 'confirmed', 'min:8'],
            ]);

            $mobile = $request->input('phone');
            $loginId = $request->input('login_id');
            $token = $request->input('phone_token');
            $cached = Cache::get('password_phone_token:' . $token);
            if (! is_array($cached) || ($cached['mobile'] ?? null) !== $mobile || ($cached['login_id'] ?? null) !== $loginId) {
                return back()->withErrors(['phone' => 'This reset token is invalid or has expired.']);
            }

            $user = User::where('mobile_number', $mobile)
                ->where('login_id', $loginId)
                ->first();
            if (! $user) {
                return back()->withErrors(['phone' => 'We could not find an account matching that mobile number and ID.']);
            }

            $user->forceFill(['password' => $request->input('password')])->save();
            Cache::forget('password_phone_token:' . $token);

            return redirect()->route('home')->with('status', 'Your password has been reset. You can now log in.');
        }

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