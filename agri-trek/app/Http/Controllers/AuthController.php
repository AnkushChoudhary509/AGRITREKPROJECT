<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Farmer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // ══════════════════════════════════════════════════════════════
    // EXPERT CREDENTIALS (predefined — registration blocked)
    // ══════════════════════════════════════════════════════════════
    const EXPERT_EMAIL    = 'ankushnagokay4631@gmail.com';
    const EXPERT_PASSWORD = 'AnkushJatt23@aR';

    // ══════════════════════════════════════════════════════════════
    // LOGIN
    // ══════════════════════════════════════════════════════════════

    public function showLogin()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $loginRole = $request->input('login_role', 'farmer');

        // ── Expert tab: validate against hardcoded credentials only ─────
        if ($loginRole === 'expert') {
            if ($credentials['email'] !== self::EXPERT_EMAIL) {
                return back()->withErrors(['email' => 'No expert account found with this email.'])->onlyInput('email');
            }
            if ($credentials['password'] !== self::EXPERT_PASSWORD) {
                return back()->withErrors(['password' => 'Incorrect password. Please try again.'])->onlyInput('email');
            }

            // Auto-create/sync expert user in DB if not present
            $user = User::firstOrCreate(
                ['email' => self::EXPERT_EMAIL],
                [
                    'name'           => 'Ankush (Expert)',
                    'password'       => Hash::make(self::EXPERT_PASSWORD),
                    'role'           => 'expert',
                    'is_active'      => true,
                    'email_verified' => true,
                    'organization'   => 'Agri-Trek Expert Team',
                ]
            );

            if (!$user->is_active) {
                return back()->withErrors(['email' => 'Your account has been deactivated. Contact support.'])->onlyInput('email');
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        // ── Farmer tab: database login, block expert email ──────────────
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.'])->onlyInput('email');
        }

        if ($user->role === 'expert') {
            return back()->withErrors(['email' => 'Please use the Expert tab to sign in as an expert.'])->onlyInput('email');
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'Your account has been deactivated. Contact support.'])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withErrors(['password' => 'Incorrect password. Please try again.'])
            ->onlyInput('email');
    }

    // ══════════════════════════════════════════════════════════════
    // REGISTER  (expert role blocked — farmer only)
    // ══════════════════════════════════════════════════════════════

    public function showRegister()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.register');
    }

    public function register(Request $request)
    {
        if ($request->input('role') === 'expert') {
            return back()
                ->withErrors(['role' => 'Expert accounts cannot be created through registration. Only sign-in is available for experts.'])
                ->onlyInput('name', 'email', 'phone');
        }

        $validated = $request->validate([
            'name'      => ['required', 'string', 'min:2', 'max:100'],
            'email'     => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'     => ['nullable', 'string', 'regex:/^[0-9]{10}$/'],
            'role'      => ['required', 'in:farmer'],
            'organization' => ['nullable', 'string', 'max:100'],
            'password'  => [
                'required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
        ], [
            'name.required'      => 'Full name is required.',
            'name.min'           => 'Name must be at least 2 characters.',
            'email.unique'       => 'This email is already registered. Try logging in.',
            'phone.regex'        => 'Phone must be a 10-digit number.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        $user = User::create([
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'] ?? null,
            'password'           => Hash::make($validated['password']),
            'role'               => 'farmer',
            'organization'       => $validated['organization'] ?? null,
            'is_active'          => true,
            'email_verified'     => true,
            'email_verify_token' => Str::random(64),
        ]);

        $farmer = Farmer::create([
            'name'   => $validated['name'],
            'mobile' => $validated['phone'] ?? '0000000000',
            'village'=> 'Not set',
        ]);
        $user->update(['farmer_id' => $farmer->id]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', "Welcome to Agri-Trek, {$user->name}! Your account has been created.");
    }

    // ══════════════════════════════════════════════════════════════
    // FORGOT PASSWORD  (email sent for both farmers and expert)
    // ══════════════════════════════════════════════════════════════

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $email = trim($request->email);

        // ── Expert forgot-password ──────────────────────────────────────
        if ($email === self::EXPERT_EMAIL) {
            $user = User::firstOrCreate(
                ['email' => self::EXPERT_EMAIL],
                [
                    'name'           => 'Ankush (Expert)',
                    'password'       => Hash::make(self::EXPERT_PASSWORD),
                    'role'           => 'expert',
                    'is_active'      => true,
                    'email_verified' => true,
                    'organization'   => 'Agri-Trek Expert Team',
                ]
            );

            $token = Str::random(64);
            $user->update([
                'password_reset_token'      => hash('sha256', $token),
                'password_reset_expires_at' => now()->addHours(2),
            ]);

            $resetUrl = route('password.reset', $token) . '?email=' . urlencode($email);
            $this->dispatchResetEmail($email, $user->name, $resetUrl);

            return back()->with('success', 'A password reset link has been sent to your email. Please check your inbox (and spam folder).');
        }

        // ── Farmer / admin forgot-password ─────────────────────────────
        $user = User::where('email', $email)->first();

        if ($user) {
            $token = Str::random(64);
            $user->update([
                'password_reset_token'      => hash('sha256', $token),
                'password_reset_expires_at' => now()->addHours(2),
            ]);

            $resetUrl = route('password.reset', $token) . '?email=' . urlencode($email);
            $this->dispatchResetEmail($email, $user->name, $resetUrl);
        }

        // Always show success message (prevents email enumeration)
        return back()->with('success', 'If this email is registered, a password reset link has been sent to your inbox.');
    }

    /**
     * Send the password reset email via Laravel Mail (SMTP).
     */
    private function dispatchResetEmail(string $toEmail, string $toName, string $resetUrl): void
    {
        try {
            Mail::send([], [], function ($message) use ($toEmail, $toName, $resetUrl) {
                $year = date('Y');
                $message->to($toEmail, $toName)
                    ->subject('Agri-Trek – Reset Your Password')
                    ->html("<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8'>
<style>
  body{margin:0;padding:20px;background:#f4f6f4;font-family:'Segoe UI',Arial,sans-serif;}
  .wrap{max-width:520px;margin:0 auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);}
  .hdr{background:linear-gradient(135deg,#1b5e20,#43a047);padding:28px 36px;text-align:center;}
  .hdr h1{color:#fff;font-size:22px;margin:0 0 4px;}
  .hdr p{color:rgba(255,255,255,.85);font-size:13px;margin:0;}
  .bdy{padding:30px 36px;}
  .bdy p{color:#444;font-size:14.5px;line-height:1.75;margin:0 0 16px;}
  .btn{display:inline-block;background:linear-gradient(135deg,#2e7d32,#43a047);color:#fff!important;text-decoration:none;padding:13px 30px;border-radius:10px;font-weight:700;font-size:15px;margin:6px 0 20px;}
  .linkbox{background:#f1f8e9;border:1px solid #c8e6c9;border-radius:8px;padding:12px 16px;word-break:break-all;font-size:12px;color:#555;margin-bottom:18px;}
  .ftr{background:#f8f9fa;padding:14px 36px;text-align:center;font-size:12px;color:#aaa;}
</style>
</head>
<body>
<div class='wrap'>
  <div class='hdr'>
    <h1>✈ Agri-Trek</h1>
    <p>Precision Agriculture Monitoring System</p>
  </div>
  <div class='bdy'>
    <p>Hello <strong>{$toName}</strong>,</p>
    <p>We received a request to reset the password for your Agri-Trek account. Click the button below — this link is valid for <strong>2 hours</strong>.</p>
    <div style='text-align:center;'>
      <a href='{$resetUrl}' class='btn'>🔑 Reset My Password</a>
    </div>
    <p>Or copy this link into your browser:</p>
    <div class='linkbox'>{$resetUrl}</div>
    <p style='color:#999;font-size:13px;'>If you did not request a password reset, please ignore this email. Your account remains secure.</p>
  </div>
  <div class='ftr'>© {$year} Agri-Trek &nbsp;|&nbsp; Automated message — please do not reply.</div>
</div>
</body>
</html>");
            });
        } catch (\Exception $e) {
            Log::error('Agri-Trek password reset email failed: ' . $e->getMessage());
        }
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => [
                'required', 'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = User::where('email', $request->email)
                    ->where('password_reset_token', hash('sha256', $request->token))
                    ->where('password_reset_expires_at', '>', now())
                    ->first();

        if (!$user) {
            return back()->withErrors(['token' => 'Invalid or expired reset link. Please request a new one.']);
        }

        $user->update([
            'password'                  => Hash::make($request->password),
            'password_reset_token'      => null,
            'password_reset_expires_at' => null,
        ]);

        return redirect()->route('login')
            ->with('success', 'Password reset successfully! You can now log in with your new password.');
    }

    // ══════════════════════════════════════════════════════════════
    // LOGOUT
    // ══════════════════════════════════════════════════════════════

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been logged out safely.');
    }
}
