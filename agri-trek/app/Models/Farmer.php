<?php
// ========== app/Models/Farmer.php ==========
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Farmer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','mobile','address','village','district',
        'aadhaar','dob','bank_account','ifsc_code','notes'
    ];

    protected $casts = ['dob' => 'date'];

    // Relationships
    public function lands()        { return $this->hasMany(Land::class); }
    public function applications() { return $this->hasMany(SchemeApplication::class); }
    public function user()         { return $this->hasOne(User::class); }
}
