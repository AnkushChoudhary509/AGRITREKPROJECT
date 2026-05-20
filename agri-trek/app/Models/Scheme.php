<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Scheme extends Model {
    protected $fillable = ['name','description','eligibility','subsidy_amount','start_date','end_date','is_active','department'];
    protected $casts    = ['is_active' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'subsidy_amount' => 'float'];
    public function applications() { return $this->hasMany(SchemeApplication::class); }
    public function farmers()      { return $this->belongsToMany(Farmer::class, 'scheme_applications'); }
}
