@extends('layouts.admin')

@section('title', 'Institution Profile & Branding Settings')

@section('content')
<div class="container-fluid" style="max-width: 950px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Institution Profile & Branding Configurator</h4>
            <p class="text-secondary small mb-0">Configure institution identity, classification, accent colors, survey header, and branding text.</p>
        </div>
        @if(auth()->user() && auth()->user()->isSuperAdmin())
            <a href="{{ route('admin.university.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Directory
            </a>
        @endif
    </div>

    <div class="card-custom p-4">
        <form action="{{ route('admin.university.update', $university->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="university_id" value="{{ $university->id }}">

            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-building me-2 text-primary"></i> Basic Identity & Classification</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Institution Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $university->name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Short Name / Code <span class="text-danger">*</span></label>
                    <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $university->short_name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Institution Classification Type <span class="text-danger">*</span></label>
                    <select name="type" id="edit_type_select" class="form-select border-primary" required onchange="toggleParentUniEdit(this.value)">
                        <option value="ini" {{ old('type', $university->type) === 'ini' ? 'selected' : '' }}>Institute of National Importance (IIT / NIT / IIM / AIIMS)</option>
                        <option value="university" {{ old('type', $university->type) === 'university' ? 'selected' : '' }}>Central / State University</option>
                        <option value="autonomous_college" {{ old('type', $university->type) === 'autonomous_college' ? 'selected' : '' }}>Autonomous College (Independent Academic Autonomy)</option>
                        <option value="affiliated_college" {{ old('type', $university->type) === 'affiliated_college' ? 'selected' : '' }}>Affiliated College (Works Under Parent University)</option>
                        <option value="polytechnic_iti" {{ old('type', $university->type) === 'polytechnic_iti' ? 'selected' : '' }}>Polytechnic & ITI (Technical / Skill Institute)</option>
                    </select>
                </div>

                <div class="col-md-6" id="edit_parent_container" style="{{ old('type', $university->type) === 'affiliated_college' ? 'display:block;' : 'display:none;' }}">
                    <label class="form-label fw-semibold">Affiliated Parent University</label>
                    <select name="parent_id" class="form-select border-primary">
                        <option value="">-- Select Parent University --</option>
                        @if(isset($parentUniversities))
                            @foreach($parentUniversities as $pUni)
                                <option value="{{ $pUni->id }}" {{ old('parent_id', $university->parent_id) == $pUni->id ? 'selected' : '' }}>
                                    {{ $pUni->name }} ({{ $pUni->short_name }})
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Tagline / Mission Statement</label>
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
                        <input type="color" name="primary_color" class="form-control form-control-color" value="{{ old('primary_color', $university->primary_color ?? '#1e40af') }}">
                        <input type="text" class="form-control" value="{{ old('primary_color', $university->primary_color ?? '#1e40af') }}" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Secondary Accent Color</label>
                    <div class="d-flex gap-2">
                        <input type="color" name="secondary_color" class="form-control form-control-color" value="{{ old('secondary_color', $university->secondary_color ?? '#0f766e') }}">
                        <input type="text" class="form-control" value="{{ old('secondary_color', $university->secondary_color ?? '#0f766e') }}" readonly>
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
                    <i class="bi bi-check-circle me-1"></i> Save Institution Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleParentUniEdit(type) {
    const parentContainer = document.getElementById('edit_parent_container');
    if (type === 'affiliated_college') {
        parentContainer.style.display = 'block';
    } else {
        parentContainer.style.display = 'none';
    }
}
</script>
@endsection
