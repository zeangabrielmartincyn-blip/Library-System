<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard | ISU Library System</title>

    <style>
        :root {
            --green: #064225;
            --gold: #f2c84b;
            --line: #d7e1d7;
            --bg: #f4f8f1;
        }

        * {
            box-sizing: border-box;
        }

        .guest-body {
            margin: 0;
            background: var(--bg);
            color: #163021;
            font-family: Arial, Helvetica, sans-serif;
        }

        .guest-topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 5%;
            background: var(--green);
            color: white;
        }

        .guest-brand,
        .guest-actions {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .guest-brand {
            color: inherit;
            text-decoration: none;
        }

        .guest-brand img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
        }

        .guest-brand small {
            display: block;
            opacity: .8;
        }

        .guest-button,
        .primary-button,
        .catalog-form button {
            padding: .65rem .9rem;
            border: 1px solid white;
            border-radius: .5rem;
            color: var(--green);
            background: white;
            text-decoration: none;
            cursor: pointer;
        }

        .guest-shell {
            width: min(1150px, 92%);
            margin: 2rem auto;
        }

        .guest-hero,
        .guest-panel,
        .guest-stats article {
            border: 1px solid var(--line);
            border-radius: .8rem;
            background: white;
            padding: 1.25rem;
        }

        .guest-hero {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hero-icon {
            color: var(--green);
            font-size: 5rem;
        }

        .primary-button,
        .catalog-form button {
            display: inline-block;
            margin-top: .75rem;
            border-color: var(--green);
            color: white;
            background: var(--green);
        }

        .guest-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 1rem 0;
        }

        .guest-stats span {
            display: block;
            margin: .5rem 0;
            color: var(--green);
            font-size: 2rem;
            font-weight: bold;
        }

        .guest-panel {
            margin: 1rem 0;
        }

        .announcement {
            padding: .75rem 0;
            border-bottom: 1px solid var(--line);
        }

        .catalog-form {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .catalog-form input,
        .catalog-form select {
            flex: 1;
            min-width: 180px;
            padding: .7rem;
            border: 1px solid var(--line);
            border-radius: .5rem;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: .75rem;
            border-bottom: 1px solid var(--line);
            text-align: left;
        }

        th {
            background: #fff8dc;
        }

        .muted {
            color: #607267;
        }

        @media (max-width: 700px) {
            .guest-topbar,
            .guest-hero {
                align-items: flex-start;
                flex-direction: column;
            }

            .guest-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="guest-body">
    <header class="guest-topbar">
        <a class="guest-brand" href="{{ route('dashboard.guest') }}">
            <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
            <span>
                <strong>ISU Library System</strong>
                <small>Guest dashboard</small>
            </span>
        </a>

        <div class="guest-actions">
            <a class="guest-button" href="{{ route('login.student') }}">Login</a>
            <a class="guest-button" href="{{ route('register.student.form') }}">Register</a>

            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button class="guest-button" type="submit">Logout</button>
            </form>
        </div>
    </header>

    <main class="guest-shell">
        <section class="guest-hero">
            <div>
                <p class="muted">ISU Library System · Public access</p>
                <h1>Welcome, Guest Reader</h1>
                <p>Browse the public catalog and library information.</p>
                <a class="primary-button" href="{{ route('guest.catalog') }}">Explore Catalog</a>
            </div>

            <div class="hero-icon">▤</div>
        </section>

        <section class="guest-stats">
            <article>
                <strong>Total Books</strong>
                <span>{{ number_format($stats['books'] ?? 0) }}</span>
                <p>Titles in the public catalog.</p>
            </article>

            <article>
                <strong>Available Books</strong>
                <span>{{ number_format($stats['available_books'] ?? 0) }}</span>
                <p>Titles ready to borrow.</p>
            </article>

            <article>
                <strong>Genres</strong>
                <span>{{ number_format($stats['genres'] ?? 0) }}</span>
                <p>Categories in the collection.</p>
            </article>
        </section>

        <section class="guest-panel">
            <h2>Library Announcements</h2>

            @forelse ($announcements as $announcement)
                <article class="announcement">
                    <h3>{{ $announcement->title }}</h3>
                    <p>{{ $announcement->body }}</p>
                    <small>{{ $announcement->published_at ?: $announcement->created_at }}</small>
                </article>
            @empty
                <p class="muted">There are no public announcements right now.</p>
            @endforelse
        </section>

        <section class="guest-panel">
            <h2>Explore Catalog</h2>

            <form class="catalog-form" method="GET" action="{{ route('guest.catalog') }}">
                <input
                    type="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Search title, author, ISBN, or genre"
                >

                <select name="genre">
                    <option value="">All genres</option>

                    @foreach ($genres as $genre)
                        <option value="{{ $genre }}" @selected($selectedGenre === $genre)>
                            {{ $genre }}
                        </option>
                    @endforeach
                </select>

                <button type="submit">Search</button>
            </form>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Genre</th>
                            <th>Location</th>
                            <th>Availability</th>
                            <th>Rating</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($books as $book)
                            <tr>
                                <td>{{ $book['isbn'] }}</td>
                                <td>{{ $book['title'] }}</td>
                                <td>{{ $book['author'] }}</td>
                                <td>{{ $book['genre'] }}</td>
                                <td>{{ $book['location'] ?: 'Not specified' }}</td>
                                <td>{{ $book['available_quantity'] > 0 ? 'Available' : 'Unavailable' }}</td>
                                <td>{{ $book['avg_rating'] ? number_format($book['avg_rating'], 1).' / 5' : 'Not rated' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">No books matched your search.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="guest-panel">
            <h2>FAQ / Help</h2>

            <details>
                <summary>How do I borrow a book?</summary>
                <p>Log in with an eligible student or instructor account.</p>
            </details>

            <details>
                <summary>Do I need an account?</summary>
                <p>You can browse as a guest. Borrowing and reservations require an account.</p>
            </details>
        </section>
    </main>
</body>
</html>