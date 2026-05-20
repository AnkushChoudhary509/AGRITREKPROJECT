<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – Agri-Trek</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0a3d0a 0%, #1b5e20 40%, #2e7d32 70%, #388e3c 100%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif; padding: 20px;
        }
        .auth-wrapper { width: 100%; max-width: 500px; }
        .auth-card { background: #fff; border-radius: 24px; padding: 40px; box-shadow: 0 25px 80px rgba(0,0,0,0.3); }
        .brand-logo {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #1b5e20, #43a047);
            border-radius: 18px; display: flex; align-items: center;
            justify-content: center; margin: 0 auto 14px;
        }
        .brand-logo i { font-size: 30px; color: #fff; }
        .form-label { font-weight: 600; font-size: 13px; color: #444; margin-bottom: 5px; }
        .form-control, .input-group-text {
            border-radius: 10px !important; border-color: #e0e0e0; font-size: 14px;
        }
        .form-control.is-invalid { border-color: #dc3545; }
        .form-control:focus { border-color: #4caf50; box-shadow: 0 0 0 3px rgba(76,175,80,0.15); }
        .input-group .form-control { border-left: none; }
        .input-group-text { background: #f8f9fa; border-right: none; color: #666; }
        .btn-register {
            background: linear-gradient(135deg, #2e7d32, #43a047);
            border: none; color: #fff; padding: 13px;
            font-weight: 700; border-radius: 12px; font-size: 15px;
        }
        .btn-register:hover { background: linear-gradient(135deg, #1b5e20, #2e7d32); color:#fff; }
        .role-card {
            border: 2px solid #e0e0e0; border-radius: 12px;
            padding: 14px; cursor: pointer; transition: 0.2s; text-align: center;
        }
        .role-card.selected { border-color: #2e7d32; background: #f1f8e9; }
        .role-card i { font-size: 28px; display: block; margin-bottom: 6px; }
        .pw-strength { height: 4px; border-radius: 2px; transition: 0.3s; margin-top: 6px; }
        .strength-0 { background: #e0e0e0; width: 100%; }
        .strength-1 { background: #f44336; width: 25%; }
        .strength-2 { background: #ff9800; width: 50%; }
        .strength-3 { background: #ffc107; width: 75%; }
        .strength-4 { background: #4caf50; width: 100%; }
        .req-item { font-size: 12px; display: flex; align-items: center; gap: 6px; margin-bottom: 3px; }
        .req-item .bi-check-circle-fill { color: #4caf50; }
        .req-item .bi-circle { color: #ccc; }
        .alert { border-radius: 10px; font-size: 13.5px; }
        .password-toggle { cursor: pointer; background: #f8f9fa; border-left: none; border-color: #e0e0e0; border-radius: 0 10px 10px 0 !important; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Brand -->
        <div class="text-center mb-4">
            <div class="brand-logo"><i class="bi bi-airplane-engines-fill"></i></div>
            <h4 class="fw-bold mb-1" style="color:#1b5e20;">Create Account</h4>
            <p class="text-muted mb-0" style="font-size:13px;">Join Agri-Trek Precision Agriculture Platform</p>
        </div>

        <!-- Errors -->
        @if($errors->any())
        <div class="alert alert-danger py-2 mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Please fix the following:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                    <li style="font-size:13px;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf

            <!-- Role Selection -->
            <div class="mb-4">
                <label class="form-label">Account Type</label>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="role-card selected" id="cardFarmer">
                            <i class="bi bi-person-fill text-success"></i>
                            <strong style="font-size:14px;">Farmer</strong>
                            <div style="font-size:11px;color:#666;">Manage lands & crops</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="role-card" style="opacity:0.55;cursor:not-allowed;background:#f8f9fa;"
                             title="Expert accounts cannot be registered. Sign in only.">
                            <i class="bi bi-shield-lock text-secondary"></i>
                            <strong style="font-size:14px;color:#888;">Expert</strong>
                            <div style="font-size:11px;color:#f44336;"><i class="bi bi-lock-fill"></i> Sign-in only</div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="role" id="roleInput" value="farmer">
                <div class="mt-2 p-2 rounded" style="background:#fff3e0;border:1px solid #ffcc80;font-size:12px;color:#e65100;">
                    <i class="bi bi-info-circle me-1"></i>
                    Expert accounts are pre-configured and cannot be created here. Expert login is available on the Sign In page.
                </div>
            </div>

            <div class="row g-3">
                <!-- Name -->
                <div class="col-12">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill text-success"></i></span>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                               placeholder="Your full name" value="{{ old('name') }}" required>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-12">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill text-success"></i></span>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                               placeholder="your@email.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <!-- Phone -->
                <div class="col-12">
                    <label class="form-label">Mobile Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-phone-fill text-success"></i></span>
                        <input type="text" name="phone" maxlength="10"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="10-digit mobile number" value="{{ old('phone') }}">
                    </div>
                </div>

                <!-- Organization (Expert only) -->
                <div class="col-12" id="orgField" style="{{ old('role') === 'expert' ? '' : 'display:none;' }}">
                    <label class="form-label">Organization / Institution</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-building text-success"></i></span>
                        <input type="text" name="organization" class="form-control"
                               placeholder="e.g. State Agriculture Dept" value="{{ old('organization') }}">
                    </div>
                </div>

                <!-- Password -->
                <div class="col-12">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill text-success"></i></span>
                        <input type="password" name="password" id="pwField"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Create a strong password"
                               oninput="checkStrength(this.value)" required>
                        <span class="input-group-text password-toggle" onclick="togglePw('pwField','eye1')">
                            <i class="bi bi-eye" id="eye1"></i>
                        </span>
                    </div>
                    <!-- Strength bar -->
                    <div class="pw-strength strength-0" id="strengthBar"></div>
                    <div class="mt-2" id="pwReqs">
                        <div class="req-item"><i class="bi bi-circle" id="req-len"></i> At least 8 characters</div>
                        <div class="req-item"><i class="bi bi-circle" id="req-upper"></i> Uppercase letter (A–Z)</div>
                        <div class="req-item"><i class="bi bi-circle" id="req-lower"></i> Lowercase letter (a–z)</div>
                        <div class="req-item"><i class="bi bi-circle" id="req-num"></i> Number (0–9)</div>
                        <div class="req-item"><i class="bi bi-circle" id="req-sym"></i> Symbol (@#$%!...)</div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="col-12">
                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill text-success"></i></span>
                        <input type="password" name="password_confirmation" id="pwConfirm"
                               class="form-control"
                               placeholder="Re-enter password"
                               oninput="checkMatch()" required>
                        <span class="input-group-text password-toggle" onclick="togglePw('pwConfirm','eye2')">
                            <i class="bi bi-eye" id="eye2"></i>
                        </span>
                    </div>
                    <div id="matchMsg" style="font-size:12px;margin-top:4px;"></div>
                </div>
            </div>

            <!-- Terms -->
            <div class="form-check mt-3 mb-4">
                <input class="form-check-input" type="checkbox" id="terms" required>
                <label class="form-check-label" for="terms" style="font-size:13px;">
                    I agree to the <a href="#" class="text-success">Terms of Service</a> and
                    <a href="#" class="text-success">Privacy Policy</a>
                </label>
            </div>

            <button type="submit" class="btn btn-register w-100" id="submitBtn">
                <i class="bi bi-person-plus-fill me-2"></i>Create Account
            </button>
        </form>

        <div class="text-center mt-3">
            <span style="font-size:13px;color:#666;">Already have an account?</span>
            <a href="{{ route('login') }}" class="text-success fw-bold text-decoration-none ms-1" style="font-size:13px;">
                Sign In
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function selectRole(role) {
    // Only 'farmer' is allowed through registration
    document.getElementById('roleInput').value = 'farmer';
    document.getElementById('cardFarmer').classList.add('selected');
    const orgField = document.getElementById('orgField');
    if (orgField) orgField.style.display = 'none';
}

function togglePw(fieldId, iconId) {
    const f = document.getElementById(fieldId);
    const i = document.getElementById(iconId);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}

function setReq(id, pass) {
    const el = document.getElementById(id);
    el.className = pass ? 'bi bi-check-circle-fill' : 'bi bi-circle';
    el.style.color = pass ? '#4caf50' : '#ccc';
}

function checkStrength(val) {
    const len    = val.length >= 8;
    const upper  = /[A-Z]/.test(val);
    const lower  = /[a-z]/.test(val);
    const num    = /[0-9]/.test(val);
    const sym    = /[^A-Za-z0-9]/.test(val);
    setReq('req-len', len); setReq('req-upper', upper);
    setReq('req-lower', lower); setReq('req-num', num); setReq('req-sym', sym);
    const score = [len, upper, lower, num, sym].filter(Boolean).length;
    const bar = document.getElementById('strengthBar');
    bar.className = `pw-strength strength-${score}`;
}

function checkMatch() {
    const pw  = document.getElementById('pwField').value;
    const cpw = document.getElementById('pwConfirm').value;
    const msg = document.getElementById('matchMsg');
    if (!cpw) { msg.innerHTML = ''; return; }
    if (pw === cpw) {
        msg.innerHTML = '<span style="color:#4caf50"><i class="bi bi-check-circle-fill me-1"></i>Passwords match</span>';
    } else {
        msg.innerHTML = '<span style="color:#f44336"><i class="bi bi-x-circle-fill me-1"></i>Passwords do not match</span>';
    }
}
</script>
</body>
</html>
