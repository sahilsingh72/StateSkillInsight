@extends('layouts.admin')

@section('title', 'Edit Survey Campaign')

@section('content')
<div class="container-fluid" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Edit Survey Campaign</h4>
            <p class="text-secondary small mb-0">Update details for survey #{{ $survey->id }} - {{ $survey->title }}</p>
        </div>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card-custom p-4">
        <form action="{{ route('admin.surveys.update', $survey->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to save changes to &quot;{{ addslashes($survey->title) }}&quot;?');">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-semibold">Survey Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $survey->title) }}" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Subtitle</label>
                <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $survey->subtitle) }}">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description', $survey->description) }}</textarea>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Target Respondents Description</label>
                    <input type="text" name="target_respondents" class="form-control" value="{{ old('target_respondents', $survey->target_respondents) }}" placeholder="e.g. All Final Year Students">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status', $survey->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $survey->status) == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="paused" {{ old('status', $survey->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                        <option value="closed" {{ old('status', $survey->status) == 'closed' ? 'selected' : '' }}>Closed</option>
                        <option value="archived" {{ old('status', $survey->status) == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
            </div>

            <!-- Campaign Duration & Respondent Limit Settings -->
            <div class="card bg-light border p-3 mb-4 rounded-3">
                <h6 class="fw-bold text-dark mb-2">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Survey Duration & Respondent Limit
                </h6>
                <p class="text-secondary small mb-3">Set the active start/end duration window and a maximum respondent capacity for this survey.</p>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            <i class="bi bi-calendar-event me-1 text-primary"></i> Duration From (Start Date)
                        </label>
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ old('start_date', $survey->start_date?->format('Y-m-d')) }}">
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Leave blank to open immediately.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            <i class="bi bi-calendar-check me-1 text-danger"></i> Duration To (End Date)
                        </label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ old('end_date', $survey->end_date?->format('Y-m-d')) }}">
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Leave blank for no expiration date.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            <i class="bi bi-people-fill me-1 text-success"></i> Respondent Limit (Number)
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-hash"></i></span>
                            <input type="number" name="max_respondents" class="form-control form-control-sm" min="1" step="1" placeholder="e.g. 500" value="{{ old('max_respondents', $survey->max_respondents) }}">
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Leave blank for unlimited submissions.</small>
                    </div>
                </div>
            </div>

            <div class="mb-4 p-3 bg-light rounded-3 border">
                <label class="form-label fw-semibold text-primary mb-2">
                    <i class="bi bi-building me-1"></i> Target Institution Scope
                </label>
                
                <div class="d-flex gap-4 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope_type" id="scopeGlobal" value="global" {{ old('scope_type', $currentScopeType ?? 'global') == 'global' ? 'checked' : '' }} onchange="toggleScopeSelection()">
                        <label class="form-check-label fw-semibold text-dark" for="scopeGlobal">
                            <i class="bi bi-globe me-1 text-primary"></i> Global (All Universities & Colleges)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope_type" id="scopeSpecific" value="specific" {{ old('scope_type', $currentScopeType ?? 'global') == 'specific' ? 'checked' : '' }} onchange="toggleScopeSelection()">
                        <label class="form-check-label fw-semibold text-dark" for="scopeSpecific">
                            <i class="bi bi-diagram-3 me-1 text-success"></i> Select Specific Universities / Colleges
                        </label>
                    </div>
                </div>

                <div id="universitySelectionContainer" class="p-3 bg-white rounded-3 border" style="display: none; max-height: 240px; overflow-y: auto;">
                    <small class="text-muted d-block mb-2 fw-semibold"><i class="bi bi-check2-square me-1"></i> Select the universities/colleges that will see and respond to this survey:</small>
                    <div class="row g-2">
                        @foreach($universities as $u)
                            <div class="col-md-6">
                                <div class="form-check p-2 rounded hover-bg-light border mb-1">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="university_ids[]" value="{{ $u->id }}" id="uni_{{ $u->id }}" {{ (in_array($u->id, old('university_ids', $selectedUniversityIds ?? []))) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="uni_{{ $u->id }}">
                                        <strong class="text-dark">{{ $u->short_name }}</strong> — <span class="text-secondary">{{ Str::limit($u->name, 35) }}</span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.surveys.index') }}" class="btn btn-light border">Cancel</a>
                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-check-circle me-1"></i> Save Survey Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleScopeSelection() {
        const isSpecific = document.getElementById('scopeSpecific').checked;
        const container = document.getElementById('universitySelectionContainer');
        if (container) {
            container.style.display = isSpecific ? 'block' : 'none';
        }
    }
    document.addEventListener('DOMContentLoaded', toggleScopeSelection);
</script>
@endsection
