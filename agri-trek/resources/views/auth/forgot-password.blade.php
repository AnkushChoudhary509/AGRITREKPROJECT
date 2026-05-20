<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password – Agri-Trek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height:100vh;
            background:linear-gradient(135deg,#0a3d0a,#1b5e20,#2e7d32,#388e3c);
            display:flex;align-items:center;justify-content:center;
            font-family:'Segoe UI',sans-serif; padding:20px;
        }
        .auth-card {
            background:#fff;border-radius:24px;padding:44px 40px;
            max-width:420px;width:100%;box-shadow:0 25px 80px rgba(0,0,0,0.3);
        }
        .brand-logo {
            width:64px;height:64px;background:linear-gradient(135deg,#1b5e20,#43a047);
            border-radius:18px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;
        }
        .form-control:focus{border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.15);}
        .btn-green{background:linear-gradient(135deg,#2e7d32,#43a047);border:none;color:#fff;padding:12px;border-radius:12px;font-weight:700;}
        .btn-green:hover{background:linear-gradient(135deg,#1b5e20,#2e7d32);color:#fff;}
        .input-group-text{background:#f8f9fa;border-right:none;border-radius:10px 0 0 10px!important;}
        .form-control{border-left:none;border-radius:0 10px 10px 0!important;}
        .alert{border-radius:10px;}
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div class="brand-logo"><i class="bi bi-lock-fill text-white fs-4"></i></div>
        <h5 class="fw-bold" style="color:#1b5e20;">Forgot Password?</h5>
        <p class="text-muted" style="font-size:13px;">Enter your registered email. A reset link will be sent to your inbox — works for farmers and experts.</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        <i class="bi bi-check-circle me-2"></i>{!! session('success') !!}
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:13px;">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope-fill text-success"></i></span>
                <input type="email" name="email" class="form-control"
                       placeholder="your@email.com" value="{{ old('email') }}" required>
            </div>
        </div>
        <button type="submit" class="btn btn-green w-100">
            <i class="bi bi-send me-2"></i>Send Reset Link
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="{{ route('login') }}" class="text-success text-decoration-none" style="font-size:13px;">
            <i class="bi bi-arrow-left me-1"></i>Back to Login
        </a>
    </div>
</div>
</body>
</html>
