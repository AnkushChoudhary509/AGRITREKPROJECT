@extends('layouts.app')
@section('title', isset($waypoint) ? 'Edit Waypoint' : 'Add Waypoint')
@section('page-title', isset($waypoint) ? 'Edit Waypoint' : 'Add Waypoint')

@push('styles')
<style>#wp-pick-map { height: 280px; border-radius: 10px; cursor: crosshair; }</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('waypoints.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ isset($waypoint) ? 'Edit Waypoint' : 'Add New Waypoint' }}</h5>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="bi bi-pin-map-fill me-2"></i>Waypoint Details
    </div>
    <div class="card-body">
        <form action="{{ isset($waypoint) ? route('waypoints.update', $waypoint) : route('waypoints.store') }}" method="POST">
            @csrf
            @if(isset($waypoint)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Waypoint Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $waypoint->name ?? '') }}" required placeholder="e.g. North Field WP-1">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Route Name <span class="text-danger">*</span></label>
                    <input type="text" name="route_name" class="form-control @error('route_name') is-invalid @enderror"
                           value="{{ old('route_name', $waypoint->route_name ?? '') }}"
                           list="route-list" required placeholder="e.g. North Field Survey">
                    <datalist id="route-list">
                        @foreach(\App\Models\Waypoint::distinct()->pluck('route_name') as $r)
                            <option value="{{ $r }}">
                        @endforeach
                    </datalist>
                    @error('route_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Sequence <span class="text-danger">*</span></label>
                    <input type="number" name="sequence" class="form-control" min="1"
                           value="{{ old('sequence', $waypoint->sequence ?? 1) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Target Altitude (m)</label>
                    <input type="number" name="altitude" class="form-control" min="0" max="500"
                           value="{{ old('altitude', $waypoint->altitude ?? 50) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Speed (km/h)</label>
                    <input type="number" name="speed" class="form-control" min="0" max="120"
                           value="{{ old('speed', $waypoint->speed ?? 30) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Assigned Drone</label>
                    <select name="drone_id" class="form-select">
                        <option value="">None</option>
                        @foreach($drones as $drone)
                        <option value="{{ $drone->id }}" {{ old('drone_id', $waypoint->drone_id ?? '') == $drone->id ? 'selected' : '' }}>
                            {{ $drone->name }} ({{ $drone->drone_id }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Notes</label>
                    <input type="text" name="notes" class="form-control"
                           value="{{ old('notes', $waypoint->notes ?? '') }}" placeholder="Optional note">
                </div>

                <!-- GPS coordinates with map picker -->
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-geo-alt-fill text-success me-1"></i>Location
                        <small class="text-muted fw-normal">(click map to pick)</small>
                    </label>
                    <div id="wp-pick-map" class="mb-2"></div>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" step="0.0000001" name="latitude" id="wpLat"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $waypoint->latitude ?? '') }}"
                                   placeholder="Latitude" required>
                            @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <input type="number" step="0.0000001" name="longitude" id="wpLng"
                                   class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude', $waypoint->longitude ?? '') }}"
                                   placeholder="Longitude" required>
                            @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('waypoints.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>{{ isset($waypoint) ? 'Update' : 'Save Waypoint' }}
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection

@push('scripts')
<script>
const initLat = {{ old('latitude', $waypoint->latitude ?? 23.5) }};
const initLng = {{ old('longitude', $waypoint->longitude ?? 72.5) }};
const wpMap   = L.map('wp-pick-map').setView([initLat, initLng], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(wpMap);

let wpMarker = null;
@if(isset($waypoint) && $waypoint->latitude)
wpMarker = L.marker([{{ $waypoint->latitude }}, {{ $waypoint->longitude }}]).addTo(wpMap);
@endif

wpMap.on('click', e => {
    const { lat, lng } = e.latlng;
    document.getElementById('wpLat').value = lat.toFixed(7);
    document.getElementById('wpLng').value = lng.toFixed(7);
    if (wpMarker) wpMap.removeLayer(wpMarker);
    wpMarker = L.marker([lat, lng]).addTo(wpMap);
});
</script>
@endpush
