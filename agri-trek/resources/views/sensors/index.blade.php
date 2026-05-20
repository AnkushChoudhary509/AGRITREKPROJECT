@extends('layouts.app')
@section('title', 'Sensor Fusion')
@section('page-title', 'Sensor Fusion Dashboard')

@push('styles')
<style>
    .sensor-card { border-top: 4px solid; border-radius: 10px; }
    .gauge-container { position: relative; height: 120px; }
    .reading-badge { font-size: 22px; font-weight: 700; }
</style>
@endpush

@section('content')
<div class="mb-4">
    <h5 class="fw-bold mb-1">Sensor Fusion Module</h5>
    <p class="text-muted small mb-0">
        Combining GPS, camera, speed, and altitude sensors to improve drone position estimation accuracy.
    </p>
</div>

<!-- Fusion Concept Banner -->
<div class="alert alert-success d-flex align-items-start gap-3 mb-4">
    <i class="bi bi-cpu-fill fs-3 mt-1"></i>
    <div>
        <strong>How Sensor Fusion Works:</strong> Individual sensors have noise and errors.
        By combining GPS coordinates, speed sensors, altimeter readings, and camera data using a
        weighted average fusion algorithm, we get a more accurate estimate of the drone's true position.
        This is a simulation of concepts used in real drone navigation systems.
    </div>
</div>

<!-- Live Sensor Readings -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card sensor-card p-3" style="border-color: #4caf50;">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1"><i class="bi bi-geo-fill me-1 text-success"></i>GPS Sensor</p>
                    <div class="reading-badge text-success">{{ $sensorData['gps_accuracy'] }}%</div>
                    <small class="text-muted">Accuracy</small>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Lat: {{ $sensorData['gps_lat'] }}</div>
                    <div class="small text-muted">Lng: {{ $sensorData['gps_lng'] }}</div>
                    <span class="badge bg-success mt-2">Active</span>
                </div>
            </div>
            <div class="progress mt-2" style="height:4px;">
                <div class="progress-bar bg-success" style="width:{{ $sensorData['gps_accuracy'] }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card sensor-card p-3" style="border-color: #2196f3;">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1"><i class="bi bi-speedometer me-1 text-primary"></i>Speed Sensor</p>
                    <div class="reading-badge text-primary">{{ $sensorData['speed'] }} <small>km/h</small></div>
                    <small class="text-muted">Current Speed</small>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Max: {{ $sensorData['max_speed'] }}</div>
                    <div class="small text-muted">Noise: ±{{ $sensorData['speed_noise'] }}</div>
                    <span class="badge bg-primary mt-2">Active</span>
                </div>
            </div>
            <div class="progress mt-2" style="height:4px;">
                <div class="progress-bar bg-primary" style="width:{{ min(100, $sensorData['speed']/$sensorData['max_speed']*100) }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card sensor-card p-3" style="border-color: #ff9800;">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1"><i class="bi bi-arrow-up me-1 text-warning"></i>Altimeter</p>
                    <div class="reading-badge text-warning">{{ $sensorData['altitude'] }}<small>m</small></div>
                    <small class="text-muted">Current Altitude</small>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Max: {{ $sensorData['max_altitude'] }}m</div>
                    <div class="small text-muted">Noise: ±{{ $sensorData['alt_noise'] }}m</div>
                    <span class="badge bg-warning mt-2">Active</span>
                </div>
            </div>
            <div class="progress mt-2" style="height:4px;">
                <div class="progress-bar bg-warning" style="width:{{ min(100, $sensorData['altitude']/$sensorData['max_altitude']*100) }}%"></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card sensor-card p-3" style="border-color: #9c27b0;">
            <div class="d-flex justify-content-between">
                <div>
                    <p class="text-muted small mb-1"><i class="bi bi-camera-fill me-1 text-purple"></i>Camera Sensor</p>
                    <div class="reading-badge" style="color:#9c27b0">{{ $sensorData['camera_fps'] }}<small>FPS</small></div>
                    <small class="text-muted">Frame Rate</small>
                </div>
                <div class="text-end">
                    <div class="small text-muted">Res: {{ $sensorData['camera_res'] }}</div>
                    <div class="small text-muted">FOV: {{ $sensorData['camera_fov'] }}°</div>
                    <span class="badge mt-2" style="background:#9c27b0;">Active</span>
                </div>
            </div>
            <div class="progress mt-2" style="height:4px;">
                <div class="progress-bar" style="background:#9c27b0;width:{{ $sensorData['camera_fps']/60*100 }}%"></div>
            </div>
        </div>
    </div>
</div>

<!-- Fusion Output vs Individual Sensors -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-graph-up me-2"></i>Individual Sensors vs Fused Output (Position Error)
            </div>
            <div class="card-body">
                <canvas id="fusionChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="bi bi-lightning-charge-fill me-2"></i>Fused Position Estimate
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted">Fused Latitude</td>
                        <td><strong class="text-success">{{ $fusedData['latitude'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Fused Longitude</td>
                        <td><strong class="text-success">{{ $fusedData['longitude'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Fused Speed</td>
                        <td><strong>{{ $fusedData['speed'] }} km/h</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Fused Altitude</td>
                        <td><strong>{{ $fusedData['altitude'] }}m</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Confidence</td>
                        <td>
                            <div class="progress" style="height:8px;width:80px;display:inline-flex;">
                                <div class="progress-bar bg-success" style="width:{{ $fusedData['confidence'] }}%"></div>
                            </div>
                            <span class="ms-1 small">{{ $fusedData['confidence'] }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Error Reduction</td>
                        <td><strong class="text-success">{{ $fusedData['error_reduction'] }}%</strong></td>
                    </tr>
                </table>

                <div class="alert alert-success py-2 mt-2 mb-0 small">
                    <i class="bi bi-check-circle me-1"></i>
                    Fusion reduced position error by <strong>{{ $fusedData['error_reduction'] }}%</strong>
                    compared to single GPS sensor.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sensor Weights -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-pie-chart me-2"></i>Sensor Weight Distribution
            </div>
            <div class="card-body">
                <canvas id="weightChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <i class="bi bi-table me-2"></i>Historical Sensor Readings
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>GPS Lat</th>
                            <th>Speed</th>
                            <th>Alt</th>
                            <th>Fused Lat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historyData as $row)
                        <tr>
                            <td class="small text-muted">{{ $row['time'] }}</td>
                            <td class="small">{{ $row['gps_lat'] }}</td>
                            <td class="small">{{ $row['speed'] }}</td>
                            <td class="small">{{ $row['alt'] }}</td>
                            <td class="small text-success"><strong>{{ $row['fused_lat'] }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const timeLabels = {!! json_encode($timeLabels) !!};

// Fusion comparison chart
new Chart(document.getElementById('fusionChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: timeLabels,
        datasets: [
            {
                label: 'GPS Only Error (m)',
                data: {!! json_encode($gpsErrors) !!},
                borderColor: '#f44336',
                borderWidth: 2,
                pointRadius: 3,
                fill: false,
                tension: 0.4
            },
            {
                label: 'Fused Output Error (m)',
                data: {!! json_encode($fusedErrors) !!},
                borderColor: '#4caf50',
                borderWidth: 2,
                pointRadius: 3,
                backgroundColor: 'rgba(76,175,80,0.1)',
                fill: true,
                tension: 0.4
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true, title: { display: true, text: 'Position Error (meters)' } } }
    }
});

// Weight pie chart
new Chart(document.getElementById('weightChart').getContext('2d'), {
    type: 'pie',
    data: {
        labels: ['GPS (40%)', 'Speed Sensor (25%)', 'Altimeter (20%)', 'Camera (15%)'],
        datasets: [{
            data: [40, 25, 20, 15],
            backgroundColor: ['#4caf50','#2196f3','#ff9800','#9c27b0'],
            borderWidth: 2
        }]
    },
    options: {
        plugins: {
            legend: { position: 'right', labels: { font: { size: 12 } } }
        }
    }
});
</script>
@endpush
