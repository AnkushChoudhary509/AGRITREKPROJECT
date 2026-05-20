@extends('layouts.app')
@section('title', isset($farmer) ? 'Edit Farmer' : 'Add Farmer')
@section('page-title', isset($farmer) ? 'Edit Farmer' : 'Add New Farmer')

@section('content')
<div class="row justify-content-center">
<div class="col-md-8">

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('farmers.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="fw-bold mb-0">{{ isset($farmer) ? 'Edit Farmer Record' : 'Register New Farmer' }}</h5>
</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="bi bi-person-fill me-2"></i>Farmer Information
    </div>
    <div class="card-body">
        <form action="{{ isset($farmer) ? route('farmers.update', $farmer) : route('farmers.store') }}"
              method="POST">
            @csrf
            @if(isset($farmer)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $farmer->name ?? '') }}" placeholder="Enter farmer's full name" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                    <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                           value="{{ old('mobile', $farmer->mobile ?? '') }}" placeholder="10-digit mobile number" required>
                    @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror"
                              rows="2" placeholder="Full address">{{ old('address', $farmer->address ?? '') }}</textarea>
                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Village <span class="text-danger">*</span></label>
                    <input type="text" name="village" class="form-control @error('village') is-invalid @enderror"
                           value="{{ old('village', $farmer->village ?? '') }}" placeholder="Village name" required>
                    @error('village')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">District</label>
                    <input type="text" name="district" class="form-control"
                           value="{{ old('district', $farmer->district ?? '') }}" placeholder="District">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Aadhaar Number</label>
                    <input type="text" name="aadhaar" class="form-control @error('aadhaar') is-invalid @enderror"
                           value="{{ old('aadhaar', $farmer->aadhaar ?? '') }}" placeholder="12-digit Aadhaar number"
                           maxlength="12">
                    @error('aadhaar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date of Birth</label>
                    <input type="date" name="dob" class="form-control"
                           value="{{ old('dob', $farmer->dob ?? '') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Bank Account Number</label>
                    <input type="text" name="bank_account" class="form-control"
                           value="{{ old('bank_account', $farmer->bank_account ?? '') }}" placeholder="Bank account number">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">IFSC Code</label>
                    <input type="text" name="ifsc_code" class="form-control"
                           value="{{ old('ifsc_code', $farmer->ifsc_code ?? '') }}" placeholder="Bank IFSC code">
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Any additional notes">{{ old('notes', $farmer->notes ?? '') }}</textarea>
                </div>
            </div>

            <hr class="my-4">
            <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('farmers.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-lg me-1"></i>
                    {{ isset($farmer) ? 'Update Farmer' : 'Save Farmer' }}
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
@endsection
