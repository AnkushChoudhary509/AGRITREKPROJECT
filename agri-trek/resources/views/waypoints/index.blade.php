@extends('layouts.app')
@section('title', 'Waypoints')
@section('page-title', 'Waypoint-Based Navigation')

@push('styles')
<style>
    #waypoint-map { height: 460px; border-radius: 10px; }
    .waypoint-item { cursor: pointer; transition: 0.2s; }
    .waypoint-item:hover { background: #f1f8e9; }
    .waypoint-reached { opacity: 0.5; text-decoration: line-through; }
    .sim-progress { height: 8px; border-radius: 4px; transition: width 0.5s; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.4;} }
    .drone-moving { animation: pulse 1s infinite; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Waypoint Navigation</h5>
        <p class="text-muted small mb-0">{{ $totalWaypoints }} waypoints across {{ $totalRoutes }} routes</p>
    </div>
    <a href="{{ route('waypoints.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add Waypoint
    </a>
</div>

<div class="row g-3">
    <!-- Route Selector & List -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-success text-white">
                <i class="bi bi-pin-map me-2"></i>Waypoint Routes
            </div>
            <div class="card-body py-2">
                @foreach($routes as $routeName)
                <button class="btn btn-outline-success btn-sm mb-1 me-1 route-btn"
                        data-route="{{ $routeName }}"
                        onclick="loadRoute('{{ $routeName }}')">
                    <i class="bi bi-route me-1"></i>{{ $routeName }}
                </button>
                @endforeach
            </div>
        </div>

        <!-- Waypoint List Panel -->
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span id="activeRouteName"><i class="bi bi-list-ol me-2"></i>Select a Route</span>
            </div>
            <div class="card-body p-2" id="waypointListBody" style="max-height:320px;overflow-y:auto;">
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-pin-map fs-2 d-block mb-2 opacity-25"></i>
                    Click a route button above to view waypoints
                </div>
            </div>
        </div>

        <!-- Simulation Controls -->
        <div class="card mt-3">
            <div class="card-header bg-warning">
                <i class="bi bi-play-circle me-2"></i>Simulation Controls
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Progress</small>
                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-success sim-progress" id="simProgress" style="width:0%"></div>
                    </div>
                </div>
                <div id="simStatus" class="small text-muted mb-3">Select a route and click Start to simulate</div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success flex-fill" id="startSimBtn" onclick="startSimulation()" disabled>
                        <i class="bi bi-play-fill me-1"></i>Start
                    </button>
                    <button class="btn btn-outline-danger" id="resetSimBtn" onclick="resetSimulation()" disabled>
                        <i class="bi bi-stop-fill"></i>
                    </button>
                </div>
                <div class="mt-2">
                    <small class="text-muted">Simulation Speed</small>
                    <input type="range" class="form-range" id="simSpeed" min="500" max="3000" step="500" value="1500">
                    <small class="text-muted">Slower ←→ Faster</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Map -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-map me-2"></i>Route Visualization Map
            </div>
            <div class="card-body p-2">
                <div id="waypoint-map"></div>
            </div>
        </div>

        <!-- All Waypoints Table -->
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-table me-2"></i>All Waypoints
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 220px; overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr><th>Name</th><th>Route</th><th>Seq</th><th>Lat</th><th>Lng</th><th>Alt(m)</th><th>Spd</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($routeData as $route)
                                @foreach($route['waypoints'] as $wp)
                                <tr class="{{ $wp['is_reached'] ? 'table-success' : '' }}">
                                    <td class="small fw-semibold">{{ $wp['name'] }}</td>
                                    <td class="small text-muted">{{ $route['name'] }}</td>
                                    <td class="small"><span class="badge bg-secondary">{{ $wp['sequence'] }}</span></td>
                                    <td class="small font-monospace">{{ number_format($wp['lat'], 4) }}</td>
                                    <td class="small font-monospace">{{ number_format($wp['lng'], 4) }}</td>
                                    <td class="small">{{ $wp['altitude'] }}</td>
                                    <td class="small">{{ $wp['speed'] }}</td>
                                    <td>
                                        <form action="{{ route('waypoints.destroy', $wp['id']) }}" method="POST"
                                              onsubmit="return confirm('Delete?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-xs btn-outline-danger py-0 px-1" style="font-size:10px;">✗</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const routeData = {!! json_encode($routeData) !!};
const map = L.map('waypoint-map').setView([23.5, 72.5], 11);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

let currentRoute     = null;
let currentRouteData = null;
let droneMarker      = null;
let simInterval      = null;
let currentWpIndex   = 0;
let routeLayers      = [];

// Draw all routes lightly on load
routeData.forEach(route => {
    const coords = route.waypoints.map(w => [w.lat, w.lng]);
    if (coords.length > 1) {
        L.polyline(coords, { color: '#aaa', weight: 1, dashArray: '4,4', opacity: 0.5 }).addTo(map);
    }
    route.waypoints.forEach(wp => {
        L.circleMarker([wp.lat, wp.lng], {
            radius: 5, fillColor: wp.is_reached ? '#4caf50' : '#bbb',
            color: '#fff', weight: 1, fillOpacity: 0.8
        }).addTo(map).bindPopup(`<strong>${wp.name}</strong><br>Seq: ${wp.sequence}`);
    });
});

function loadRoute(routeName) {
    // Clear old layers
    routeLayers.forEach(l => map.removeLayer(l));
    routeLayers = [];

    currentRoute     = routeName;
    currentRouteData = routeData.find(r => r.name === routeName);
    if (!currentRouteData) return;

    document.getElementById('activeRouteName').innerHTML = `<i class="bi bi-route me-2"></i>${routeName}`;
    document.getElementById('startSimBtn').disabled = false;

    const wps   = currentRouteData.waypoints;
    const coords = wps.map(w => [w.lat, w.lng]);

    // Draw active route line
    const poly = L.polyline(coords, { color: '#4caf50', weight: 3, opacity: 0.9 }).addTo(map);
    routeLayers.push(poly);
    map.fitBounds(poly.getBounds().pad(0.15));

    // Draw waypoint markers
    wps.forEach((wp, i) => {
        const icon = L.divIcon({
            html: `<div style="background:#2e7d32;color:#fff;border-radius:50%;width:24px;height:24px;
                               display:flex;align-items:center;justify-content:center;font-size:11px;
                               font-weight:bold;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.3);">
                       ${wp.sequence}
                   </div>`,
            className: '', iconSize: [24, 24], iconAnchor: [12, 12]
        });
        const m = L.marker([wp.lat, wp.lng], { icon }).addTo(map)
            .bindPopup(`<strong>WP${wp.sequence}: ${wp.name}</strong><br>
                        Alt: ${wp.altitude}m | Speed: ${wp.speed}km/h`);
        routeLayers.push(m);
    });

    // Update waypoint list
    let html = '';
    wps.forEach((wp, i) => {
        html += `<div class="waypoint-item d-flex align-items-center gap-2 p-2 border-bottom ${wp.is_reached ? 'waypoint-reached' : ''}" id="wp-${wp.id}">
            <span class="badge bg-success rounded-circle">${wp.sequence}</span>
            <div class="flex-fill">
                <div class="small fw-semibold">${wp.name}</div>
                <div class="text-muted" style="font-size:11px;">${wp.lat.toFixed(4)}, ${wp.lng.toFixed(4)} | ${wp.altitude}m | ${wp.speed}km/h</div>
            </div>
            ${wp.is_reached ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-circle text-muted"></i>'}
        </div>`;
    });
    document.getElementById('waypointListBody').innerHTML = html;
    document.getElementById('simStatus').textContent = `Route loaded: ${wps.length} waypoints`;
}

function startSimulation() {
    if (!currentRouteData) return;
    const wps = currentRouteData.waypoints;
    currentWpIndex = 0;
    document.getElementById('startSimBtn').disabled = true;
    document.getElementById('resetSimBtn').disabled = false;

    // Create drone marker at first waypoint
    if (droneMarker) map.removeLayer(droneMarker);
    const droneIcon = L.divIcon({
        html: `<div class="drone-moving" style="font-size:24px;">🚁</div>`,
        className: '', iconSize: [28, 28], iconAnchor: [14, 14]
    });
    droneMarker = L.marker([wps[0].lat, wps[0].lng], { icon: droneIcon }).addTo(map);
    routeLayers.push(droneMarker);

    moveToNextWaypoint(wps);
}

function moveToNextWaypoint(wps) {
    if (currentWpIndex >= wps.length) {
        document.getElementById('simStatus').textContent = '✅ Route completed!';
        document.getElementById('simProgress').style.width = '100%';
        return;
    }

    const wp   = wps[currentWpIndex];
    const pct  = Math.round(((currentWpIndex + 1) / wps.length) * 100);
    document.getElementById('simProgress').style.width = pct + '%';
    document.getElementById('simStatus').textContent =
        `Moving to WP${wp.sequence}: ${wp.name} (${pct}%)`;

    if (droneMarker) droneMarker.setLatLng([wp.lat, wp.lng]).openPopup();
    map.panTo([wp.lat, wp.lng], { animate: true, duration: 0.5 });

    // Mark waypoint as reached in UI
    const el = document.getElementById('wp-' + wp.id);
    if (el) {
        el.classList.add('waypoint-reached');
        el.querySelector('.bi-circle')?.classList.replace('bi-circle', 'bi-check-circle-fill');
    }

    // Mark in backend
    fetch(`/waypoints/${wp.id}/reached`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
    });

    currentWpIndex++;
    const delay = 3500 - parseInt(document.getElementById('simSpeed').value);
    simInterval = setTimeout(() => moveToNextWaypoint(wps), Math.max(500, delay));
}

function resetSimulation() {
    clearTimeout(simInterval);
    if (droneMarker) { map.removeLayer(droneMarker); droneMarker = null; }
    document.getElementById('simProgress').style.width = '0%';
    document.getElementById('simStatus').textContent = 'Simulation reset.';
    document.getElementById('startSimBtn').disabled = false;
    document.getElementById('resetSimBtn').disabled = true;
    currentWpIndex = 0;
    if (currentRoute) loadRoute(currentRoute);
}
</script>
@endpush
