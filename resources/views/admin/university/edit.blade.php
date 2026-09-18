@extends('layouts.admin')

@section('title', 'University Profile & Branding Settings')

@section('content')
<div class="container-fluid" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">University Profile & Branding Configurator</h4>
            <p class="text-secondary small mb-0">Configure university identity, accent colors, survey header, and footer text without code edits.</p>
        </div>
    </div>

    <div class="card-custom p-4">
        <form action="{{ route('admin.university.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-building me-2 text-primary"></i> Basic Identity</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">University Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $university->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Short Name <span class="text-danger">*</span></label>
                    <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $university->short_name) }}" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">University Tagline / Subtitle</label>
                    <input type="text" name="tagline" class="form-control" value="{{ old('tagline', $university->tagline) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Official Website URL</label>
                    <input type="url" name="website" class="form-control" value="{{ old('website', $university->website) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $university->email) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Contact Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $university->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Campus Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $university->address) }}">
                </div>
            </div>

            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-palette me-2 text-primary"></i> Theme & Branding Colors</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Primary Accent Color</label>
                    <div class="d-flex gap-2">
                        <input type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $university->primary_color) }}">
                        <input type="text" class="form-control" value="{{ old('primary_color', $university->primary_color) }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Secondary Accent Color</label>
                    <div class="d-flex gap-2">
                        <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ old('secondary_color', $university->secondary_color) }}">
                        <input type="text" class="form-control" value="{{ old('secondary_color', $university->secondary_color) }}" readonly>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-card-text me-2 text-primary"></i> Public Header & Footer Text</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Survey Header Title</label>
                    <input type="text" name="survey_header" class="form-control" value="{{ old('survey_header', $university->survey_header) }}">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Footer Copyright Text</label>
                    <textarea name="footer_text" class="form-control" rows="2">{{ old('footer_text', $university->footer_text) }}</textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Privacy Policy Statement</label>
                    <textarea name="privacy_text" class="form-control" rows="3">{{ old('privacy_text', $university->privacy_text) }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-check-circle me-1"></i> Save University Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
