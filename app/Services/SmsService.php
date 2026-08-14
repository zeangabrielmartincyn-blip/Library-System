<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): bool
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');
        $enabled = config('services.twilio.enabled', false);

        if (! $enabled || blank($sid) || blank($token) || blank($from)) {
            return $this->fallbackSend($to, $message);
        }

        try {
            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->timeout(10)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $to,
                    'From' => $from,
                    'Body' => $message,
                ]);

            return $response->successful();
        } catch (\Throwable $exception) {
            report($exception);
            return false;
        }
    }

    protected function fallbackSend(string $to, string $message): bool
    {
        Log::info("SMS fallback for {$to}: {$message}");

        return app()->environment(['local', 'testing']);
    }
}
