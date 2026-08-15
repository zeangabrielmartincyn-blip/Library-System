<?php

namespace App\Http\Controllers;

use App\Services\LibraryRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestDashboardController extends Controller
{
    public function __construct(protected LibraryRepository $library)
    {
        abort_unless(auth()->check() && auth()->user()?->role === 'guest', 403);
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

    public function bookDetails(string $isbn): View
    {
        $book = $this->library->publicBookDetails($isbn);

        abort_unless($book !== null, 404);

        return view('Dashboard.guestdashboard', [
            'guestPage' => 'details',
            'bookDetails' => $book,
            'bookReviews' => $this->library->reviewsForBook($isbn)->take(8),
            'relatedBooks' => $this->library->relatedBooks($isbn),
            'message' => 'Review the available public information before visiting the library.',
            'libraryHours' => $this->library->libraryHours(),
        ]);
    }

    protected function buildData(Request $request, string $page): array
    {
        $sorts = ['title_asc', 'title_desc', 'newest', 'oldest', 'highest_rating', 'most_popular'];
        $availabilityOptions = ['available', 'unavailable'];
        $search = trim((string) $request->query('search', ''));
        $genre = trim((string) $request->query('genre', ''));
        $author = trim((string) $request->query('author', ''));
        $availability = in_array($request->query('availability'), $availabilityOptions, true)
            ? (string) $request->query('availability')
            : '';
        $sort = in_array($request->query('sort'), $sorts, true)
            ? (string) $request->query('sort')
            : 'title_asc';
        $year = $request->query('year');
        $year = is_numeric($year) && (int) $year > 0 ? (int) $year : null;

        $filters = compact('search', 'genre', 'author', 'availability', 'sort', 'year');
        $popularBooks = $this->library->popularBooks(8);

        return [
            'guestPage' => $page,
            'message' => 'Browse public resources, discover new arrivals, and plan your next library visit.',
            'books' => $this->library->publicCatalogPage($filters, 8),
            'genres' => $this->library->genres(),
            'authors' => $this->library->authors(),
            'publicationYears' => $this->library->publicationYears(),
            'search' => $search,
            'selectedGenre' => $genre,
            'selectedAuthor' => $author,
            'selectedAvailability' => $availability,
            'selectedYear' => $year,
            'selectedSort' => $sort,
            'newArrivals' => $this->library->newArrivals(8),
            'popularBooks' => $popularBooks->isNotEmpty() ? $popularBooks : $this->library->highlyRatedBooks(8),
            'announcements' => $this->library->publicAnnouncements($page === 'announcements' ? 20 : 6),
            'libraryHours' => $this->library->libraryHours(),
            'stats' => [
                'books' => $this->library->bookCount(),
                'available_books' => $this->library->availableBookCount(),
                'genres' => $this->library->genres()->count(),
            ],
        ];
    }
}
