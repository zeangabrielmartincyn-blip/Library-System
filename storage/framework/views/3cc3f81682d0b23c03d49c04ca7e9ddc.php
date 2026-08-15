<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ISU Library System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            
            --isu-green: #0b6b3a;
            --isu-deep: #064225;
            --isu-gold: #f2c84b;
            --isu-red: #c8282d;
            
            
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.75);
            --glass-border: rgba(255, 255, 255, 0.5);
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(242, 200, 75, 0.3) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(11, 107, 58, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(242, 200, 75, 0.2) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(11, 107, 58, 0.2) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem clamp(1.5rem, 5vw, 4rem);
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--glass-border);
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
        }

        .brand img {
            width: 3.5rem;
            height: 3.5rem;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            box-shadow: var(--shadow-sm);
        }

        .brand-title {
            display: flex;
            flex-direction: column;
        }

        .brand-title strong {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--isu-deep);
            letter-spacing: -0.02em;
        }

        .brand-title span {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--isu-deep);
            background: rgba(242, 200, 75, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            border: 1px solid rgba(242, 200, 75, 0.5);
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem clamp(1rem, 5vw, 4rem);
        }

        
        .content-card {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            max-width: 100%;
            width: 100%;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: clamp(2rem, 5vw, 4rem);
            box-shadow: var(--shadow-lg);
        }

        .hero {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .hero-logo {
            width: 6rem;
            height: 6rem;
            margin-bottom: 1.5rem;
            border-radius: 50%;
            box-shadow: 0 10px 20px rgba(11, 107, 58, 0.15);
        }

        h1 {
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 800;
            line-height: 1.1;
            letter-spacing: -0.03em;
            color: var(--isu-deep);
            margin-bottom: 1rem;
        }

        .hero p {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 90%;
        }

        .roles {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: center;
        }

        
        .role-button {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.1rem 1.5rem;
            text-decoration: none;
            background: #ffffff;
            border: 1px solid rgba(11, 107, 58, 0.1);
            border-radius: 12px;
            color: var(--text-main);
            font-weight: 600;
            font-size: 1.05rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .role-button .icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            background: rgba(11, 107, 58, 0.1);
            color: var(--isu-green);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .role-button .arrow {
            margin-left: auto;
            color: var(--text-muted);
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }

        
        .role-button:hover,
        .role-button:focus-visible {
            transform: translateY(-4px);
            border-color: var(--isu-green);
            box-shadow: 0 12px 20px -8px rgba(11, 107, 58, 0.3);
            outline: none;
        }

        .role-button:hover .icon-wrapper {
            background: var(--isu-green);
            color: #ffffff;
        }

        .role-button:hover .arrow {
            opacity: 1;
            transform: translateX(0);
            color: var(--isu-green);
        }

        footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(10px);
            border-top: 1px solid var(--glass-border);
        }

        
        @media (max-width: 900px) {
            .content-card {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 2rem;
            }
            
            .hero {
                text-align: center;
                align-items: center;
            }

            .hero p {
                max-width: 100%;
            }
        }

        @media (min-width: 901px) {
            .content-card {
                width: min(1200px, calc(100vw - 4rem));
            }
        }

        @media (max-width: 600px) {
            .topbar {
                flex-direction: column;
                gap: 1rem;
                align-items: center;
                text-align: center;
            }
            .brand {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php
        $roles = [
            [
                'name' => 'Student',
                'route' => route('login.student'),
                'icon' => 'fa-user-graduate'
            ],
            [
                'name' => 'Instructor',
                'route' => route('login.instructor'),
                'icon' => 'fa-chalkboard-user'
            ],
            [
                'name' => 'Librarian',
                'route' => route('login.librarian'),
                'icon' => 'fa-book-open-reader'
            ],
            [
                'name' => 'Guest',
                'route' => route('login.guest'),
                'icon' => 'fa-user-clock'
            ],
        ];
    ?>

    <div class="page">
        <header class="topbar">
            <a class="brand" href="<?php echo e(route('home')); ?>" aria-label="ISU Library System home">
                <img src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
                <span class="brand-title">
                    <strong>ISU Library System</strong>
                    <span>BookTrack access portal</span>
                </span>
            </a>
            <div class="status">Choose your role to continue</div>
        </header>

        <main>
            <div class="content-card">
                <section class="hero">
                    <img class="hero-logo" src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
                    <h1>Welcome to the ISU Library</h1>
                    <p>Your gateway to knowledge. Please select your account type to browse, reserve, and borrow books securely.</p>
                </section>

                <section class="roles" aria-label="Library roles">
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="role-button" href="<?php echo e($role['route']); ?>">
                            <div class="icon-wrapper">
                                <i class="fa-solid <?php echo e($role['icon']); ?>"></i>
                            </div>
                            <span><?php echo e($role['name']); ?></span>
                            <i class="fa-solid fa-chevron-right arrow"></i>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </section>
            </div>
        </main>

        <footer>
            <small>&copy; <?php echo e(date('Y')); ?> Isabela State University Library System</small>
        </footer>
    </div>
</body>
</html>

<?php /**PATH C:\laragon\www\system integ\librarya_System\resources\views/index.blade.php ENDPATH**/ ?>