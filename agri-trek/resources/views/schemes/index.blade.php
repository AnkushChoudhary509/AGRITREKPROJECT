@extends('layouts.app')
@section('title', 'Beneficiary Schemes')
@section('page-title', 'Beneficiary Scheme Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Government Schemes</h5>
        <p class="text-muted small mb-0">Agriculture support and subsidy programmes</p>
    </div>
    @if(auth()->user()->isExpert())
    <a href="{{ route('schemes.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add Scheme
    </a>
    @endif
</div>

<div class="row g-3">
    @forelse($schemes as $scheme)
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="rounded p-2 bg-success bg-opacity-10">
                        <i class="bi bi-award-fill text-success fs-4"></i>
                    </div>
                    <span class="badge {{ $scheme->is_active ? 'bg-success' : 'bg-secondary' }}">
                        {{ $scheme->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <h6 class="fw-bold mt-2 mb-1">{{ $scheme->name }}</h6>
                <p class="text-muted small mb-2">{{ Str::limit($scheme->description, 80) }}</p>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Subsidy Amount</span>
                        <strong class="text-success">₹{{ number_format($scheme->subsidy_amount) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Department</span>
                        <span>{{ $scheme->department ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Applications</span>
                        <span class="badge bg-primary rounded-pill">{{ $scheme->applications_count }}</span>
                    </div>
                    @if($scheme->end_date)
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Valid Until</span>
                        <span class="{{ $scheme->end_date->isPast() ? 'text-danger' : 'text-success' }}">
                            {{ $scheme->end_date->format('d M Y') }}
                        </span>
                    </div>
                    @endif
                </div>

                <div class="d-flex gap-2 mt-auto">
                    <a href="{{ route('schemes.show', $scheme) }}" class="btn btn-sm btn-outline-success flex-fill">
                        <i class="bi bi-eye me-1"></i>Details
                    </a>
                    @if(auth()->user()->role === 'farmer' && $scheme->is_active)
                    <form action="{{ route('schemes.apply', $scheme) }}" method="POST">
                        @csrf
                        <button class="btn btn-sm btn-success">Apply</button>
                    </form>
                    @endif
                    @if(auth()->user()->isExpert())
                    <a href="{{ route('schemes.edit', $scheme) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-award fs-1 d-block mb-2 opacity-25"></i>
        No schemes found. <a href="{{ route('schemes.create') }}">Create the first scheme</a>.
    </div>
    @endforelse
</div>

@if($schemes->hasPages())
<div class="mt-4">{{ $schemes->links() }}</div>
@endif
@endsection
