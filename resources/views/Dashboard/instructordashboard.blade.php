<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard | ISU Library System</title>
    <link rel="stylesheet" href="{{ asset('css/isu-ui-fix.css') }}">
    <style>
        :root {
            --green: #0b6b3a;
            --green-deep: #064225;
            --gold: #f2c84b;
            --red: #c8282d;
            --bg: #f7fbf1;
            --panel: rgba(255, 255, 255, 0.95);
            --text: #173423;
            --muted: #617265;
            --line: rgba(11, 107, 58, 0.14);
            --shadow: 0 18px 45px rgba(6, 66, 37, 0.10);
        }

        * { box-sizing: border-box; }
        html {
            font-size: 14px;
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: "Segoe UI", Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(242, 200, 75, 0.18), transparent 24rem),
                radial-gradient(circle at top right, rgba(11, 107, 58, 0.12), transparent 18rem),
                linear-gradient(180deg, #f8fbf5 0%, var(--bg) 100%);
        }

        a { color: inherit; text-decoration: none; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem clamp(1rem, 3vw, 2.2rem);
            background: rgba(6, 66, 37, 0.94);
            color: #fff;
            backdrop-filter: blur(16px);
            box-shadow: 0 10px 30px rgba(6, 66, 37, 0.16);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .brand img {
            width: 3.6rem;
            height: 3.6rem;
            object-fit: cover;
            border-radius: 1rem;
            background: #fff;
        }

        .brand-copy strong,
        .brand-copy span { display: block; }

        .brand-copy strong { font-size: 1.02rem; }

        .brand-copy span {
            margin-top: 0.15rem;
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.88rem;
        }

        .topbar-meta {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 999px;
            padding: 0.55rem 0.85rem;
            background: rgba(255, 255, 255, 0.08);
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.95);
            white-space: nowrap;
        }

        .logout {
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            padding: 0.7rem 1rem;
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
            font: inherit;
            font-weight: 800;
            cursor: pointer;
        }
        .chat-launcher, .chat-close { position: fixed; right: 1.25rem; bottom: 1.25rem; z-index: 80; border: 0; border-radius: 999px; padding: 0.95rem 1.1rem; background: linear-gradient(135deg, var(--green), #0a7a43); color: #fff; font: inherit; font-weight: 900; box-shadow: 0 18px 36px rgba(11, 107, 58, 0.28); cursor: pointer; }
        .chat-panel { position: fixed; right: 1.25rem; bottom: 5.25rem; z-index: 80; width: min(380px, calc(100vw - 2.5rem)); max-height: min(70vh, 680px); display: none; grid-template-rows: auto 1fr auto; border: 1px solid var(--line); border-radius: 1.25rem; overflow: hidden; background: rgba(255,255,255,.98); box-shadow: 0 28px 80px rgba(6,66,37,.22); }
        .chat-panel.open { display: grid; }
        .chat-head { display: flex; justify-content: space-between; gap: 1rem; padding: 1rem 1.05rem; background: linear-gradient(135deg, var(--green-deep), var(--green)); color: #fff; }
        .chat-head h3, .chat-head p { margin: 0; }
        .chat-head p { margin-top: .2rem; color: rgba(255,255,255,.8); font-size: .85rem; }
        .chat-close { position: static; width: 2.2rem; height: 2.2rem; padding: 0; background: rgba(255,255,255,.14); }
        .chat-log { display: grid; gap: .75rem; padding: 1rem; overflow: auto; background: radial-gradient(circle at top left, rgba(242,200,75,.10), transparent 12rem), #fbfcf8; }
        .chat-bubble { max-width: 85%; border-radius: 1rem; padding: .75rem .85rem; line-height: 1.55; white-space: pre-wrap; }
        .chat-bubble.user { margin-left: auto; background: linear-gradient(135deg, var(--green), #0a7a43); color: #fff; }
        .chat-bubble.bot { background: #fff; color: var(--text); border: 1px solid var(--line); }
        .chat-form { display: flex; gap: .6rem; padding: .9rem; border-top: 1px solid var(--line); background: #fff; }
        .chat-form textarea { min-height: 3rem; max-height: 8rem; width: 100%; border: 1px solid var(--line); border-radius: .9rem; padding: .72rem .9rem; font: inherit; }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            min-height: calc(100vh - 5.9rem);
        }

        .sidebar {
            position: fixed;
            top: 5.9rem;
            left: 0;
            width: clamp(15rem, 20vw, 18rem);
            height: calc(100dvh - 5.9rem);
            overflow-y: auto;
            padding: 1.15rem;
            border-right: 1px solid var(--line);
            background: rgba(255, 255, 255, 0.62);
            backdrop-filter: blur(10px);
        }

        .sidebar-card {
            display: grid;
            gap: 1rem;
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            padding: 1rem;
            background: linear-gradient(180deg, rgba(255,255,255,0.94), rgba(245,250,242,0.94));
            box-shadow: var(--shadow);
        }

        .sidebar-header {
            display: grid;
            gap: 0.65rem;
        }

        .sidebar-header img {
            width: 4rem;
            height: 4rem;
            object-fit: cover;
            border-radius: 1rem;
            background: #fff;
        }

        .sidebar-header h2,
        .sidebar-header p { margin: 0; }

        .sidebar-header h2 {
            color: var(--green-deep);
            font-size: 1.2rem;
        }

        .sidebar-header p {
            color: var(--muted);
            line-height: 1.55;
        }

        .nav-group {
            display: grid;
            gap: 0.45rem;
        }

        .nav-group-label {
            margin: 0.25rem 0 0;
            color: var(--muted);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .nav-link {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            border: 0;
            border-radius: 0.95rem;
            padding: 0.85rem 0.95rem;
            color: var(--green-deep);
            font: inherit;
            font-weight: 700;
            text-align: left;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .nav-link:hover,
        .nav-link:focus-visible,
        .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, var(--green), #0a7a43);
            transform: translateX(2px);
            outline: none;
        }

        main {
            margin-left: clamp(15rem, 20vw, 18rem);
            padding: clamp(1.1rem, 3vw, 2rem);
            min-height: calc(100dvh - 5.9rem);
            overflow-y: auto;
            width: calc(100% - clamp(15rem, 20vw, 18rem));
        }

        .shell {
            display: grid;
            gap: 1.25rem;
            max-width: 1400px;
        }

        .hero {
            display: grid;
            gap: 1rem;
            padding: clamp(1.1rem, 3vw, 1.7rem);
            border: 1px solid var(--line);
            border-radius: 1.4rem;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(245, 250, 242, 0.8));
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.42rem 0.7rem;
            border-radius: 999px;
            background: rgba(11, 107, 58, 0.1);
            color: var(--green);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 0.65rem 0 0;
            color: var(--green-deep);
            font-size: clamp(2rem, 4vw, 3.6rem);
            line-height: 1;
        }

        .hero p {
            max-width: 760px;
            margin: 0.85rem 0 0;
            color: var(--muted);
            line-height: 1.7;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .status-card,
        .panel,
        .stack-card {
            border: 1px solid var(--line);
            border-radius: 1.2rem;
            background: var(--panel);
            box-shadow: var(--shadow);
        }

        .status-card {
            display: grid;
            gap: 0.85rem;
            min-height: 10rem;
            padding: 1.2rem;
        }

        .status-card .label {
            color: var(--muted);
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .status-card .count {
            color: var(--green-deep);
            font-size: clamp(2rem, 4vw, 3.4rem);
            font-weight: 900;
            line-height: 0.92;
        }

        .status-card p { margin: 0; color: var(--muted); line-height: 1.55; }

        .status-card.warning { border-top: 0.45rem solid var(--gold); }
        .status-card.good { border-top: 0.45rem solid var(--green); }
        .status-card.danger { border-top: 0.45rem solid var(--red); }

        .panel { overflow: hidden; }

        .panel-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.15rem 1.2rem 0.95rem;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .panel-header h2 {
            margin: 0;
            color: var(--green-deep);
            font-size: clamp(1.25rem, 2vw, 1.7rem);
        }

        .panel-header p { margin: 0.35rem 0 0; color: var(--muted); }

        .panel-subtle { padding: 0 1.2rem 1.2rem; }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.94);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 0;
        }

        th, td {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            background: rgba(242, 200, 75, 0.14);
            color: var(--green-deep);
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 0.3rem 0.55rem;
            background: rgba(242, 200, 75, 0.22);
            color: var(--green-deep);
            font-size: 0.82rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .badge.good { background: rgba(11, 107, 58, 0.12); color: var(--green); }
        .badge.warn { background: rgba(230, 126, 34, 0.16); color: #b45c11; }
        .badge.danger { background: rgba(200, 40, 45, 0.13); color: var(--red); }

<<<<<<< HEAD
=======
        .stars { display: inline-flex; align-items: center; gap: 0.1rem; white-space: nowrap; }
        .star { color: var(--gold); font-size: 1rem; line-height: 1; }
        .star.empty { color: var(--line); }
        .rating-value { color: var(--text); font-weight: 600; font-size: 0.85rem; margin-left: 0.35rem; }
        .muted { color: var(--muted); }

>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
        .empty-state,
        .flash {
            border: 1px solid var(--line);
            border-radius: 1rem;
            padding: 1rem 1.05rem;
            background: rgba(255, 255, 255, 0.9);
        }

        .flash {
            color: var(--green-deep);
            background: rgba(242, 200, 75, 0.18);
            font-weight: 700;
        }

        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            padding: 1rem 1.2rem 1.2rem;
        }

        .toolbar input,
        .toolbar select {
            flex: 1 1 220px;
            min-width: 0;
        }

        input, select, textarea {
            min-height: 2.9rem;
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            padding: 0.72rem 0.9rem;
            background: rgba(255, 255, 255, 0.96);
            color: var(--text);
            font: inherit;
        }

        textarea {
            min-height: 7rem;
            resize: vertical;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.9rem;
            padding: 0.72rem 1rem;
            border-radius: 0.95rem;
            border: 1px solid transparent;
            font: inherit;
            font-weight: 800;
            white-space: nowrap;
        }

        .btn-green {
            background: linear-gradient(135deg, var(--green), #0a7a43);
            color: #fff;
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.92);
            color: var(--green-deep);
            border-color: var(--line);
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .profile-item {
            border: 1px solid var(--line);
            border-radius: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.96);
        }

        .profile-item span,
        .profile-item strong { display: block; }

        .profile-item span {
            margin-bottom: 0.35rem;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .profile-item strong { color: var(--green-deep); }

        .review-form {
            max-width: 760px;
            margin-top: 1rem;
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
        }

        .review-form h3 {
            margin: 0 0 0.5rem;
            color: var(--green-deep);
        }

        .notification-list {
            display: grid;
            gap: 0.85rem;
        }

        .notification-item {
            border: 1px solid var(--line);
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.96);
            padding: 1rem;
            box-shadow: var(--shadow);
        }

        .notification-item.unread {
            border-color: rgba(11, 107, 58, 0.35);
            background: rgba(242, 200, 75, 0.12);
        }

        .notification-item strong {
            display: block;
            color: var(--green-deep);
            margin-bottom: 0.3rem;
        }

        .notification-head {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }

        .notification-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .reserve-form {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .reserve-quantity {
            width: 5rem;
            border: 1px solid var(--line);
            border-radius: 0.85rem;
            padding: 0.7rem 0.8rem;
            font: inherit;
            color: var(--text);
            background: #fff;
        }

        .catalog-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        @media (max-width: 900px) {
            .layout { grid-template-columns: 1fr; }
            .sidebar {
                position: static;
                width: auto;
                height: auto;
                overflow-y: visible;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }
            main {
                margin-left: 0;
                min-height: auto;
                width: 100%;
            }
            .status-grid, .profile-grid { grid-template-columns: 1fr; }
            .topbar { align-items: flex-start; flex-direction: column; }
        }
        
        .error-box {
    border: 1px solid var(--line);
    border-radius: 1rem;
    padding: 1rem 1.05rem;
    color: var(--red);
    background: rgba(200, 40, 45, 0.08);
    margin-bottom: 1rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.85rem;
    margin-top: 0.8rem;
}

.field { display: grid; gap: 0.4rem; }

.field label {
    color: var(--green-deep);
    font-size: 0.9rem;
    font-weight: 700;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 1rem;
}
    </style>
</head>
<body>
    @php
        $page = $dashboardPage ?? 'dashboard';
        $isActive = fn (string $name) => $page === $name ? 'active' : '';
        $user = auth()->user();
        $announcements = collect($announcements ?? []);
        $books = collect($books ?? []);
        $reservations = collect($reservations ?? []);
        $borrowed = collect($borrowedBooks ?? []);
        $historyItems = collect($historyItems ?? []);
        $profile = $profile ?? [];
    @endphp

    <header class="topbar">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
            <span class="brand-copy">
                <strong>ISU Library System</strong>
                <span>Instructor dashboard</span>
            </span>
        </a>
        <div class="topbar-meta">
            <span class="pill">Role: Instructor</span>
            <span class="pill">Signed in as {{ $user->name ?? 'Instructor' }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log out</button>
            </form>
        </div>
    </header>

    <div class="layout">
        <aside class="sidebar" aria-label="Instructor navigation">
            <div class="sidebar-card">
                <div class="sidebar-header">
                    <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
                    <h2>Instructor Panel</h2>
                    <p>Access your library account and borrowing history.</p>
                </div>
                <nav class="nav-group">
                    <p class="nav-group-label">Workspace</p>
                    <a class="nav-link {{ $isActive('dashboard') }}" href="{{ route('dashboard.instructor') }}">Dashboard</a>
                    <a class="nav-link {{ $isActive('catalog') }}" href="{{ route('instructor.catalog') }}">Library Catalog</a>
                    <a class="nav-link {{ $isActive('reservations') }}" href="{{ route('instructor.reservations') }}">Book Reservation</a>
                    <a class="nav-link {{ $isActive('borrowed') }}" href="{{ route('instructor.borrowed') }}">Borrowed Books</a>
                    <a class="nav-link {{ $isActive('history') }}" href="{{ route('instructor.history') }}">Borrow History</a>
                    <a class="nav-link {{ $isActive('fines') }}" href="{{ route('instructor.fines') }}">Fines</a>
                    <a class="nav-link {{ $isActive('notifications') }}" href="{{ route('instructor.notifications') }}">Notifications</a>
                    <a class="nav-link {{ $isActive('profile') }}" href="{{ route('instructor.profile') }}">Profile</a>
                </nav>
            </div>
        </aside>

        <main>
            <div class="shell">
                @if (session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="flash" style="background: rgba(200, 40, 45, 0.10); color: var(--red);">{{ session('error') }}</div>
                @endif

                @if ($page === 'dashboard')
                    <section class="hero">
                        <span class="eyebrow">Instructor library access</span>
                        <h1>Welcome, {{ $user->name ?? 'Instructor' }}.</h1>
                        <p>Search the collection, monitor reservations, and keep track of borrowed books.</p>
                    </section>

                    <section class="status-grid" aria-label="Instructor summary">
                        <article class="status-card warning">
                            <span class="label">Pending Reservations</span>
                            <span class="count">{{ $pendingReservations ?? 0 }}</span>
                            <p>Books waiting for approval or pickup confirmation.</p>
                        </article>
                        <article class="status-card good">
                            <span class="label">Borrowed Books</span>
                            <span class="count">{{ $borrowedBooks ?? 0 }}</span>
                            <p>Books currently checked out under your account.</p>
                        </article>
                        <article class="status-card danger">
                            <span class="label">Overdue Books</span>
                            <span class="count">{{ $overdueBooks ?? 0 }}</span>
                            <p>Borrowed books that have passed their due date.</p>
                        </article>
                        <article class="status-card good">
                            <span class="label">Borrow Limit</span>
                            <span class="count">{{ $borrowedSlotsLeft ?? 0 }}/{{ $borrowLimit ?? 0 }}</span>
                            <p>{{ $borrowedSlotsLeft ?? 0 }} slot(s) left for borrowing right now.</p>
                        </article>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Announcements</h2>
                                <p>Notices published for instructors, plus public announcements.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            <div class="table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Message</th>
                                            <th>Audience</th>
                                            <th>Published</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($announcements as $announcement)
                                            <tr>
                                                <td><strong>{{ $announcement->title }}</strong></td>
                                                <td>{{ \Illuminate\Support\Str::limit($announcement->body, 120) }}</td>
                                                <td>{{ ucfirst($announcement->audience) }}</td>
                                                <td>{{ $announcement->published_at ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="empty-state">No announcements available.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                @elseif ($page === 'catalog')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Library Catalog</h2>
                                <p>Search books by ISBN, title, or author, then filter by genre.</p>
                            </div>
                        </div>
                        <form class="toolbar" method="GET" action="{{ route('instructor.catalog') }}">
                            <input name="search" type="search" value="{{ $search ?? '' }}" placeholder="Search ISBN, title, or author">
                            <select name="genre" aria-label="Filter by genre">
                                <option value="">All genres</option>
                                @foreach ($genres as $genre)
                                    <option value="{{ $genre }}" @selected(($selectedGenre ?? '') === $genre)>{{ $genre }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-green" type="submit">Filter</button>
                        </form>
                        <div class="panel-subtle">
                            @if (($books ?? collect())->isEmpty())
                                <div class="empty-state">No books matched your search.</div>
                            @else
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ISBN</th>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Genre</th>
                                                <th>Year Published</th>
                                                <th>Quantity</th>
                                                <th>Available</th>
                                                <th>Location</th>
                                                <th>Status</th>
<<<<<<< HEAD
=======
                                                <th>Rating</th>
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($books as $book)
                                                <tr>
                                                    <td>{{ $book['isbn'] }}</td>
                                                    <td>{{ $book['title'] }}</td>
                                                    <td>{{ $book['author'] }}</td>
                                                    <td>{{ $book['genre'] }}</td>
                                                    <td>{{ $book['year'] ?? 'N/A' }}</td>
                                                    <td>{{ $book['quantity'] }}</td>
                                                    <td>{{ $book['available_quantity'] ?? 0 }}</td>
                                                    <td>{{ $book['location'] ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge {{ ($book['available_quantity'] ?? 0) > 0 ? 'good' : 'danger' }}">
                                                            {{ $book['status'] }}
                                                        </span>
                                                    </td>
                                                    <td>
<<<<<<< HEAD
=======
                                                        @php $avg = $book['avg_rating'] ?? null; @endphp
                                                        @if ($avg)
                                                            <div class="stars">
                                                                @for ($s = 1; $s <= 5; $s++)
                                                                    <span class="star {{ $s <= round($avg) ? '' : 'empty' }}">★</span>
                                                                @endfor
                                                                <span class="rating-value">{{ number_format($avg, 1) }}</span>
                                                                <span class="muted">({{ $book['review_count'] ?? 0 }})</span>
                                                            </div>
                                                        @else
                                                            <span class="muted">—</span>
                                                        @endif
                                                    </td>
                                                    <td>
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
                                                        <div class="catalog-actions">
                                                            @if (!in_array($book['isbn'], $reservedIsbns ?? [], true))
                                                                 <form class="reserve-form" method="POST" action="{{ route('instructor.reserve', $book['isbn']) }}">
                                                                      @csrf
                                                                      <input class="reserve-quantity" name="quantity" type="number" min="1" max="{{ $book['available_quantity'] ?? 1 }}" value="1" aria-label="Copies to reserve">
                                                                      <button class="btn btn-green" type="submit">Reserve</button>
                                                                 </form>
                                                            @else
                                                                <span class="badge">Reserved</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Review Your Borrowed Books</h2>
                                <p>Leave a rating and short review for books you have borrowed.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            <form class="review-form" method="POST" id="instructorReviewForm" action="{{ route('instructor.review', 'ISBN-HERE') }}" onsubmit="this.action = this.action.replace('ISBN-HERE', encodeURIComponent(this.querySelector('[name=isbn]').value));">
                                @csrf
                                <h3>Submit Review</h3>
                                <div style="display:grid; gap:0.75rem;">
                                    <input id="reviewIsbn" name="isbn" type="text" placeholder="Enter ISBN to review" required>
                                    <input name="rating" type="number" min="1" max="5" placeholder="Rating 1-5" required>
                                    <textarea name="review" placeholder="Optional review"></textarea>
                                </div>
                                <div class="action-row" style="margin-top: 0.75rem;">
                                    <button class="btn btn-green" type="submit">Save Review</button>
                                </div>
                            </form>
                        </div>
                    </section>

                @elseif ($page === 'reservations')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Book Reservation</h2>
                                <p>View and manage your active reservations.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            @if (($reservations ?? collect())->isEmpty())
                                <div class="empty-state">No active reservations.</div>
                            @else
                                <div class="table-wrap">
                                    <table>
                                          <thead>
                                              <tr>
                                                  <th>ISBN</th>
                                                  <th>Title</th>
                                                  <th>Author</th>
                                                  <th>Genre</th>
                                                  <th>Copies</th>
                                                  <th>Reserved At</th>
                                                  <th>Action</th>
                                              </tr>
                                          </thead>
                                          <tbody>
                                            @foreach ($reservations as $reservation)
                                                <tr>
                                                    <td>{{ $reservation['isbn'] }}</td>
                                                     <td>{{ $reservation['title'] }}</td>
                                                     <td>{{ $reservation['author'] }}</td>
                                                     <td>{{ $reservation['genre'] }}</td>
                                                     <td>{{ $reservation['quantity'] ?? 1 }}</td>
                                                     <td>{{ $reservation['reserved_at'] }}</td>
                                                     <td>
                                                        <form method="POST" action="{{ route('instructor.reservations.cancel', $reservation['isbn']) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-green" type="submit">Cancel</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </section>

                @elseif ($page === 'borrowed')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Borrowed Books</h2>
                                <p>Books currently checked out or already returned.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            @if (($borrowedItems ?? collect())->isEmpty())
                                <div class="empty-state">No borrowed books found.</div>
                            @else
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ISBN</th>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Genre</th>
                                                <th>Year</th>
                                                <th>Borrowed</th>
                                                <th>Returned</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($borrowedItems as $item)
                                                <tr>
                                                    <td>{{ $item['isbn'] }}</td>
                                                    <td><strong>{{ $item['title'] }}</strong></td>
                                                    <td>{{ $item['author'] }}</td>
                                                    <td>{{ $item['genre'] }}</td>
                                                    <td>{{ $item['year'] }}</td>
                                                    <td>{{ $item['borrowed_date'] }}</td>
                                                    <td>{{ $item['returned_date'] }}</td>
                                                    <td><span class="badge {{ $item['status'] === 'Overdue' ? 'danger' : 'good' }}">{{ $item['status'] }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </section>

                @elseif ($page === 'history')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Borrow History</h2>
                                <p>All of your past and current borrowing records.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            @if (($historyItems ?? collect())->isEmpty())
                                <div class="empty-state">No borrow history found.</div>
                            @else
                                <div class="table-wrap">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>ISBN</th>
                                                <th>Title</th>
                                                <th>Author</th>
                                                <th>Genre</th>
                                                <th>Year</th>
                                                <th>Borrowed</th>
                                                <th>Returned</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($historyItems as $item)
                                                <tr>
                                                    <td>{{ $item['isbn'] }}</td>
                                                    <td><strong>{{ $item['title'] }}</strong></td>
                                                    <td>{{ $item['author'] }}</td>
                                                    <td>{{ $item['genre'] }}</td>
                                                    <td>{{ $item['year'] }}</td>
                                                    <td>{{ $item['borrowed_date'] }}</td>
                                                    <td>{{ $item['returned_date'] }}</td>
                                                    <td><span class="badge {{ $item['status'] === 'Overdue' ? 'danger' : 'good' }}">{{ $item['status'] }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </section>

                @elseif ($page === 'fines')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Fines</h2>
                                <p>Review unpaid and paid fines associated with your account.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            @if (($fines ?? collect())->isEmpty())
                                <div class="empty-state">You do not have any fines at the moment.</div>
                            @else
                                <div class="fines-list">
                                    @foreach ($fines as $fine)
                                        <article class="fine-card">
                                            <strong>{{ $fine->title }}</strong>
                                            <div>{{ $fine->isbn }}</div>
                                            <div class="fine-meta">
                                                <span>Amount: ₱{{ number_format($fine->amount, 2) }}</span>
                                                <span>Overdue: {{ $fine->overdue_days }} day(s)</span>
                                                <span>Status: {{ $fine->status }}</span>
                                                <span>Due: {{ $fine->due_at }}</span>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                @elseif ($page === 'notifications')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Notifications</h2>
                                <p>System alerts for reservations, fines, and announcements.</p>
                            </div>
                            <div class="action-row">
                                <form method="POST" action="{{ route('instructor.notifications.read') }}">
                                    @csrf
                                    <button class="btn btn-green" type="submit">Mark All Read</button>
                                </form>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            @if (($notifications ?? collect())->isEmpty())
                                <div class="empty-state">No notifications yet.</div>
                            @else
                                <div class="notification-list">
                                    @foreach ($notifications as $notif)
                                        <article class="notification-item {{ $notif->read_at ? '' : 'unread' }}">
                                            <div class="notification-head">
                                                <div>
                                                    <strong>{{ $notif->title }}</strong>
                                                    <p>{{ $notif->body }}</p>
                                                </div>
                                                <span class="badge {{ $notif->read_at ? 'good' : 'danger' }}">
                                                    {{ $notif->read_at ? 'Read' : 'Unread' }}
                                                </span>
                                            </div>
                                            <div class="notification-actions">
                                                @if (! $notif->read_at)
                                                    <form method="POST" action="{{ route('instructor.notifications.read.one', $notif->id) }}">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button class="btn btn-green" type="submit">Mark Read</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>

                @elseif ($page === 'profile')
                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Profile</h2>
                                <p>Your instructor account information.</p>
                            </div>
                        </div>
                        <div class="panel-subtle">
                            <section class="profile-grid" aria-label="Instructor profile information">
                                @foreach ($profile as $label => $value)
                                    <div class="profile-item">
                                        <span>{{ $label }}</span>
                                        <strong>{{ $value }}</strong>
                                    </div>
                                @endforeach
                            </section>
                        </div>
                    </section>

                    <section class="panel">
                        <div class="panel-header">
                            <div>
                                <h2>Change Password</h2>
                                <p>Update your account password.</p>
                            </div>
                        </div>
                        <div class="panel-subtle" style="padding-top: 1rem;">
                            @if (session('status'))
                                <div class="flash">{{ session('status') }}</div>
                            @endif
                            @if ($errors->any())
                                <div class="error-box">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif
                            <form method="POST" action="{{ route('instructor.profile.password') }}">
                                @csrf
                                @method('PATCH')
                                <div class="form-grid">
                                    <div class="field">
                                        <label>Current Password</label>
                                        <input name="current_password" type="password" required>
                                    </div>
                                    <div class="field">
                                        <label>New Password</label>
<<<<<<< HEAD
                                        <input id="instructor-password" name="password" type="password" required>
                                        <div class="strength-meter" aria-live="polite" style="margin-top:0.45rem;">
                                            <div style="height:6px; border-radius:999px; background:#e2e8f0; overflow:hidden;"><div id="instructor-strength-fill" style="height:100%; width:0; border-radius:999px; background:#ef4444; transition:all 0.2s ease;"></div></div>
                                            <div id="instructor-strength-text" style="font-size:0.82rem; color:#64748b; margin-top:0.35rem;">Enter a password</div>
                                        </div>
=======
                                        <input name="password" type="password" required>
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
                                    </div>
                                    <div class="field">
                                        <label>Confirm New Password</label>
                                        <input name="password_confirmation" type="password" required>
                                    </div>
                                </div>
                                <div class="hero-actions">
                                    <button class="btn btn-green" type="submit">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </section>
                @endif
            </div>
        </main>
    </div>

    <button class="chat-launcher" type="button" id="chatLauncher">Chat with AI</button>
    <div class="chat-panel" id="chatPanel" aria-hidden="true">
        <div class="chat-head">
            <div>
                <h3>AI Library Assistant</h3>
                <p>Ask about books, reservations, fines, or dashboard actions.</p>
            </div>
            <button class="chat-close" type="button" id="chatClose">&times;</button>
        </div>
        <div class="chat-log" id="chatLog">
            <div class="chat-bubble bot">Hi, I’m your library assistant. Ask me anything about the system.</div>
        </div>
        <form class="chat-form" id="chatForm">
            <textarea id="chatInput" placeholder="Type your question..." required></textarea>
            <button class="logout" type="button" id="chatNew" style="background: rgba(255,255,255,.92); color: var(--green-deep); border: 1px solid var(--line);">New Chat</button>
            <button class="logout" style="background: var(--green); border-color: transparent;" type="submit" id="chatSend">Send</button>
        </form>
    </div>
</body>
<script>
<<<<<<< HEAD
    const instructorPasswordInput = document.getElementById('instructor-password');
    const instructorStrengthFill = document.getElementById('instructor-strength-fill');
    const instructorStrengthText = document.getElementById('instructor-strength-text');

    if (instructorPasswordInput && instructorStrengthFill && instructorStrengthText) {
        const scorePassword = (value) => {
            let score = 0;
            if (!value) return 0;
            if (value.length >= 8) score += 1;
            if (/[A-Z]/.test(value)) score += 1;
            if (/[a-z]/.test(value)) score += 1;
            if (/\d/.test(value)) score += 1;
            if (/[^A-Za-z0-9]/.test(value)) score += 1;
            return score;
        };

        const renderInstructorStrength = () => {
            const score = scorePassword(instructorPasswordInput.value);
            const percent = Math.min(100, score * 20);
            const colors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#16a34a'];
            const labels = ['Very weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const index = Math.min(labels.length - 1, Math.max(0, score - 1));
            instructorStrengthFill.style.width = `${percent}%`;
            instructorStrengthFill.style.background = colors[index];
            instructorStrengthText.textContent = instructorPasswordInput.value ? labels[index] : 'Enter a password';
        };

        instructorPasswordInput.addEventListener('input', renderInstructorStrength);
        renderInstructorStrength();
    }
</script>
<script>
=======
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
    const chatPanel = document.getElementById('chatPanel');
    const chatLauncher = document.getElementById('chatLauncher');
    const chatClose = document.getElementById('chatClose');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatLog = document.getElementById('chatLog');
    const chatSend = document.getElementById('chatSend');
    const chatNew = document.getElementById('chatNew');
    const chatStateKey = 'librarya_instructor_chat_state';
    const appendChat = (text, role) => { const bubble = document.createElement('div'); bubble.className = `chat-bubble ${role}`; bubble.textContent = text; chatLog.appendChild(bubble); chatLog.scrollTop = chatLog.scrollHeight; };
    const saveChatState = () => {
        const messages = Array.from(chatLog.querySelectorAll('.chat-bubble')).map((bubble) => ({ role: bubble.classList.contains('user') ? 'user' : 'bot', text: bubble.textContent ?? '' }));
        localStorage.setItem(chatStateKey, JSON.stringify({ open: chatPanel.classList.contains('open'), messages }));
    };
    const restoreChatState = () => {
        try {
            const raw = localStorage.getItem(chatStateKey);
            if (!raw) return;
            const state = JSON.parse(raw);
            if (Array.isArray(state.messages) && state.messages.length) {
                chatLog.innerHTML = '';
                state.messages.forEach((item) => appendChat(item.text, item.role === 'user' ? 'user' : 'bot'));
            }
            if (state.open) toggleChat(true);
        } catch (error) { localStorage.removeItem(chatStateKey); }
    };
    const startNewChat = () => {
        chatLog.innerHTML = '';
        appendChat('Hi, I am your library assistant. Ask me anything about the system.', 'bot');
        chatPanel.classList.add('open');
        chatPanel.setAttribute('aria-hidden', 'false');
        localStorage.removeItem(chatStateKey);
        saveChatState();
    };
    const toggleChat = (open) => { chatPanel.classList.toggle('open', open); chatPanel.setAttribute('aria-hidden', open ? 'false' : 'true'); if (open) chatInput.focus(); saveChatState(); };
    chatLauncher?.addEventListener('click', () => toggleChat(!chatPanel.classList.contains('open')));
    chatClose?.addEventListener('click', () => toggleChat(false));
    chatNew?.addEventListener('click', startNewChat);
    chatForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;
        appendChat(message, 'user');
        chatInput.value = '';
        chatSend.disabled = true;
        appendChat('Thinking...', 'bot');
        const thinkingBubble = chatLog.lastElementChild;
        try {
<<<<<<< HEAD
            const response = await fetch("{{ route('chat.send') }}", {
=======
            const response = await fetch('{{ route('chat.send') }}', {
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                body: JSON.stringify({ message }),
            });
            const data = await response.json();
            thinkingBubble.textContent = data.reply ?? 'No reply received.';
            saveChatState();
        } catch (error) {
            thinkingBubble.textContent = 'Sorry, I could not reach the chat service.';
            saveChatState();
        } finally {
            chatSend.disabled = false;
            chatLog.scrollTop = chatLog.scrollHeight;
        }
    });

    restoreChatState();
</script>
</html>


