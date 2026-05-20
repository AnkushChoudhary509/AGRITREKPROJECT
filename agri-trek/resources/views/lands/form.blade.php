@extends('layouts.app')
@section('title', isset($land) ? 'Edit Land' : 'Add Land')
@section('page-title', isset($land) ? 'Edit Land Record' : 'Add Land Record')

@push('styles')
<style>#pick-map { height: 300px; border-radius: 10px; cursor: crosshair; }</style>
@endpush

@section('content')
<div class="row justify-content-center">
<div class="col-md-9">

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('lands.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ isset($land) ? 'Edit Land Record' : 'Register New Land' }}</h5>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-map-fill me-2"></i>Land Details
    </div>
    <div class="card-body">
        <form action="{{ isset($land) ? route('lands.update', $land) : route('lands.store') }}" method="POST">
            @csrf
            @if(isset($land)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Farmer <span class="text-danger">*</span></label>
                    <select name="farmer_id" class="form-select @error('farmer_id') is-invalid @enderror" required>
                        <option value="">Select Farmer</option>
                        @foreach($farmers as $farmer)
                            <option value="{{ $farmer->id }}"
                                {{ old('farmer_id', $land->farmer_id ?? '') == $farmer->id ? 'selected' : '' }}>
                                {{ $farmer->name }} – {{ $farmer->village }}
                            </option>
                        @endforeach
                    </select>
                    @error('farmer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Crop Type <span class="text-danger">*</span></label>
                    <input type="text" name="crop_type" class="form-control @error('crop_type') is-invalid @enderror"
                           value="{{ old('crop_type', $land->crop_type ?? '') }}"
                           list="crop-list" placeholder="e.g. Wheat, Rice, Cotton" required>
                    <datalist id="crop-list">
                        @foreach(['Wheat','Rice','Cotton','Sugarcane','Maize','Groundnut','Soybean','Mustard','Sunflower','Barley'] as $c)
                            <option value="{{ $c }}">
                        @endforeach
                    </datalist>
                    @error('crop_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Land Area (acres) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="area"
                           class="form-control @error('area') is-invalid @enderror"
                           value="{{ old('area', $land->area ?? '') }}" placeholder="e.g. 2.50" required>
                    @error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Soil Type <span class="text-danger">*</span></label>
                    <select name="soil_type" class="form-select @error('soil_type') is-invalid @enderror" required>
                        <option value="">Select Soil Type</option>
                        @foreach(['Clay','Sandy','Loamy','Silty','Peaty','Chalky','Black Cotton'] as $s)
                            <option value="{{ $s }}" {{ old('soil_type', $land->soil_type ?? '') == $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                    @error('soil_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Irrigation Type <span class="text-danger">*</span></label>
                    <select name="irrigation_type" class="form-select" required>
                        <option value="">Select Type</option>
                        @foreach(['Canal','Drip','Sprinkler','Rainfed','Borewell','Pond'] as $i)
                            <option value="{{ $i }}" {{ old('irrigation_type', $land->irrigation_type ?? '') == $i ? 'selected' : '' }}>
                                {{ $i }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Survey Number</label>
                    <input type="text" name="survey_number" class="form-control"
                           value="{{ old('survey_number', $land->survey_number ?? '') }}" placeholder="e.g. SY-402">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Description</label>
                    <input type="text" name="description" class="form-control"
                           value="{{ old('description', $land->description ?? '') }}" placeholder="Optional notes">
                </div>

                <!-- GPS Section -->
                <div class="col-12">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-geo-alt-fill text-success me-1"></i>GPS Coordinates
                        <small class="text-muted fw-normal">(click map to pick location)</small>
                    </label>
                    <div id="pick-map" class="mb-2"></div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="number" step="0.0000001" name="latitude" id="latInput"
                                   class="form-control @error('latitude') is-invalid @enderror"
                                   value="{{ old('latitude', $land->latitude ?? '') }}"
                                   placeholder="Latitude (e.g. 23.5120000)">
                            @error('latitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <input type="number" step="0.0000001" name="longitude" id="lngInput"
                                   class="form-control @error('longitude') is-invalid @enderror"
                                   value="{{ old('longitude', $land->longitude ?? '') }}"
                                   placeholder="Longitude (e.g. 72.5000000)">
                            @error('longitude')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('lands.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($land) ? 'Update Land' : 'Save Land' }}
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
const initLat  = {{ old('latitude',  $land->latitude  ?? 23.5) }};
const initLng  = {{ old('longitude', $land->longitude ?? 72.5) }};
const pickMap  = L.map('pick-map').setView([initLat, initLng], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(pickMap);

let marker = null;
if ({{ isset($land) && $land->latitude ? 'true' : 'false' }}) {
    marker = L.marker([initLat, initLng]).addTo(pickMap);
}

pickMap.on('click', function (e) {
    const { lat, lng } = e.latlng;
    document.getElementById('latInput').value = lat.toFixed(7);
    document.getElementById('lngInput').value = lng.toFixed(7);
    if (marker) pickMap.removeLayer(marker);
    marker = L.marker([lat, lng]).addTo(pickMap).bindPopup('Selected Location').openPopup();
});

// Sync manual input back to map
['latInput','lngInput'].forEach(id => {
    document.getElementById(id).addEventListener('input', () => {
        const lat = parseFloat(document.getElementById('latInput').value);
        const lng = parseFloat(document.getElementById('lngInput').value);
        if (!isNaN(lat) && !isNaN(lng)) {
            if (marker) pickMap.removeLayer(marker);
            marker = L.marker([lat, lng]).addTo(pickMap);
            pickMap.setView([lat, lng], 13);
        }
    });
});
</script>
@endpush
