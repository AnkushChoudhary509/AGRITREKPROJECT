@extends('layouts.app')
@section('title', 'Precision Clustering')
@section('page-title', 'Precision Clustering – Trajectory Analysis')

@push('styles')
<style>
    #cluster-map { height: 480px; border-radius: 10px; }
    .cluster-legend-dot { width: 14px; height: 14px; border-radius: 50%; display: inline-block; }
    .cluster-info-card { border-left: 4px solid; border-radius: 8px; }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Trajectory Clustering</h5>
        <p class="text-muted small mb-0">K-Means clustering of drone movement data to identify hotspot zones</p>
    </div>
    <form method="POST" action="{{ route('clustering.run') }}">
        @csrf
        <div class="input-group">
            <input type="number" name="k" class="form-control" style="width:80px;"
                   value="{{ $k ?? 4 }}" min="2" max="10" placeholder="K">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-diagram-3-fill me-1"></i>Run Clustering
            </button>
        </div>
    </form>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fw-bold fs-4 text-success">{{ $totalPoints }}</div>
            <small class="text-muted">Total Trajectory Points</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fw-bold fs-4 text-primary">{{ $clusterCount }}</div>
            <small class="text-muted">Clusters Identified</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fw-bold fs-4 text-warning">{{ $hotspotCluster ?? 'N/A' }}</div>
            <small class="text-muted">Largest Cluster</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center py-3">
            <div class="fw-bold fs-4 text-danger">{{ $coverageArea ?? '—' }}</div>
            <small class="text-muted">Coverage Area</small>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Map -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between">
                <span><i class="bi bi-map me-2"></i>Cluster Visualization Map</span>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($clusters as $i => $cluster)
                    <span class="badge" style="background-color:{{ $cluster['color'] }};">
                        Cluster {{ $i+1 }} ({{ count($cluster['points']) }})
                    </span>
                    @endforeach
                </div>
            </div>
            <div class="card-body p-2">
                <div id="cluster-map"></div>
            </div>
        </div>
    </div>

    <!-- Cluster Info -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-info-circle me-2"></i>Cluster Details
            </div>
            <div class="card-body p-2" style="max-height:460px;overflow-y:auto;">
                @foreach($clusters as $i => $cluster)
                <div class="cluster-info-card p-3 mb-3 bg-light"
                     style="border-color: {{ $cluster['color'] }}">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong>
                            <span class="cluster-legend-dot me-2" style="background:{{ $cluster['color'] }}"></span>
                            Cluster {{ $i+1 }}
                        </strong>
                        <span class="badge bg-secondary">{{ count($cluster['points']) }} pts</span>
                    </div>
                    <div class="mt-2 small">
                        <div class="row g-1">
                            <div class="col-6">
                                <span class="text-muted">Center Lat:</span><br>
                                <strong>{{ number_format($cluster['centroid']['lat'], 5) }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted">Center Lng:</span><br>
                                <strong>{{ number_format($cluster['centroid']['lng'], 5) }}</strong>
                            </div>
                            <div class="col-6 mt-1">
                                <span class="text-muted">Avg Speed:</span><br>
                                <strong>{{ number_format($cluster['avg_speed'] ?? 0, 1) }} km/h</strong>
                            </div>
                            <div class="col-6 mt-1">
                                <span class="text-muted">Avg Altitude:</span><br>
                                <strong>{{ number_format($cluster['avg_altitude'] ?? 0, 1) }}m</strong>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-muted">Zone Type:</span>
                            <span class="badge"
                                  style="background:{{ $cluster['color'] }};font-size:10px;">
                                {{ $cluster['zone_type'] ?? 'Monitoring Zone' }}
                            </span>
                        </div>
                    </div>
                </div>
                @endforeach

                @if(empty($clusters))
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-diagram-3 fs-2 d-block mb-2 opacity-25"></i>
                    Run clustering to see results
                </div>
                @endif
            </div>
        </div>

        <!-- Algorithm Info -->
        <div class="card mt-3">
            <div class="card-header bg-info text-white">
                <i class="bi bi-cpu me-2"></i>Algorithm Info
            </div>
            <div class="card-body small">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted">Algorithm</td><td><strong>K-Means</strong></td></tr>
                    <tr><td class="text-muted">K Value</td><td><strong>{{ $k ?? 4 }}</strong></td></tr>
                    <tr><td class="text-muted">Iterations</td><td><strong>{{ $iterations ?? 10 }}</strong></td></tr>
                    <tr><td class="text-muted">Metric</td><td><strong>Euclidean Distance</strong></td></tr>
                    <tr><td class="text-muted">Feature</td><td><strong>GPS (lat, lng)</strong></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Cluster Chart -->
<div class="row g-3 mt-1">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-bar-chart me-2"></i>Points per Cluster
            </div>
            <div class="card-body">
                <canvas id="clusterBarChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-speedometer me-2"></i>Avg Speed per Cluster
            </div>
            <div class="card-body">
                <canvas id="speedChart" height="120"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const clusterData = {!! json_encode($clusters) !!};
const map = L.map('cluster-map').setView([23.5, 72.5], 11);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
}).addTo(map);

clusterData.forEach((cluster, i) => {
    // Draw cluster points
    cluster.points.forEach(p => {
        L.circleMarker([p.latitude, p.longitude], {
            radius: 5,
            fillColor: cluster.color,
            color: cluster.color,
            weight: 1,
            fillOpacity: 0.7
        }).addTo(map).bindPopup(`
            <strong>Cluster ${i+1}</strong><br>
            Speed: ${p.speed} km/h<br>
            Alt: ${p.altitude}m
        `);
    });

    // Draw centroid
    if (cluster.centroid) {
        const centIcon = L.divIcon({
            html: `<div style="background:${cluster.color};border:3px solid #fff;border-radius:50%;
                               width:20px;height:20px;box-shadow:0 2px 8px rgba(0,0,0,0.4);
                               display:flex;align-items:center;justify-content:center;
                               color:#fff;font-weight:bold;font-size:10px;">${i+1}</div>`,
            className: '',
            iconSize: [20,20],
            iconAnchor: [10,10]
        });
        L.marker([cluster.centroid.lat, cluster.centroid.lng], { icon: centIcon })
            .addTo(map)
            .bindPopup(`<strong>Centroid ${i+1}</strong><br>${cluster.zone_type}`);

        // Draw convex hull circle approx
        L.circle([cluster.centroid.lat, cluster.centroid.lng], {
            radius: cluster.radius || 500,
            color: cluster.color,
            fillColor: cluster.color,
            fillOpacity: 0.08,
            weight: 2,
            dashArray: '6,4'
        }).addTo(map);
    }
});

// Bar chart
const labels = clusterData.map((_, i) => `Cluster ${i+1}`);
const counts = clusterData.map(c => c.points.length);
const colors = clusterData.map(c => c.color);

new Chart(document.getElementById('clusterBarChart').getContext('2d'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Points', data: counts, backgroundColor: colors, borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

const speeds = clusterData.map(c => parseFloat(c.avg_speed || 0).toFixed(1));
new Chart(document.getElementById('speedChart').getContext('2d'), {
    type: 'radar',
    data: {
        labels,
        datasets: [{
            label: 'Avg Speed (km/h)',
            data: speeds,
            backgroundColor: 'rgba(255,152,0,0.2)',
            borderColor: '#ff9800',
            pointBackgroundColor: colors,
            pointRadius: 5
        }]
    },
    options: { plugins: { legend: { display: false } } }
});
</script>
@endpush
