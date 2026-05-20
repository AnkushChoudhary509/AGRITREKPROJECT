<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Waypoint extends Model {
    protected $fillable = ['name','route_name','drone_id','latitude','longitude','sequence','altitude','speed','notes','is_reached'];
    protected $casts    = ['latitude' => 'float', 'longitude' => 'float', 'is_reached' => 'boolean'];
    public function drone() { return $this->belongsTo(Drone::class); }
}
