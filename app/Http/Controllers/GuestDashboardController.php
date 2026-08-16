<?php

namespace App\Http\Controllers;

use App\Services\LibraryRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestDashboardController extends Controller
{
    public function __construct(protected LibraryRepository $library)
    {
    }

    public function dashboard(Request $request): View
    {
        return view('Dashboard.guestdashboard', $this->buildData($request, 'dashboard'));
    }

    public function catalog(Request $request): View
    {
        return view('Dashboard.guestdashboard', $this->buildData($request, 'catalog'));
    }

    public function announcements(Request $request): View
    {
        return view('Dashboard.guestdashboard', $this->buildData($request, 'announcements'));
    }

    protected function buildData(Request $request, string $page): array
    {
        $search = trim((string) $request->query('search', ''));
        $genre = trim((string) $request->query('genre', ''));
        $books = $this->library->publicBooks($search !== '' ? $search : null, $genre !== '' ? $genre : null);

        return [
            'dashboardPage' => $page,
            'guestPage' => $page,
            'books' => $books,
            'genres' => $this->library->genres(),
            'search' => $search,
            'selectedGenre' => $genre,
            'featuredBooks' => $books->where('available_quantity', '>', 0)->take(3)->values(),
            'announcements' => $this->library->announcements('public'),
            'stats' => [
                'books' => $this->library->bookCount(),
                'available_books' => $this->library->availableBookCount(),
                'genres' => $this->library->genres()->count(),
            ],
        ];
    }

    public function bookDetails(string $isbn): View
    {
        $book = $this->library->publicBooks()->firstWhere('isbn', $isbn);

        abort_unless($book, 404);

        $relatedBooks = $this->library->publicBooks()
            ->filter(fn ($item) =>
                $item['isbn'] !== $book['isbn']
                && ($item['genre'] === $book['genre'] || $item['author'] === $book['author'])
            )
            ->take(4)
            ->values();

        return view('Dashboard.guestdashboard', [
            'guestPage' => 'details',
            'bookDetails' => $book,
            'bookReviews' => $this->library->reviewsForBook($isbn),
            'relatedBooks' => $relatedBooks,
        ]);
    }
}


