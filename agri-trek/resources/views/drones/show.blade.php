@extends('layouts.app')
@section('title', $drone->name)
@section('page-title', 'Drone Detail')

@push('styles')
<style>#drone-detail-map { height: 320px; border-radius: 10px; }</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('drones.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ $drone->name }}</h5>
    <span class="badge bg-{{ $drone->status === 'active' ? 'success' : ($drone->status === 'idle' ? 'warning' : 'danger') }} ms-1">
        {{ ucfirst($drone->status) }}
    </span>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('drones.edit', $drone) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form action="{{ route('drones.destroy', $drone) }}" method="POST"
              onsubmit="return confirm('Delete this drone?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <!-- Drone Info -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-airplane-engines me-2"></i>Drone Info
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted">Drone ID</td><td><strong>{{ $drone->drone_id }}</strong></td></tr>
                    <tr><td class="text-muted">Model</td><td>{{ $drone->model ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge bg-{{ $drone->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($drone->status) }}</span></td></tr>
                    <tr><td class="text-muted">Total Logs</td><td>{{ $logs->count() }}</td></tr>
                    <tr><td class="text-muted">Avg Speed</td><td>{{ round($logs->avg('speed') ?? 0) }} km/h</td></tr>
                    <tr><td class="text-muted">Avg Altitude</td><td>{{ round($logs->avg('altitude') ?? 0) }}m</td></tr>
                    <tr><td class="text-muted">Max Speed</td><td>{{ $logs->max('speed') ?? 0 }} km/h</td></tr>
                    <tr><td class="text-muted">Registered</td><td>{{ $drone->created_at->format('d M Y') }}</td></tr>
                </table>
                @if($drone->description)
                <hr>
                <small class="text-muted">{{ $drone->description }}</small>
                @endif
            </div>
        </div>
    </div>

    <!-- Route Map -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-map me-2"></i>Flight Path Map
            </div>
            <div class="card-body p-2">
                <div id="drone-detail-map"></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-speedometer me-2"></i>Speed Over Time
            </div>
            <div class="card-body">
                <canvas id="speedChart" height="130"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-arrow-up me-2"></i>Altitude Over Time
            </div>
            <div class="card-body">
                <canvas id="altChart" height="130"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Logs -->
<div class="card">
    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clock-history me-2"></i>Recent Telemetry Logs</span>
        <a href="{{ route('drones.logs', $drone) }}" class="btn btn-sm btn-light text-info">View All</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>Time</th><th>Latitude</th><th>Longitude</th><th>Speed</th><th>Altitude</th><th>Direction</th></tr>
                </thead>
                <tbody>
                    @forelse($logs->take(10) as $log)
                    <tr>
                        <td class="small text-muted">{{ $log->created_at->format('H:i:s') }}</td>
                        <td class="small">{{ number_format($log->latitude, 6) }}</td>
                        <td class="small">{{ number_format($log->longitude, 6) }}</td>
                        <td><span class="badge bg-primary">{{ $log->speed }} km/h</span></td>
                        <td><span class="badge bg-warning text-dark">{{ $log->altitude }}m</span></td>
                        <td class="small text-muted">{{ $log->direction }}°</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No telemetry logs</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Flight path map
const map = L.map('drone-detail-map').setView([23.5, 72.5], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

const path = {!! json_encode($pathCoords) !!};
if (path.length > 0) {
    const polyline = L.polyline(path, { color: '#4caf50', weight: 3, opacity: 0.8 }).addTo(map);
    map.fitBounds(polyline.getBounds().pad(0.1));

    // Start marker
    L.circleMarker(path[0], { radius: 10, fillColor: '#2196f3', color: '#fff', weight: 2, fillOpacity: 1 })
        .addTo(map).bindPopup('🛫 Start');

    // End marker
    const last = path[path.length - 1];
    L.circleMarker(last, { radius: 10, fillColor: '#f44336', color: '#fff', weight: 2, fillOpacity: 1 })
        .addTo(map).bindPopup('🛬 Current Position');
}

// Speed Chart
new Chart(document.getElementById('speedChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Speed (km/h)',
            data: {!! json_encode($speedData) !!},
            borderColor: '#2196f3', backgroundColor: 'rgba(33,150,243,0.1)',
            fill: true, tension: 0.4, pointRadius: 2
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

// Altitude Chart
new Chart(document.getElementById('altChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [{
            label: 'Altitude (m)',
            data: {!! json_encode($altData) !!},
            borderColor: '#ff9800', backgroundColor: 'rgba(255,152,0,0.1)',
            fill: true, tension: 0.4, pointRadius: 2
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>
@endpush
