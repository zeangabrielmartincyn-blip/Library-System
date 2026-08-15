@php
    $guestPage = $guestPage ?? 'dashboard';
    $bookValue = static function ($book, string $key, $fallback = null) {
        return is_array($book) ? ($book[$key] ?? $fallback) : ($book->{$key} ?? $fallback);
    };
    $renderStars = static function ($rating) {
        if ($rating === null || $rating === '') {
            return 'Not rated';
        }

        return str_repeat('★', max(0, min(5, (int) round((float) $rating)))
            ).str_repeat('☆', max(0, 5 - (int) round((float) $rating)));
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $guestPage === 'details' ? ($bookDetails['title'] ?? 'Book Details') : 'Guest Dashboard' }} | ISU Library System</title>
    <link rel="stylesheet" href="{{ asset('css/guest-dashboard.css') }}">
</head>
<body class="guest-body">
    <header class="guest-topbar">
        <a class="guest-brand" href="{{ route('dashboard.guest') }}">
            <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
            <span><strong>ISU Library System</strong><span>Guest dashboard</span></span>
        </a>
        <div class="guest-actions">
            <a class="guest-button" href="{{ route('login.student') }}">Login</a>
            <a class="guest-button" href="{{ route('register.student.form') }}">Register</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="guest-button" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <main class="guest-shell">
        @if ($guestPage === 'details')
            @php
                $book = $bookDetails;
                $bookReviews = collect($bookReviews ?? []);
                $relatedBooks = collect($relatedBooks ?? []);
            @endphp
            <div class="guest-section-head">
                <div>
                    <a class="guest-link" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('guest.catalog') }}">← Back to Catalog</a>
                    <p class="guest-muted">Public book information</p>
                </div>
            </div>

            <section class="detail-layout" aria-label="Book details">
                <article class="detail-card">
                    <div class="detail-cover" aria-label="No cover available">▤</div>
                    <h1>{{ $book['title'] }}</h1>
                    <p class="guest-muted">by {{ $book['author'] }}</p>
                    @if ($book['description'])
                        <p style="margin-top:1rem;">{{ $book['description'] }}</p>
                    @endif
                    <div class="detail-actions">
                        <button class="guest-link primary protected-action" type="button" data-action="Borrowing a book requires an eligible library account. Please log in with your student or instructor account.">Login to Borrow</button>
                        <button class="guest-link protected-action" type="button" data-action="Reserving a book is available only to authenticated eligible users. Please log in to continue.">Login to Reserve</button>
                    </div>
                </article>
                <aside class="detail-card">
                    <h2>Catalog information</h2>
                    <dl class="detail-list">
                        <div><dt>ISBN</dt><dd>{{ $book['isbn'] }}</dd></div>
                        <div><dt>Genre</dt><dd>{{ $book['genre'] }}</dd></div>
                        @if ($book['year'])<div><dt>Publication year</dt><dd>{{ $book['year'] }}</dd></div>@endif
                        @if ($book['location'])<div><dt>Shelf / location</dt><dd>{{ $book['location'] }}</dd></div>@endif
                        <div><dt>Availability</dt><dd>{{ $book['available_quantity'] > 0 ? 'Available' : 'Unavailable' }}</dd></div>
                        <div><dt>Available copies</dt><dd>{{ $book['available_quantity'] }}</dd></div>
                        <div><dt>Total copies</dt><dd>{{ $book['quantity'] }}</dd></div>
                        <div><dt>Rating</dt><dd>{{ $book['avg_rating'] ? number_format($book['avg_rating'], 1).' / 5' : 'Not rated' }}</dd></div>
                        <div><dt>Reviews</dt><dd>{{ $book['review_count'] }}</dd></div>
                    </dl>
                    <p class="guest-muted">Publisher, edition, and cover details are not present in the current database.</p>
                </aside>
            </section>

            <section class="guest-panel" style="margin-top:1rem;" aria-labelledby="reviews-heading">
                <div class="guest-section-head"><div><h2 id="reviews-heading">Ratings and reviews</h2><p>Guests can read reviews but cannot submit them.</p></div><button class="guest-link protected-action" type="button" data-action="Please log in to write a review.">Write Review</button></div>
                @if ($bookReviews->isEmpty())
                    <div class="guest-empty">No reviews have been submitted for this book yet.</div>
                @else
                    <div class="review-list">
                        @foreach ($bookReviews as $review)
                            <article class="review"><strong>{{ $review->reviewer_name ?? 'Library user' }}</strong><span class="stars" aria-label="{{ $review->rating }} out of 5"> · {{ $renderStars($review->rating) }}</span><small class="guest-muted"> · {{ $review->created_at }}</small>@if ($review->review)<p>{{ $review->review }}</p>@endif</article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="guest-panel" style="margin-top:1rem;" aria-labelledby="related-heading">
                <div class="guest-section-head"><div><h2 id="related-heading">Related books</h2><p>Recommendations based on the same author or genre.</p></div></div>
                @if ($relatedBooks->isEmpty())
                    <div class="guest-empty">No related books are available yet.</div>
                @else
                    <div class="book-cards">
                        @foreach ($relatedBooks as $related)
                            <a class="book-card" href="{{ route('guest.book-details', $bookValue($related, 'isbn')) }}"><div class="book-cover-placeholder">▤</div><h3>{{ $bookValue($related, 'title') }}</h3><div class="book-meta">{{ $bookValue($related, 'author') }} · {{ $bookValue($related, 'genre') }}</div><span class="availability {{ $bookValue($related, 'available_quantity', 0) > 0 ? 'good' : 'bad' }}">{{ $bookValue($related, 'available_quantity', 0) > 0 ? 'Available' : 'Unavailable' }}</span></a>
                        @endforeach
                    </div>
                @endif
            </section>
        @else
            <section class="guest-hero">
                <div><p class="guest-muted">ISU Library System · Public access</p><h1>Welcome, Guest Reader</h1><p>{{ $message ?? 'Browse the public catalog and library information.' }}</p><div class="detail-actions"><a class="guest-link primary" href="{{ route('guest.catalog') }}">Explore Catalog</a><a class="guest-link" href="{{ route('login.student') }}">Log in for borrowing</a></div></div>
                <div class="guest-hero-art" aria-hidden="true">▤</div>
            </section>

            <section class="guest-stats" aria-label="Library summary">
                <div class="guest-stat"><strong>Total Books</strong><span class="number">{{ number_format((int) ($stats['books'] ?? 0)) }}</span><p>Titles in the public catalog.</p></div>
                <div class="guest-stat"><strong>Available Books</strong><span class="number">{{ number_format((int) ($stats['available_books'] ?? 0)) }}</span><p>Titles with at least one available copy.</p></div>
                <div class="guest-stat"><strong>Genres</strong><span class="number">{{ number_format((int) ($stats['genres'] ?? 0)) }}</span><p>Categories represented in the collection.</p></div>
            </section>

            <section class="guest-panel" aria-labelledby="announcements-heading">
                <div class="guest-section-head"><div><h2 id="announcements-heading">Library Announcements</h2><p>Active public notices, newest first.</p></div>@if ($guestPage !== 'announcements')<a class="guest-link" href="{{ route('guest.announcements') }}">View all</a>@endif</div>
                @if (collect($announcements ?? [])->isEmpty())
                    <div class="guest-empty">There are no active public announcements right now.</div>
                @else
                    <div class="announcement-list">
                        @foreach ($announcements as $announcement)
                            <article class="announcement"><h3>{{ $announcement->title }}</h3><p>{{ $announcement->body }}</p><small>{{ $announcement->published_at ?: $announcement->created_at }}</small></article>
                        @endforeach
                    </div>
                @endif
            </section>

            <div class="guest-grid">
                <section class="guest-panel" aria-labelledby="new-arrivals-heading"><div class="guest-section-head"><div><h2 id="new-arrivals-heading">New Arrivals</h2><p>Recently added titles from the catalog.</p></div></div>@if (collect($newArrivals ?? [])->isEmpty())<div class="guest-empty">No new arrivals are available yet.</div>@else<div class="book-cards">@foreach ($newArrivals as $book)<a class="book-card" href="{{ route('guest.book-details', $bookValue($book, 'isbn')) }}"><div class="book-cover-placeholder">▤</div><h3>{{ $bookValue($book, 'title') }}</h3><div class="book-meta">{{ $bookValue($book, 'author') }} · {{ $bookValue($book, 'genre') }}</div><span class="availability {{ $bookValue($book, 'available_quantity', 0) > 0 ? 'good' : 'bad' }}">{{ $bookValue($book, 'available_quantity', 0) > 0 ? 'Available' : 'Unavailable' }}</span></a>@endforeach</div>@endif</section>
                <section class="guest-panel" aria-labelledby="popular-heading"><div class="guest-section-head"><div><h2 id="popular-heading">Popular Books</h2><p>Based on actual borrowing history.</p></div></div>@if (collect($popularBooks ?? [])->isEmpty())<div class="guest-empty">Popularity data will appear after books are borrowed.</div>@else<div class="book-cards">@foreach ($popularBooks as $book)<a class="book-card" href="{{ route('guest.book-details', $bookValue($book, 'isbn')) }}"><div class="book-cover-placeholder">▤</div><h3>{{ $bookValue($book, 'title') }}</h3><div class="book-meta">{{ $bookValue($book, 'author') }} · {{ $bookValue($book, 'borrow_count', 0) }} borrow{{ $bookValue($book, 'borrow_count', 0) == 1 ? '' : 's' }}</div><span class="stars">{{ $renderStars($bookValue($book, 'avg_rating')) }}</span><span class="availability {{ $bookValue($book, 'available_quantity', 0) > 0 ? 'good' : 'bad' }}">{{ $bookValue($book, 'available_quantity', 0) > 0 ? 'Available' : 'Unavailable' }}</span></a>@endforeach</div>@endif</section>
            </div>

            <section class="guest-panel" aria-labelledby="catalog-heading">
                <div class="guest-section-head"><div><h2 id="catalog-heading">Explore Catalog</h2><p>Search by title, author, ISBN, genre, or description. Combine filters and sorting.</p></div></div>
                <form class="guest-toolbar" method="GET" action="{{ route('guest.catalog') }}">
                    <input name="search" type="search" value="{{ $search ?? '' }}" placeholder="Search title, author, ISBN, or genre" aria-label="Search books">
                    <select name="genre" aria-label="Filter by genre"><option value="">All genres</option>@foreach ($genres as $genre)<option value="{{ $genre }}" @selected(($selectedGenre ?? '') === $genre)>{{ $genre }}</option>@endforeach</select>
                    <select name="author" aria-label="Filter by author"><option value="">All authors</option>@foreach ($authors as $author)<option value="{{ $author }}" @selected(($selectedAuthor ?? '') === $author)>{{ $author }}</option>@endforeach</select>
                    <select name="availability" aria-label="Filter by availability"><option value="">Any availability</option><option value="available" @selected(($selectedAvailability ?? '') === 'available')>Available</option><option value="unavailable" @selected(($selectedAvailability ?? '') === 'unavailable')>Unavailable</option></select>
                    <select name="year" aria-label="Filter by publication year"><option value="">Any year</option>@foreach ($publicationYears as $year)<option value="{{ $year }}" @selected((string) ($selectedYear ?? '') === (string) $year)>{{ $year }}</option>@endforeach</select>
                    <select name="sort" aria-label="Sort catalog"><option value="title_asc" @selected(($selectedSort ?? '') === 'title_asc')>Title A–Z</option><option value="title_desc" @selected(($selectedSort ?? '') === 'title_desc')>Title Z–A</option><option value="newest" @selected(($selectedSort ?? '') === 'newest')>Newest</option><option value="oldest" @selected(($selectedSort ?? '') === 'oldest')>Oldest</option><option value="highest_rating" @selected(($selectedSort ?? '') === 'highest_rating')>Highest Rated</option><option value="most_popular" @selected(($selectedSort ?? '') === 'most_popular')>Most Popular</option></select>
                    <button type="submit">Search</button>
                </form>
                @if ($books->isEmpty())
                    <div class="guest-empty">No books matched your search and filters.</div>
                @else
                    <div class="guest-table-wrap"><table class="guest-table"><thead><tr><th>ISBN</th><th>Title</th><th>Author</th><th>Genre</th><th>Shelf / location</th><th>Availability</th><th>Rating</th></tr></thead><tbody>@foreach ($books as $book)<tr><td>{{ $book['isbn'] }}</td><td><a href="{{ route('guest.book-details', $book['isbn']) }}">{{ $book['title'] }}</a></td><td>{{ $book['author'] }}</td><td>{{ $book['genre'] }}</td><td>{{ $book['location'] ?: 'Not specified' }}</td><td><span class="availability {{ $book['available_quantity'] > 0 ? 'good' : 'bad' }}">{{ $book['available_quantity'] > 0 ? 'Available' : 'Unavailable' }} · {{ $book['available_quantity'] }}</span></td><td><span class="stars">{{ $renderStars($book['avg_rating']) }}</span><br><small class="guest-muted">{{ $book['review_count'] }} review{{ $book['review_count'] == 1 ? '' : 's' }}</small></td></tr>@endforeach</tbody></table></div>
                    <div class="pagination"><span class="guest-muted">Page {{ $books->currentPage() }} of {{ $books->lastPage() }} · {{ $books->total() }} result{{ $books->total() == 1 ? '' : 's' }}</span><div class="pagination-links">@if ($books->onFirstPage())<span class="disabled">Previous</span>@else<a href="{{ $books->previousPageUrl() }}">Previous</a>@endif@php $startPage=max(1,$books->currentPage()-2); $endPage=min($books->lastPage(),$books->currentPage()+2); @endphp @for ($page=$startPage; $page <= $endPage; $page++)<a class="{{ $page === $books->currentPage() ? 'current' : '' }}" href="{{ $books->url($page) }}">{{ $page }}</a>@endfor @if ($books->hasMorePages())<a href="{{ $books->nextPageUrl() }}">Next</a>@else<span class="disabled">Next</span>@endif</div></div>
                @endif
            </section>

            <div class="guest-grid">
                <section class="guest-panel" aria-labelledby="hours-heading"><div class="guest-section-head"><div><h2 id="hours-heading">Library Hours</h2><p>Official hours can be configured in <code>config/library.php</code>.</p></div></div><table class="hours-table"><thead><tr><th>Day</th><th>Opening time</th><th>Closing time</th></tr></thead><tbody>@forelse (($libraryHours ?? []) as $hours)<tr><td>{{ $hours['day'] ?? 'Day' }}</td><td>{{ $hours['open'] ?: 'Not configured' }}</td><td>{{ $hours['close'] ?: 'Not configured' }}</td></tr>@empty<tr><td colspan="3">Library hours have not been configured.</td></tr>@endforelse</tbody></table></section>
                <section class="guest-panel" aria-labelledby="faq-heading"><div class="guest-section-head"><div><h2 id="faq-heading">FAQ / Help</h2><p>Quick answers for guest readers.</p></div></div><div class="faq-list"><details><summary>How do I borrow a book?</summary><p>Log in with an eligible student or instructor account, then use the borrowing options provided by the library staff.</p></details><details><summary>Do I need an account?</summary><p>You can browse the public catalog as a guest. An eligible account is required for borrowing, reservations, and reviews.</p></details><details><summary>How do I find a book?</summary><p>Open the book details page and check its shelf or location information, such as a floor and shelf number.</p></details><details><summary>How are unavailable books handled?</summary><p>Unavailable titles remain visible so you can check again later or ask library staff about reservations.</p></details><details><summary>Who can I contact for assistance?</summary><p>Use the library’s official contact channel or ask at the circulation desk.</p></details></div></section>
            </div>
        @endif
    </main>

    <button class="guest-link primary" id="chatLauncher" type="button" style="position:fixed;right:1rem;bottom:1rem;z-index:80;box-shadow:0 12px 28px rgba(6,66,37,.2);">Chat with AI</button>
    <section id="chatPanel" class="guest-modal" hidden style="position:fixed;right:1rem;bottom:4.8rem;z-index:80;width:min(25rem,calc(100vw - 2rem));">
        <button class="guest-close" id="chatClose" type="button" aria-label="Close chat">&times;</button><h2>AI Library Assistant</h2><div id="chatLog" style="max-height:16rem;overflow:auto;padding:.4rem 0;"><p class="guest-empty">Ask about books, availability, or using the library system.</p></div><form id="chatForm"><textarea id="chatInput" required placeholder="Type your question..." style="width:100%;min-height:4rem;margin:.5rem 0;padding:.6rem;border:1px solid var(--guest-line);border-radius:.6rem;font:inherit;"></textarea><button class="guest-link primary" type="submit">Send</button></form>
    </section>

    <div class="modal-backdrop" id="protectedModal" role="dialog" aria-modal="true" aria-labelledby="protectedTitle">
        <div class="guest-modal"><button class="guest-close" type="button" data-close-modal>&times;</button><h2 id="protectedTitle">Account required</h2><p id="protectedMessage">Please log in to continue.</p><div class="guest-modal-actions"><button class="guest-link" type="button" data-close-modal>Back</button><a class="guest-link primary" href="{{ route('login.student') }}">Login</a><a class="guest-link" href="{{ route('register.student.form') }}">Register</a></div></div>
    </div>

    <script>
        const protectedModal = document.getElementById('protectedModal');
        const protectedMessage = document.getElementById('protectedMessage');
        document.querySelectorAll('.protected-action').forEach((button) => button.addEventListener('click', () => {
            protectedMessage.textContent = button.dataset.action || 'Please log in to continue.';
            protectedModal.classList.add('open');
        }));
        document.querySelectorAll('[data-close-modal]').forEach((button) => button.addEventListener('click', () => protectedModal.classList.remove('open')));
        protectedModal?.addEventListener('click', (event) => { if (event.target === protectedModal) protectedModal.classList.remove('open'); });

        const chatPanel = document.getElementById('chatPanel');
        const chatLog = document.getElementById('chatLog');
        const appendChat = (text, role) => { const item = document.createElement('p'); item.className = 'guest-empty'; item.style.margin = '.4rem 0'; item.textContent = text; if (role === 'user') item.style.borderColor = 'var(--guest-gold)'; chatLog.appendChild(item); chatLog.scrollTop = chatLog.scrollHeight; };
        document.getElementById('chatLauncher')?.addEventListener('click', () => { chatPanel.hidden = !chatPanel.hidden; });
        document.getElementById('chatClose')?.addEventListener('click', () => { chatPanel.hidden = true; });
        document.getElementById('chatForm')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const input = document.getElementById('chatInput'); const message = input.value.trim(); if (!message) return;
            appendChat(message, 'user'); input.value = '';
            try {
                const response = await fetch(@json(route('chat.send')), { method: 'POST', headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body: JSON.stringify({message}) });
                const data = await response.json(); appendChat(data.reply || 'No reply received.', 'bot');
            } catch (error) { appendChat('The chat service is unavailable right now.', 'bot'); }
        });
    </script>
</body>
</html>
