<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verify OTP | ISU Library System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root{--green:#0b6b3a;--muted:#64748b;}
        body{font-family:Inter,system-ui,Arial;display:flex;align-items:center;justify-content:center;min-height:100dvh;background:#f8fafc;padding:2rem}
        .card{background:#fff;border-radius:12px;padding:1.5rem;max-width:28rem;width:100%;box-shadow:0 12px 36px rgba(6,66,37,.08)}
        .field{margin-bottom:1rem}
        label{font-weight:700;color:var(--green);display:block;margin-bottom:.35rem}
        .input{width:100%;padding:.65rem;border:1px solid #e6edf2;border-radius:.6rem}
        .hint{color:var(--muted);margin-bottom:1rem}
        button{background:var(--green);color:#fff;padding:.75rem;border-radius:.6rem;border:0;font-weight:800;width:100%}
        .error{color:#c8282d;margin-top:.4rem}
    </style>
</head>
<body>
    <div class="card">
        <h2>Verify mobile OTP</h2>
        <p class="hint">We sent a one-time code to <strong>{{ $mobile }}</strong>. Enter your registered mobile number and account ID to continue.</p>
        @if(session('status'))<div class="hint">{{ session('status') }}</div>@endif
        @if(session('demo_otp'))
            <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:.6rem;padding:.75rem;margin-bottom:1rem;color:#065f46;">
                Your code: <strong style="font-size:1.2em;letter-spacing:.1em;">{{ session('demo_otp') }}</strong>
            </div>
        @endif
        <form method="POST" action="{{ route('password.otp.verify') }}">
            @csrf
            <input type="hidden" name="mobile" value="{{ $mobile }}">
            <input type="hidden" name="login_id" value="{{ $login_id }}">
            <div class="field">
                <label for="login_id_display">Account ID</label>
                <input id="login_id_display" class="input" type="text" value="{{ $login_id }}" disabled>
            </div>
            <div class="field">
                <label for="mobile_display">Registered mobile number</label>
                <input id="mobile_display" class="input" type="text" value="{{ $mobile }}" disabled>
            </div>
            <div class="field">
                <label for="otp">One-time code (6 digits)</label>
                <input id="otp" name="otp" class="input" inputmode="numeric" pattern="\d{6}" maxlength="6" required autofocus>
                @error('otp')<div class="error">{{ $message }}</div>@enderror
            </div>
            <button type="submit">Verify code</button>
        </form>
    </div>
</body>
</html>