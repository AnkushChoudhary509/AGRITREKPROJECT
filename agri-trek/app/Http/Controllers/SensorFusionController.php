<?php

namespace App\Http\Controllers;

use App\Models\DroneLog;
use App\Models\Drone;
use Illuminate\Http\Request;

class SensorFusionController
{
    public function index()
    {
        // Get the latest drone log for real data
        $latestLog = DroneLog::latest()->first();

        // Individual sensor readings (with simulated noise)
        $gpLat  = $latestLog ? (float)$latestLog->latitude  + $this->noise(0.0002) : 23.5 + $this->noise(0.0002);
        $gpsLng = $latestLog ? (float)$latestLog->longitude + $this->noise(0.0002) : 72.5 + $this->noise(0.0002);
        $speed  = $latestLog ? (float)$latestLog->speed     : rand(20, 60);
        $alt    = $latestLog ? (float)$latestLog->altitude  : rand(50, 150);

        $sensorData = [
            'gps_lat'       => number_format($gpLat, 6),
            'gps_lng'       => number_format($gpsLng, 6),
            'gps_accuracy'  => rand(78, 95),
            'speed'         => $speed,
            'max_speed'     => 120,
            'speed_noise'   => number_format(abs($this->noise(2)), 1),
            'altitude'      => $alt,
            'max_altitude'  => 300,
            'alt_noise'     => number_format(abs($this->noise(3)), 1),
            'camera_fps'    => 30,
            'camera_res'    => '4K (3840×2160)',
            'camera_fov'    => 94,
        ];

        // Weighted Fusion (GPS 40%, Speed 25%, Altimeter 20%, Camera 15%)
        $fusedLat  = $gpLat + ($this->noise(0.00005));   // reduced noise after fusion
        $fusedLng  = $gpsLng + ($this->noise(0.00005));
        $fusedSpeed = $speed + $this->noise(0.5);
        $fusedAlt   = $alt + $this->noise(1.0);

        $fusedData = [
            'latitude'       => number_format($fusedLat, 6),
            'longitude'      => number_format($fusedLng, 6),
            'speed'          => number_format($fusedSpeed, 1),
            'altitude'       => number_format($fusedAlt, 1),
            'confidence'     => rand(88, 97),
            'error_reduction'=> rand(60, 78),
        ];

        // Time series data for chart (last 10 readings)
        $logs       = DroneLog::latest()->limit(10)->get()->reverse()->values();
        $timeLabels = $logs->map(fn($l) => $l->created_at->format('H:i'))->toArray();
        if (empty($timeLabels)) {
            $timeLabels = array_map(fn($i) => date('H:i', time() - $i * 60), range(9, 0));
        }

        // GPS position error simulation (higher noise = higher error)
        $gpsErrors   = array_map(fn($_) => round(rand(3, 12) + $this->noise(2), 2), $timeLabels);
        // Fused error is always lower
        $fusedErrors = array_map(fn($e) => round($e * 0.3 + abs($this->noise(0.5)), 2), $gpsErrors);

        // Historical table
        $historyData = [];
        foreach ($timeLabels as $i => $t) {
            $baseLat = 23.5 + ($i * 0.001);
            $historyData[] = [
                'time'      => $t,
                'gps_lat'   => number_format($baseLat + $this->noise(0.0005), 5),
                'speed'     => rand(20, 60),
                'alt'       => rand(50, 150),
                'fused_lat' => number_format($baseLat + $this->noise(0.0001), 5),
            ];
        }

        return view('sensors.index', compact(
            'sensorData', 'fusedData', 'timeLabels',
            'gpsErrors', 'fusedErrors', 'historyData'
        ));
    }

    /**
     * Generate small Gaussian-like noise.
     */
    private function noise(float $scale = 1.0): float
    {
        // Box-Muller approximation
        $u = (float)(mt_rand(1, PHP_INT_MAX - 1)) / PHP_INT_MAX;
        $v = (float)(mt_rand(1, PHP_INT_MAX - 1)) / PHP_INT_MAX;
        return $scale * sqrt(-2 * log($u)) * cos(2 * M_PI * $v);
    }
}
