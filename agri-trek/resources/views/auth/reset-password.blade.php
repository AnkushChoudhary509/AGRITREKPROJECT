<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – Agri-Trek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body{min-height:100vh;background:linear-gradient(135deg,#0a3d0a,#1b5e20,#2e7d32);display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;padding:20px;}
        .auth-card{background:#fff;border-radius:24px;padding:40px;max-width:420px;width:100%;box-shadow:0 25px 80px rgba(0,0,0,.3);}
        .form-control:focus{border-color:#4caf50;box-shadow:0 0 0 3px rgba(76,175,80,.15);}
        .btn-green{background:linear-gradient(135deg,#2e7d32,#43a047);border:none;color:#fff;padding:12px;border-radius:12px;font-weight:700;}
        .btn-green:hover{background:linear-gradient(135deg,#1b5e20,#2e7d32);color:#fff;}
        .input-group-text{background:#f8f9fa;border-right:none;border-radius:10px 0 0 10px!important;}
        .form-control{border-left:none;border-radius:0 10px 10px 0!important;}
        .alert{border-radius:10px;}
        .pw-strength{height:4px;border-radius:2px;transition:.3s;margin-top:6px;}
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <h5 class="fw-bold" style="color:#1b5e20;"><i class="bi bi-key-fill text-success me-2"></i>Reset Password</h5>
        <p class="text-muted" style="font-size:13px;">Create a new strong password</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill text-success"></i></span>
                <input type="password" name="password" id="pw" class="form-control"
                       placeholder="New password" oninput="checkStr(this.value)" required>
            </div>
            <div class="pw-strength" id="bar" style="background:#e0e0e0;width:100%;"></div>
            <div style="font-size:11px;color:#888;margin-top:4px;">Min 8 chars • Uppercase • Lowercase • Number • Symbol</div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:13px;">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill text-success"></i></span>
                <input type="password" name="password_confirmation" class="form-control"
                       placeholder="Repeat password" required>
            </div>
        </div>
        <button type="submit" class="btn btn-green w-100">
            <i class="bi bi-check-lg me-2"></i>Reset Password
        </button>
    </form>
</div>
<script>
function checkStr(v) {
    const s = [v.length>=8,/[A-Z]/.test(v),/[a-z]/.test(v),/[0-9]/.test(v),/[^A-Za-z0-9]/.test(v)].filter(Boolean).length;
    const colors=['#e0e0e0','#f44336','#ff9800','#ffc107','#4caf50'];
    const widths=['100%','25%','50%','75%','100%'];
    const b=document.getElementById('bar');
    b.style.background=colors[s]; b.style.width=widths[s];
}
</script>
</body>
</html>
