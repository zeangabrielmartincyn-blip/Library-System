<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LibraryRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LibrarianDashboardController extends Controller
{
    public function __construct(protected LibraryRepository $library)
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

        return view('Dashboard.librariandashboard', [
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
                'admin' => 'Admin Only',
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
            'loginLogs' => $this->library->recentActivities(8, 'login'),
            'activityLogs' => $this->library->recentActivities(12),
        ]);
    }

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
            'audience'     => ['required', 'in:public,all,admin,librarian,instructor,student,guest'],
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
            'audience'     => ['required', 'in:public,all,admin,librarian,instructor,student,guest'],
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
}