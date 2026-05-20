<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DroneLog extends Model {
    protected $fillable = ['drone_id','latitude','longitude','speed','altitude','direction','extra_data'];
    protected $casts    = ['latitude' => 'float', 'longitude' => 'float', 'extra_data' => 'array'];
    public function drone() { return $this->belongsTo(Drone::class); }
}
