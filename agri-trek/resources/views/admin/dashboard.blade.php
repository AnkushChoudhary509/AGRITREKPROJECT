@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@push('styles')
<style>
    .bg-g-green  { background:linear-gradient(135deg,#1b5e20,#43a047); }
    .bg-g-blue   { background:linear-gradient(135deg,#0277bd,#29b6f6); }
    .bg-g-orange { background:linear-gradient(135deg,#e65100,#ffa726); }
    .bg-g-purple { background:linear-gradient(135deg,#6a1b9a,#ab47bc); }
    .bg-g-teal   { background:linear-gradient(135deg,#00695c,#26c6da); }
    .bg-g-red    { background:linear-gradient(135deg,#b71c1c,#ef5350); }
    #dashboard-map { height:340px; border-radius:10px; }
    .activity-item { padding:10px 0; border-bottom:1px solid #f0f0f0; font-size:13px; }
    .activity-item:last-child { border-bottom:none; }
    .drone-dot { width:10px;height:10px;border-radius:50%;display:inline-block; }
</style>
@endpush

@section('content')

<!-- ── Welcome Banner ─────────────────────────────────────────────── -->
<div class="alert border-0 mb-4 d-flex align-items-center gap-3"
     style="background:linear-gradient(135deg,#e8f5e9,#f1f8e9);border-radius:14px;">
    <i class="bi bi-sun-fill text-warning fs-3"></i>
    <div>
        <strong class="text-success">Good {{ now()->hour < 12 ? 'Morning' : (now()->hour < 17 ? 'Afternoon' : 'Evening') }}, {{ auth()->user()->name }}!</strong>
        <div class="text-muted" style="font-size:13px;">
            Here's your Agri-Trek overview for {{ now()->format('l, d F Y') }}.
        </div>
    </div>
</div>

<!-- ── Stats Row ──────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card bg-g-green">
            <i class="bi bi-people-fill stat-icon"></i>
            <h3>{{ $stats['farmers'] }}</h3>
            <p>Total Farmers</p>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card bg-g-blue">
            <i class="bi bi-map-fill stat-icon"></i>
            <h3>{{ $stats['lands'] }}</h3>
            <p>Land Records</p>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card bg-g-orange">
            <i class="bi bi-award-fill stat-icon"></i>
            <h3>{{ $stats['schemes'] }}</h3>
            <p>Active Schemes</p>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card bg-g-purple">
            <i class="bi bi-airplane-engines-fill stat-icon"></i>
            <h3>{{ $stats['drones'] }}</h3>
            <p>Active Drones</p>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card bg-g-teal">
            <i class="bi bi-pin-map-fill stat-icon"></i>
            <h3>{{ $stats['waypoints'] }}</h3>
            <p>Waypoints</p>
        </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card bg-g-red">
            <i class="bi bi-diagram-3-fill stat-icon"></i>
            <h3>{{ $stats['clusters'] }}</h3>
            <p>Clusters Found</p>
        </div>
    </div>
</div>

<!-- ── Charts Row ─────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bar-chart-fill me-2"></i>Drone Activity — Last 7 Days</span>
                <span class="badge bg-light text-success">{{ array_sum($chartData) }} total logs</span>
            </div>
            <div class="card-body">
                <canvas id="droneChart" height="75"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-pie-chart-fill me-2"></i>Crop Distribution
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="cropChart" style="max-height:200px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- ── Map + Activity ─────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-geo-alt-fill me-2"></i>Live Drone & Land Map</span>
                <div class="d-flex gap-2">
                    <span class="badge bg-success"><span class="drone-dot bg-white me-1"></span>Active</span>
                    <span class="badge bg-primary"><span class="drone-dot bg-white me-1"></span>Land</span>
                </div>
            </div>
            <div class="card-body p-2">
                <div id="dashboard-map"></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-warning">
                <i class="bi bi-clock-history me-2"></i>Recent Drone Logs
            </div>
            <div class="card-body p-3" style="max-height:370px;overflow-y:auto;">
                @forelse($recentLogs as $log)
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <strong class="text-success">{{ $log->drone->name ?? 'Drone-'.$log->drone_id }}</strong>
                        <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-geo-alt me-1"></i>{{ number_format($log->latitude,4) }},{{ number_format($log->longitude,4) }}
                        &nbsp;·&nbsp;<i class="bi bi-speedometer me-1"></i>{{ $log->speed }} km/h
                        &nbsp;·&nbsp;<i class="bi bi-arrow-up me-1"></i>{{ $log->altitude }}m
                    </small>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>No logs yet
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- ── Bottom Row ─────────────────────────────────────────────────── -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between">
                <span><i class="bi bi-file-earmark-check-fill me-2"></i>Pending Applications</span>
                <a href="{{ route('schemes.applications') }}" class="btn btn-sm btn-light text-info">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Farmer</th><th>Scheme</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                        @forelse($pendingApplications as $app)
                        <tr>
                            <td style="font-size:13px;">{{ $app->farmer->name ?? '—' }}</td>
                            <td style="font-size:13px;">{{ Str::limit($app->scheme->name ?? '—',20) }}</td>
                            <td style="font-size:12px;color:#888;">{{ $app->created_at->format('d M') }}</td>
                            <td>
                                <form action="{{ route('schemes.applications.update',$app) }}" method="POST" class="d-flex gap-1">
                                    @csrf @method('PATCH')
                                    <button name="status" value="approved" class="btn btn-xs btn-success py-0 px-2" style="font-size:11px;">✓</button>
                                    <button name="status" value="rejected" class="btn btn-xs btn-danger py-0 px-2" style="font-size:11px;">✗</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3" style="font-size:13px;">No pending applications</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-success text-white d-flex justify-content-between">
                <span><i class="bi bi-people-fill me-2"></i>Recent Farmers</span>
                <a href="{{ route('farmers.index') }}" class="btn btn-sm btn-light text-success">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>Name</th><th>Village</th><th>Lands</th><th></th></tr></thead>
                    <tbody>
                        @forelse($recentFarmers as $f)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                         style="width:30px;height:30px;font-size:12px;font-weight:700;flex-shrink:0;">
                                        {{ strtoupper(substr($f->name,0,1)) }}
                                    </div>
                                    <span style="font-size:13px;">{{ $f->name }}</span>
                                </div>
                            </td>
                            <td style="font-size:13px;">{{ $f->village }}</td>
                            <td><span class="badge bg-primary">{{ $f->lands_count ?? 0 }}</span></td>
                            <td><a href="{{ route('farmers.show',$f) }}" class="btn btn-sm btn-outline-success py-0 px-2">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-3" style="font-size:13px;">No farmers yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Drone Activity Chart
new Chart(document.getElementById('droneChart'),{
    type:'bar',
    data:{
        labels:{!! json_encode($chartLabels) !!},
        datasets:[{
            label:'Drone Logs',
            data:{!! json_encode($chartData) !!},
            backgroundColor:'rgba(46,125,50,0.75)',
            borderColor:'#2e7d32',borderWidth:1,borderRadius:6
        }]
    },
    options:{responsive:true,plugins:{legend:{display:false}},
             scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});

// Crop Chart
const cropLabels = {!! json_encode($cropLabels) !!};
if(cropLabels.length){
    new Chart(document.getElementById('cropChart'),{
        type:'doughnut',
        data:{
            labels:cropLabels,
            datasets:[{data:{!! json_encode($cropData) !!},
                backgroundColor:['#4caf50','#2196f3','#ff9800','#9c27b0','#f44336','#009688','#795548'],
                borderWidth:2}]
        },
        options:{plugins:{legend:{position:'bottom',labels:{font:{size:11}}}}}
    });
}

// Map
const map=L.map('dashboard-map').setView([23.5,72.5],10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);

{!! json_encode($droneLocations) !!}.forEach(d=>{
    if(!d.latitude||!d.longitude)return;
    L.circleMarker([d.latitude,d.longitude],{radius:10,fillColor:'#4caf50',color:'#fff',weight:2,fillOpacity:.9})
     .addTo(map).bindPopup(`<strong>🚁 ${d.name}</strong><br>Speed: ${d.speed} km/h<br>Alt: ${d.altitude}m`);
});
{!! json_encode($landLocations) !!}.forEach(l=>{
    L.circleMarker([l.latitude,l.longitude],{radius:7,fillColor:'#2196f3',color:'#fff',weight:2,fillOpacity:.8})
     .addTo(map).bindPopup(`<strong>🌾 ${l.crop_type}</strong><br>${l.area} acres · ${l.soil_type}`);
});
</script>
@endpush
