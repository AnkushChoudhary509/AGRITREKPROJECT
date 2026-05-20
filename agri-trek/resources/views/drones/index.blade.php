@extends('layouts.app')
@section('title', 'Drone Monitoring')
@section('page-title', 'Drone Monitoring & Tracking')

@push('styles')
<style>
    #drone-map { height: 480px; border-radius: 10px; }
    .drone-status-active { color: #4caf50; }
    .drone-status-idle { color: #ff9800; }
    .drone-status-offline { color: #f44336; }
    .drone-card { cursor: pointer; transition: 0.2s; }
    .drone-card:hover { border-color: #4caf50 !important; background: #f1f8e9; }
    .sensor-badge { background: #e8f5e9; color: #2e7d32; border-radius: 8px; padding: 4px 10px; font-size: 12px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Drone Fleet Monitoring</h5>
        <p class="text-muted small mb-0">Real-time drone position and telemetry data</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('drones.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add Drone
        </a>
        <button class="btn btn-outline-success" id="refreshBtn" onclick="refreshDrones()">
            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
        </button>
    </div>
</div>

<div class="row g-3">
    <!-- Drone List Panel -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-airplane-engines me-2"></i>Drone Fleet ({{ $drones->count() }})
            </div>
            <div class="card-body p-2" style="max-height:460px;overflow-y:auto;">
                @forelse($drones as $drone)
                <div class="drone-card border rounded p-3 mb-2" onclick="focusDrone({{ $drone->id }})">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">{{ $drone->name }}</div>
                            <small class="text-muted">ID: {{ $drone->drone_id }}</small>
                        </div>
                        <span class="badge bg-{{ $drone->status === 'active' ? 'success' : ($drone->status === 'idle' ? 'warning' : 'danger') }}">
                            {{ ucfirst($drone->status) }}
                        </span>
                    </div>
                    @if($drone->latestLog)
                    <div class="row g-1 mt-2">
                        <div class="col-6">
                            <span class="sensor-badge"><i class="bi bi-speedometer me-1"></i>{{ $drone->latestLog->speed }} km/h</span>
                        </div>
                        <div class="col-6">
                            <span class="sensor-badge"><i class="bi bi-arrow-up me-1"></i>{{ $drone->latestLog->altitude }}m</span>
                        </div>
                        <div class="col-12 mt-1">
                            <small class="text-muted">
                                <i class="bi bi-geo me-1"></i>
                                {{ number_format($drone->latestLog->latitude, 5) }},
                                {{ number_format($drone->latestLog->longitude, 5) }}
                            </small>
                        </div>
                    </div>
                    @else
                    <small class="text-muted d-block mt-2">No telemetry data yet</small>
                    @endif
                    <div class="d-flex gap-1 mt-2">
                        <a href="{{ route('drones.show', $drone) }}" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-eye me-1"></i>Details
                        </a>
                        <a href="{{ route('drones.logs', $drone) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-clock-history me-1"></i>Logs
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-airplane-engines fs-2 d-block mb-2 opacity-25"></i>
                    No drones registered
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Map Panel -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-map me-2"></i>Live Tracking Map</span>
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-circle-fill text-success me-1" style="font-size:8px;"></i>Active
                    </span>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-circle-fill text-warning me-1" style="font-size:8px;"></i>Idle
                    </span>
                    <span class="badge bg-light text-dark">
                        <i class="bi bi-circle-fill text-danger me-1" style="font-size:8px;"></i>Offline
                    </span>
                </div>
            </div>
            <div class="card-body p-2">
                <div id="drone-map"></div>
            </div>
        </div>

        <!-- Telemetry Summary -->
        <div class="row g-2 mt-2">
            <div class="col-3">
                <div class="card text-center py-3">
                    <div class="fw-bold fs-5 text-success">{{ $drones->where('status','active')->count() }}</div>
                    <small class="text-muted">Active</small>
                </div>
            </div>
            <div class="col-3">
                <div class="card text-center py-3">
                    <div class="fw-bold fs-5 text-warning">{{ $drones->where('status','idle')->count() }}</div>
                    <small class="text-muted">Idle</small>
                </div>
            </div>
            <div class="col-3">
                <div class="card text-center py-3">
                    <div class="fw-bold fs-5 text-primary">{{ $avgSpeed }}</div>
                    <small class="text-muted">Avg Speed km/h</small>
                </div>
            </div>
            <div class="col-3">
                <div class="card text-center py-3">
                    <div class="fw-bold fs-5 text-info">{{ $avgAltitude }}</div>
                    <small class="text-muted">Avg Altitude m</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const droneData = {!! json_encode($droneMapData) !!};
const map = L.map('drone-map').setView([23.5, 72.5], 11);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

const droneMarkers = {};
const droneColors = { active: '#4caf50', idle: '#ff9800', offline: '#f44336' };

droneData.forEach(drone => {
    if (!drone.lat || !drone.lng) return;

    const color = droneColors[drone.status] || '#666';

    // Drone icon
    const icon = L.divIcon({
        html: `<div style="background:${color};border:2px solid #fff;border-radius:50%;width:16px;height:16px;
                           box-shadow:0 2px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;">
                   <span style="color:#fff;font-size:8px;">✈</span>
               </div>`,
        className: '',
        iconSize: [16, 16],
        iconAnchor: [8, 8]
    });

    const marker = L.marker([drone.lat, drone.lng], { icon })
        .addTo(map)
        .bindPopup(`
            <div style="min-width:160px;">
                <strong>🚁 ${drone.name}</strong><br>
                <small>Status: <b style="color:${color}">${drone.status}</b></small><br>
                <small>Speed: ${drone.speed} km/h</small><br>
                <small>Altitude: ${drone.altitude}m</small><br>
                <small>Direction: ${drone.direction}°</small>
            </div>
        `);

    droneMarkers[drone.id] = { marker, data: drone };

    // Draw route trail if path data available
    if (drone.path && drone.path.length > 1) {
        const pathCoords = drone.path.map(p => [p.latitude, p.longitude]);
        L.polyline(pathCoords, {
            color: color,
            weight: 2,
            opacity: 0.5,
            dashArray: '5,5'
        }).addTo(map);
    }
});

function focusDrone(id) {
    const d = droneMarkers[id];
    if (d) {
        map.setView([d.data.lat, d.data.lng], 14);
        d.marker.openPopup();
    }
}

function refreshDrones() {
    const btn = document.getElementById('refreshBtn');
    btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1 spin"></i>Refreshing...';
    setTimeout(() => {
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh';
    }, 1500);
}
</script>
@endpush
