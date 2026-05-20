<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role',
        'farmer_id', 'is_active', 'email_verified',
        'email_verify_token', 'password_reset_token',
        'password_reset_expires_at', 'profile_photo',
        'bio', 'organization',
    ];

    protected $hidden = ['password', 'remember_token', 'email_verify_token', 'password_reset_token'];

    protected $casts = [
        'password'                  => 'hashed',
        'is_active'                 => 'boolean',
        'email_verified'            => 'boolean',
        'password_reset_expires_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────
    public function farmer() { return $this->belongsTo(Farmer::class); }

    // ── Role Helpers ───────────────────────────────────────────
    public function isAdmin():  bool { return $this->role === 'admin'; }
    public function isExpert(): bool { return in_array($this->role, ['admin', 'expert']); }
    public function isFarmer(): bool { return $this->role === 'farmer'; }

    // ── Scopes ─────────────────────────────────────────────────
    public function scopeActive($q)  { return $q->where('is_active', true); }
    public function scopeFarmers($q) { return $q->where('role', 'farmer'); }
    public function scopeExperts($q) { return $q->whereIn('role', ['admin', 'expert']); }

    // ── Computed ───────────────────────────────────────────────
    public function getAvatarAttribute(): string
    {
        if ($this->profile_photo) return asset('storage/' . $this->profile_photo);
        $initials = strtoupper(substr($this->name, 0, 2));
        return "https://ui-avatars.com/api/?name={$initials}&background=2e7d32&color=fff&size=80";
    }

    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            'admin'  => 'danger',
            'expert' => 'primary',
            default  => 'success',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin'  => 'Administrator',
            'expert' => 'Expert',
            default  => 'Farmer',
        };
    }
}
