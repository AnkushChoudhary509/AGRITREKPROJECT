@extends('layouts.app')
@section('title', 'My Dashboard')
@section('page-title', 'My Farm Overview')

@push('styles')
<style>
    #farm-map { height:280px; border-radius:10px; }
    .land-card { border-left:4px solid #4caf50; border-radius:8px; padding:14px; background:#f9fbe7; margin-bottom:10px; }
    .scheme-status { font-size:11px; padding:3px 8px; border-radius:10px; }
</style>
@endpush

@section('content')
@php $farmer = auth()->user()->farmer; @endphp

<!-- Welcome -->
<div class="alert border-0 mb-4 d-flex align-items-center gap-3"
     style="background:linear-gradient(135deg,#e8f5e9,#f1f8e9);border-radius:14px;">
    <i class="bi bi-sun-fill text-warning fs-3"></i>
    <div>
        <strong class="text-success">Welcome back, {{ auth()->user()->name }}!</strong>
        <div class="text-muted" style="font-size:13px;">
            {{ now()->format('l, d F Y') }} · Farmer Account
        </div>
    </div>
</div>

@if(!$farmer)
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    No farmer profile is linked to your account.
    Please contact the administrator to link your profile.
</div>
@else

<!-- Stats -->
<div class="row g-3 mb-4">
    @php
        $totalArea = $farmer->lands->sum('area');
        $approvedSchemes = $farmer->applications->where('status','approved')->count();
        $pendingSchemes  = $farmer->applications->where('status','pending')->count();
    @endphp
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1b5e20,#43a047);">
            <i class="bi bi-map-fill stat-icon"></i>
            <h3>{{ $farmer->lands->count() }}</h3>
            <p>Land Parcels</p>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#0277bd,#29b6f6);">
            <i class="bi bi-rulers stat-icon"></i>
            <h3>{{ number_format($totalArea,1) }}</h3>
            <p>Total Acres</p>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#43a047,#a5d6a7);">
            <i class="bi bi-check-circle-fill stat-icon"></i>
            <h3>{{ $approvedSchemes }}</h3>
            <p>Approved Schemes</p>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#e65100,#ffa726);">
            <i class="bi bi-hourglass-split stat-icon"></i>
            <h3>{{ $pendingSchemes }}</h3>
            <p>Pending Applications</p>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Left: Lands + Map -->
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header bg-success text-white d-flex justify-content-between">
                <span><i class="bi bi-map-fill me-2"></i>My Land Holdings</span>
                <span class="badge bg-light text-success">{{ $farmer->lands->count() }} parcels</span>
            </div>
            <div class="card-body">
                @forelse($farmer->lands as $land)
                <div class="land-card">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold">🌾 {{ $land->crop_type }}</div>
                            <small class="text-muted">
                                Survey: {{ $land->survey_number ?? 'N/A' }} ·
                                {{ $land->area }} acres · {{ $land->soil_type }} ·
                                {{ $land->irrigation_type }}
                            </small>
                        </div>
                        <span class="badge bg-info text-dark">{{ $land->soil_type }}</span>
                    </div>
                    @if($land->latitude && $land->longitude)
                    <small class="text-success mt-1 d-block">
                        <i class="bi bi-geo-alt me-1"></i>
                        {{ number_format($land->latitude,4) }}, {{ number_format($land->longitude,4) }}
                    </small>
                    @endif
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-map fs-2 d-block mb-2 opacity-25"></i>
                    No land records found. Contact admin to add your land.
                </div>
                @endforelse
            </div>
        </div>

        <!-- Map -->
        @if($farmer->lands->whereNotNull('latitude')->count())
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-geo-alt-fill me-2"></i>My Land Locations
            </div>
            <div class="card-body p-2">
                <div id="farm-map"></div>
            </div>
        </div>
        @endif
    </div>

    <!-- Right: Schemes + Profile -->
    <div class="col-md-5">
        <!-- Schemes -->
        <div class="card mb-3">
            <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                <span><i class="bi bi-award-fill me-2"></i>My Scheme Applications</span>
                <a href="{{ route('schemes.index') }}" class="btn btn-sm btn-dark" style="font-size:11px;">Browse More</a>
            </div>
            <div class="card-body p-0" style="max-height:280px;overflow-y:auto;">
                @forelse($farmer->applications->load('scheme') as $app)
                <div class="d-flex align-items-start gap-2 p-3 border-bottom">
                    <i class="bi bi-award-fill text-warning mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold" style="font-size:13px;">{{ $app->scheme->name ?? '—' }}</div>
                        <small class="text-muted">
                            ₹{{ number_format($app->scheme->subsidy_amount ?? 0) }} subsidy ·
                            {{ $app->applied_date ? $app->applied_date->format('d M Y') : '—' }}
                        </small>
                        @if($app->remarks)
                        <div class="text-muted" style="font-size:11px;">{{ $app->remarks }}</div>
                        @endif
                    </div>
                    <span class="scheme-status bg-{{ $app->status==='approved'?'success':($app->status==='rejected'?'danger':'warning') }} text-{{ $app->status==='pending'?'dark':'white' }}">
                        {{ ucfirst($app->status) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-award fs-2 d-block mb-2 opacity-25"></i>
                    No applications yet.
                    <a href="{{ route('schemes.index') }}">Browse schemes</a>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Profile -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-person-lines-fill me-2"></i>My Profile
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                    <tr><td class="text-muted" style="width:40%;">Full Name</td><td><strong>{{ $farmer->name }}</strong></td></tr>
                    <tr><td class="text-muted">Mobile</td><td>{{ $farmer->mobile }}</td></tr>
                    <tr><td class="text-muted">Village</td><td>{{ $farmer->village }}</td></tr>
                    <tr><td class="text-muted">District</td><td>{{ $farmer->district ?? '—' }}</td></tr>
                    @if($farmer->aadhaar)
                    <tr><td class="text-muted">Aadhaar</td>
                        <td class="text-muted">{{ substr($farmer->aadhaar,0,4) }} XXXX {{ substr($farmer->aadhaar,-4) }}</td></tr>
                    @endif
                    @if($farmer->bank_account)
                    <tr><td class="text-muted">Bank A/C</td><td>{{ $farmer->bank_account }}</td></tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(isset($farmer) && $farmer && $farmer->lands->whereNotNull('latitude')->count())
<script>
const lands = {!! $farmer->lands->whereNotNull('latitude')->map(fn($l)=>['lat'=>$l->latitude,'lng'=>$l->longitude,'crop'=>$l->crop_type,'area'=>$l->area])->values()->toJson() !!};
const map = L.map('farm-map').setView([lands[0].lat, lands[0].lng], 13);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);
lands.forEach(l=>{
    L.circleMarker([l.lat,l.lng],{radius:10,fillColor:'#4caf50',color:'#fff',weight:2,fillOpacity:.9})
     .addTo(map).bindPopup(`<strong>🌾 ${l.crop}</strong><br>${l.area} acres`).openPopup();
});
</script>
@endif
@endpush
