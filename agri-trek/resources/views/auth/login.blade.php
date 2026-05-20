<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Agri-Trek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0a3d0a 0%, #1b5e20 40%, #2e7d32 70%, #388e3c 100%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 20px;
        }
        .auth-wrapper { width: 100%; max-width: 440px; }
        .auth-card {
            background: #fff;
            border-radius: 24px;
            padding: 44px 40px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
        }
        .brand-logo {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #1b5e20, #43a047);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(46,125,50,0.4);
        }
        .brand-logo i { font-size: 34px; color: #fff; }
        .form-label { font-weight: 600; font-size: 13px; color: #444; margin-bottom: 6px; }
        .form-control, .input-group-text {
            border-radius: 10px !important;
            border-color: #e0e0e0;
            font-size: 14px;
        }
        .form-control:focus { border-color: #4caf50; box-shadow: 0 0 0 3px rgba(76,175,80,0.15); }
        .input-group .form-control { border-left: none; }
        .input-group-text { background: #f8f9fa; border-right: none; color: #666; }
        .btn-primary-green {
            background: linear-gradient(135deg, #2e7d32, #43a047);
            border: none; color: #fff;
            padding: 13px; font-weight: 700;
            border-radius: 12px; font-size: 15px;
            transition: all 0.2s;
            letter-spacing: 0.3px;
        }
        .btn-primary-green:hover {
            background: linear-gradient(135deg, #1b5e20, #2e7d32);
            color: #fff; transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(46,125,50,0.35);
        }
        .divider { display: flex; align-items: center; gap: 12px; color: #aaa; font-size: 12px; margin: 20px 0; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e8e8e8; }
        .info-box {
            background: #f1f8e9; border: 1px solid #c8e6c9;
            border-radius: 12px; padding: 14px 16px; font-size: 12.5px;
        }
        .password-toggle { cursor: pointer; background: #f8f9fa; border-left: none; border-color: #e0e0e0; border-radius: 0 10px 10px 0 !important; }
        .tab-btn {
            flex: 1; padding: 10px; border: 2px solid #e0e0e0;
            background: #fff; border-radius: 10px; font-weight: 600;
            font-size: 13px; color: #666; cursor: pointer; transition: 0.2s;
        }
        .tab-btn.active { border-color: #2e7d32; background: #f1f8e9; color: #2e7d32; }
        .alert { border-radius: 10px; font-size: 13.5px; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Brand -->
        <div class="text-center mb-4">
            <div class="brand-logo">
                <i class="bi bi-airplane-engines-fill"></i>
            </div>
            <h4 class="fw-bold mb-1" style="color:#1b5e20;">Agri-Trek</h4>
            <p class="text-muted mb-0" style="font-size:13px;">Precision Agriculture Monitoring System</p>
        </div>

        <!-- Flash messages -->
        @if(session('success'))
            <div class="alert alert-success py-2 mb-3">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger py-2 mb-3">
                <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <!-- Role Tabs -->
        <div class="d-flex gap-2 mb-4">
            <button class="tab-btn active" id="tabFarmer" onclick="setRole('farmer')">
                <i class="bi bi-person-fill me-1"></i>Farmer
            </button>
            <button class="tab-btn" id="tabExpert" onclick="setRole('expert')">
                <i class="bi bi-shield-check me-1"></i>Expert / Admin
            </button>
        </div>

        <!-- Login Form -->
        <form method="POST" action="/login" id="loginForm">
            @csrf
            <input type="hidden" name="login_role" id="loginRole" value="farmer">

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope-fill text-success"></i></span>
                    <input type="email" name="email" class="form-control"
                           placeholder="your@email.com"
                           value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-2">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill text-success"></i></span>
                    <input type="password" name="password" class="form-control" id="passwordField"
                           placeholder="Enter your password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </span>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember" style="font-size:13px;">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="text-success text-decoration-none" style="font-size:13px;font-weight:600;">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn btn-primary-green w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="divider">or</div>

        <div class="text-center">
            <span style="font-size:13px;color:#666;">Don't have an account?</span>
            <a href="{{ route('register') }}" class="text-success fw-bold text-decoration-none ms-1" style="font-size:13px;">
                Create Account
            </a>
        </div>

        <!-- Info Box -->
        <div class="info-box mt-4">
            <div class="fw-bold text-success mb-2"><i class="bi bi-info-circle me-1"></i>Getting Started</div>
            <div class="text-muted" style="font-size:12px;">
                <i class="bi bi-person-fill text-success me-1"></i><strong>Farmers</strong> — Register a new account or sign in with your credentials.<br><br>
                <i class="bi bi-shield-check text-primary me-1"></i><strong>Experts</strong> — Use the Expert tab with your assigned credentials. Expert accounts cannot be self-registered.
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setRole(role) {
    document.getElementById('loginRole').value = role;
    document.getElementById('tabFarmer').classList.toggle('active', role === 'farmer');
    document.getElementById('tabExpert').classList.toggle('active', role === 'expert');
}
function togglePassword() {
    const f = document.getElementById('passwordField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
    else { f.type = 'password'; i.className = 'bi bi-eye'; }
}
</script>
</body>
</html>
