@extends('layouts.app')
@section('title', $scheme->name)
@section('page-title', 'Scheme Details')

@section('content')
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('schemes.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ $scheme->name }}</h5>
    <div class="ms-auto d-flex gap-2">
        @if(auth()->user()->role === 'farmer' && $scheme->is_active)
        <form action="{{ route('schemes.apply', $scheme) }}" method="POST">
            @csrf
            <button class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Apply Now</button>
        </form>
        @endif
        @if(auth()->user()->isExpert())
        <a href="{{ route('schemes.edit', $scheme) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-warning"><i class="bi bi-award-fill me-2"></i>Scheme Info</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted">Status</td>
                        <td><span class="badge {{ $scheme->is_active ? 'bg-success' : 'bg-secondary' }}">
                            {{ $scheme->is_active ? 'Active' : 'Inactive' }}</span></td></tr>
                    <tr><td class="text-muted">Subsidy</td>
                        <td><strong class="text-success fs-5">₹{{ number_format($scheme->subsidy_amount) }}</strong></td></tr>
                    <tr><td class="text-muted">Department</td><td>{{ $scheme->department ?? '—' }}</td></tr>
                    <tr><td class="text-muted">Start Date</td>
                        <td>{{ $scheme->start_date ? $scheme->start_date->format('d M Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">End Date</td>
                        <td>{{ $scheme->end_date ? $scheme->end_date->format('d M Y') : '—' }}</td></tr>
                    <tr><td class="text-muted">Applications</td>
                        <td><span class="badge bg-primary">{{ $scheme->applications->count() }}</span></td></tr>
                </table>

                @if($scheme->eligibility)
                <hr>
                <strong class="text-muted small d-block mb-1">Eligibility</strong>
                <p class="small mb-0">{{ $scheme->eligibility }}</p>
                @endif

                @if($scheme->description)
                <hr>
                <strong class="text-muted small d-block mb-1">Description</strong>
                <p class="small mb-0">{{ $scheme->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-people-fill me-2"></i>Applicants ({{ $scheme->applications->count() }})
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr><th>Farmer</th><th>Village</th><th>Applied</th><th>Status</th>
                            @if(auth()->user()->isExpert())<th>Action</th>@endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($scheme->applications as $app)
                        <tr>
                            <td>
                                <a href="{{ route('farmers.show', $app->farmer) }}" class="text-success fw-semibold">
                                    {{ $app->farmer->name ?? 'N/A' }}
                                </a>
                            </td>
                            <td class="small text-muted">{{ $app->farmer->village ?? '—' }}</td>
                            <td class="small text-muted">{{ $app->applied_date ? $app->applied_date->format('d M Y') : '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $app->status === 'approved' ? 'success' : ($app->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($app->status) }}
                                </span>
                            </td>
                            @if(auth()->user()->isExpert())
                            <td>
                                @if($app->status === 'pending')
                                <form action="{{ route('schemes.applications.update', $app) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-xs btn-success py-0 px-2">✓</button>
                                </form>
                                <form action="{{ route('schemes.applications.update', $app) }}" method="POST" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <button class="btn btn-xs btn-danger py-0 px-2">✗</button>
                                </form>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No applications yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
