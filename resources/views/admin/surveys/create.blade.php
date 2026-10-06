@extends('layouts.admin')

@section('title', 'Create Survey Campaign')

@section('content')
<div class="container-fluid" style="max-width: 800px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">Create New Survey Campaign</h4>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card-custom p-4">
        <form action="{{ route('admin.surveys.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Survey Title <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. National Graduate Readiness Survey 2026" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Subtitle</label>
                <input type="text" name="subtitle" class="form-control" placeholder="e.g. Longitudinal Research on Competency & Skill Gap">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Target Respondents Description</label>
                    <input type="text" name="target_respondents" class="form-control" value="{{ old('target_respondents', 'All Students & Alumni') }}" placeholder="e.g. All Final Year Students">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', 'published') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>Paused</option>
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
                        <input type="date" name="start_date" class="form-control form-control-sm" value="{{ old('start_date') }}">
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Leave blank to open immediately.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            <i class="bi bi-calendar-check me-1 text-danger"></i> Duration To (End Date)
                        </label>
                        <input type="date" name="end_date" class="form-control form-control-sm" value="{{ old('end_date') }}">
                        <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Leave blank for no expiration date.</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            <i class="bi bi-people-fill me-1 text-success"></i> Respondent Limit (Number)
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-hash"></i></span>
                            <input type="number" name="max_respondents" class="form-control form-control-sm" min="1" step="1" placeholder="e.g. 500" value="{{ old('max_respondents') }}">
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
                        <input class="form-check-input" type="radio" name="scope_type" id="scopeGlobal" value="global" {{ old('scope_type', 'global') == 'global' ? 'checked' : '' }} onchange="toggleScopeSelection()">
                        <label class="form-check-label fw-semibold text-dark cursor-pointer" for="scopeGlobal">
                            <i class="bi bi-globe me-1 text-primary"></i> Global (All Universities & Colleges)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope_type" id="scopeSpecific" value="specific" {{ old('scope_type') == 'specific' ? 'checked' : '' }} onchange="toggleScopeSelection()">
                        <label class="form-check-label fw-semibold text-dark cursor-pointer" for="scopeSpecific">
                            <i class="bi bi-diagram-3 me-1 text-success"></i> Select Specific Universities / Colleges
                        </label>
                    </div>
                </div>

                <div id="universitySelectionContainer" class="p-3 bg-white rounded-3 border" style="display: none;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                        <div>
                            <small class="text-muted fw-semibold d-block">
                                <i class="bi bi-check2-square me-1 text-primary"></i> Select the universities/colleges that will see and respond to this survey:
                            </small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-pill" onclick="selectAllUniversities(true)" style="font-size: 0.78rem;">
                                <i class="bi bi-check-all me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill" onclick="selectAllUniversities(false)" style="font-size: 0.78rem;">
                                <i class="bi bi-x-lg me-1"></i> Deselect All
                            </button>
                        </div>
                    </div>

                    <!-- Search Input Bar -->
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="uniSearchInput" class="form-control border-start-0" placeholder="Search by university / college name, code..." oninput="filterUniversities()">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearUniSearch()" title="Clear Search">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                            <small class="text-muted" style="font-size: 0.75rem;" id="uniFilterCount">Showing all {{ count($universities) }} institutions</small>
                            <small class="text-muted" style="font-size: 0.75rem;" id="uniSelectedCount">0 selected</small>
                        </div>
                    </div>

                    <div class="row g-2" id="uniCheckboxList" style="max-height: 250px; overflow-y: auto;">
                        @foreach($universities as $u)
                            @php
                                $typeLabel = '';
                                if($u->type === 'university') $typeLabel = 'University';
                                elseif($u->type === 'ini') $typeLabel = 'INI';
                                elseif($u->type === 'affiliated_college') $typeLabel = 'Affiliated College';
                                elseif($u->type === 'polytechnic_iti') $typeLabel = 'Polytechnic/ITI';
                            @endphp
                            <div class="col-md-6 uni-item" data-search-text="{{ strtolower($u->name . ' ' . $u->short_name . ' ' . $typeLabel) }}">
                                <div class="form-check p-2 rounded hover-bg-light border mb-1 h-100 d-flex align-items-center">
                                    <input class="form-check-input ms-0 me-2 uni-checkbox" type="checkbox" name="university_ids[]" value="{{ $u->id }}" id="uni_{{ $u->id }}" {{ (in_array($u->id, old('university_ids', []))) ? 'checked' : '' }} onchange="updateSelectedCount()">
                                    <label class="form-check-label small w-100 cursor-pointer" for="uni_{{ $u->id }}">
                                        <strong class="text-dark">{{ $u->short_name }}</strong> — <span class="text-secondary">{{ Str::limit($u->name, 40) }}</span>
                                        @if($typeLabel)
                                            <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem;">{{ $typeLabel }}</span>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div id="noUniMatchMsg" class="text-center py-3 text-muted small d-none">
                        <i class="bi bi-search me-1"></i> No universities or colleges matching your search.
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-check-circle me-1"></i> Create Survey Campaign</button>
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
        updateSelectedCount();
    }

    function filterUniversities() {
        const query = (document.getElementById('uniSearchInput')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('#uniCheckboxList .uni-item');
        let visibleCount = 0;

        items.forEach(item => {
            const text = item.getAttribute('data-search-text') || '';
            if (!query || text.includes(query)) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        const noMatch = document.getElementById('noUniMatchMsg');
        if (noMatch) {
            noMatch.classList.toggle('d-none', visibleCount > 0);
        }

        const filterCount = document.getElementById('uniFilterCount');
        if (filterCount) {
            filterCount.innerText = query ? `Showing ${visibleCount} of ${items.length} institutions` : `Showing all ${items.length} institutions`;
        }
    }

    function clearUniSearch() {
        const input = document.getElementById('uniSearchInput');
        if (input) {
            input.value = '';
            filterUniversities();
            input.focus();
        }
    }

    function selectAllUniversities(check) {
        // Only select/deselect currently visible items or all items
        const visibleItems = document.querySelectorAll('#uniCheckboxList .uni-item:not(.d-none) .uni-checkbox');
        visibleItems.forEach(cb => {
            cb.checked = check;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const totalChecked = document.querySelectorAll('#uniCheckboxList .uni-checkbox:checked').length;
        const countEl = document.getElementById('uniSelectedCount');
        if (countEl) {
            countEl.innerText = `${totalChecked} selected`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleScopeSelection();
        updateSelectedCount();
    });
</script>
@endsection
