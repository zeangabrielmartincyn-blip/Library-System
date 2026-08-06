<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class BookScanController extends Controller
{
    public function showScanner(string $token): View
    {
        $payload = Cache::get($this->bookScanCacheKey($token));

        abort_unless(is_array($payload), 404, 'Scan session expired.');

        return view('scan.book-scan', [
            'token' => $token,
        ]);
    }

    public function submitBookScan(Request $request, string $token): JsonResponse
    {
        $validated = $request->validate([
            'isbn' => ['required', 'string', 'max:255'],
        ]);

        $cacheKey = $this->bookScanCacheKey($token);
        $payload = Cache::get($cacheKey);

        if (! is_array($payload)) {
            return response()->json(['message' => 'Scan session expired.'], 404);
        }

        $isbn = preg_replace('/[^0-9Xx]/', '', $validated['isbn']) ?? '';
        if ($isbn === '') {
            return response()->json(['message' => 'Invalid ISBN.'], 422);
        }

        $payload['isbn'] = $isbn;
        $payload['status'] = 'scanned';
        $payload['scanned_at'] = now()->toIso8601String();
        Cache::put($cacheKey, $payload, now()->addMinutes(10));

        return response()->json([
            'message' => 'ISBN received.',
            'isbn' => $isbn,
        ]);
    }

    public function bookScanStatus(string $token): JsonResponse
    {
        $payload = Cache::get($this->bookScanCacheKey($token));

        if (! is_array($payload)) {
            return response()->json([
                'status' => 'expired',
                'isbn' => null,
            ], 404);
        }

        return response()->json([
            'status' => $payload['status'] ?? 'waiting',
            'isbn' => $payload['isbn'] ?? null,
        ]);
    }

    protected function bookScanCacheKey(string $token): string
    {
        return 'book-scan:'.$token;
    }
}
