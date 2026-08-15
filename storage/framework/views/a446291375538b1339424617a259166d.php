<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | ISU Library System</title>

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
            --glass-bg: rgba(255, 255, 255, 0.9);
            --glass-border: rgba(255, 255, 255, 0.5);
            --shadow-xl: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 14px; }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #f8fafc;
            background-image:
                radial-gradient(at 0% 0%, rgba(242, 200, 75, 0.25) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(11, 107, 58, 0.2) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(242, 200, 75, 0.15) 0px, transparent 50%),
                radial-gradient(at 0% 100%, rgba(11, 107, 58, 0.25) 0px, transparent 50%);
            background-attachment: fixed;
        }
        .card {
            width: 100%;
            max-width: 26rem;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
            border-radius: 1.25rem;
            box-shadow: var(--shadow-xl);
            padding: 2.5rem 2rem;
        }
        .logo-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; }
        .logo-row img { width: 2.75rem; height: 2.75rem; border-radius: 50%; object-fit: cover; }
        .logo-row span { font-weight: 700; color: var(--isu-deep); font-size: 1.05rem; }
        h1 { font-size: 1.4rem; font-weight: 800; color: var(--isu-deep); margin-bottom: 0.4rem; }
        .hint { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.5; }
        .field { margin-bottom: 1.1rem; }
        label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.4rem; }
        .input-wrapper {
            display: flex; align-items: center; gap: 0.6rem;
            border: 1px solid #cbd5e1; border-radius: 0.6rem;
            padding: 0.7rem 0.9rem; background: #fff;
        }
        .input-wrapper i { color: var(--text-muted); }
        .input-wrapper input { border: none; outline: none; flex: 1; font-size: 0.95rem; font-family: inherit; }
        button {
            width: 100%; padding: 0.8rem; border: none; border-radius: 0.6rem;
            background: var(--isu-green); color: #fff; font-weight: 700; font-size: 0.95rem;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            margin-top: 0.5rem;
        }
        button:hover { background: var(--isu-deep); }
        .status { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; padding: 0.75rem 1rem; border-radius: 0.6rem; margin-bottom: 1.1rem; font-size: 0.9rem; }
        .error { color: var(--isu-red); font-size: 0.85rem; margin-top: 0.4rem; }
        .back-link { display: inline-block; margin-top: 1.4rem; font-size: 0.9rem; color: var(--isu-green); text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-row">
            <img src="<?php echo e(asset('picture/ISU.jpg')); ?>" alt="ISU logo">
            <span>ISU Library System</span>
        </div>

        <h1>Forgot your password?</h1>
        <p class="hint">Choose whether to receive a reset link by email or an OTP by mobile number. Just enter your Student ID — we'll use the contact details on file.</p>

        <?php if(session('status')): ?>
            <div class="status"><i class="fa-solid fa-circle-check"></i> <?php echo e(session('status')); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('password.email')); ?>">
            <?php echo csrf_field(); ?>
            <div class="field">
                <label for="method">Choose method</label>
                <div style="display:flex;gap:.5rem;">
                    <label style="display:flex;gap:.4rem;align-items:center"><input type="radio" name="method" value="email" <?php if(old('method', 'email') === 'email'): echo 'checked'; endif; ?>> Email</label>
                    <label style="display:flex;gap:.4rem;align-items:center"><input type="radio" name="method" value="mobile" <?php if(old('method') === 'mobile'): echo 'checked'; endif; ?>> Mobile (OTP)</label>
                </div>
            </div>

            <div class="field" id="emailField">
                <p class="hint" style="margin-bottom:0;">We'll send the reset link to the email address already on file for this Student ID.</p>
            </div>

            <div class="field" id="mobileField" style="display:none;">
                <label for="mobile">Mobile Number</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-phone"></i>
                    <input id="mobile" name="mobile" type="text" value="<?php echo e(old('mobile')); ?>" placeholder="e.g. +639171234567" autocomplete="tel">
                </div>
                <?php $__errorArgs = ['mobile'];
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
            <div class="field">
                <label for="login_id">Student ID</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-id-card"></i>
                    <input id="login_id" name="login_id" type="text" value="<?php echo e(old('login_id')); ?>" placeholder="e.g. STU001" autocomplete="off" required>
                </div>
                <?php $__errorArgs = ['login_id'];
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
                <span>Send Reset Link</span>
                <i class="fa-solid fa-paper-plane"></i>
            </button>
        </form>

        <a class="back-link" href="<?php echo e(route('home')); ?>"><i class="fa-solid fa-arrow-left"></i> Back to login</a>
    </div>
    <script>
        const methodRadios = document.querySelectorAll('input[name="method"]');
        const mobileField = document.getElementById('mobileField');
        const emailField = document.getElementById('emailField');
        const mobileInput = document.getElementById('mobile');
        const toggleFields = () => {
            const method = document.querySelector('input[name="method"]:checked')?.value || 'email';
            const isMobile = method === 'mobile';
            mobileField.style.display = isMobile ? 'block' : 'none';
            emailField.style.display = isMobile ? 'none' : 'block';
            mobileInput.required = isMobile;
        };
        methodRadios.forEach(r => r.addEventListener('change', () => {
            toggleFields();
        }));
        toggleFields();
    </script>
</body>
</html>
<?php /**PATH /home/zeanmartin/public_html/miini.zeanmartin.online/librarya_System/resources/views/LoginPage/forgot-password.blade.php ENDPATH**/ ?>