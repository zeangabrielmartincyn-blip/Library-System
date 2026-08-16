<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($role); ?> Dashboard | ISU Library System</title>
    <style>
        :root {
            --isu-green: #0b6b3a;
            --isu-deep: #064225;
            --isu-gold: #f2c84b;
            --isu-red: #c8282d;
            --isu-cream: #fffdf4;
            --text: #163021;
            --muted: #607267;
            --line: rgba(11, 107, 58, 0.16);
        }

        * {
            box-sizing: border-box;
        }

        html {
            font-size: 14px;
        }

        body {
            margin: 0;
            min-height: 100dvh;
            font-family: Arial, Helvetica, sans-serif;
            font-size: clamp(0.98rem, 0.45vw + 0.85rem, 1.08rem);
            line-height: clamp(1.45, 1.1 + 0.2vw, 1.7);
            color: var(--text);
            background:
                radial-gradient(circle at top right, rgba(242, 200, 75, 0.26), transparent 26rem),
                radial-gradient(circle at top left, rgba(11, 107, 58, 0.12), transparent 24rem),
                linear-gradient(180deg, #fffdf4 0%, #f4f8f1 100%);
            overflow-x: hidden;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: clamp(0.8rem, 1.5vw, 1.2rem);
            padding: clamp(0.9rem, 2vw, 1.2rem) clamp(1rem, 4vw, 3.5rem);
            background: var(--isu-deep);
            color: #fff;
        }

        .brand,
        .logout {
            color: inherit;
            text-decoration: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand img {
            width: clamp(3rem, 5vw, 4rem);
            height: clamp(3rem, 5vw, 4rem);
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
        }

        .brand strong,
        .brand span {
            display: block;
        }

        .brand span {
            color: rgba(255, 255, 255, 0.76);
            font-size: clamp(0.8rem, 0.35vw + 0.72rem, 0.95rem);
            margin-top: 0.1rem;
        }

        .logout {
            border: 1px solid rgba(255, 255, 255, 0.42);
            border-radius: 999px;
            padding: clamp(0.55rem, 1vw, 0.8rem) clamp(0.8rem, 1.3vw, 1rem);
            font-weight: 700;
            background: rgba(255, 255, 255, 0.1);
            font: inherit;
            cursor: pointer;
        }

        .chat-launcher {
            position: fixed;
            right: clamp(0.9rem, 2vw, 1.25rem);
            bottom: clamp(0.9rem, 2vw, 1.25rem);
            z-index: 80;
            border: 0;
            border-radius: 999px;
            padding: clamp(0.85rem, 1.6vw, 1rem) clamp(0.95rem, 1.8vw, 1.15rem);
            background: linear-gradient(135deg, var(--isu-green), #0a7a43);
            color: #fff;
            font: inherit;
            font-weight: 900;
            box-shadow: 0 18px 36px rgba(11, 107, 58, 0.28);
            cursor: pointer;
        }

        .chat-panel {
            position: fixed;
            right: clamp(0.9rem, 2vw, 1.25rem);
            bottom: clamp(4.8rem, 7vw, 5.6rem);
            z-index: 80;
            width: min(24rem, calc(100vw - 2rem));
            max-height: min(72dvh, 42rem);
            display: none;
            grid-template-rows: auto 1fr auto;
            border: 1px solid var(--line);
            border-radius: 1.25rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 28px 80px rgba(6, 66, 37, 0.22);
        }

        .chat-panel.open { display: grid; }
        .chat-head { display: flex; justify-content: space-between; gap: 1rem; padding: clamp(0.95rem, 1.8vw, 1.05rem); background: linear-gradient(135deg, var(--isu-deep), var(--isu-green)); color: #fff; }
        .chat-head h3, .chat-head p { margin: 0; }
        .chat-head p { margin-top: .2rem; color: rgba(255,255,255,.8); font-size: clamp(0.78rem, 0.35vw + 0.7rem, 0.9rem); }
        .chat-close { border: 0; border-radius: 999px; width: clamp(2rem, 3.5vw, 2.2rem); height: clamp(2rem, 3.5vw, 2.2rem); background: rgba(255,255,255,.14); color: #fff; font: inherit; font-size: 1.2rem; font-weight: 900; cursor: pointer; }
        .chat-log { display: grid; gap: .75rem; padding: clamp(0.9rem, 1.6vw, 1rem); overflow: auto; background: radial-gradient(circle at top left, rgba(242,200,75,.10), transparent 12rem), #fbfcf8; }
        .chat-bubble { max-width: 85%; border-radius: 1rem; padding: clamp(0.7rem, 1.3vw, 0.85rem); line-height: clamp(1.45, 1.1 + 0.15vw, 1.65); white-space: pre-wrap; }
        .chat-bubble.user { margin-left: auto; background: linear-gradient(135deg, var(--isu-green), #0a7a43); color: #fff; }
        .chat-bubble.bot { background: #fff; color: var(--text); border: 1px solid var(--line); }
        .chat-form { display: flex; gap: .6rem; padding: clamp(0.8rem, 1.6vw, 0.95rem); border-top: 1px solid var(--line); background: #fff; }
        .chat-form textarea { min-height: clamp(2.8rem, 4vw, 3.2rem); max-height: 8rem; width: 100%; border: 1px solid var(--line); border-radius: .9rem; padding: .72rem .9rem; font: inherit; }

        .dashboard-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            min-height: calc(100dvh - clamp(4.75rem, 7vw, 6rem));
        }

        .sidebar {
            position: fixed;
            top: clamp(4.75rem, 7vw, 6rem);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100dvh - clamp(4.75rem, 7vw, 6rem));
            overflow-y: auto;
            padding: clamp(0.9rem, 2vw, 1.2rem);
            border-right: 1px solid var(--line);
            background: rgba(255, 253, 244, 0.9);
        }

        .sidebar-title {
            margin: 0 0 1rem;
            color: var(--isu-deep);
            font-weight: 800;
        }

        .sidebar-nav {
            display: grid;
            gap: 0.55rem;
        }

        .sidebar-nav a {
            display: block;
            padding: clamp(0.75rem, 1.2vw, 0.95rem) clamp(0.85rem, 1.2vw, 1rem);
            color: var(--isu-deep);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a:focus-visible,
        .sidebar-nav a.active {
            color: #fff;
            background: var(--isu-green);
            outline: none;
        }

        main {
            margin-left: var(--sidebar-width);
            padding: clamp(1.2rem, 4vw, 4rem);
            min-height: calc(100dvh - clamp(4.75rem, 7vw, 6rem));
            overflow-y: auto;
            width: calc(100% - var(--sidebar-width));
        }

        .welcome {
            display: grid;
            gap: clamp(0.9rem, 1.6vw, 1.2rem);
            max-width: 100%;
        }

        .welcome img {
            width: clamp(4rem, 7vw, 5rem);
            height: clamp(4rem, 7vw, 5rem);
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 12px 28px rgba(6, 66, 37, 0.16);
        }

        h1 {
            margin: 0;
            color: var(--isu-deep);
            font-size: clamp(2rem, 5vw, 4rem);
            line-height: clamp(1, 0.96 + 0.06vw, 1.1);
            letter-spacing: 0;
        }

        p {
            max-width: min(100%, 39rem);
            margin: 0;
            color: var(--muted);
            line-height: clamp(1.5, 1.15 + 0.15vw, 1.75);
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(14rem, 28vw, 18rem), 1fr));
            gap: clamp(0.85rem, 1.8vw, 1.1rem);
            margin-top: clamp(1.2rem, 3vw, 2rem);
            max-width: 100%;
        }

        .card {
            min-height: clamp(8.5rem, 14vw, 10rem);
            border: 1px solid var(--line);
            border-top: 0.35rem solid var(--isu-green);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.88);
            padding: clamp(0.95rem, 1.8vw, 1.1rem);
            box-shadow: 0 16px 36px rgba(6, 66, 37, 0.08);
        }

        .card:nth-child(2) {
            border-top-color: var(--isu-gold);
        }

        .card:nth-child(3) {
            border-top-color: var(--isu-red);
        }

        .card strong {
            display: block;
            color: var(--isu-deep);
            margin-bottom: 0.45rem;
        }

        .student-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(14rem, 26vw, 18rem), 1fr));
            gap: clamp(0.85rem, 1.8vw, 1.1rem);
            margin-top: clamp(1.2rem, 3vw, 2rem);
            max-width: 980px;
        }

        .stat-card {
            min-height: clamp(9rem, 16vw, 11rem);
            display: grid;
            align-content: space-between;
            gap: 1rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            padding: clamp(1rem, 2vw, 1.25rem);
            box-shadow: 0 16px 36px rgba(6, 66, 37, 0.08);
        }

        .stat-card strong {
            color: var(--isu-deep);
            font-size: clamp(0.95rem, 0.5vw + 0.8rem, 1.05rem);
        }

        .stat-card .count {
            color: var(--isu-green);
            font-size: clamp(2.25rem, 6vw, 4rem);
            font-weight: 900;
            line-height: 0.9;
        }

        .stat-card.warning .count {
            color: var(--isu-gold);
        }

        .stat-card.danger .count {
            color: var(--isu-red);
        }

        .stat-card p {
            font-size: clamp(0.88rem, 0.4vw + 0.78rem, 0.98rem);
        }

        .section-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
            max-width: 1120px;
            margin-bottom: 1rem;
        }

        .section-header h2 {
            margin: 0;
            color: var(--isu-deep);
            font-size: clamp(1.45rem, 3vw, 2.2rem);
        }

        .toolbar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(clamp(12rem, 20vw, 16rem), 1fr));
            gap: 0.75rem;
            max-width: 1120px;
            margin-bottom: 1rem;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 2.9rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 0.75rem 0.85rem;
            color: var(--text);
            background: #fff;
            font: inherit;
        }

        textarea {
            min-height: 7rem;
            resize: vertical;
        }

        input:focus,
        select:focus {
            border-color: var(--isu-green);
            box-shadow: 0 0 0 4px rgba(11, 107, 58, 0.12);
            outline: none;
        }

        .table-wrap {
            max-width: 1120px;
            overflow-x: auto;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 16px 36px rgba(6, 66, 37, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 100%;
        }

        th,
        td {
            padding: 0.9rem;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--isu-deep);
            background: rgba(242, 200, 75, 0.16);
            font-size: 0.88rem;
            text-transform: uppercase;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .action-button,
        .filter-button {
            min-height: 2.55rem;
            border: 0;
            border-radius: 8px;
            padding: 0.65rem 0.85rem;
            color: #fff;
            background: var(--isu-green);
            font: inherit;
            font-weight: 800;
            cursor: pointer;
            white-space: nowrap;
        }

        .action-button:hover,
        .filter-button:hover {
            background: var(--isu-deep);
        }

        .action-button.danger {
            background: var(--isu-red);
        }

        .action-button.danger:hover {
            background: #9f1f23;
        }

        .action-button:disabled {
            cursor: not-allowed;
            background: #9aa79f;
        }

        .badge {
            display: inline-flex;
            border-radius: 999px;
            padding: 0.35rem 0.55rem;
            color: var(--isu-deep);
            background: rgba(242, 200, 75, 0.24);
            font-size: 0.85rem;
            font-weight: 800;
        }

        .badge.good {
            color: #fff;
            background: var(--isu-green);
        }

        .badge.danger {
            color: #fff;
            background: var(--isu-red);
        }

        .stars { display: inline-flex; align-items: center; gap: 0.1rem; white-space: nowrap; }
        .star { color: var(--isu-gold); font-size: 1rem; line-height: 1; }
        .star.empty { color: var(--line); }
        .rating-value { color: var(--text); font-weight: 600; font-size: 0.85rem; margin-left: 0.35rem; }
        .muted { color: var(--muted); }

        .empty-state,
        .flash {
            max-width: 1120px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.88);
            padding: clamp(0.9rem, 1.8vw, 1rem);
            color: var(--muted);
        }

        .flash {
            margin-bottom: 1rem;
            color: var(--isu-deep);
            background: rgba(242, 200, 75, 0.2);
            font-weight: 700;
        }

        .profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
            max-width: min(100%, 47.5rem);
        }

        .student-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .profile-item {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            padding: clamp(0.9rem, 1.8vw, 1rem);
        }

        .profile-item span,
        .profile-item strong {
            display: block;
        }

        .profile-item span {
            margin-bottom: 0.35rem;
            color: var(--muted);
            font-size: clamp(0.82rem, 0.35vw + 0.74rem, 0.95rem);
        }

        .profile-item strong {
            color: var(--isu-deep);
        }

        .notification-list,
        .fines-list {
            display: grid;
            gap: 0.85rem;
            max-width: 1120px;
        }

        .notification-item,
        .fine-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            padding: clamp(0.9rem, 1.8vw, 1rem);
            box-shadow: 0 16px 36px rgba(6, 66, 37, 0.08);
        }

        .notification-item.unread {
            border-color: rgba(11, 107, 58, 0.35);
            background: rgba(242, 200, 75, 0.12);
        }

        .notification-item strong,
        .fine-card strong {
            display: block;
            color: var(--isu-deep);
            margin-bottom: 0.3rem;
        }

        .fine-card .fine-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.75rem;
            color: var(--muted);
            font-size: clamp(0.84rem, 0.35vw + 0.76rem, 0.95rem);
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

        .review-form {
            max-width: min(100%, 47.5rem);
            margin-top: 1rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.9);
            padding: clamp(0.9rem, 1.8vw, 1rem);
        }

        .review-form h3 {
            margin: 0 0 0.5rem;
            color: var(--isu-deep);
        }

        @media (max-width: 1024px) {
            .dashboard-layout {
                grid-template-columns: 1fr;
            }

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

            .cards,
            .student-stats,
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .sidebar-nav {
                grid-template-columns: 1fr;
            }

            .section-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .toolbar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php
        $hasPatronSidebar = in_array($role, ['Student', 'Instructor'], true);
        $roleRoutePrefix = strtolower($role);
        $activePage = $dashboardPage ?? $studentPage ?? 'dashboard';
        $isGuest = $role === 'Guest';
    ?>

    <header class="topbar">
        <a class="brand" href="<?php echo e(route('home')); ?>">
            <img src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
            <span>
                <strong>ISU Library System</strong>
                <span><?php echo e($role); ?> dashboard</span>
            </span>
        </a>
        <form method="POST" action="<?php echo e(route('logout')); ?>">
            <?php echo csrf_field(); ?>
            <button class="logout" type="submit">Log out</button>
        </form>
    </header>

    <div class="<?php echo e($hasPatronSidebar ? 'dashboard-layout' : ''); ?>">
        <?php if($hasPatronSidebar): ?>
            <aside class="sidebar" aria-label="<?php echo e($role); ?> navigation">
                <p class="sidebar-title"><?php echo e($role); ?> Menu</p>
                <nav class="sidebar-nav">
                    <a class="<?php echo e($activePage === 'dashboard' ? 'active' : ''); ?>" href="<?php echo e(route('dashboard.'.$roleRoutePrefix)); ?>">Dashboard</a>
                    <a class="<?php echo e($activePage === 'catalog' ? 'active' : ''); ?>" href="<?php echo e(route($roleRoutePrefix.'.catalog')); ?>">Library Catalog</a>
                    <a class="<?php echo e($activePage === 'reservations' ? 'active' : ''); ?>" href="<?php echo e(route($roleRoutePrefix.'.reservations')); ?>">Book Reservation</a>
                    <a class="<?php echo e($activePage === 'borrowed' ? 'active' : ''); ?>" href="<?php echo e(route($roleRoutePrefix.'.borrowed')); ?>">Borrowed Books</a>
                    <a class="<?php echo e($activePage === 'history' ? 'active' : ''); ?>" href="<?php echo e(route($roleRoutePrefix.'.history')); ?>">Borrow History</a>
                    <?php if($role === 'Student'): ?>
                        <a class="<?php echo e($activePage === 'fines' ? 'active' : ''); ?>" href="<?php echo e(route('student.fines')); ?>">Fines</a>
                        <a class="<?php echo e($activePage === 'notifications' ? 'active' : ''); ?>" href="<?php echo e(route('student.notifications')); ?>">Notifications</a>
                    <?php endif; ?>
                    <a class="<?php echo e($activePage === 'profile' ? 'active' : ''); ?>" href="<?php echo e(route($roleRoutePrefix.'.profile')); ?>">Profile</a>
                </nav>
            </aside>
        <?php endif; ?>

        <main>
            <?php if($hasPatronSidebar): ?>
                <?php if(session('status')): ?>
                    <div class="flash"><?php echo e(session('status')); ?></div>
                <?php endif; ?>

                <?php if($activePage === 'dashboard'): ?>
                    <?php
                        $pendingReservations = $pendingReservations ?? 0;
                        $borrowedBooks = $borrowedBooks ?? 0;
                        $overdueBooks = $overdueBooks ?? 0;
                        $announcements = collect($announcements);
                    ?>

                    <section class="welcome">
                        <img src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
                        <h1>Welcome, <?php echo e(auth()->user()->name ?? $role); ?>.</h1>
                        <p><?php echo e($message); ?></p>
                    </section>

                    <section class="student-stats" aria-label="Student library summary">
                        <div class="stat-card warning">
                            <strong>Pending Reservations</strong>
                            <span class="count"><?php echo e($pendingReservations); ?></span>
                            <p>Books waiting for approval or pickup confirmation.</p>
                        </div>
                        <div class="stat-card">
                            <strong>Borrowed Books</strong>
                            <span class="count"><?php echo e($borrowedBooks); ?></span>
                            <p>Books currently checked out under your account.</p>
                        </div>
                        <div class="stat-card danger">
                            <strong>Overdue Books</strong>
                            <span class="count"><?php echo e($overdueBooks); ?></span>
                            <p>Borrowed books that have passed their due date.</p>
                        </div>
                    </section>

                    <section class="section-header" style="margin-top: 2rem;">
                        <div>
                            <h2>Announcements</h2>
                            <p>Notices published for your role, plus public announcements.</p>
                        </div>
                    </section>

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
                                <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($announcement->title); ?></strong></td>
                                        <td><?php echo e(\Illuminate\Support\Str::limit($announcement->body, 120)); ?></td>
                                        <td><?php echo e(ucfirst($announcement->audience)); ?></td>
                                        <td><?php echo e($announcement->published_at ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="4">No announcements available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif($activePage === 'catalog'): ?>
                    <section class="section-header">
                        <div>
                            <h2>Library Catalog</h2>
                            <p>Search books by ISBN, title, or author, then filter by genre.</p>
                        </div>
                    </section>

                    <form class="toolbar" method="GET" action="<?php echo e(route($roleRoutePrefix.'.catalog')); ?>">
                        <input name="search" type="search" value="<?php echo e($search ?? ''); ?>" placeholder="Search ISBN, title, or author">
                        <select name="genre" aria-label="Filter by genre">
                            <option value="">All genres</option>
                            <?php $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($genre); ?>" <?php if(($selectedGenre ?? '') === $genre): echo 'selected'; endif; ?>><?php echo e($genre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button class="filter-button" type="submit">Filter</button>
                    </form>

                    <?php if($books->isEmpty()): ?>
                        <div class="empty-state">No books matched your search.</div>
                    <?php else: ?>
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
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Rating</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($book['isbn']); ?></td>
                                            <td><?php echo e($book['title']); ?></td>
                                            <td><?php echo e($book['author']); ?></td>
                                            <td><?php echo e($book['genre']); ?></td>
                                            <td><?php echo e($book['year']); ?></td>
                                            <td><?php echo e($book['quantity']); ?></td>
                                            <td><?php echo e($book['location'] ?? 'N/A'); ?></td>
                                            <td><span class="badge <?php echo e($book['status'] === 'Available' ? 'good' : ''); ?>"><?php echo e($book['status']); ?></span></td>
                                            <td>
                                                <?php $avg = $book['avg_rating'] ?? null; ?>
                                                <?php if($avg): ?>
                                                    <div class="stars">
                                                        <?php for($s = 1; $s <= 5; $s++): ?>
                                                            <span class="star <?php echo e($s <= round($avg) ? '' : 'empty'); ?>">★</span>
                                                        <?php endfor; ?>
                                                        <span class="rating-value"><?php echo e(number_format($avg, 1)); ?></span>
                                                        <span class="muted">(<?php echo e($book['review_count'] ?? 0); ?>)</span>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                    <form method="POST" action="<?php echo e(route($roleRoutePrefix.'.reserve', $book['isbn'])); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <button class="action-button" type="submit" <?php if($book['quantity'] < 1 || in_array($book['isbn'], $reservedIsbns ?? [], true)): echo 'disabled'; endif; ?>>
                                                        <?php echo e(in_array($book['isbn'], $reservedIsbns ?? [], true) ? 'Reserved' : 'Reserve'); ?>

                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <section class="review-form">
                        <h3>Submit a Review</h3>
                        <p>Enter the ISBN of a book you have borrowed and share your rating.</p>
                        <form method="POST" action="#" id="studentReviewForm">
                            <?php echo csrf_field(); ?>
                            <div class="toolbar" style="grid-template-columns: repeat(auto-fit, minmax(clamp(12rem, 20vw, 16rem), 1fr)); max-width: min(100%, 47.5rem); margin-bottom: 0.85rem;">
                                <input id="reviewIsbn" name="isbn" type="text" placeholder="ISBN" required>
                                <select id="reviewRating" name="rating" required>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Good</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="1">1 - Poor</option>
                                </select>
                            </div>
                            <div style="max-width: min(100%, 47.5rem);">
                                <textarea id="reviewBody" name="review" placeholder="Write your review here..."></textarea>
                            </div>
                            <div class="student-actions">
                                <button class="filter-button" type="submit" id="reviewSubmitBtn">Submit Review</button>
                            </div>
                        </form>
                    </section>
                <?php elseif($activePage === 'reservations'): ?>
                    <section class="section-header">
                        <div>
                            <h2>Book Reservation</h2>
                            <p>View and cancel your current reserved books.</p>
                        </div>
                    </section>

                    <?php if($reservations->isEmpty()): ?>
                        <div class="empty-state">You do not have reserved books yet.</div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>ISBN</th>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $reservations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td><?php echo e($book['isbn']); ?></td>
                                            <td><?php echo e($book['title']); ?></td>
                                            <td><?php echo e($book['author']); ?></td>
                                            <td>
                                                <form method="POST" action="<?php echo e(route($roleRoutePrefix.'.reservations.cancel', $book['isbn'])); ?>">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button class="action-button danger" type="submit">Cancel Reservation</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php elseif($activePage === 'borrowed'): ?>
                    <section class="section-header">
                        <div>
                            <h2>Borrowed Books</h2>
                            <p>Check your active borrowed books and due status.</p>
                        </div>
                    </section>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ISBN</th>
                                    <th>Title</th>
                                    <th>Borrowed Date</th>
                                    <th>Returned Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $borrowedItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($item['isbn']); ?></td>
                                        <td><?php echo e($item['title']); ?></td>
                                        <td><?php echo e($item['borrowed_date']); ?></td>
                                        <td><?php echo e($item['returned_date']); ?></td>
                                        <td><span class="badge <?php echo e($item['status'] === 'Overdue' ? 'danger' : 'good'); ?>"><?php echo e($item['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif($activePage === 'history'): ?>
                    <section class="section-header">
                        <div>
                            <h2>Borrow History</h2>
                            <p>Review books you have borrowed, including returned and overdue records.</p>
                        </div>
                    </section>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ISBN</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Genre</th>
                                    <th>Year Published</th>
                                    <th>Borrowed Date</th>
                                    <th>Returned Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $historyItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td><?php echo e($item['isbn']); ?></td>
                                        <td><?php echo e($item['title']); ?></td>
                                        <td><?php echo e($item['author']); ?></td>
                                        <td><?php echo e($item['genre']); ?></td>
                                        <td><?php echo e($item['year']); ?></td>
                                        <td><?php echo e($item['borrowed_date']); ?></td>
                                        <td><?php echo e($item['returned_date']); ?></td>
                                        <td><span class="badge <?php echo e($item['status'] === 'Overdue' ? 'danger' : 'good'); ?>"><?php echo e($item['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php elseif($activePage === 'profile'): ?>
                    <section class="section-header">
                        <div>
                            <h2>Profile</h2>
                            <p>Review and update your account information.</p>
                        </div>
                    </section>

                    <section class="profile-grid" aria-label="Student profile information">
                        <?php $__currentLoopData = $profile; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="profile-item">
                                <span><?php echo e($label); ?></span>
                                <strong><?php echo e($value); ?></strong>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </section>

                    <?php if($role === 'Instructor'): ?>
                        <div class="review-form">
                            <h3>Update Profile</h3>
                            <form method="POST" action="<?php echo e(route($roleRoutePrefix.'.profile.update')); ?>" style="display:grid; gap:0.75rem;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <input type="text" name="name" value="<?php echo e(old('name', $profileForm['name'] ?? auth()->user()->name)); ?>" placeholder="Full name">
                                <input type="email" name="email" value="<?php echo e(old('email', $profileForm['email'] ?? auth()->user()->email)); ?>" placeholder="Email address">
                                <input type="text" name="mobile_number" value="<?php echo e(old('mobile_number', $profileForm['mobile_number'] ?? auth()->user()->mobile_number)); ?>" placeholder="Mobile number">
                                <button class="filter-button" type="submit">Save Profile</button>
                            </form>
                        </div>

                        <div class="review-form">
                            <h3>Change Password</h3>
                            <form method="POST" action="<?php echo e(route($roleRoutePrefix.'.profile.password')); ?>" style="display:grid; gap:0.75rem;">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PATCH'); ?>
                                <input type="password" name="current_password" placeholder="Current password">
                                <input id="role-password" type="password" name="password" placeholder="New password">
                                <div class="strength-meter" aria-live="polite" style="margin-top:0.35rem;">
                                    <div style="height:6px; border-radius:999px; background:#e2e8f0; overflow:hidden;"><div id="role-strength-fill" style="height:100%; width:0; border-radius:999px; background:#ef4444; transition:all 0.2s ease;"></div></div>
                                    <div id="role-strength-text" style="font-size:0.82rem; color:#64748b; margin-top:0.35rem;">Enter a password</div>
                                </div>
                                <input type="password" name="password_confirmation" placeholder="Confirm new password">
                                <button class="filter-button" type="submit">Update Password</button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php elseif($activePage === 'fines'): ?>
                    <section class="section-header">
                        <div>
                            <h2>Fines</h2>
                            <p>Review unpaid and paid fines associated with your account.</p>
                        </div>
                    </section>

                    <?php if(($fines ?? collect())->isEmpty()): ?>
                        <div class="empty-state">You do not have any fines at the moment.</div>
                    <?php else: ?>
                        <div class="fines-list">
                            <?php $__currentLoopData = $fines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <article class="fine-card">
                                    <strong><?php echo e($fine->title); ?></strong>
                                    <div><?php echo e($fine->isbn); ?></div>
                                    <div class="fine-meta">
                                        <span>Amount: ₱<?php echo e(number_format($fine->amount, 2)); ?></span>
                                        <span>Overdue: <?php echo e($fine->overdue_days); ?> day(s)</span>
                                        <span>Status: <?php echo e($fine->status); ?></span>
                                        <span>Due: <?php echo e($fine->due_at); ?></span>
                                    </div>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                <?php elseif($activePage === 'notifications'): ?>
                    <section class="section-header">
                        <div>
                            <h2>Notifications</h2>
                            <p>System alerts for reservations, fines, and announcements.</p>
                        </div>
                        <div class="student-actions">
                            <form method="POST" action="<?php echo e(route('student.notifications.read')); ?>">
                                <?php echo csrf_field(); ?>
                                <button class="filter-button" type="submit">Mark All Read</button>
                            </form>
                        </div>
                    </section>

                    <?php if(($notifications ?? collect())->isEmpty()): ?>
                        <div class="empty-state">No notifications yet.</div>
                    <?php else: ?>
                        <div class="notification-list">
                            <?php $__currentLoopData = $notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <article class="notification-item <?php echo e($notif->read_at ? '' : 'unread'); ?>">
                                    <div class="notification-head">
                                        <div>
                                            <strong><?php echo e($notif->title); ?></strong>
                                            <p><?php echo e($notif->body); ?></p>
                                        </div>
                                        <span class="badge <?php echo e($notif->read_at ? 'good' : 'danger'); ?>">
                                            <?php echo e($notif->read_at ? 'Read' : 'Unread'); ?>

                                        </span>
                                    </div>
                                    <p class="muted" style="margin-top:0.5rem;"><?php echo e($notif->created_at); ?></p>
                                    <?php if(! $notif->read_at): ?>
                                        <div class="notification-actions">
                                            <form method="POST" action="<?php echo e(route('student.notifications.read.one', $notif->id)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <button class="filter-button" type="submit">Mark Read</button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if($isGuest): ?>
                    <?php
                        $books = collect($books);
                        $announcements = collect($announcements);
                        $featuredBooks = collect($featuredBooks);
                        $search = $search ?? '';
                        $selectedGenre = $selectedGenre ?? '';
                    ?>

                    <section class="welcome">
                        <img src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
                        <h1>Welcome, <?php echo e(auth()->user()->name ?? $role); ?>.</h1>
                        <p><?php echo e($message); ?></p>
                    </section>

                    <section class="student-stats" aria-label="Guest library summary">
                        <div class="stat-card">
                            <strong>Book Catalog</strong>
                            <span class="count"><?php echo e(number_format((int) ($stats['books'] ?? 0))); ?></span>
                            <p>Browse the full public catalog with search and genre filtering.</p>
                        </div>
                        <div class="stat-card warning">
                            <strong>Available Now</strong>
                            <span class="count"><?php echo e(number_format((int) ($stats['available_books'] ?? 0))); ?></span>
                            <p>Items currently open for viewing and future borrowing.</p>
                        </div>
                        <div class="stat-card danger">
                            <strong>Genres</strong>
                            <span class="count"><?php echo e(number_format((int) ($stats['genres'] ?? 0))); ?></span>
                            <p>Discover books across programming, science, and related topics.</p>
                        </div>
                    </section>


                    <form class="toolbar" method="GET" action="<?php echo e(route($roleRoutePrefix.'.catalog')); ?>">
                        <input name="search" type="search" value="<?php echo e($search); ?>" placeholder="Search ISBN, title, or author">
                        <select name="genre" aria-label="Filter by genre">
                            <option value="">All genres</option>
                            <?php $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($genre); ?>" <?php if($selectedGenre === $genre): echo 'selected'; endif; ?>><?php echo e($genre); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button class="filter-button" type="submit">Search Books</button>
                    </form>

                    <h2 style="margin-top: 2rem; color: var(--isu-deep);">Public Books</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>ISBN</th>
                                    <th>Title</th>
                                    <th>Author</th>
                                    <th>Genre</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($book['isbn']); ?></td>
                                        <td><?php echo e($book['title']); ?></td>
                                        <td><?php echo e($book['author']); ?></td>
                                        <td><?php echo e($book['genre']); ?></td>
                                        <td><?php echo e($book['location'] ?? 'N/A'); ?></td>
                                        <td><span class="badge <?php echo e(($book['available_quantity'] ?? 0) > 0 ? 'good' : 'danger'); ?>"><?php echo e($book['status']); ?></span></td>
                                        <td>
                                            <?php $avg = $book['avg_rating'] ?? null; ?>
                                            <?php if($avg): ?>
                                                <div class="stars">
                                                    <?php for($s = 1; $s <= 5; $s++): ?>
                                                        <span class="star <?php echo e($s <= round($avg) ? '' : 'empty'); ?>">★</span>
                                                    <?php endfor; ?>
                                                    <span class="rating-value"><?php echo e(number_format($avg, 1)); ?></span>
                                                    <span class="muted">(<?php echo e($book['review_count'] ?? 0); ?>)</span>
                                                </div>
                                            <?php else: ?>
                                                <span class="muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="7">No public books found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                   

                    <h2 style="margin-top: 2rem; color: var(--isu-deep);">Announcements</h2>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>Audience</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($announcement->title); ?></td>
                                        <td><?php echo e($announcement->body); ?></td>
                                        <td><?php echo e(ucfirst($announcement->audience)); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="3">No announcements available.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <section class="welcome">
                        <img src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
                        <h1>Welcome, <?php echo e(auth()->user()->name ?? $role); ?>.</h1>
                        <p><?php echo e($message); ?></p>
                    </section>

                    <section class="cards" aria-label="Dashboard modules">
                        <div class="card">
                            <strong>Book Records</strong>
                            <p>Review catalog items, availability, and library collection updates.</p>
                        </div>
                        <div class="card">
                            <strong>Borrowing</strong>
                            <p>Track requests, checkouts, returns, reservations, and due dates.</p>
                        </div>
                        <div class="card">
                            <strong>Reports</strong>
                            <p>View library activity summaries and account-related notifications.</p>
                        </div>
                    </section>
                <?php endif; ?>
            <?php endif; ?>
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
            <button class="logout" type="button" id="chatNew" style="background: rgba(255,255,255,.92); color: var(--isu-deep); border: 1px solid var(--line);">New Chat</button>
            <button class="logout" style="background: var(--isu-green); border-color: transparent;" type="submit" id="chatSend">Send</button>
        </form>
    </div>
</body>
<script>
    const reviewForm = document.getElementById('studentReviewForm');
    if (reviewForm) {
        reviewForm.addEventListener('submit', (event) => {
            event.preventDefault();
            const isbn = document.getElementById('reviewIsbn').value.trim();
            if (!isbn) {
                return;
            }

            reviewForm.action = `<?php echo e(url('/dashboard/student/catalog')); ?>/${encodeURIComponent(isbn)}/review`;
            reviewForm.submit();
        });
    }

    const chatPanel = document.getElementById('chatPanel');
    const chatLauncher = document.getElementById('chatLauncher');
    const chatClose = document.getElementById('chatClose');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatLog = document.getElementById('chatLog');
    const chatSend = document.getElementById('chatSend');
    const chatNew = document.getElementById('chatNew');
    const chatStateKey = 'librarya_guest_chat_state';
    const saveChatState = () => {
        const messages = Array.from(chatLog.querySelectorAll('.chat-bubble')).map((bubble) => ({
            role: bubble.classList.contains('user') ? 'user' : 'bot',
            text: bubble.textContent ?? '',
        }));
        localStorage.setItem(chatStateKey, JSON.stringify({
            open: chatPanel.classList.contains('open'),
            messages,
        }));
    };
    const restoreChatState = () => {
        try {
            const raw = localStorage.getItem(chatStateKey);
            if (!raw) return;
            const state = JSON.parse(raw);
            if (Array.isArray(state.messages) && state.messages.length > 0) {
                chatLog.innerHTML = '';
                state.messages.forEach((item) => appendChat(item.text, item.role === 'user' ? 'user' : 'bot'));
            }
            if (state.open) {
                toggleChat(true);
            }
        } catch (error) {
            localStorage.removeItem(chatStateKey);
        }
    };
    const startNewChat = () => {
        chatLog.innerHTML = '';
        appendChat('Hi, I am your library assistant. Ask me anything about the system.', 'bot');
        chatPanel.classList.add('open');
        chatPanel.setAttribute('aria-hidden', 'false');
        localStorage.removeItem(chatStateKey);
        saveChatState();
    };
    const appendChat = (text, role) => {
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${role}`;
        bubble.textContent = text;
        chatLog.appendChild(bubble);
        chatLog.scrollTop = chatLog.scrollHeight;
    };

    const toggleChat = (open) => {
        chatPanel.classList.toggle('open', open);
        chatPanel.setAttribute('aria-hidden', open ? 'false' : 'true');
        if (open) chatInput.focus();
        saveChatState();
    };

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
            const response = await fetch("<?php echo e(route('chat.send')); ?>", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                },
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

    const rolePasswordInput = document.getElementById('role-password');
    const roleStrengthFill = document.getElementById('role-strength-fill');
    const roleStrengthText = document.getElementById('role-strength-text');

    if (rolePasswordInput && roleStrengthFill && roleStrengthText) {
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

        const renderRoleStrength = () => {
            const score = scorePassword(rolePasswordInput.value);
            const percent = Math.min(100, score * 20);
            const colors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#16a34a'];
            const labels = ['Very weak', 'Weak', 'Fair', 'Good', 'Strong'];
            const index = Math.min(labels.length - 1, Math.max(0, score - 1));
            roleStrengthFill.style.width = `${percent}%`;
            roleStrengthFill.style.background = colors[index];
            roleStrengthText.textContent = rolePasswordInput.value ? labels[index] : 'Enter a password';
        };

        rolePasswordInput.addEventListener('input', renderRoleStrength);
        renderRoleStrength();
    }

    restoreChatState();
</script>
</html>


<?php /**PATH C:\laragon\www\system integ\librarya_System\resources\views/Dashboard/role-dashboard.blade.php ENDPATH**/ ?>