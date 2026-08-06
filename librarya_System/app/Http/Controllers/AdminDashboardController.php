<?php

namespace App\Http\Controllers;

use App\Services\LibraryRepository;
use App\Services\ChatBotService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __construct(protected LibraryRepository $library, protected ChatBotService $chatBot)
    {
        abort_unless(Auth::check() && Auth::user()?->role === 'admin', 403);
    }


    public function dashboard(Request $request): View
    {
        return view('Dashboard.admindashboard', $this->buildData($request, 'dashboard'));
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $context = [
            'role' => Auth::user()?->role ?? 'admin',
            'user_name' => Auth::user()?->name ?? 'Admin',
            'stats' => $this->library->stats(),
        ];

        try {
            $reply = $this->chatBot->reply($validated['message'], $context);
        } catch (\Throwable $e) {
            report($e);
            $reply = 'The chat service is not available right now. Please check the chatbot configuration.';
        }

        return response()->json([
            'reply' => $reply,
        ]);
    }

    public function users(Request $request): View
    {
        return view('Dashboard.admindashboard', $this->buildData($request, 'users'));
    }

    public function books(Request $request): View
    {
        return view('Dashboard.admindashboard', $this->buildData($request, 'books'));
    }

    public function isbnMetadata(string $isbn): JsonResponse
    {
        $normalized = $this->normalizeIsbn($isbn);

        if ($normalized === '') {
            return response()->json(['message' => 'Invalid ISBN.'], 422);
        }

        $metadata = [
            'isbn' => $normalized,
            'title' => '',
            'author' => '',
            'genre' => '',
            'publisher' => '',
            'year_published' => '',
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(5)
                ->retry(2, 250)
                ->get('https://www.googleapis.com/books/v1/volumes', [
                    'q' => 'isbn:'.$normalized,
                    'maxResults' => 1,
                ]);

            if ($response->failed()) {
                return response()->json($metadata, 200);
            }

            $item = $response->json('items.0.volumeInfo');

            if (! is_array($item)) {
                return response()->json($metadata, 200);
            }

            $metadata['title'] = (string) ($item['title'] ?? '');
            $metadata['author'] = collect($item['authors'] ?? [])
                ->filter()
                ->join(', ');
            $metadata['genre'] = (string) collect($item['categories'] ?? [])
                ->filter()
                ->first();
            $metadata['publisher'] = (string) ($item['publisher'] ?? '');

            if (! empty($item['publishedDate']) && preg_match('/\b(19|20)\d{2}\b/', (string) $item['publishedDate'], $matches)) {
                $metadata['year_published'] = (int) $matches[0];
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json($metadata);
    }

    protected function normalizeIsbn(string $isbn): string
    {
        $normalized = preg_replace('/[^0-9Xx]/', '', $isbn) ?? '';

        return strtoupper($normalized);
    }

    public function reports(Request $request): View
    {
        return view('Dashboard.admindashboard', $this->buildData($request, 'reports'));
    }

    public function activity(Request $request): View
    {
        return view('Dashboard.admindashboard', $this->buildData($request, 'activity'));
    }

    public function loginHistory(Request $request): View
    {
        return view('Dashboard.admindashboard', $this->buildData($request, 'login-history'));
    }

    public function announcements(Request $request): View
    {
        return view('Dashboard.admindashboard', $this->buildData($request, 'announcements'));
    }


    public function charts(Request $request): View
    {
        $data = $this->buildData($request, 'charts');
        $selectedMonth = $request->query('month', now()->format('Y-m'));
        $data['chartMonth'] = $selectedMonth;
        $data['chartMonthLabel'] = Carbon::parse($selectedMonth.'-01')->format('F Y');
        $data['chartMonths'] = collect(range(0, 11))->map(function (int $offset) {
            $month = now()->startOfMonth()->subMonths($offset);

            return [
                'value' => $month->format('Y-m'),
                'label' => $month->format('F Y'),
            ];
        })->values();
        $data['genreStats'] = DB::table('books')
            ->selectRaw('genre, COUNT(*) as count')
            ->groupBy('genre')
            ->orderByDesc('count')
            ->get();
        $data['topBorrowedBooks'] = $this->library->mostBorrowedBooksForMonth($selectedMonth, 6);
        $data['borrowedGenreStats'] = $this->library->borrowedGenresForMonth($selectedMonth);
        $data['borrowTrend'] = $this->library->borrowTrend(6);
        return view('Dashboard.admindashboard', $data);
    }


    public function fines(Request $request): View
    {
        $data = $this->buildData($request, 'fines');
        $data['fines'] = $this->library->allFines($request->query('status'));
        $data['fineStatusFilter'] = $request->query('status', '');
        return view('Dashboard.admindashboard', $data);
    }

    public function recalculateFines(): RedirectResponse
    {
        $count = $this->library->calculateAndStoreFines();
        $this->library->logActivity(Auth::id(), 'recalculate_fines', null, null, ['new_fines' => $count]);
        return redirect()->route('admin.fines')->with('status', "Fines recalculated. {$count} new fine(s) created.");
    }

    public function payFine(int $id): RedirectResponse
    {
        $this->library->markFinePaid($id, Auth::id());
        $this->library->logActivity(Auth::id(), 'pay_fine', 'fine', $id);
        return back()->with('status', 'Fine marked as paid.');
    }

    public function waiveFine(int $id): RedirectResponse
    {
        $this->library->waiveFine($id, Auth::id());
        $this->library->logActivity(Auth::id(), 'waive_fine', 'fine', $id);
        return back()->with('status', 'Fine waived.');
    }

    public function exportFines(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fines = $this->library->allFines();
        return response()->streamDownload(function () use ($fines) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Borrower', 'Borrower ID', 'Book', 'ISBN', 'Overdue Days', 'Amount', 'Due Date', 'Status']);
            foreach ($fines as $fine) {
                fputcsv($out, [
                    $fine->borrower_name, $fine->borrower_id,
                    $fine->title, $fine->isbn,
                    $fine->overdue_days, $fine->amount,
                    $fine->due_at, $fine->status,
                ]);
            }
            fclose($out);
        }, 'fines.csv', ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="fines.csv"']);
    }


    public function reviews(Request $request): View
    {
        $data = $this->buildData($request, 'reviews');
        $data['topRated'] = $this->library->topRatedBooks(10);
        $query = DB::table('book_reviews')->join('users', 'users.id', '=', 'book_reviews.user_id');

        if (Schema::hasColumn('book_reviews', 'book_id')) {
            $query->join('books', 'books.id', '=', 'book_reviews.book_id');
        } else {
            $query->join('books', 'books.isbn', '=', 'book_reviews.book_isbn');
        }

        $data['allReviews'] = $query->orderByDesc('book_reviews.created_at')->get([
            'book_reviews.id',
            'book_reviews.user_id',
            Schema::hasColumn('book_reviews', 'book_id') ? 'book_reviews.book_id' : DB::raw('NULL as book_id'),
            'book_reviews.book_isbn',
            'book_reviews.rating',
            Schema::hasColumn('book_reviews', 'review_text') ? 'book_reviews.review_text as review' : 'book_reviews.review',
            'book_reviews.created_at',
            'book_reviews.updated_at',
            'users.name as reviewer_name',
            'books.title',
            'books.isbn',
        ]);
        return view('Dashboard.admindashboard', $data);
    }

    public function exportReviews(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = DB::table('book_reviews')->join('users', 'users.id', '=', 'book_reviews.user_id');

        if (Schema::hasColumn('book_reviews', 'book_id')) {
            $query->join('books', 'books.id', '=', 'book_reviews.book_id');
        } else {
            $query->join('books', 'books.isbn', '=', 'book_reviews.book_isbn');
        }

        $reviews = $query->get([
            'book_reviews.id',
            'book_reviews.rating',
            Schema::hasColumn('book_reviews', 'review_text') ? 'book_reviews.review_text as review' : 'book_reviews.review',
            'book_reviews.created_at',
            'users.name as reviewer_name',
            'books.title',
        ]);
        return response()->streamDownload(function () use ($reviews) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reviewer', 'Book', 'Rating', 'Review', 'Date']);
            foreach ($reviews as $r) {
                fputcsv($out, [$r->reviewer_name, $r->title, $r->rating, $r->review, $r->created_at]);
            }
            fclose($out);
        }, 'reviews.csv', ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="reviews.csv"']);
    }


    public function notifications(Request $request): View
    {
        $data = $this->buildData($request, 'notifications');
        $data['adminNotifications'] = $this->library->notificationsForUser(Auth::id(), 50);
        return view('Dashboard.admindashboard', $data);
    }

    public function markNotificationsRead(): RedirectResponse
    {
        $this->library->markNotificationsRead(Auth::id());

        return back()->with('status', 'Notifications marked as read.');
    }

    public function markNotificationRead(int $notificationId): RedirectResponse
    {
        $updated = $this->library->markNotificationRead(Auth::id(), $notificationId);

        return back()->with($updated ? 'status' : 'error', $updated ? 'Notification marked as read.' : 'Notification not found.');
    }


    public function exportReport(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $type = $request->query('type', 'loans');

        if ($type === 'charts') {
            $month = $request->query('month', now()->format('Y-m'));
            $topBooks = $this->library->mostBorrowedBooksForMonth($month, 10);
            $genreStats = $this->library->borrowedGenresForMonth($month);
            $trend = $this->library->borrowTrend(6);

            return response()->streamDownload(function () use ($month, $topBooks, $genreStats, $trend) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['Chart Export', $month]);
                fputcsv($out, []);
                fputcsv($out, ['Top Borrowed Books']);
                fputcsv($out, ['Rank', 'Book', 'Author', 'Genre', 'Borrow Count']);
                foreach ($topBooks as $index => $book) {
                    fputcsv($out, [
                        $index + 1,
                        $book->title,
                        $book->author,
                        $book->genre,
                        $book->borrow_count,
                    ]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Borrowed Genres']);
                fputcsv($out, ['Genre', 'Borrow Count']);
                foreach ($genreStats as $genre) {
                    fputcsv($out, [$genre->genre, $genre->borrow_count]);
                }
                fputcsv($out, []);
                fputcsv($out, ['Borrow Trend']);
                fputcsv($out, ['Month', 'Borrow Count']);
                foreach ($trend as $row) {
                    fputcsv($out, [$row->month, $row->borrow_count]);
                }
                fclose($out);
            }, "charts-{$month}.csv", ['Content-Type' => 'text/csv']);
        }

        $rows = match($type) {
            'reservations' => $this->library->activeReservations(),
            'history'      => $this->library->loanHistory(),
            default        => $this->library->activeLoans(),
        };
        return response()->streamDownload(function () use ($rows, $type) {
            $out = fopen('php://output', 'w');
            if ($type === 'reservations') {
                fputcsv($out, ['Borrower', 'Book', 'ISBN', 'Borrower ID', 'Reserved At']);
                foreach ($rows as $r) {
                    fputcsv($out, [$r->borrower_name, $r->title, $r->isbn, $r->borrower_id, $r->reserved_at]);
                }
            } else {
                fputcsv($out, ['Borrower', 'Book', 'ISBN', 'Borrowed', 'Due', 'Returned', 'Status']);
                foreach ($rows as $r) {
                    fputcsv($out, [$r->borrower_name, $r->title, $r->isbn, $r->borrowed_at, $r->due_at, $r->returned_at ?? '', $r->status]);
                }
            }
            fclose($out);
        }, "{$type}.csv", ['Content-Type' => 'text/csv']);
    }


    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:users,login_id'],
            'role'     => ['required', 'in:librarian,instructor,student'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $userId = $this->library->createUser([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'login_id' => $validated['login_id'] ?: null,
            'role'     => $validated['role'],
            'status'   => 'active',
            'password' => Hash::make($validated['password']),
        ]);

        $this->library->logActivity(Auth::id(), 'create_user', 'user', $userId, [
            'role'     => $validated['role'],
            'login_id' => $validated['login_id'] ?? null,
        ]);

        return redirect()->route('admin.users')->with('status', 'User created successfully.');
    }

    public function toggleUserStatus(int $userId): RedirectResponse
    {
        $this->library->toggleUserStatus($userId);
        $this->library->logActivity(Auth::id(), 'toggle_user_status', 'user', $userId);

        return back()->with('status', 'User status updated.');
    }


    public function storeBook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'isbn'               => ['required', 'string', 'max:255'],
            'title'              => ['required', 'string', 'max:255'],
            'author'             => ['required', 'string', 'max:255'],
            'genre'              => ['required', 'string', 'max:255'],
            'year_published'     => ['nullable', 'integer', 'min:1000', 'max:9999'],
            'quantity'           => ['required', 'integer', 'min:0'],
            'available_quantity' => ['nullable', 'integer', 'min:0'],
            'location'           => ['nullable', 'string', 'max:255'],
            'description'        => ['nullable', 'string'],
        ]);

        $validated['available_quantity'] = $validated['available_quantity'] ?? $validated['quantity'];
        $validated['available_quantity'] = min((int) $validated['available_quantity'], (int) $validated['quantity']);
        $validated['status'] = (int) $validated['available_quantity'] > 0 ? 'Available' : 'Unavailable';

        $this->library->createOrUpdateBook($validated, Auth::id());
        $this->library->logActivity(Auth::id(), 'create_book', 'book', null, [
            'isbn'  => $validated['isbn'],
            'title' => $validated['title'],
        ]);

        return redirect()->route('admin.books')->with('status', 'Book added successfully.');
    }

    public function issueBook(Request $request, string $isbn): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $result = $this->library->issueBookByIsbn($isbn, (int) $validated['user_id'], Auth::id());

        return back()->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function updateBook(Request $request, string $isbn): RedirectResponse
    {
        $validated = $request->validate([
            'title'              => ['required', 'string', 'max:255'],
            'author'             => ['required', 'string', 'max:255'],
            'genre'              => ['required', 'string', 'max:255'],
            'year_published'     => ['nullable', 'integer', 'min:1000', 'max:9999'],
            'quantity'           => ['required', 'integer', 'min:0'],
            'available_quantity' => ['nullable', 'integer', 'min:0'],
            'location'           => ['nullable', 'string', 'max:255'],
            'status'             => ['required', 'in:Available,Unavailable,Archived'],
            'description'        => ['nullable', 'string'],
        ]);

        $validated['available_quantity'] = $validated['available_quantity'] ?? $validated['quantity'];
        $validated['available_quantity'] = min((int) $validated['available_quantity'], (int) $validated['quantity']);

        $this->library->updateBook($isbn, $validated, Auth::id());
        $this->library->logActivity(Auth::id(), 'update_book', 'book', null, [
            'isbn'  => $isbn,
            'title' => $validated['title'],
        ]);

        return redirect()->route('admin.books')->with('status', 'Book updated successfully.');
    }

    public function deleteBook(string $isbn): RedirectResponse
    {
        $this->library->deleteBook($isbn);
        $this->library->logActivity(Auth::id(), 'delete_book', 'book', null, [
            'isbn' => $isbn,
        ]);

        return redirect()->route('admin.books')->with('status', 'Book deleted successfully.');
    }


    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'audience'     => ['required', 'in:public,all,admin,librarian,instructor,student,guest'],
            'published_at' => ['nullable', 'date'],
            'status'       => ['required', 'in:Draft,Published,Archived'],
        ]);

        $announcementId = $this->library->createAnnouncement($validated, Auth::id());
        $this->library->logActivity(Auth::id(), 'create_announcement', 'announcement', $announcementId, [
            'title'    => $validated['title'],
            'audience' => $validated['audience'],
        ]);

        return redirect()->route('admin.announcements')->with('status', 'Announcement published.');
    }

    public function updateAnnouncement(Request $request, int $announcementId): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'audience'     => ['required', 'in:public,all,admin,librarian,instructor,student,guest'],
            'published_at' => ['nullable', 'date'],
            'status'       => ['required', 'in:Draft,Published,Archived'],
        ]);

        $this->library->updateAnnouncement($announcementId, $validated);
        $this->library->logActivity(Auth::id(), 'update_announcement', 'announcement', $announcementId, [
            'title'    => $validated['title'],
            'audience' => $validated['audience'],
        ]);

        return redirect()->route('admin.announcements')->with('status', 'Announcement updated.');
    }

    public function deleteAnnouncement(int $announcementId): RedirectResponse
    {
        $this->library->deleteAnnouncement($announcementId);
        $this->library->logActivity(Auth::id(), 'delete_announcement', 'announcement', $announcementId);

        return redirect()->route('admin.announcements')->with('status', 'Announcement deleted.');
    }

    protected function buildData(Request $request, string $page): array
    {
        $search         = trim((string) $request->query('search', ''));
        $roleFilter     = (string) $request->query('role', '');
        $genreFilter    = (string) $request->query('genre', '');
        $statusFilter   = (string) $request->query('status', '');
        $audienceFilter = (string) $request->query('audience', '');
        $activityFilter = (string) $request->query('activity', '');
        $activityRoleFilter = (string) $request->query('activity_role', '');
        $activityDateFrom   = (string) $request->query('activity_date_from', '');
        $activityDateTo     = (string) $request->query('activity_date_to', '');
        $loginRoleFilter    = (string) $request->query('login_role', '');
        $loginDateFrom      = (string) $request->query('login_date_from', '');
        $loginDateTo        = (string) $request->query('login_date_to', '');

        return [
            'dashboardPage'    => $page,
            'search'           => $search,
            'roleFilter'       => $roleFilter,
            'genreFilter'      => $genreFilter,
            'statusFilter'     => $statusFilter,
            'audienceFilter'   => $audienceFilter,
            'activityFilter'   => $activityFilter,
            'activityRoleFilter' => $activityRoleFilter,
            'activityDateFrom' => $activityDateFrom,
            'activityDateTo'   => $activityDateTo,
            'loginRoleFilter'  => $loginRoleFilter,
            'loginDateFrom'    => $loginDateFrom,
            'loginDateTo'      => $loginDateTo,
            'stats'            => $this->library->stats(),
            'unreadCount'      => $this->library->unreadCount(Auth::id()),
            'genres'           => $this->library->genres(),
            'users'            => $this->library->allUsers($search, $roleFilter),
            'books'            => $this->library->books($search, $genreFilter, $statusFilter),
            'reservations'     => $this->library->activeReservations(),
            'loans'            => $this->library->activeLoans(),
            'historyItems'     => $this->library->loanHistory(),
            'announcements'    => $this->library->announcements($audienceFilter, $search, false),
            'activityLogs'     => $this->library->recentActivities(12, $activityFilter !== '' ? $activityFilter : null, $search !== '' ? $search : null, $activityRoleFilter !== '' ? $activityRoleFilter : null, $activityDateFrom !== '' ? $activityDateFrom : null, $activityDateTo !== '' ? $activityDateTo : null),
            'loginLogs'        => $this->library->recentActivities(8, 'login'),
            'loginHistoryLogs' => $this->library->recentActivities(50, 'login', $search !== '' ? $search : null, $loginRoleFilter !== '' ? $loginRoleFilter : null, $loginDateFrom !== '' ? $loginDateFrom : null, $loginDateTo !== '' ? $loginDateTo : null),
            'chartMonth'       => now()->format('Y-m'),
            'chartMonthLabel'  => now()->format('F Y'),
            'chartMonths'      => collect(),
            'genreStats'       => collect(),
            'topBorrowedBooks' => collect(),
            'borrowedGenreStats' => collect(),
            'borrowTrend'      => collect(),
            'scanToken'    => $this->createBookScanSession(),
            'scanPhoneUrl' => route('scan.show', $this->createBookScanSession()),
        ];
    }
    
    protected function createBookScanSession(): string
    {
        $token = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\Cache::put('book-scan:'.$token, [
            'status' => 'waiting',
            'isbn'   => null,
        ], now()->addMinutes(15));
        return $token;
    }
    
        protected function bookScanCacheKey(string $token): string
    {
        return 'book-scan:'.$token;
    }
}
