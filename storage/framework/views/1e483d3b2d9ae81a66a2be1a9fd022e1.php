<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard | ISU Library System</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/guest-dashboard.css')); ?>">
</head>
<body class="guest-body">
    <header class="guest-topbar">
        <a class="guest-brand" href="<?php echo e(route('dashboard.guest')); ?>">
            <img src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
            <span>
                <strong>ISU Library System</strong>
                <small>Guest dashboard</small>
            </span>
        </a>

        <div class="guest-actions">
            <a class="guest-button" href="<?php echo e(route('login.student')); ?>">Login</a>
            <a class="guest-button" href="<?php echo e(route('register.student.form')); ?>">Register</a>
            <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin: 0;">
                <?php echo csrf_field(); ?>
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
                <a class="primary-button" href="<?php echo e(route('guest.catalog')); ?>">Explore Catalog</a>
            </div>
            <div class="hero-icon">▤</div>
        </section>

        <section class="guest-stats">
            <article>
                <strong>Total Books</strong>
                <span><?php echo e(number_format($stats['books'] ?? 0)); ?></span>
                <p>Titles in the public catalog.</p>
            </article>
            <article>
                <strong>Available Books</strong>
                <span><?php echo e(number_format($stats['available_books'] ?? 0)); ?></span>
                <p>Titles ready to borrow.</p>
            </article>
            <article>
                <strong>Genres</strong>
                <span><?php echo e(number_format($stats['genres'] ?? 0)); ?></span>
                <p>Categories in the collection.</p>
            </article>
        </section>

        <section class="guest-panel">
            <h2>Library Announcements</h2>

            <?php $__empty_1 = true; $__currentLoopData = $announcements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $announcement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="announcement">
                    <h3><?php echo e($announcement->title); ?></h3>
                    <p><?php echo e($announcement->body); ?></p>
                    <small><?php echo e($announcement->published_at ?: $announcement->created_at); ?></small>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="muted">There are no public announcements right now.</p>
            <?php endif; ?>
        </section>

        <section class="guest-panel">
            <h2>Explore Catalog</h2>

            <form class="catalog-form" method="GET" action="<?php echo e(route('guest.catalog')); ?>">
                <input
                    type="search"
                    name="search"
                    value="<?php echo e($search); ?>"
                    placeholder="Search title, author, ISBN, or genre"
                >

                <select name="genre">
                    <option value="">All genres</option>
                    <?php $__currentLoopData = $genres; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $genre): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($genre); ?>" <?php if($selectedGenre === $genre): echo 'selected'; endif; ?>>
                            <?php echo e($genre); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                        <?php $__empty_1 = true; $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($book['isbn']); ?></td>
                                <td><?php echo e($book['title']); ?></td>
                                <td><?php echo e($book['author']); ?></td>
                                <td><?php echo e($book['genre']); ?></td>
                                <td><?php echo e($book['location'] ?: 'Not specified'); ?></td>
                                <td><?php echo e($book['available_quantity'] > 0 ? 'Available' : 'Unavailable'); ?></td>
                                <td><?php echo e($book['avg_rating'] ? number_format($book['avg_rating'], 1).' / 5' : 'Not rated'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7">No books matched your search.</td>
                            </tr>
                        <?php endif; ?>
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
</html><?php /**PATH C:\laragon\www\system integ\librarya_System\resources\views/Dashboard/guestdashboard.blade.php ENDPATH**/ ?>