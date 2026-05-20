@extends('layouts.app')
@section('title', 'Land Detail')
@section('page-title', 'Land Record Detail')

@push('styles')
<style>#detail-map { height: 260px; border-radius: 10px; }</style>
@endpush

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('lands.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">Land Record – {{ $land->crop_type }}</h5>
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('lands.edit', $land) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form action="{{ route('lands.destroy', $land) }}" method="POST"
              onsubmit="return confirm('Delete this record?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-info-circle me-2"></i>Land Information
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="40%">Farmer</td>
                        <td>
                            <a href="{{ route('farmers.show', $land->farmer) }}" class="text-success fw-semibold">
                                {{ $land->farmer->name }}
                            </a>
                            <small class="text-muted"> – {{ $land->farmer->village }}</small>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Crop Type</td>
                        <td><span class="badge bg-success fs-6">🌾 {{ $land->crop_type }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Land Area</td>
                        <td><strong>{{ $land->area }} acres</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Soil Type</td>
                        <td>{{ $land->soil_type }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Irrigation</td>
                        <td><span class="badge bg-info text-dark">{{ $land->irrigation_type }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Survey Number</td>
                        <td>{{ $land->survey_number ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">GPS Latitude</td>
                        <td>{{ $land->latitude ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">GPS Longitude</td>
                        <td>{{ $land->longitude ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Description</td>
                        <td>{{ $land->description ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Registered On</td>
                        <td>{{ $land->created_at->format('d M Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="bi bi-geo-alt-fill me-2"></i>Location on Map
            </div>
            <div class="card-body p-2">
                @if($land->latitude && $land->longitude)
                    <div id="detail-map"></div>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-geo-alt fs-1 d-block mb-2 opacity-25"></i>
                        No GPS coordinates set
                    </div>
                @endif
            </div>
        </div>

        <!-- Farmer Quick Info -->
        <div class="card mt-3">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-person-fill me-2"></i>Farmer Info
            </div>
            <div class="card-body py-3">
                <div class="d-flex gap-3 align-items-center">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                         style="width:48px;height:48px;font-size:20px;font-weight:700;">
                        {{ strtoupper(substr($land->farmer->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $land->farmer->name }}</div>
                        <div class="text-muted small">{{ $land->farmer->mobile }}</div>
                        <div class="text-muted small">{{ $land->farmer->village }}, {{ $land->farmer->district }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($land->latitude && $land->longitude)
<script>
const map = L.map('detail-map').setView([{{ $land->latitude }}, {{ $land->longitude }}], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
L.marker([{{ $land->latitude }}, {{ $land->longitude }}])
    .addTo(map)
    .bindPopup('<strong>🌾 {{ $land->crop_type }}</strong><br>{{ $land->area }} acres')
    .openPopup();
// Draw a simple polygon to represent land boundary
const bounds = [
    [{{ $land->latitude }} - 0.003, {{ $land->longitude }} - 0.004],
    [{{ $land->latitude }} + 0.003, {{ $land->longitude }} - 0.004],
    [{{ $land->latitude }} + 0.003, {{ $land->longitude }} + 0.004],
    [{{ $land->latitude }} - 0.003, {{ $land->longitude }} + 0.004],
];
L.polygon(bounds, { color: '#4caf50', fillColor: '#4caf50', fillOpacity: 0.15, weight: 2 }).addTo(map);
</script>
@endif
@endpush
