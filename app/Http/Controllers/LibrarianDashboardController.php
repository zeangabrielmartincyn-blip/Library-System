<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Services\ChatBotService;
use App\Services\LibraryRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;
=======
use App\Models\User;
use App\Services\LibraryRepository;
use Illuminate\Support\Facades\Cache;
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
<<<<<<< HEAD
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
=======
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
use Illuminate\Support\Str;
use Illuminate\View\View;

class LibrarianDashboardController extends Controller
{
<<<<<<< HEAD
    public function __construct(protected LibraryRepository $library, protected ChatBotService $chatBot)
=======
    public function __construct(protected LibraryRepository $library)
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
    {
        abort_unless(Auth::check() && Auth::user()?->role === 'librarian', 403);
    }

    public function dashboard(Request $request): View
    {
        $stats = $this->dashboardStats();
        $scanToken = $this->createBookScanSession();
        $bookSearch = trim((string) $request->query('search', ''));
        $bookGenreFilter = trim((string) $request->query('genre', ''));
        $bookStatusFilter = trim((string) $request->query('status', ''));
        $announcementSearch = trim((string) $request->query('announcement_search', ''));
        $announcementAudienceFilter = trim((string) $request->query('audience', ''));
<<<<<<< HEAD
        $userSearch = trim((string) $request->query('user_search', ''));
        $userRoleFilter = trim((string) $request->query('user_role', ''));
        $fineStatusFilter = trim((string) $request->query('fine_status', ''));
        $chartMonth = $request->query('month', now()->format('Y-m'));

        return view('Dashboard.librariandashboard', [
            'users' => $this->library->allUsers($userSearch, $userRoleFilter),
            'userSearch' => $userSearch,
            'userRoleFilter' => $userRoleFilter,
            'fines' => $this->library->allFines($fineStatusFilter !== '' ? $fineStatusFilter : null),
            'fineStatusFilter' => $fineStatusFilter,
            'allReviews' => $this->allReviews(),
            'notifications' => $this->library->notificationsForUser(Auth::id(), 50),
            'unreadCount' => $this->library->unreadCount(Auth::id()),
            'chartMonth' => $chartMonth,
            'chartMonthLabel' => Carbon::parse($chartMonth.'-01')->format('F Y'),
            'chartMonths' => collect(range(0, 11))->map(function (int $offset) {
                $month = now()->startOfMonth()->subMonths($offset);

                return ['value' => $month->format('Y-m'), 'label' => $month->format('F Y')];
            })->values(),
            'genreStats' => DB::table('books')->selectRaw('genre, COUNT(*) as count')->groupBy('genre')->orderByDesc('count')->get(),
            'topBorrowedBooks' => $this->library->mostBorrowedBooksForMonth($chartMonth, 6),
            'borrowTrend' => $this->library->borrowTrend(6),
=======

        return view('Dashboard.librariandashboard', [
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
            'stats' => $stats,
            'scanToken' => $scanToken,
            'scanPhoneUrl' => route('scan.show', $scanToken),
            'search' => $bookSearch,
            'genreFilter' => $bookGenreFilter,
            'statusFilter' => $bookStatusFilter,
            'genres' => $this->library->genres(),
            'bookStatuses' => ['Available', 'Unavailable', 'Archived'],
            'audienceFilter' => $announcementAudienceFilter,
            'announcements' => $this->library->announcements(
                $announcementAudienceFilter !== '' ? $announcementAudienceFilter : null,
                $announcementSearch
            ),
            'announcementSearch' => $announcementSearch,
            'announcementAudiences' => [
                'public' => 'Public / Guest',
                'all' => 'All Roles',
<<<<<<< HEAD
=======
                'admin' => 'Admin Only',
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
                'librarian' => 'Librarian Only',
                'instructor' => 'Instructor Only',
                'student' => 'Student Only',
                'guest' => 'Guest Only',
            ],
            'announcementStatuses' => ['Draft', 'Published', 'Archived'],
            'inventory' => $this->library->books(
                $bookSearch !== '' ? $bookSearch : null,
                $bookGenreFilter !== '' ? $bookGenreFilter : null,
                $bookStatusFilter !== '' ? $bookStatusFilter : null
            )->map(fn ($book) => [
                'id' => $book['id'],
                'isbn' => $book['isbn'],
                'title' => $book['title'],
                'author' => $book['author'],
                'genre' => $book['genre'],
                'year' => $book['year'],
                'qty' => $book['quantity'],
                'available_quantity' => $book['available_quantity'],
                'location' => $book['location'] ?? null,
                'status' => $book['status'],
                'avg_rating' => $book['avg_rating'] ?? null,
                'review_count' => $book['review_count'] ?? 0,
            ]),
            'borrowedBooks' => $this->library->activeLoans()->map(fn ($loan) => [
                'id' => $loan->id,
                'title' => $loan->title,
                'borrower' => $loan->borrower_id,
                'due_date' => $loan->due_at,
                'status' => $loan->returned_at ? 'Returned' : (($loan->due_at < now()->toDateString()) ? 'Overdue' : 'Borrowed'),
            ]),
            'reservations' => $this->library->activeReservations()->map(fn ($reservation) => [
                'id' => $reservation->id,
                'isbn' => $reservation->isbn,
                'title' => $reservation->title,
                'borrower_name' => $reservation->borrower_name,
                'borrower_user_id' => $reservation->borrower_user_id,
                'borrower_id' => $reservation->borrower_id,
                'reserved_at' => $reservation->reserved_at,
            ]),
            'historyItems' => $this->library->loanHistory()->map(fn ($loan) => [
                'isbn' => $loan->isbn,
                'title' => $loan->title,
                'borrower' => $loan->borrower_id,
                'borrowed_date' => $loan->borrowed_at,
                'returned_date' => $loan->returned_at ?? '---',
                'status' => $loan->returned_at ? 'Returned' : (($loan->due_at < now()->toDateString()) ? 'Overdue' : 'Borrowed'),
            ]),
<<<<<<< HEAD
=======
            'students' => $this->library->studentDirectory()->map(fn ($student) => [
                'id' => $student->login_id,
                'name' => $student->name,
                'email' => $student->email,
            ]),
            'instructors' => $this->library->instructorDirectory()->map(fn ($instructor) => [
                'id' => $instructor->login_id,
                'name' => $instructor->name,
                'email' => $instructor->email,
            ]),
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
            'loginLogs' => $this->library->recentActivities(8, 'login'),
            'activityLogs' => $this->library->recentActivities(12),
        ]);
    }

<<<<<<< HEAD
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $context = [
            'role' => Auth::user()?->role ?? 'librarian',
            'user_name' => Auth::user()?->name ?? 'Librarian',
            'stats' => $this->library->stats(),
        ];

        try {
            $reply = $this->chatBot->reply($validated['message'], $context);
        } catch (\Throwable $e) {
            report($e);
            $reply = 'The chat service is not available right now. Please check the chatbot configuration.';
        }

        return response()->json(['reply' => $reply]);
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
            $metadata['author'] = collect($item['authors'] ?? [])->filter()->join(', ');
            $metadata['genre'] = (string) collect($item['categories'] ?? [])->filter()->first();
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
        return strtoupper(preg_replace('/[^0-9Xx]/', '', $isbn) ?? '');
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
            'isbn' => $isbn,
            'title' => $validated['title'],
        ]);

        return redirect()->to(route('dashboard.librarian').'#inventory')->with('status', 'Book updated successfully.');
    }

    public function deleteBook(string $isbn): RedirectResponse
    {
        $this->library->deleteBook($isbn);
        $this->library->logActivity(Auth::id(), 'delete_book', 'book', null, ['isbn' => $isbn]);

        return redirect()->to(route('dashboard.librarian').'#inventory')->with('status', 'Book deleted successfully.');
    }

    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'mobile_number' => ['required', 'string', 'max:20', 'unique:users,mobile_number'],
            'login_id' => ['nullable', 'string', 'max:255', 'unique:users,login_id'],
            'role'     => ['required', 'in:librarian,instructor,student'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $userId = $this->library->createUser([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'mobile_number' => $validated['mobile_number'],
            'login_id' => $validated['login_id'] ?: null,
            'role'     => $validated['role'],
            'status'   => 'active',
            'password' => Hash::make($validated['password']),
        ]);

        $this->library->logActivity(Auth::id(), 'create_user', 'user', $userId, [
            'role'     => $validated['role'],
            'login_id' => $validated['login_id'] ?? null,
            'mobile_number' => $validated['mobile_number'],
        ]);

        $anchor = match ($validated['role']) {
            'student' => 'students',
            'instructor' => 'instructors',
            default => 'users',
        };

        return redirect()->to(route('dashboard.librarian')."#{$anchor}")->with('status', ucfirst($validated['role']).' account created successfully.');
    }

    public function toggleUserStatus(int $userId): RedirectResponse
    {
        $this->library->toggleUserStatus($userId);
        $this->library->logActivity(Auth::id(), 'toggle_user_status', 'user', $userId);

        return back()->with('status', 'User status updated.');
    }

    public function recalculateFines(): RedirectResponse
    {
        $count = $this->library->calculateAndStoreFines();
        $this->library->logActivity(Auth::id(), 'recalculate_fines', null, null, ['new_fines' => $count]);

        return redirect()->to(route('dashboard.librarian').'#fines')->with('status', "Fines recalculated. {$count} new fine(s) created.");
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

    protected function allReviews()
    {
        $query = DB::table('book_reviews')->join('users', 'users.id', '=', 'book_reviews.user_id');

        if (Schema::hasColumn('book_reviews', 'book_id')) {
            $query->join('books', 'books.id', '=', 'book_reviews.book_id');
        } else {
            $query->join('books', 'books.isbn', '=', 'book_reviews.book_isbn');
        }

        return $query->orderByDesc('book_reviews.created_at')->get([
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
    }

    public function exportReviews(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $reviews = $this->allReviews();

        return response()->streamDownload(function () use ($reviews) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Reviewer', 'Book', 'Rating', 'Review', 'Date']);
            foreach ($reviews as $r) {
                fputcsv($out, [$r->reviewer_name, $r->title, $r->rating, $r->review, $r->created_at]);
            }
            fclose($out);
        }, 'reviews.csv', ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="reviews.csv"']);
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
                    fputcsv($out, [$index + 1, $book->title, $book->author, $book->genre, $book->borrow_count]);
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

        $rows = match ($type) {
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

=======
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
    public function addBook(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'isbn' => ['required', 'string'],
            'title' => ['required', 'string'],
            'author' => ['required', 'string'],
            'genre' => ['required', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'year_published' => ['nullable', 'integer', 'min:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['available_quantity'] = (int) $validated['quantity'];
        $validated['status'] = 'Available';

        $this->library->createOrUpdateBook($validated, Auth::id());
        $this->library->logActivity(Auth::id(), 'create_book', 'book', null, ['isbn' => $validated['isbn']]);

        return redirect()->route('dashboard.librarian')->with('status', 'Book saved successfully.');
    }

<<<<<<< HEAD
=======
    public function addStudent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['nullable', 'in:student'],
        ]);

        $this->library->createUser([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'login_id' => $validated['login_id'],
            'role' => 'student',
            'password' => Hash::make($validated['password']),
        ]);
        $this->library->logActivity(Auth::id(), 'create_user', 'user', null, [
            'role' => 'student',
            'login_id' => $validated['login_id'],
        ]);

        return redirect()->route('dashboard.librarian')->with('status', 'Student registered successfully.');
    }

    public function addInstructor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $this->library->createUser([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'login_id' => $validated['login_id'],
            'role' => 'instructor',
            'password' => Hash::make($validated['password']),
        ]);
        $this->library->logActivity(Auth::id(), 'create_user', 'user', null, [
            'role' => 'instructor',
            'login_id' => $validated['login_id'],
        ]);

        return redirect()->route('dashboard.librarian')->with('status', 'Instructor registered successfully.');
    }

>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
    public function issue(Request $request, string $isbn): RedirectResponse
    {
        $validated = $request->validate([
            'user_id'  => ['required', 'string', 'max:255'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ]);

        $borrowerInput = trim($validated['user_id']);
        $borrowerId = ctype_digit($borrowerInput)
            ? (int) $borrowerInput
            : (int) DB::table('users')->where('login_id', $borrowerInput)->value('id');

        if ($borrowerId < 1) {
            return back()->with('error', 'Borrower not found. Enter a valid user ID or login ID.');
        }

        $quantity = max(1, (int) ($validated['quantity'] ?? 1));
        $issued = 0;
        $lastResult = null;

        for ($i = 0; $i < $quantity; $i++) {
            $lastResult = $this->library->issueBookByIsbn($isbn, $borrowerId, Auth::id());
            if (! $lastResult['ok']) {
                break;
            }
            $issued++;
        }

        if ($issued === 0) {
            return back()->with('error', $lastResult['message']);
        }

        $message = $issued === $quantity
            ? "{$issued} copy/copies of the book issued successfully."
            : "Only {$issued} out of {$quantity} copies could be issued. " . $lastResult['message'];

        return back()->with('status', $message);
    }

    public function issueReservation(int $reservationId): RedirectResponse
    {
        $result = $this->library->issueReservation($reservationId, Auth::id());

        return back()->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function declineReservation(int $reservationId): RedirectResponse
    {
        $result = $this->library->declineReservation($reservationId, Auth::id());

        return back()->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function return(Request $request, int $loanId): RedirectResponse
    {
        $result = $this->library->returnBookByLoanId($loanId, Auth::id());

        return back()->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
<<<<<<< HEAD
            'audience'     => ['required', 'in:public,all,librarian,instructor,student,guest'],
=======
            'audience'     => ['required', 'in:public,all,admin,librarian,instructor,student,guest'],
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
            'published_at' => ['nullable', 'date'],
            'status'       => ['required', 'in:Draft,Published,Archived'],
        ]);

        $announcementId = $this->library->createAnnouncement($validated, Auth::id());
        $this->library->logActivity(Auth::id(), 'create_announcement', 'announcement', $announcementId, [
            'title' => $validated['title'],
            'audience' => $validated['audience'],
        ]);

        return redirect()->to(route('dashboard.librarian').'#announcements')
            ->with('status', 'Announcement created successfully.');
    }

    public function updateAnnouncement(Request $request, int $announcementId): RedirectResponse
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
<<<<<<< HEAD
            'audience'     => ['required', 'in:public,all,librarian,instructor,student,guest'],
=======
            'audience'     => ['required', 'in:public,all,admin,librarian,instructor,student,guest'],
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
            'published_at' => ['nullable', 'date'],
            'status'       => ['required', 'in:Draft,Published,Archived'],
        ]);

        $this->library->updateAnnouncement($announcementId, $validated);
        $this->library->logActivity(Auth::id(), 'update_announcement', 'announcement', $announcementId, [
            'title' => $validated['title'],
            'audience' => $validated['audience'],
        ]);

        return redirect()->to(route('dashboard.librarian').'#announcements')
            ->with('status', 'Announcement updated successfully.');
    }

    public function deleteAnnouncement(int $announcementId): RedirectResponse
    {
        $this->library->deleteAnnouncement($announcementId);
        $this->library->logActivity(Auth::id(), 'delete_announcement', 'announcement', $announcementId);

        return redirect()->to(route('dashboard.librarian').'#announcements')
            ->with('status', 'Announcement deleted successfully.');
    }

    protected function dashboardStats(): array
    {
        $stats = $this->library->stats();

        return [
            'registered_students' => $stats['users']['student'],
            'registered_instructors' => $stats['users']['instructor'],
            'pending_pickups' => $stats['reservations'],
            'active_overdue' => $stats['loans'],
        ];
    }

    protected function createBookScanSession(): string
    {
        $token = (string) Str::uuid();

        Cache::put($this->bookScanCacheKey($token), [
            'status' => 'waiting',
            'isbn' => null,
        ], now()->addMinutes(15));

        return $token;
    }

    protected function bookScanCacheKey(string $token): string
    {
        return 'book-scan:'.$token;
    }
<<<<<<< HEAD
}
=======
}
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
