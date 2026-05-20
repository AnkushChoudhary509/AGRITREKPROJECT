<?php

namespace App\Http\Controllers;

use App\Models\Drone;
use App\Models\DroneLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DroneController
{
    public function index()
    {
        $drones = Drone::with('latestLog')->get();

        // Build map data
        $droneMapData = $drones->map(function ($drone) {
            $log = $drone->latestLog;
            // Get last 20 path points for trail
            $path = DroneLog::where('drone_id', $drone->id)
                            ->latest()
                            ->limit(20)
                            ->get(['latitude','longitude'])
                            ->toArray();

            return [
                'id'        => $drone->id,
                'name'      => $drone->name,
                'status'    => $drone->status,
                'lat'       => $log->latitude ?? null,
                'lng'       => $log->longitude ?? null,
                'speed'     => $log->speed ?? 0,
                'altitude'  => $log->altitude ?? 0,
                'direction' => $log->direction ?? 0,
                'path'      => array_reverse($path),
            ];
        })->filter(fn($d) => $d['lat'] !== null)->values();

        $avgSpeed    = round(DroneLog::avg('speed') ?? 0);
        $avgAltitude = round(DroneLog::avg('altitude') ?? 0);

        return view('drones.index', compact('drones', 'droneMapData', 'avgSpeed', 'avgAltitude'));
    }

    public function create()
    {
        return view('drones.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'drone_id'    => 'required|string|max:50|unique:drones,drone_id',
            'model'       => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,idle,offline',
        ]);

        Drone::create($validated);

        return redirect()->route('drones.index')
                         ->with('success', 'Drone added successfully!');
    }

    public function show(Drone $drone)
    {
        $drone->load('logs');
        $logs = DroneLog::where('drone_id', $drone->id)->latest()->limit(50)->get();

        // Build chart data
        $chartLabels = $logs->pluck('created_at')->map(fn($t) => $t->format('H:i'))->reverse()->values();
        $speedData   = $logs->pluck('speed')->reverse()->values();
        $altData     = $logs->pluck('altitude')->reverse()->values();

        $pathCoords = $logs->map(fn($l) => [$l->latitude, $l->longitude])->reverse()->values();

        return view('drones.show', compact('drone', 'logs', 'chartLabels', 'speedData', 'altData', 'pathCoords'));
    }

    public function edit(Drone $drone)
    {
        return view('drones.form', compact('drone'));
    }

    public function update(Request $request, Drone $drone)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'drone_id'    => 'required|string|max:50|unique:drones,drone_id,' . $drone->id,
            'model'       => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status'      => 'required|in:active,idle,offline',
        ]);

        $drone->update($validated);

        return redirect()->route('drones.show', $drone)
                         ->with('success', 'Drone updated!');
    }

    public function destroy(Drone $drone)
    {
        $drone->delete();
        return redirect()->route('drones.index')->with('success', 'Drone deleted.');
    }

    /**
     * Show route history / log timeline for a drone.
     */
    public function logs(Drone $drone)
    {
        $logs = DroneLog::where('drone_id', $drone->id)->latest()->paginate(20);
        return view('drones.logs', compact('drone', 'logs'));
    }

    // =====================
    // API Endpoints
    // =====================

    /**
     * GET /api/drones — list all drones with latest telemetry
     */
    public function apiIndex()
    {
        $drones = Drone::with('latestLog')->get()->map(fn($d) => [
            'id'       => $d->id,
            'name'     => $d->name,
            'drone_id' => $d->drone_id,
            'status'   => $d->status,
            'lat'      => $d->latestLog->latitude ?? null,
            'lng'      => $d->latestLog->longitude ?? null,
            'speed'    => $d->latestLog->speed ?? null,
            'altitude' => $d->latestLog->altitude ?? null,
        ]);

        return response()->json(['status' => 'success', 'data' => $drones]);
    }

    /**
     * POST /api/drones/{id}/log — accept telemetry from a drone
     */
    public function apiLog(Request $request, $id)
    {
        $drone = Drone::where('drone_id', $id)->firstOrFail();

        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed'     => 'required|numeric|min:0|max:200',
            'altitude'  => 'required|numeric|min:0|max:1000',
            'direction' => 'nullable|numeric|between:0,360',
        ]);

        $log = DroneLog::create([
            'drone_id'  => $drone->id,
            'latitude'  => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'speed'     => $validated['speed'],
            'altitude'  => $validated['altitude'],
            'direction' => $validated['direction'] ?? 0,
        ]);

        // Update drone status to active
        $drone->update(['status' => 'active']);

        return response()->json(['status' => 'success', 'log_id' => $log->id]);
    }

    /**
     * GET /api/drones/{id}/path — get route history as GeoJSON
     */
    public function apiPath($id)
    {
        $drone = Drone::where('drone_id', $id)->firstOrFail();
        $logs  = DroneLog::where('drone_id', $drone->id)
                          ->latest()
                          ->limit(100)
                          ->get(['latitude','longitude','speed','altitude','created_at']);

        $features = $logs->map(fn($l) => [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$l->longitude, $l->latitude],
            ],
            'properties' => [
                'speed'     => $l->speed,
                'altitude'  => $l->altitude,
                'timestamp' => $l->created_at,
            ],
        ]);

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
