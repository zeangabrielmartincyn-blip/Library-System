<?php

namespace App\Http\Controllers;

use App\Services\ChatBotService;
use App\Services\LibraryRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function __construct(
        protected ChatBotService $chatBot,
        protected LibraryRepository $library
    ) {
        abort_unless(Auth::check(), 403);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
<<<<<<< HEAD
        $role = $user?->role ?? 'guest';
        $userId = $user?->id;

        try {
            // Guests never have personal loans/reservations/fines, so keep their
            // path lightweight and skip straight to catalog/announcement lookups.
            $intent = $this->detectIntent(strtolower($validated['message']));
            $databaseContext = $this->buildDatabaseContext($validated['message'], $role, $userId);
            $directReply = $this->buildDirectReply($intent, $validated['message'], $databaseContext, $role, $userId);

            if ($directReply !== null && trim($directReply) !== '') {
                return response()->json([
                    'reply' => $directReply,
                ]);
            }

            $context = [
                'role' => $role,
                'user_name' => $user?->name ?? 'Guest',
                'stats' => $this->library->stats(),
                'database_context' => $databaseContext,
            ];

            $reply = $this->chatBot->reply($validated['message'], $context);

            if (trim((string) $reply) === '') {
                $reply = 'I could not generate a reply right now. Please try rephrasing your question.';
            }
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'reply' => 'Sorry, something went wrong while answering that. Please try again in a moment.',
            ]);
=======
        $intent = $this->detectIntent(strtolower($validated['message']));
        $databaseContext = $this->buildDatabaseContext($validated['message'], $user?->role ?? 'user', $user?->id);
        $directReply = $this->buildDirectReply($intent, $validated['message'], $databaseContext, $user?->role ?? 'user', $user?->id);

        if ($directReply !== null) {
            return response()->json([
                'reply' => $directReply,
            ]);
        }

        $context = [
            'role' => $user?->role ?? 'user',
            'user_name' => $user?->name ?? 'User',
            'stats' => $this->library->stats(),
            'database_context' => $databaseContext,
        ];

        try {
            $reply = $this->chatBot->reply($validated['message'], $context);
        } catch (\Throwable $e) {
            report($e);
            $reply = 'The chat service is not available right now. Please check the Gemini configuration.';
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }

    protected function buildDirectReply(string $intent, string $message, array $context, string $role, ?int $userId): ?string
    {
        return match ($intent) {
            'availability' => $this->replyAvailability($context),
            'overdue' => $this->replyOverdue($context, $role, $userId),
            'fines' => $this->replyFines($context, $role, $userId),
            'announcement' => $this->replyAnnouncements($context),
            'book_search' => $this->replyBookSearch($context, $message),
            default => null,
        };
    }

    protected function replyAvailability(array $context): ?string
    {
        $availability = $context['availability'] ?? null;
        if (! is_array($availability)) {
            return null;
        }

        if (! empty($availability['book_title'])) {
            return "There are {$availability['available_quantity']} available copies of {$availability['book_title']} out of {$availability['total_quantity']} total copies.";
        }

        return "There are {$availability['books_available']} available books out of {$availability['books_total']} total books in the library.";
    }

    protected function replyOverdue(array $context, string $role, ?int $userId): ?string
    {
        if (($role === 'student' || $role === 'instructor') && $userId && ! empty($context['my_loans'])) {
            $overdue = collect($context['my_loans'])->filter(fn ($loan) => ! empty($loan['due_at']) && empty($loan['returned_at']) && strtotime($loan['due_at']) < strtotime(date('Y-m-d')));

            if ($overdue->isNotEmpty()) {
                $titles = $overdue->pluck('title')->take(5)->implode(', ');

                return "You have overdue items: {$titles}. Please return them as soon as possible.";
            }

            return 'You do not appear to have any overdue items right now.';
        }

        $summary = $context['overdue_summary'] ?? null;
        if (! is_array($summary)) {
            return null;
        }

        return "There are {$summary['overdue_loans']} overdue loans out of {$summary['active_loans']} active loans.";
    }

    protected function replyFines(array $context, string $role, ?int $userId): ?string
    {
        if (($role === 'student' || $role === 'instructor') && $userId && ! empty($context['my_fines'])) {
            $fines = collect($context['my_fines']);
            $unpaid = $fines->where('status', 'Unpaid');
            $total = number_format((float) $fines->sum('amount'), 2);

            if ($fines->isEmpty()) {
                return 'You do not have any fines right now.';
            }

            if ($unpaid->isNotEmpty()) {
                $titles = $unpaid->pluck('title')->take(5)->implode(', ');

                return "You have fines totaling ₱{$total}. Unpaid fines are linked to: {$titles}.";
            }

            return "You have fines totaling ₱{$total}, and none of them are currently unpaid.";
        }

        $summary = $context['overdue_summary'] ?? null;
        if (! is_array($summary)) {
            return null;
        }

        return "There are {$summary['overdue_loans']} overdue loans currently, which may generate fines depending on your fine policy.";
    }

    protected function replyAnnouncements(array $context): ?string
    {
        $announcements = $context['announcements'] ?? null;
        if (! is_iterable($announcements) || count($announcements) === 0) {
            return null;
        }

        $items = collect($announcements)->take(3)->map(function ($item) {
            $title = trim((string) ($item['title'] ?? 'Announcement'));
            $body = trim((string) ($item['body'] ?? ''));
            $publishedAt = trim((string) ($item['published_at'] ?? 'recent'));
            $bodyText = $body !== '' ? " - {$body}" : '';

            return "- {$title} (published {$publishedAt}){$bodyText}";
        })->implode(PHP_EOL);

        return "Here are the latest announcements:".PHP_EOL.$items;
    }

    protected function replyBookSearch(array $context, string $message): ?string
    {
        $books = $context['books'] ?? null;
        $searchTerm = trim((string) ($context['search_term'] ?? ''));

        if (! is_iterable($books) || count($books) === 0) {
            if ($searchTerm !== '') {
                return "I couldn't find any books related to '{$searchTerm}'. Try a broader topic or a different keyword.";
            }

            return 'I could not find any matching books in the catalog.';
        }

        $items = collect($books)->take(3)->map(function ($book) {
            $status = $book['available_quantity'] > 0 ? 'available' : 'unavailable';
            $reason = $book['reason'] ?? 'matched your topic';

            return "- {$book['title']} by {$book['author']} ({$book['genre']}, {$status}) - {$reason}";
        })->implode(PHP_EOL);

        if ($searchTerm !== '') {
            return "I found these related books for '{$searchTerm}':".PHP_EOL.$items;
        }

        return "Here are the closest matching books:".PHP_EOL.$items;
    }

    protected function buildDatabaseContext(string $message, string $role, ?int $userId): array
    {
        $text = strtolower($message);
        $intent = $this->detectIntent($text);

        $context = [
            'intent' => $intent,
            'summary' => [
                'books_total' => $this->library->bookCount(),
                'books_available' => $this->library->availableBookCount(),
                'active_loans' => $this->library->activeLoanCount(),
                'active_reservations' => $this->library->reservedCount(),
                'overdue_loans' => $this->library->overdueCount(),
            ],
        ];

        if (in_array($intent, ['announcement', 'general'], true) && ($this->containsWord($text, 'announcement') || $this->containsWord($text, 'notice') || $this->containsWord($text, 'latest'))) {
            $context['announcements'] = $this->library->announcements(null, null, true)
                ->take(5)
                ->map(fn ($item) => [
                    'title' => $item->title,
                    'body' => $item->body,
                    'audience' => $item->audience,
                    'status' => $item->status,
                    'published_at' => $item->published_at,
                ])
                ->values();
        }

        if (in_array($intent, ['book_search', 'catalog', 'general'], true) && (
            $this->containsAnyWord($text, ['book', 'books']) ||
            $this->containsAnyWord($text, ['catalog', 'catalogue']) ||
            $this->containsWord($text, 'title') ||
            $this->containsWord($text, 'author')
        )) {
            $search = $this->extractSearchTerm($message);
            if ($search === '') {
                $search = trim(preg_replace('/\b(look|find|search|get|show|related|related to|books?|book|catalog|catalogue|about|on|for|of|to)\b/i', '', strtolower($message)) ?? '');
                $search = trim(preg_replace('/\s+/', ' ', $search) ?? '');
            }
            $books = $search !== ''
                ? $this->library->books($search)
                    ->map(fn ($book) => array_merge($book, [
                        'score' => 1,
                        'reason' => 'keyword match',
                    ]))
                : $this->library->books()
                    ->map(fn ($book) => array_merge($book, [
                        'score' => 0,
                        'reason' => 'popular catalog item',
                    ]));

            if ($books->isEmpty() && $search !== '') {
                $books = $this->rankRelatedBooks($search);
            }

            $context['books'] = $books;
            $context['search_term'] = $search;
        }

        if (in_array($intent, ['availability', 'general'], true) && (
            $this->containsWord($text, 'available') ||
            $this->containsWord($text, 'availability') ||
            str_contains($text, 'how many books')
        )) {
            $availabilitySearch = $this->extractSearchTerm($message);
            $matchedBook = null;

            if ($availabilitySearch !== '') {
                $matchedBook = $this->library->books($availabilitySearch)->first();
            }

            if (! $matchedBook && preg_match('/\b(?:in|for|of|about)\s+(.+)$/i', $message, $matches)) {
                $matchedBook = $this->library->books(trim($matches[1]))->first();
            }

            if ($matchedBook) {
                $context['availability'] = [
                    'book_title' => $matchedBook['title'] ?? 'the book',
                    'available_quantity' => (int) ($matchedBook['available_quantity'] ?? 0),
                    'total_quantity' => (int) ($matchedBook['quantity'] ?? 0),
                ];
            } else {
                $context['availability'] = [
                    'books_available' => $this->library->availableBookCount(),
                    'books_total' => $this->library->bookCount(),
                ];
            }
        }

        if (in_array($intent, ['overdue', 'fines', 'general'], true) && (
            $this->containsWord($text, 'overdue') ||
            $this->containsWord($text, 'late') ||
            $this->containsWord($text, 'due') ||
            $this->containsWord($text, 'fine')
        )) {
            $context['overdue_summary'] = [
                'overdue_loans' => $this->library->overdueCount(),
                'active_loans' => $this->library->activeLoanCount(),
                'active_reservations' => $this->library->reservedCount(),
            ];
        }

        $wantsLoanInfo = in_array($intent, ['overdue', 'fines'], true)
            || $this->containsAnyWord($text, ['borrow', 'borrowed', 'loan', 'loans', 'checked out', 'due']);

        $wantsReservationInfo = $this->containsAnyWord($text, ['reserv', 'hold', 'holds']);

        $wantsFineInfo = in_array($intent, ['fines', 'overdue'], true)
            || $this->containsWord($text, 'fine');

        if (($role === 'student' || $role === 'instructor') && $userId) {
            if ($wantsReservationInfo) {
                $context['my_reservations'] = $this->library->reservationsForUser($userId)->take(5)->map(fn ($item) => [
                    'isbn' => $item->isbn,
                    'title' => $item->title,
                    'quantity' => $item->quantity ?? 1,
                    'reserved_at' => $item->reserved_at,
                ])->values();
            }

            if ($wantsLoanInfo) {
                $context['my_loans'] = $this->library->loansForUser($userId)->take(5)->map(fn ($item) => [
                    'isbn' => $item->isbn,
                    'title' => $item->title,
                    'due_at' => $item->due_at,
                    'returned_at' => $item->returned_at,
                ])->values();

                $context['my_active_loans'] = $this->library->loansForUser($userId)
                    ->filter(fn ($item) => empty($item->returned_at))
                    ->take(5)
                    ->map(fn ($item) => [
                        'isbn' => $item->isbn,
                        'title' => $item->title,
                        'due_at' => $item->due_at,
                        'borrowed_at' => $item->borrowed_at,
                    ])->values();
            }

            if ($wantsFineInfo) {
                $context['my_fines'] = $this->library->finesForUser($userId)->take(5)->map(fn ($item) => [
                    'isbn' => $item->isbn,
                    'title' => $item->title,
                    'amount' => $item->amount,
                    'status' => $item->status,
                    'due_at' => $item->due_at,
                ])->values();
            }
        }

<<<<<<< HEAD
        if ($role === 'librarian') {
=======
        if ($role === 'admin' || $role === 'librarian') {
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
            if ($wantsLoanInfo) {
                $context['recent_loans'] = $this->library->activeLoans()->take(5)->map(fn ($item) => [
                    'isbn' => $item->isbn,
                    'title' => $item->title,
                    'borrower' => $item->borrower_name ?? $item->borrower_id ?? null,
                    'due_at' => $item->due_at,
                    'status' => $item->status ?? null,
                ])->values();
            }

            if ($wantsReservationInfo) {
                $context['recent_reservations'] = $this->library->activeReservations()->take(5)->map(fn ($item) => [
                    'isbn' => $item->isbn,
                    'title' => $item->title,
                    'borrower' => $item->borrower_name ?? $item->borrower_id ?? null,
                    'reserved_at' => $item->reserved_at,
                ])->values();
            }
        }

        return $context;
    }

    protected function detectIntent(string $text): string
    {
        if ($this->containsWord($text, 'announce') || $this->containsWord($text, 'notice')) {
            return 'announcement';
        }

        if ($this->containsWord($text, 'overdue') || $this->containsWord($text, 'late') || $this->containsWord($text, 'due')) {
            return 'overdue';
        }

        if ($this->containsWord($text, 'fine')) {
            return 'fines';
        }

        if ($this->containsWord($text, 'available') || $this->containsWord($text, 'availability')) {
            return 'availability';
        }

        if ($this->containsAnyWord($text, ['catalog', 'catalogue']) || $this->containsAnyWord($text, ['book', 'books']) || $this->containsWord($text, 'title') || $this->containsWord($text, 'author')) {
            return 'book_search';
        }

        return 'general';
    }

    protected function containsWord(string $text, string $word): bool
    {
        return (bool) preg_match('/\b'.preg_quote($word, '/').'\b/i', $text);
    }

    protected function containsAnyWord(string $text, array $words): bool
    {
        foreach ($words as $word) {
            if ($this->containsWord($text, $word)) {
                return true;
            }
        }

        return false;
    }

    protected function extractSearchTerm(string $message): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $message) ?? '');

        if (preg_match('/(?:related to|about|on|for|of|regarding)\s+(.+)$/i', $clean, $matches)) {
            $clean = $matches[1];
        }

        $clean = preg_replace('/^(show|find|search|get|look for|look up|find me|tell me about|what about|any|do you have|is there|list)\b/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\b(book|books|catalog|catalogue|title|author|related|similar|topic|topics)\b/i', '', $clean) ?? $clean;

<<<<<<< HEAD
        // Strip ordinary question/stop words. Without this, a generic question like
        // "how many books are available" leaves behind "how many are available",
        // which then fuzzy-matches any book whose description happens to contain
        // a common word like "available" -- returning a random book instead of
        // correctly falling through to a catalog-wide summary.
        $stopwords = [
            'how', 'many', 'much', 'are', 'is', 'am', 'was', 'were', 'be', 'been', 'being',
            'there', 'here', 'do', 'does', 'did', 'can', 'could', 'would', 'should', 'will',
            'what', 'which', 'who', 'whom', 'when', 'where', 'why', 'a', 'an', 'the',
            'in', 'on', 'at', 'to', 'from', 'with', 'and', 'or', 'but', 'if', 'so',
            'available', 'availability', 'currently', 'right', 'now', 'today', 'please',
            'me', 'my', 'you', 'your', 'i', 'we', 'us', 'our', 'it', 'its', 'this', 'that',
            'these', 'those', 'have', 'has', 'had', 'left', 'left over', 'remaining', 'copies', 'copy',
        ];
        $stopwordPattern = '/\b('.implode('|', array_map('preg_quote', $stopwords)).')\b/i';
        $clean = preg_replace($stopwordPattern, '', $clean) ?? $clean;
        $clean = trim(preg_replace('/\s+/', ' ', $clean) ?? '');

        // If nothing meaningful survives (e.g. every word was a stopword, or only
        // very short fragments remain), treat it as no specific book named at all.
        $meaningfulWords = array_filter(explode(' ', $clean), fn ($word) => strlen($word) > 2);
        if (empty($meaningfulWords)) {
            return '';
        }

        return $clean;
=======
        return trim(preg_replace('/\s+/', ' ', $clean) ?? '');
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
    }

    protected function rankRelatedBooks(string $search): \Illuminate\Support\Collection
    {
        $books = $this->library->books();

        if (trim($search) === '') {
            return $books->map(function ($book) {
                $book['reason'] = 'popular catalog item';

                return $book;
            })->take(8)->values();
        }

        $needle = strtolower(trim($search));
        $tokens = array_values(array_filter(array_unique(preg_split('/\s+/', $needle) ?: []), fn ($token) => strlen($token) > 2));

        return $books->map(function ($book) use ($needle, $tokens) {
            $score = 0;
            $reasons = [];
            $fields = [
                strtolower((string) ($book['title'] ?? '')),
                strtolower((string) ($book['author'] ?? '')),
                strtolower((string) ($book['genre'] ?? '')),
                strtolower((string) ($book['description'] ?? '')),
            ];
            $haystack = implode(' ', $fields);

            if (str_contains($fields[0], $needle)) {
                $score += 6;
                $reasons[] = 'title match';
            }

            if (str_contains($fields[2], $needle)) {
                $score += 5;
                $reasons[] = 'genre match';
            }

            if (str_contains($fields[3], $needle)) {
                $score += 4;
                $reasons[] = 'description match';
            }

            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    $score += 2;
                    $reasons[] = $token;
                }
            }

            $book['score'] = $score;
            $book['reason'] = $reasons !== [] ? 'matched '.implode(', ', array_slice(array_unique($reasons), 0, 3)) : 'related by catalog text';

            return $book;
        })
            ->sortByDesc('score')
            ->take(8)
            ->values();
    }
}
