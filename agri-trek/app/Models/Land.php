<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

// ========== Land ==========
class Land extends Model {
    protected $fillable = ['farmer_id','area','soil_type','crop_type','latitude','longitude','irrigation_type','survey_number','description'];
    protected $casts    = ['latitude' => 'float', 'longitude' => 'float'];
    public function farmer() { return $this->belongsTo(Farmer::class); }
}
