<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements ValidationRule
{
    /**
     * Verify the submitted reCAPTCHA token against Google's siteverify API.
     *
     * Skipped automatically during automated tests (there's no browser to
     * solve a real challenge), so this never has to be mocked or configured
     * just to run the test suite.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $secret = config('services.recaptcha.secret_key');
        $siteKey = config('services.recaptcha.site_key');

        if (app()->environment('local') && blank($secret) && blank($siteKey)) {
            // Local development should stay runnable without external Google
            // captcha credentials. Production still requires real keys.
            return;
        }

        if (blank($secret)) {
            $fail('reCAPTCHA is not configured on the server. Set RECAPTCHA_SITE_KEY and RECAPTCHA_SECRET_KEY in .env.');

            return;
        }

        if (blank($value)) {
            $fail('Please complete the reCAPTCHA challenge.');

            return;
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                    'remoteip' => request()->ip(),
                ]);

            if (! $response->json('success')) {
                $fail('reCAPTCHA verification failed. Please try again.');
            }
        } catch (\Throwable $e) {
            report($e);
            $fail('Could not verify reCAPTCHA right now. Please try again.');
        }
    }
}
