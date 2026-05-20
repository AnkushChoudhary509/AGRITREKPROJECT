<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Drone extends Model {
    protected $fillable = ['name','drone_id','model','description','status'];

    public function logs()     { return $this->hasMany(DroneLog::class); }
    public function waypoints(){ return $this->hasMany(Waypoint::class); }

    /** Get the very latest telemetry log. */
    public function latestLog() {
        return $this->hasOne(DroneLog::class)->latestOfMany();
    }
}
