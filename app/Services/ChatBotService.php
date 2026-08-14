<?php

namespace App\Services;

<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Http;
use RuntimeException;

>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
class ChatBotService
{
    public function reply(string $message, array $context = []): string
    {
<<<<<<< HEAD
        return $this->fallbackReply($message, $context, 'Using the library database context only.');
=======
        $apiKey = (string) config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
        $models = array_values(array_filter(array_unique(array_merge(
            [(string) config('services.gemini.model', env('GEMINI_MODEL', 'gemini-3.5-flash'))],
            (array) config('services.gemini.fallback_models', [])
        ))));

        if ($apiKey === '') {
            return $this->fallbackReply($message, $context, 'Gemini is not configured yet. Set GEMINI_API_KEY in your .env file.');
        }

        $systemPrompt = $this->systemPrompt($context);
        $databaseContext = $this->databaseContextPrompt($context['database_context'] ?? []);

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $systemPrompt."\n\n".$databaseContext."\n\nUser message: ".$message,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => (int) config('services.gemini.max_output_tokens', env('GEMINI_MAX_OUTPUT_TOKENS', 220)),
                'temperature' => (float) config('services.gemini.temperature', env('GEMINI_TEMPERATURE', 0.3)),
            ],
        ];

        $response = null;
        $lastError = null;

        foreach ($models as $model) {
            $endpoint = sprintf(
                'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
                rawurlencode($model)
            );

            $response = Http::timeout(30)
                ->acceptJson()
                ->when(! (bool) config('services.gemini.verify_ssl', false), fn ($request) => $request->withoutVerifying())
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post($endpoint, $payload);

            if ($response->successful()) {
                break;
            }

            $lastError = $response->body();

            $isRetryable = str_contains($lastError, 'UNAVAILABLE')
                || str_contains($lastError, 'high demand')
                || str_contains($lastError, 'RESOURCE_EXHAUSTED')
                || str_contains($lastError, 'Quota exceeded')
                || str_contains($lastError, 'quota');

            if (! $isRetryable) {
                break;
            }
        }

        if (! $response || ! $response->successful()) {
            if ($lastError !== null && (str_contains($lastError, 'RESOURCE_EXHAUSTED') || str_contains($lastError, 'Quota exceeded') || str_contains($lastError, 'quota'))) {
                return $this->fallbackReply($message, $context, 'The AI service is temporarily busy because the Gemini quota was reached.');
            }

            return $this->fallbackReply($message, $context, 'The AI service is temporarily unavailable right now.');
        }

        $data = $response->json();

        return data_get($data, 'candidates.0.content.parts.0.text')
            ?? data_get($data, 'text')
            ?? 'I could not generate a reply right now.';
    }

    protected function systemPrompt(array $context): string
    {
        $role = $context['role'] ?? 'user';
        $userName = $context['user_name'] ?? 'User';
        $roleInstructions = match ($role) {
            'admin' => 'Focus on system administration, users, reports, charts, announcements, fines, and audit/activity tasks.',
            'librarian' => 'Focus on catalog management, issuing books, reservations, student/instructor records, announcements, and circulation tasks.',
            'student' => 'Focus on borrowing, reservations, catalog browsing, fines, notifications, reviews, and profile actions.',
            'instructor' => 'Focus on borrowing, reservations, catalog browsing, fines, notifications, reviews, and profile actions.',
            'guest' => 'Focus on public catalog browsing, public announcements, and how to get started.',
            default => 'Focus on general library help and keep answers practical.',
        };

        return trim(<<<PROMPT
You are a helpful assistant for the ISU Library System.
Answer directly in 1-3 short sentences. No greetings, no preamble, no restating the question, no "I'd be happy to help" filler. Lead with the answer itself.
Only use a short bullet list (max 5 items) when the user asked for multiple items (e.g. a list of books or announcements); otherwise use plain sentences.
Answer any question that is related to the library system, even if it is broad or phrased indirectly, but stay within that scope — do not answer unrelated general-knowledge questions.
Use the database context whenever it helps answer questions about books, catalog items, borrowing, reservations, fines, announcements, users, and dashboard actions.
If the database context includes a direct answer, use it first and do not add unrequested extra information.
If the database context is missing something, answer with the best available system knowledge instead of refusing.
If you are unsure about a system-specific detail, say so in one short sentence instead of inventing it or padding with caveats.
For questions about borrowed books or current loans, use active loan data only. Do not count returned loan history as currently borrowed.
Always use the Philippine peso symbol (₱) for any money amount such as fines or fees. Never use the dollar sign ($) or the word USD.
When reporting fines, only report UNPAID fines as outstanding. Do not include Paid or Waived fines in the outstanding total. If all fines are paid or waived, clearly say the user has no outstanding fines.
Current user: {$userName}.
Current user role: {$role}.
Role guidance: {$roleInstructions}
PROMPT);
    }

    protected function databaseContextPrompt(array $context): string
    {
        if ($context === []) {
            return 'Database context: none provided.';
        }

        return 'Database context: '.json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
    }

    protected function fallbackReply(string $message, array $context, string $reason): string
    {
        $text = strtolower(trim($message));
        $databaseContext = $context['database_context'] ?? [];
        $summary = $databaseContext['summary'] ?? [];

        if (str_contains($text, 'announcement') || str_contains($text, 'notice') || str_contains($text, 'latest')) {
            $announcements = $databaseContext['announcements'] ?? [];
            if (is_iterable($announcements) && count($announcements) > 0) {
                $items = collect($announcements)->take(3)->map(function ($item) {
                    $title = trim((string) ($item['title'] ?? 'Announcement'));
                    $body = trim((string) ($item['body'] ?? ''));
                    $publishedAt = trim((string) ($item['published_at'] ?? 'recent'));
                    $bodyText = $body !== '' ? " - {$body}" : '';

                    return "- {$title} (published {$publishedAt}){$bodyText}";
                })->implode(PHP_EOL);

                return "Here are the latest announcements:".PHP_EOL.$items;
            }
        }

        if (str_contains($text, 'fine')) {
            $myFines = $databaseContext['my_fines'] ?? [];
            if (is_iterable($myFines) && count($myFines) > 0) {
                $fines = collect($myFines);
                $unpaid = $fines->where('status', 'Unpaid');

                if ($unpaid->isNotEmpty()) {
                    $unpaidTotal = number_format((float) $unpaid->sum('amount'), 2);
                    $titles = $unpaid->pluck('title')->take(5)->implode(', ');

                    return "You have unpaid fines totaling ₱{$unpaidTotal}, linked to: {$titles}.";
                }

                return "You have no outstanding fines. All your previous fines have been settled.";
            }

            return "You have no fines on record.";
        }

        if (str_contains($text, 'borrow') || str_contains($text, 'loan') || str_contains($text, 'borrowed')) {
            $activeLoans = $databaseContext['my_active_loans'] ?? [];
            if (is_iterable($activeLoans) && count($activeLoans) > 0) {
                $titles = collect($activeLoans)->pluck('title')->take(5)->implode(', ');

                return "You currently have these borrowed books: {$titles}.";
            }

            return 'You do not currently have any borrowed books.';
        }

        if (str_contains($text, 'reserv') || str_contains($text, 'hold')) {
            $reservations = $databaseContext['my_reservations'] ?? [];
            if (is_iterable($reservations) && count($reservations) > 0) {
                $titles = collect($reservations)->pluck('title')->take(5)->implode(', ');

                return "You currently have these reservations: {$titles}.";
            }

            return 'You do not currently have any reservations.';
        }

        if (str_contains($text, 'profile') || str_contains($text, 'account') || str_contains($text, 'dashboard')) {
            $role = $context['role'] ?? 'user';
            $name = $context['user_name'] ?? 'User';
            $booksTotal = $summary['books_total'] ?? null;
            $booksAvailable = $summary['books_available'] ?? null;

            $parts = ["You are signed in as {$name} ({$role})."];
            if ($booksAvailable !== null && $booksTotal !== null) {
                $parts[] = "The library currently has {$booksAvailable} available books out of {$booksTotal} total.";
            }

            return implode(' ', $parts);
        }

<<<<<<< HEAD
        if (($context['role'] ?? 'user') === 'librarian') {
=======
        if (in_array(($context['role'] ?? 'user'), ['admin', 'librarian'], true)) {
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
            if (str_contains($text, 'loan') || str_contains($text, 'borrow')) {
                $recentLoans = $databaseContext['recent_loans'] ?? [];
                if (is_iterable($recentLoans) && count($recentLoans) > 0) {
                    $items = collect($recentLoans)->take(3)->map(function ($item) {
                        $title = trim((string) ($item['title'] ?? 'Unknown title'));
                        $borrower = trim((string) ($item['borrower'] ?? 'Unknown borrower'));
                        $dueAt = trim((string) ($item['due_at'] ?? 'unknown due date'));

                        return "- {$title} for {$borrower} (due {$dueAt})";
                    })->implode(PHP_EOL);

                    return "Here are the recent active loans:".PHP_EOL.$items;
                }
            }

            if (str_contains($text, 'reservation') || str_contains($text, 'hold')) {
                $recentReservations = $databaseContext['recent_reservations'] ?? [];
                if (is_iterable($recentReservations) && count($recentReservations) > 0) {
                    $items = collect($recentReservations)->take(3)->map(function ($item) {
                        $title = trim((string) ($item['title'] ?? 'Unknown title'));
                        $borrower = trim((string) ($item['borrower'] ?? 'Unknown borrower'));
                        $reservedAt = trim((string) ($item['reserved_at'] ?? 'unknown date'));

                        return "- {$title} reserved by {$borrower} ({$reservedAt})";
                    })->implode(PHP_EOL);

                    return "Here are the recent active reservations:".PHP_EOL.$items;
                }
            }
        }

        if (str_contains($text, 'available') && isset($summary['books_available'], $summary['books_total'])) {
            return "There are {$summary['books_available']} available books out of {$summary['books_total']} total books in the library.";
        }

        if (str_contains($text, 'book') || str_contains($text, 'catalog') || str_contains($text, 'author') || str_contains($text, 'title')) {
            $books = $databaseContext['books'] ?? [];
            if (is_iterable($books) && count($books) > 0) {
                $items = collect($books)->take(3)->map(function ($book) {
                    $status = ((int) ($book['available_quantity'] ?? 0) > 0) ? 'available' : 'unavailable';
                    $reason = $book['reason'] ?? 'matched your topic';
                    $title = trim((string) ($book['title'] ?? 'Untitled'));
                    $author = trim((string) ($book['author'] ?? 'Unknown author'));
                    $genre = trim((string) ($book['genre'] ?? 'Unknown genre'));

                    return "- {$title} by {$author} ({$genre}, {$status}) - {$reason}";
                })->implode(PHP_EOL);

                $searchTerm = trim((string) ($databaseContext['search_term'] ?? ''));
                $header = $searchTerm !== '' ? "Here are related books for '{$searchTerm}':" : 'Here are the closest matching books:';

                return $header.PHP_EOL.$items;
            }
        }

        if (str_contains($text, 'announcement') || str_contains($text, 'notice')) {
            return 'I could not load the latest announcements at the moment, but the announcement section in the dashboard should still show them.';
        }

        return "{$reason} I can still help with library-related questions about books, reservations, loans, fines, and announcements.";
    }
}