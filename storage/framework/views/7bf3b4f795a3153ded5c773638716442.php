<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($role); ?> Login | ISU Library System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        :root {
            
            --isu-green: #0b6b3a;
            --isu-deep: #064225;
            --isu-gold: #f2c84b;
            --isu-red: #c8282d;
            
            
            --text-main: #1e293b;
            --text-muted: #64748b;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.5);
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 14px;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            min-height: 100dvh;
            overflow-x: hidden;
            
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(242, 200, 75, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(11, 107, 58, 0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(242, 200, 75, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(11, 107, 58, 0.25) 0px, transparent 50%);
            background-attachment: fixed;
        }

        .page {
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
        }

        
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.1rem clamp(1.25rem, 4vw, 3.2rem);
            background: rgba(255, 255, 255, 0.4);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.4);
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 1rem;
            text-decoration: none;
            color: inherit;
        }

        .brand img {
            width: 2.75rem;
            height: 2.75rem;
            object-fit: contain;
            border-radius: 50%;
            background: #fff;
            box-shadow: var(--shadow-sm);
        }

        .brand-title strong {
            display: block;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--isu-deep);
            letter-spacing: -0.02em;
        }

        .brand-title span {
            display: block;
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 500;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--isu-deep);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(11, 107, 58, 0.15);
            box-shadow: var(--shadow-sm);
            transition: all 0.2s ease;
        }

        .back-link:hover {
            background: var(--isu-deep);
            color: #ffffff;
            transform: translateX(-2px);
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(2rem, 5vw, 3.5rem) clamp(1rem, 5vw, 3rem);
        }

        
        .login-shell {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            max-width: 100%;
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .panel {
            padding: clamp(2rem, 4vw, 3.25rem);
        }

        
        .intro {
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            background: 
                linear-gradient(145deg, rgba(6, 66, 37, 0.92), rgba(11, 107, 58, 0.85)),
                url("<?php echo e(asset('picture/ISU.jpg')); ?>") center/cover no-repeat;
            background-blend-mode: multiply;
            color: #ffffff;
        }

        .intro::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100%; height: 100%;
            background: radial-gradient(circle at top right, rgba(242, 200, 75, 0.45), transparent 60%);
            pointer-events: none;
        }

        .intro-logo {
            width: 5.5rem;
            height: 5.5rem;
            object-fit: contain;
            border-radius: 50%;
            background: #ffffff;
            margin-bottom: 2.25rem;
            padding: 0.25rem;
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
            z-index: 2;
        }

        .intro h1 {
            font-size: clamp(2rem, 3.4vw, 3.4rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 1.1rem;
            z-index: 2;
        }

        .intro p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1rem;
            line-height: 1.5;
            max-width: 32rem;
            z-index: 2;
        }

        
        .form-panel {
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .role-pill {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--isu-deep);
            background: rgba(242, 200, 75, 0.25);
            border: 1px solid rgba(242, 200, 75, 0.6);
            border-radius: 30px;
            padding: 0.4rem 0.85rem;
            font-weight: 700;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.75rem;
        }

        h2 {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--isu-deep);
            letter-spacing: -0.02em;
            margin-bottom: 0.35rem;
        }

        .hint {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 1.75rem;
        }

        
        .field {
            margin-bottom: 1.1rem;
            position: relative;
        }

        .hidden {
            display: none;
        }

        label {
            display: block;
            margin-bottom: 0.35rem;
            color: var(--isu-deep);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 0.85rem;
            color: var(--text-muted);
            font-size: 1rem;
            transition: color 0.2s ease;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            min-height: 3rem;
            border: 1px solid rgba(15, 23, 42, 0.15);
            border-radius: 12px;
            padding: 0.5rem 0.75rem 0.5rem 2.25rem;
            font-family: inherit;
            font-size: 0.95rem;
            color: var(--text-main);
            background: #f8fafc;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            background: #ffffff;
            border-color: var(--isu-green);
            box-shadow: 0 0 0 4px rgba(11, 107, 58, 0.12);
            outline: none;
        }

        input:focus + i {
            color: var(--isu-green);
        }

        .error {
            margin-top: 0.4rem;
            color: var(--isu-red);
            font-size: 0.9rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }

        
        .options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin: 1.15rem 0 1.35rem;
            font-size: 0.9rem;
        }

        .check {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            color: var(--text-main);
            font-weight: 500;
            cursor: pointer;
        }

        
        .check input {
            accent-color: var(--isu-green);
            width: 1rem;
            height: 1rem;
            cursor: pointer;
        }

        .forgot {
            color: var(--isu-green);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .forgot:hover {
            color: var(--isu-deep);
            text-decoration: underline;
        }

        
        button {
            width: 100%;
            min-height: 3.4rem;
            border: 0;
            border-radius: 12px;
            background: var(--isu-green);
            color: #ffffff;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            box-shadow: 0 10px 25px -5px rgba(11, 107, 58, 0.3);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        button:hover {
            background: var(--isu-deep);
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(6, 66, 37, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        
        .note {
            margin-top: 1.5rem;
            padding: 1rem 1.1rem;
            background: #f8fafc;
            border-left: 4px solid var(--isu-gold);
            border-radius: 0 8px 8px 0;
            color: var(--text-muted);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .note i {
            color: #b48a12;
            margin-right: 0.25rem;
        }

        
        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
                min-height: auto;
                max-width: 520px;
            }

            .intro {
                min-height: 280px;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 1.5rem;
            }

            .intro-logo {
                margin-bottom: 1.25rem;
            }

            .intro p {
                max-width: 100%;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                flex-direction: column;
                gap: 1.1rem;
                text-align: center;
            }
            
            .back-link {
                width: 100%;
                justify-content: center;
            }

            main {
                padding: 1.5rem;
            }
        }

    </style>
</head>
<body>
    <?php
        $isGuest = strtolower($role) === 'guest';
        $isInstructor = strtolower($role) === 'instructor';
        $roleIdLabels = [
            'librarian' => 'Librarian ID',
            'instructor' => 'Instructor ID',
            'student' => 'Student ID',
        ];
        $roleIdExamples = [
            'librarian' => 'e.g., LIB001',
            'instructor' => 'e.g., INS001',
            'student' => 'e.g., STU001',
        ];
        $roleLoginNotes = [
            'librarian' => 'LIB001 / password',
            'instructor' => 'INS001 / password',
            'student' => 'STU001 / password',
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
            <a class="back-link" href="<?php echo e(route('home')); ?>">
                <i class="fa-solid fa-arrow-left-long"></i> Change role
            </a>
        </header>

        <main>
            <section class="login-shell">
                <div class="panel intro">
                    <img class="intro-logo" src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
                    <h1><?php echo e($role); ?> Library Login</h1>
                    <p><?php echo e($message); ?></p>
                </div>

                <div class="panel form-panel">
                    <span class="role-pill">
                        <i class="fa-solid fa-circle-user"></i> <?php echo e($role); ?> Account
                    </span>
                    <h2>Sign in</h2>
                    <p class="hint"><?php echo e($isGuest ? 'Use your registered mobile number to continue.' : 'Use your role-specific ID and password to continue.'); ?></p>

                    <form method="POST" action="<?php echo e($loginAction); ?>">
                        <?php echo csrf_field(); ?>
                        
                        <div class="field">
                            <label for="identifier"><?php echo e($isGuest ? 'Mobile Number' : ($roleIdLabels[strtolower($role)] ?? 'ID Number')); ?></label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-id-card"></i>
                                <input
                                    id="identifier"
                                    name="<?php echo e($isGuest ? 'mobile_number' : 'identifier'); ?>"
                                    type="text"
                                    value="<?php echo e(old($isGuest ? 'mobile_number' : 'identifier')); ?>"
                                    autocomplete="username"
                                    placeholder="<?php echo e($isGuest ? 'e.g., 09171234567' : ($roleIdExamples[strtolower($role)] ?? 'e.g., ID001')); ?>"
                                    required
                                >
                            </div>
                            <?php $__errorArgs = [$isGuest ? 'mobile_number' : 'identifier'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <?php if (! ($isGuest)): ?>
                            <div class="field">
                                <label for="password">Password</label>
                                <div class="input-wrapper">
                                    <i class="fa-solid fa-lock"></i>
                                    <input id="password" name="password" type="password" placeholder="Enter password" autocomplete="current-password" required>
                                </div>
                                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (! ($isGuest)): ?>
                            <div class="options">
                                <label class="check" for="remember">
                                    <input id="remember" name="remember" type="checkbox" <?php if(old('remember')): echo 'checked'; endif; ?>>
                                    Remember me
                                </label>
                                <a class="forgot" href="<?php echo e(route('password.request')); ?>">Forgot password?</a>
                            </div>
                        <?php endif; ?>

                        <?php if($isGuest === false && strtolower($role) === 'student'): ?>
                            <div class="field" style="margin-top: .75rem;">
                                <a class="forgot" href="<?php echo e(route('register.student.form')); ?>" style="display:inline-flex; align-items:center; gap:.35rem;">
                                    <i class="fa-solid fa-user-plus"></i> Create student account
                                </a>
                            </div>
                        <?php endif; ?>

                        <div class="field">
                            <div class="g-recaptcha" data-sitekey="<?php echo e(config('services.recaptcha.site_key')); ?>"></div>
                            <?php $__errorArgs = ['g-recaptcha-response'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <button type="submit">
                            <span>Login as <?php echo e($role); ?></span>
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        </button>
                    </form>


                    
                </div>
            </section>
        </main>
    </div>
</body>
</html>


<?php /**PATH /home/zeanmartin/public_html/miini.zeanmartin.online/librarya_System/resources/views/LoginPage/role-login.blade.php ENDPATH**/ ?>