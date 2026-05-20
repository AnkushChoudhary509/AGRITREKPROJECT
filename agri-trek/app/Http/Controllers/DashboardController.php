<?php
namespace App\Http\Controllers;

use App\Models\Farmer;
use App\Models\Land;
use App\Models\Scheme;
use App\Models\Drone;
use App\Models\DroneLog;
use App\Models\Waypoint;
use App\Models\SchemeApplication;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Farmer role → farmer dashboard
        if ($user->isFarmer()) {
            $farmer = $user->farmer;
            if ($farmer) {
                $farmer->load(['lands', 'applications.scheme']);
            }
            return view('admin.farmer_dashboard');
        }

        // Admin / Expert → full dashboard
        $stats = [
            'farmers'   => Farmer::count(),
            'lands'     => Land::count(),
            'schemes'   => Scheme::where('is_active', true)->count(),
            'drones'    => Drone::where('status', 'active')->count(),
            'waypoints' => Waypoint::count(),
            'clusters'  => session('cluster_count', 0),
        ];

        // 7-day activity chart
        $chartLabels = [];
        $chartData   = [];
        for ($i = 6; $i >= 0; $i--) {
            $date          = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('D d');
            $chartData[]   = DroneLog::whereDate('created_at', $date)->count();
        }

        // Crop distribution
        $cropGroups = Land::selectRaw('crop_type, COUNT(*) as count')->groupBy('crop_type')->get();
        $cropLabels = $cropGroups->pluck('crop_type')->toArray();
        $cropData   = $cropGroups->pluck('count')->toArray();

        $recentLogs        = DroneLog::with('drone')->latest()->limit(6)->get();
        $droneLocations    = Drone::with('latestLog')->get()
            ->map(fn($d) => [
                'name'      => $d->name,
                'status'    => $d->status,
                'speed'     => $d->latestLog->speed    ?? 0,
                'altitude'  => $d->latestLog->altitude ?? 0,
                'latitude'  => $d->latestLog->latitude  ?? null,
                'longitude' => $d->latestLog->longitude ?? null,
            ])->filter(fn($d) => $d['latitude'])->values();

        $landLocations = Land::whereNotNull('latitude')
            ->get(['latitude','longitude','crop_type','area','soil_type']);

        $pendingApplications = SchemeApplication::with(['farmer','scheme'])
            ->where('status','pending')->latest()->limit(5)->get();

        $recentFarmers = Farmer::withCount('lands')->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats','chartLabels','chartData','cropLabels','cropData',
            'recentLogs','droneLocations','landLocations',
            'pendingApplications','recentFarmers'
        ));
    }
}
