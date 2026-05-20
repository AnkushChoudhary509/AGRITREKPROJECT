@extends('layouts.app')
@section('title', 'Land Records')
@section('page-title', 'Land Management')

@push('styles')
<style>#land-map { height: 320px; border-radius: 10px; }</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Land Records</h5>
        <p class="text-muted small mb-0">Total registered area: <strong>{{ number_format($totalArea, 2) }} acres</strong></p>
    </div>
    <a href="{{ route('lands.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add Land
    </a>
</div>

<!-- Map Card -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="bi bi-map-fill me-2"></i>Land Location Map
    </div>
    <div class="card-body p-2">
        <div id="land-map"></div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search crop, soil, farmer..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="soil_type" class="form-select">
                        <option value="">All Soil Types</option>
                        @foreach($soilTypes as $s)
                            <option value="{{ $s }}" {{ request('soil_type') == $s ? 'selected':'' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="irrigation_type" class="form-select">
                        <option value="">All Irrigation</option>
                        @foreach($irrigationTypes as $it)
                            <option value="{{ $it }}" {{ request('irrigation_type') == $it ? 'selected':'' }}>{{ $it }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Farmer</th>
                        <th>Crop Type</th>
                        <th>Area (acres)</th>
                        <th>Soil Type</th>
                        <th>Irrigation</th>
                        <th>GPS</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lands as $land)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td><a href="{{ route('farmers.show', $land->farmer) }}" class="text-success fw-semibold">
                            {{ $land->farmer->name ?? 'N/A' }}
                        </a></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success">🌾 {{ $land->crop_type }}</span></td>
                        <td><strong>{{ $land->area }}</strong></td>
                        <td>{{ $land->soil_type }}</td>
                        <td><span class="badge bg-info text-dark">{{ $land->irrigation_type }}</span></td>
                        <td>
                            @if($land->latitude)
                                <small class="text-success">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    {{ number_format($land->latitude, 3) }}, {{ number_format($land->longitude, 3) }}
                                </small>
                            @else
                                <small class="text-muted">Not set</small>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('lands.show', $land) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('lands.edit', $land) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="{{ route('lands.destroy', $land) }}" method="POST"
                                      onsubmit="return confirm('Delete this land record?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-map fs-1 d-block mb-2 opacity-25"></i>No land records found.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($lands->hasPages())
    <div class="card-footer">{{ $lands->withQueryString()->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const map = L.map('land-map').setView([23.5, 72.5], 10);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

const lands = {!! json_encode($mapLands) !!};
const cropColors = { Wheat:'#f9a825', Rice:'#66bb6a', Cotton:'#fff9c4', Sugarcane:'#a5d6a7',
    Maize:'#ffcc02', Groundnut:'#d7ccc8', Soybean:'#81c784', default:'#4caf50' };

lands.forEach(land => {
    const color = cropColors[land.crop_type] || cropColors.default;
    L.circleMarker([land.latitude, land.longitude], {
        radius: 10, fillColor: color, color: '#2e7d32', weight: 2, fillOpacity: 0.85
    }).addTo(map).bindPopup(`
        <strong>🌾 ${land.crop_type}</strong><br>
        Area: ${land.area} acres<br>Soil: ${land.soil_type}
    `);
});
</script>
@endpush
