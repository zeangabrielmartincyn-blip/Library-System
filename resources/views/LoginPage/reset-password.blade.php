<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | ISU Library System</title>

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
        .error { color: var(--isu-red); font-size: 0.85rem; margin-top: 0.4rem; }
        .back-link { display: inline-block; margin-top: 1.4rem; font-size: 0.9rem; color: var(--isu-green); text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
<<<<<<< HEAD
        .strength-meter { margin-top: 0.45rem; }
        .strength-bar { height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
        .strength-fill { height: 100%; width: 0; border-radius: 999px; background: #ef4444; transition: all 0.2s ease; }
        .strength-text { font-size: 0.82rem; color: var(--text-muted); margin-top: 0.35rem; }
=======
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-row">
            <img src="{{ asset('picture/ISU.jpg') }}" alt="ISU logo">
            <span>ISU Library System</span>
        </div>

        <h1>Set a new password</h1>
        <p class="hint">Choose a new password for your account.</p>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

<<<<<<< HEAD
            @if(! empty($phone) && ! empty($phone_token))
                <div class="field">
                    <label>Resetting for mobile</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-phone"></i>
                        <input type="text" value="{{ $phone }}" disabled class="input-wrapper" style="background:transparent;border:none;">
                    </div>
                </div>
                <input type="hidden" name="phone" value="{{ $phone }}">
                <input type="hidden" name="login_id" value="{{ $login_id }}">
                <input type="hidden" name="phone_token" value="{{ $phone_token }}">
            @else
                <div class="field">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fa-solid fa-envelope"></i>
                        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" placeholder="you@example.com" autocomplete="email" required autofocus>
                    </div>
                    @error('email')
                        <p class="error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                    @enderror
                </div>
            @endif
=======
            <div class="field">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" placeholder="you@example.com" autocomplete="email" required autofocus>
                </div>
                @error('email')
                    <p class="error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af

            <div class="field">
                <label for="password">New Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required minlength="8">
                </div>
<<<<<<< HEAD
                <div class="strength-meter" aria-live="polite">
                    <div class="strength-bar"><div id="strength-fill" class="strength-fill"></div></div>
                    <div id="strength-text" class="strength-text">Enter a password</div>
                </div>
=======
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
                @error('password')
                    <p class="error"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Confirm New Password</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Re-enter new password" autocomplete="new-password" required minlength="8">
                </div>
            </div>

            <button type="submit">
                <span>Reset Password</span>
                <i class="fa-solid fa-check"></i>
            </button>
        </form>

        <a class="back-link" href="{{ route('home') }}"><i class="fa-solid fa-arrow-left"></i> Back to login</a>
    </div>
<<<<<<< HEAD
    <script>
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');

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
    </script>
=======
>>>>>>> 90d58030f54a63f10685836543225505ca11c2af
</body>
</html>
