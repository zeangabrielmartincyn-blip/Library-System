<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class StudentDashboardController extends LibraryDashboardController
{
    public function dashboard(Request $request): View
    {
        $reservedBooks = $this->library->reservationsForUser($this->userId());
        $reservedCopies = (int) $reservedBooks->sum(fn ($item) => (int) ($item->quantity ?? 1));
        $borrowedBooks = $this->library->loansForUser($this->userId())->map(function ($item) {
            $status = $item->returned_at ? 'Returned' : (($item->due_at < now()->toDateString()) ? 'Overdue' : 'Borrowed');

            return [
                'id' => $item->id,
                'status' => $status,
                'due_at' => $item->due_at,
            ];
        });
        $borrowLimit = $this->library->borrowLimitForUser($this->userId());
        $activeBorrowed = $this->library->activeLoanCountForUser($this->userId());

        return $this->render('dashboard', [
            'pendingReservations' => $reservedCopies,
            'borrowedBooks' => $borrowedBooks->where('status', 'Borrowed')->count(),
            'overdueBooks' => $borrowedBooks->where('status', 'Overdue')->count(),
            'borrowLimit' => $borrowLimit,
            'borrowedSlotsUsed' => $activeBorrowed,
            'borrowedSlotsLeft' => max(0, $borrowLimit - $activeBorrowed),
        ]);
    }

    public function catalog(Request $request): View
    {
        $books = $this->library->books($request->query('search'), $request->query('genre'));
        $genres = $this->library->genres();

        return $this->render('catalog', [
            'books' => $books,
            'genres' => $genres,
            'selectedGenre' => (string) $request->query('genre'),
            'search' => trim((string) $request->query('search')),
            'reservedIsbns' => $this->library->reservationsForUser($this->userId())->pluck('isbn')->all(),
        ]);
    }

    public function reservations(Request $request): View
    {
        return $this->render('reservations', [
            'reservations' => $this->library->reservationsForUser($this->userId())->map(function ($item) {
                return [
                    'isbn' => $item->isbn,
                    'title' => $item->title,
                    'author' => $item->author,
                    'genre' => $item->genre,
                    'quantity' => $item->quantity ?? 1,
                    'reserved_at' => $item->reserved_at,
                ];
            }),
        ]);
    }

    public function borrowed(): View
    {
        $items = $this->library->loansForUser($this->userId())->map(function ($item) {
            $status = $item->returned_at ? 'Returned' : (($item->due_at < now()->toDateString()) ? 'Overdue' : 'Borrowed');

            return [
                'id' => $item->id,
                'isbn' => $item->isbn,
                'title' => $item->title,
                'author' => $item->author,
                'genre' => $item->genre,
                'year' => $item->year_published,
                'borrowed_date' => $item->borrowed_at,
                'returned_date' => $item->returned_at ?? '---',
                'status' => $status,
            ];
        });

        return $this->render('borrowed', [
            'borrowedItems' => $items,
        ]);
    }

    public function history(): View
    {
        $items = $this->library->historyForUser($this->userId())->map(function ($item) {
            $status = $item->returned_at ? 'Returned' : (($item->due_at < now()->toDateString()) ? 'Overdue' : 'Borrowed');

            return [
                'id' => $item->id,
                'isbn' => $item->isbn,
                'title' => $item->title,
                'author' => $item->author,
                'genre' => $item->genre,
                'year' => $item->year_published,
                'borrowed_date' => $item->borrowed_at,
                'returned_date' => $item->returned_at ?? '---',
                'status' => $status,
            ];
        });

        return $this->render('history', [
            'historyItems' => $items,
        ]);
    }

    public function profile(): View
    {
        $user = Auth::user();

        return $this->render('profile', [
            'profile' => $this->profileData($user),
        ]);
    }

    public function fines(Request $request): View
    {
        return $this->render('fines', [
            'fines' => $this->library->finesForUser($this->userId()),
        ]);
    }

    public function notifications(Request $request): View
    {
        return $this->render('notifications', [
            'notifications' => $this->library->notificationsForUser($this->userId(), 50),
            'unreadCount' => $this->library->unreadCount($this->userId()),
        ]);
    }

    public function reserve(Request $request, string $isbn): RedirectResponse
    {
        $this->authorizeRole();

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $result = $this->library->reserveBook($this->userId(), $isbn, (int) $validated['quantity']);

        return back()->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function cancelReservation(Request $request, string $isbn): RedirectResponse
    {
        $this->authorizeRole();

        $result = $this->library->cancelReservation($this->userId(), $isbn);

        return back()->with($result['ok'] ? 'status' : 'error', $result['message']);
    }

    public function submitReview(Request $request, string $isbn): RedirectResponse
    {
        $this->authorizeRole();

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['nullable', 'string', 'max:2000'],
        ]);

        $book = $this->bookLookup($isbn);

        if (! $book) {
            return back()->with('error', 'Book not found.');
        }

        if (! Gate::allows('submit-book-review', $isbn)) {
            return back()->with('error', 'You can only review books you have borrowed.');
        }

        $this->library->upsertReview(
            $this->userId(),
            $isbn,
            (int) $validated['rating'],
            $validated['review'] ?? null
        );

        $this->library->logActivity($this->userId(), 'submit_review', 'book', (int) $book->id, [
            'isbn' => $isbn,
            'rating' => (int) $validated['rating'],
        ]);

        return back()->with('status', 'Your review has been saved.');
    }

    public function markNotificationsRead(Request $request): RedirectResponse
    {
        $this->authorizeRole();

        $this->library->markNotificationsRead($this->userId());

        return back()->with('status', 'Notifications marked as read.');
    }

    public function markNotificationRead(Request $request, int $notificationId): RedirectResponse
    {
        $this->authorizeRole();

        $updated = $this->library->markNotificationRead($this->userId(), $notificationId);

        return back()->with($updated ? 'status' : 'error', $updated ? 'Notification marked as read.' : 'Notification not found.');
    }

    protected function roleKey(): string
    {
        return 'student';
    }

    protected function viewName(): string
    {
        return 'Dashboard.studentdashboard';
    }

    protected function profileData($user): array
    {
        return $this->userProfileData($user, [
            'Course' => 'Bachelor of Science in Information Technology',
            'Year level' => '3rd Year',
            'Department' => 'College of Computing Studies',
        ]);
    }
        public function updatePassword(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        return $this->updatePasswordData($request, 'student.profile', 'Password updated successfully.');
    }
}


