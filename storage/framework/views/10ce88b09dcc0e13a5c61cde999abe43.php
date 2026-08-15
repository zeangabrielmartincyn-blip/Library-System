<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Student Account | ISU Library System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --isu-green: #0b6b3a;
            --isu-deep: #064225;
            --isu-gold: #f2c84b;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --bg: #f8fafc;
        }
        * { box-sizing: border-box; margin:0; padding:0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text-main); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:2rem; }
        .card { width:min(100%, 540px); background:#fff; border-radius:24px; box-shadow:0 20px 45px rgba(0,0,0,.08); padding:2rem; }
        .title { font-size:1.8rem; font-weight:800; color:var(--isu-deep); margin-bottom:.5rem; }
        .subtitle { color:var(--text-muted); margin-bottom:1.25rem; }
        .field { margin-bottom:1rem; }
        label { display:block; font-weight:600; margin-bottom:.35rem; color:var(--isu-deep); }
        input { width:100%; border:1px solid #cbd5e1; border-radius:12px; padding:.8rem .9rem; font:inherit; }
        button { width:100%; border:0; border-radius:12px; padding:.9rem 1rem; background:var(--isu-green); color:#fff; font-weight:700; cursor:pointer; }
        .link-row { margin-top:1rem; text-align:center; }
        .link-row a { color:var(--isu-green); text-decoration:none; font-weight:600; }
        .alert { padding:.9rem 1rem; border-radius:12px; background:#ecfdf3; color:#166534; margin-bottom:1rem; }
        .errors { margin-bottom:1rem; color:#b91c1c; }
        .errors ul { margin-left:1rem; }
        .strength-meter { margin-top:0.45rem; }
        .strength-bar { height:6px; border-radius:999px; background:#e2e8f0; overflow:hidden; }
        .strength-fill { height:100%; width:0; border-radius:999px; background:#ef4444; transition: all 0.2s ease; }
        .strength-text { font-size:0.82rem; color:#64748b; margin-top:0.35rem; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="title">Create Student Account</h1>
        <p class="subtitle">Register as a student and wait for librarian approval before signing in.</p>

        <?php if(session('status')): ?>
            <div class="alert"><?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="errors">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('register.student')); ?>">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" required value="<?php echo e(old('name')); ?>">
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required value="<?php echo e(old('email')); ?>">
            </div>
            <div class="field">
                <label for="mobile_number">Mobile number</label>
                <input id="mobile_number" name="mobile_number" type="text" required value="<?php echo e(old('mobile_number')); ?>" placeholder="e.g. 09171234567">
            </div>
            <div class="field">
                <label for="login_id">Student ID</label>
                <input id="login_id" name="login_id" type="text" value="<?php echo e(old('login_id')); ?>" placeholder="xx-xxxxx" required maxlength="8" autocomplete="off">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
                <div class="strength-meter" aria-live="polite">
                    <div class="strength-bar"><div id="strength-fill" class="strength-fill"></div></div>
                    <div id="strength-text" class="strength-text">Enter a password</div>
                </div>
            </div>
            <div class="field">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required>
            </div>
            <button type="submit">Create account</button>
        </form>

        <div class="link-row">
            <a href="<?php echo e(route('login.student')); ?>"><i class="fa-solid fa-arrow-left-long"></i> Back to student login</a>
        </div>
    </div>
    <script>
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        const studentIdInput = document.getElementById('login_id');

        if (passwordInput && strengthFill && strengthText) {
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

            const renderStrength = () => {
                const score = scorePassword(passwordInput.value);
                const percent = Math.min(100, score * 20);
                const colors = ['#ef4444', '#f59e0b', '#eab308', '#22c55e', '#16a34a'];
                const labels = ['Very weak', 'Weak', 'Fair', 'Good', 'Strong'];
                const index = Math.min(labels.length - 1, Math.max(0, score - 1));
                strengthFill.style.width = `${percent}%`;
                strengthFill.style.background = colors[index];
                strengthText.textContent = passwordInput.value ? labels[index] : 'Enter a password';
            };

            passwordInput.addEventListener('input', renderStrength);
            renderStrength();
        }

        if (studentIdInput) {
            const formatStudentId = (value) => {
                const clean = value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                const first = clean.slice(0, 2);
                const second = clean.slice(2, 7);

                if (!first) return '';
                if (clean.length <= 2) return first;
                return `${first}-${second}`;
            };

            studentIdInput.addEventListener('input', () => {
                const start = studentIdInput.selectionStart ?? studentIdInput.value.length;
                studentIdInput.value = formatStudentId(studentIdInput.value);
                studentIdInput.setSelectionRange(studentIdInput.value.length, studentIdInput.value.length);
            });

            studentIdInput.addEventListener('blur', () => {
                studentIdInput.value = formatStudentId(studentIdInput.value);
            });
        }
    </script>
</body>
</html>
<?php /**PATH C:\laragon\www\system integ\librarya_System\resources\views/LoginPage/student-register.blade.php ENDPATH**/ ?>