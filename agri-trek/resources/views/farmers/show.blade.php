@extends('layouts.app')
@section('title', 'Farmer Profile')
@section('page-title', 'Farmer Profile')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('farmers.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ $farmer->name }}</h5>
    @if(auth()->user()->isExpert())
    <div class="ms-auto d-flex gap-2">
        <a href="{{ route('farmers.edit', $farmer) }}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <form action="{{ route('farmers.destroy', $farmer) }}" method="POST"
              onsubmit="return confirm('Delete this farmer?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash me-1"></i>Delete</button>
        </form>
    </div>
    @endif
</div>

<div class="row g-3">
    <!-- Profile Card -->
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body py-4">
                <div class="rounded-circle bg-success text-white mx-auto d-flex align-items-center justify-content-center mb-3"
                     style="width:80px;height:80px;font-size:32px;font-weight:700;">
                    {{ strtoupper(substr($farmer->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold mb-1">{{ $farmer->name }}</h5>
                <p class="text-muted mb-3">{{ $farmer->village }}, {{ $farmer->district }}</p>
                <div class="d-flex justify-content-center gap-3">
                    <div class="text-center">
                        <div class="fw-bold text-success fs-5">{{ $farmer->lands->count() }}</div>
                        <small class="text-muted">Lands</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold text-primary fs-5">{{ $farmer->applications->count() }}</div>
                        <small class="text-muted">Schemes</small>
                    </div>
                    <div class="text-center">
                        <div class="fw-bold text-warning fs-5">{{ number_format($farmer->lands->sum('area'),1) }}</div>
                        <small class="text-muted">Acres</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Info -->
        <div class="card mt-3">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-person-lines-fill me-2"></i>Contact Details
            </div>
            <div class="card-body py-2">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Mobile</td>
                        <td><strong>{{ $farmer->mobile }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Address</td>
                        <td>{{ $farmer->address ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Village</td>
                        <td>{{ $farmer->village }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">District</td>
                        <td>{{ $farmer->district ?? '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Date of Birth</td>
                        <td>{{ $farmer->dob ? $farmer->dob->format('d M Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Aadhaar</td>
                        <td>
                            @if($farmer->aadhaar)
                                <span class="text-muted">
                                    {{ substr($farmer->aadhaar,0,4) }} XXXX {{ substr($farmer->aadhaar,-4) }}
                                </span>
                            @else —
                            @endif
                        </td>
                    </tr>
                    @if($farmer->bank_account)
                    <tr>
                        <td class="text-muted">Bank A/C</td>
                        <td>{{ $farmer->bank_account }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">IFSC</td>
                        <td>{{ $farmer->ifsc_code }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Land & Scheme Details -->
    <div class="col-md-8">
        <!-- Lands -->
        <div class="card mb-3">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-map-fill me-2"></i>Land Holdings</span>
                @if(auth()->user()->isExpert())
                <a href="{{ route('lands.create') }}" class="btn btn-sm btn-light text-success">
                    <i class="bi bi-plus-lg"></i> Add Land
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($farmer->lands as $land)
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div>
                        <div class="fw-semibold">🌾 {{ $land->crop_type }}</div>
                        <small class="text-muted">
                            {{ $land->area }} acres &bull; {{ $land->soil_type }} soil &bull; {{ $land->irrigation_type }}
                        </small>
                        @if($land->survey_number)
                            <small class="text-muted"> &bull; Survey: {{ $land->survey_number }}</small>
                        @endif
                    </div>
                    <a href="{{ route('lands.show', $land) }}" class="btn btn-sm btn-outline-success">View</a>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-map fs-3 d-block mb-2 opacity-25"></i>No land records
                </div>
                @endforelse
            </div>
        </div>

        <!-- Scheme Applications -->
        <div class="card">
            <div class="card-header bg-warning d-flex justify-content-between align-items-center">
                <span><i class="bi bi-award-fill me-2"></i>Scheme Applications</span>
            </div>
            <div class="card-body p-0">
                @forelse($farmer->applications as $app)
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div>
                        <div class="fw-semibold">{{ $app->scheme->name ?? 'N/A' }}</div>
                        <small class="text-muted">
                            Applied: {{ $app->applied_date ? $app->applied_date->format('d M Y') : '—' }}
                            &bull; Subsidy: ₹{{ number_format($app->scheme->subsidy_amount ?? 0) }}
                        </small>
                        @if($app->remarks)
                            <div class="small text-muted mt-1">Remarks: {{ $app->remarks }}</div>
                        @endif
                    </div>
                    <span class="badge bg-{{ $app->status === 'approved' ? 'success' : ($app->status === 'rejected' ? 'danger' : 'warning') }} fs-6">
                        {{ ucfirst($app->status) }}
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-award fs-3 d-block mb-2 opacity-25"></i>No applications
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
