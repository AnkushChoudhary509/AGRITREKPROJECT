@extends('layouts.app')
@section('title', isset($drone) ? 'Edit Drone' : 'Add Drone')
@section('page-title', isset($drone) ? 'Edit Drone' : 'Register New Drone')

@section('content')
<div class="row justify-content-center">
<div class="col-md-7">
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('drones.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ isset($drone) ? 'Edit Drone' : 'Register New Drone' }}</h5>
</div>
<div class="card">
    <div class="card-header bg-dark text-white">
        <i class="bi bi-airplane-engines me-2"></i>Drone Details
    </div>
    <div class="card-body">
        <form action="{{ isset($drone) ? route('drones.update', $drone) : route('drones.store') }}" method="POST">
            @csrf
            @if(isset($drone)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Drone Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $drone->name ?? '') }}" required placeholder="e.g. AgriHawk-01">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Hardware Drone ID <span class="text-danger">*</span></label>
                    <input type="text" name="drone_id" class="form-control @error('drone_id') is-invalid @enderror"
                           value="{{ old('drone_id', $drone->drone_id ?? '') }}" required placeholder="e.g. DRONE-001">
                    @error('drone_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Model</label>
                    <input type="text" name="model" class="form-control"
                           value="{{ old('model', $drone->model ?? '') }}" placeholder="e.g. DJI Agras T30">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['active','idle','offline'] as $s)
                        <option value="{{ $s }}" {{ old('status', $drone->status ?? 'idle') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Optional description">{{ old('description', $drone->description ?? '') }}</textarea>
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('drones.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-dark">
                    <i class="bi bi-check-lg me-1"></i>{{ isset($drone) ? 'Update' : 'Register Drone' }}
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
