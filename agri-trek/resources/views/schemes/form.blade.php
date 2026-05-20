@extends('layouts.app')
@section('title', isset($scheme) ? 'Edit Scheme' : 'Add Scheme')
@section('page-title', isset($scheme) ? 'Edit Scheme' : 'Create New Scheme')

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">
<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('schemes.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0">{{ isset($scheme) ? 'Edit Scheme' : 'New Beneficiary Scheme' }}</h5>
</div>
<div class="card">
    <div class="card-header bg-warning">
        <i class="bi bi-award-fill me-2"></i>Scheme Details
    </div>
    <div class="card-body">
        <form action="{{ isset($scheme) ? route('schemes.update', $scheme) : route('schemes.store') }}" method="POST">
            @csrf
            @if(isset($scheme)) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Scheme Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $scheme->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Subsidy Amount (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="subsidy_amount"
                           class="form-control @error('subsidy_amount') is-invalid @enderror"
                           value="{{ old('subsidy_amount', $scheme->subsidy_amount ?? '') }}" required>
                    @error('subsidy_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department</label>
                    <input type="text" name="department" class="form-control"
                           value="{{ old('department', $scheme->department ?? '') }}"
                           placeholder="e.g. Ministry of Agriculture">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Eligibility Criteria</label>
                    <textarea name="eligibility" class="form-control" rows="2"
                              placeholder="Who can apply?">{{ old('eligibility', $scheme->eligibility ?? '') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $scheme->description ?? '') }}</textarea>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Start Date</label>
                    <input type="date" name="start_date" class="form-control"
                           value="{{ old('start_date', isset($scheme) && $scheme->start_date ? $scheme->start_date->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">End Date</label>
                    <input type="date" name="end_date" class="form-control"
                           value="{{ old('end_date', isset($scheme) && $scheme->end_date ? $scheme->end_date->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                               id="isActive" {{ old('is_active', $scheme->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isActive">Active</label>
                    </div>
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('schemes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-check-lg me-1"></i>{{ isset($scheme) ? 'Update' : 'Create Scheme' }}
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@endsection
