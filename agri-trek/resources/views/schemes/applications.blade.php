@extends('layouts.app')
@section('title', 'Scheme Applications')
@section('page-title', 'Scheme Applications – Admin Review')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">All Scheme Applications</h5>
    <div class="d-flex gap-2">
        <span class="badge bg-warning fs-6">{{ $applications->where('status','pending')->count() }} Pending</span>
        <span class="badge bg-success fs-6">{{ $applications->where('status','approved')->count() }} Approved</span>
        <span class="badge bg-danger fs-6">{{ $applications->where('status','rejected')->count() }} Rejected</span>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Farmer</th><th>Village</th><th>Scheme</th>
                        <th>Subsidy</th><th>Applied Date</th><th>Status</th><th>Remarks</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <a href="{{ route('farmers.show', $app->farmer) }}" class="text-success fw-semibold">
                                {{ $app->farmer->name ?? 'N/A' }}
                            </a>
                        </td>
                        <td class="small">{{ $app->farmer->village ?? '—' }}</td>
                        <td class="small fw-semibold">{{ $app->scheme->name ?? '—' }}</td>
                        <td class="text-success fw-semibold small">₹{{ number_format($app->scheme->subsidy_amount ?? 0) }}</td>
                        <td class="small text-muted">{{ $app->applied_date ? $app->applied_date->format('d M Y') : '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $app->status === 'approved' ? 'success' : ($app->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($app->status) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ $app->remarks ?? '—' }}</td>
                        <td>
                            @if($app->status === 'pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('schemes.applications.update', $app) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button class="btn btn-sm btn-success" title="Approve">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <form action="{{ route('schemes.applications.update', $app) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="rejected">
                                    <input type="hidden" name="remarks" value="Application rejected by admin">
                                    <button class="btn btn-sm btn-danger" title="Reject">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                            </div>
                            @else
                            <small class="text-muted">{{ $app->approved_date ? $app->approved_date->format('d M Y') : '—' }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>No applications found
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($applications->hasPages())
    <div class="card-footer">{{ $applications->links() }}</div>
    @endif
</div>
@endsection
