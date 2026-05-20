@extends('layouts.app')
@section('title', 'Farmers')
@section('page-title', 'Farmer Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">All Farmers</h5>
        <p class="text-muted small mb-0">Manage farmer records and land ownership</p>
    </div>
    @if(auth()->user()->isExpert())
    <a href="{{ route('farmers.create') }}" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i>Add Farmer
    </a>
    @endif
</div>

<!-- Search Bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('farmers.index') }}">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, village..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="village" class="form-select">
                        <option value="">All Villages</option>
                        @foreach($villages as $village)
                            <option value="{{ $village }}" {{ request('village') == $village ? 'selected' : '' }}>
                                {{ $village }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('farmers.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Farmers Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Farmer Name</th>
                        <th>Mobile</th>
                        <th>Village</th>
                        <th>Aadhaar</th>
                        <th>Lands</th>
                        <th>Schemes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($farmers as $farmer)
                    <tr>
                        <td class="text-muted small">{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                                     style="width:36px;height:36px;font-weight:700;font-size:14px;">
                                    {{ strtoupper(substr($farmer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $farmer->name }}</div>
                                    <small class="text-muted">{{ $farmer->address }}</small>
                                </div>
                            </div>
                        </td>
                        <td>{{ $farmer->mobile }}</td>
                        <td><span class="badge bg-light text-dark">{{ $farmer->village }}</span></td>
                        <td>
                            <span class="text-muted small">
                                {{ substr($farmer->aadhaar, 0, 4) . 'XXXX' . substr($farmer->aadhaar, -4) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-primary rounded-pill">{{ $farmer->lands_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge bg-success rounded-pill">{{ $farmer->applications_count ?? 0 }}</span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('farmers.show', $farmer) }}"
                                   class="btn btn-sm btn-outline-success" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(auth()->user()->isExpert())
                                <a href="{{ route('farmers.edit', $farmer) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('farmers.destroy', $farmer) }}" method="POST"
                                      onsubmit="return confirm('Delete this farmer?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                            No farmers found. <a href="{{ route('farmers.create') }}">Add the first farmer</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($farmers->hasPages())
    <div class="card-footer">
        {{ $farmers->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
