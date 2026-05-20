<?php
namespace App\Http\Controllers;

use App\Models\Waypoint;
use App\Models\Drone;
use Illuminate\Http\Request;

class WaypointController
{
    public function index()
    {
        $routes  = Waypoint::select('route_name')->distinct()->pluck('route_name');
        $drones  = Drone::all();

        // Build route data for map
        $routeData = [];
        foreach ($routes as $routeName) {
            $wps = Waypoint::where('route_name', $routeName)
                           ->orderBy('sequence')
                           ->get();
            $routeData[] = [
                'name'      => $routeName,
                'waypoints' => $wps->map(fn($w) => [
                    'id'         => $w->id,
                    'name'       => $w->name,
                    'lat'        => $w->latitude,
                    'lng'        => $w->longitude,
                    'sequence'   => $w->sequence,
                    'altitude'   => $w->altitude,
                    'speed'      => $w->speed,
                    'is_reached' => $w->is_reached,
                ])->toArray(),
            ];
        }

        $totalWaypoints = Waypoint::count();
        $totalRoutes    = count($routes);

        return view('waypoints.index', compact('routeData', 'drones', 'totalWaypoints', 'totalRoutes', 'routes'));
    }

    public function create()
    {
        $drones = Drone::all();
        return view('waypoints.form', compact('drones'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'route_name' => 'required|string|max:100',
            'drone_id'   => 'nullable|exists:drones,id',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'sequence'   => 'required|integer|min:1',
            'altitude'   => 'nullable|numeric|min:0|max:500',
            'speed'      => 'nullable|numeric|min:0|max:120',
            'notes'      => 'nullable|string',
        ]);

        Waypoint::create($validated);
        return redirect()->route('waypoints.index')->with('success', 'Waypoint added!');
    }

    public function edit(Waypoint $waypoint)
    {
        $drones = Drone::all();
        return view('waypoints.form', compact('waypoint', 'drones'));
    }

    public function update(Request $request, Waypoint $waypoint)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'route_name' => 'required|string|max:100',
            'drone_id'   => 'nullable|exists:drones,id',
            'latitude'   => 'required|numeric|between:-90,90',
            'longitude'  => 'required|numeric|between:-180,180',
            'sequence'   => 'required|integer|min:1',
            'altitude'   => 'nullable|numeric',
            'speed'      => 'nullable|numeric',
            'notes'      => 'nullable|string',
        ]);
        $waypoint->update($validated);
        return redirect()->route('waypoints.index')->with('success', 'Waypoint updated!');
    }

    public function destroy(Waypoint $waypoint)
    {
        $waypoint->delete();
        return redirect()->route('waypoints.index')->with('success', 'Waypoint deleted.');
    }

    /** Simulate drone traversing a waypoint route */
    public function simulate(Request $request)
    {
        $routeName = $request->route_name;
        $waypoints = Waypoint::where('route_name', $routeName)
                             ->orderBy('sequence')
                             ->get();

        // Mark all as not reached, then return them for JS simulation
        Waypoint::where('route_name', $routeName)->update(['is_reached' => false]);

        return response()->json([
            'status'     => 'success',
            'route_name' => $routeName,
            'waypoints'  => $waypoints->map(fn($w) => [
                'id'       => $w->id,
                'name'     => $w->name,
                'lat'      => $w->latitude,
                'lng'      => $w->longitude,
                'sequence' => $w->sequence,
                'altitude' => $w->altitude,
                'speed'    => $w->speed,
            ]),
        ]);
    }

    /** Mark a single waypoint as reached (called from JS during simulation) */
    public function markReached(Waypoint $waypoint)
    {
        $waypoint->update(['is_reached' => true]);
        return response()->json(['status' => 'ok']);
    }
}
