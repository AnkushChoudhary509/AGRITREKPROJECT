<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SchemeApplication extends Model {
    protected $fillable = ['farmer_id','scheme_id','status','remarks','applied_date','approved_date'];
    protected $casts    = ['applied_date' => 'date', 'approved_date' => 'date'];
    public function farmer() { return $this->belongsTo(Farmer::class); }
    public function scheme() { return $this->belongsTo(Scheme::class); }
}
