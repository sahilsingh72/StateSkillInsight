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
                        <label class="form-check-label fw-semibold text-dark cursor-pointer" for="scopeGlobal">
                            <i class="bi bi-globe me-1 text-primary"></i> Global (All Universities & Colleges)
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="scope_type" id="scopeSpecific" value="specific" {{ old('scope_type', $currentScopeType ?? 'global') == 'specific' ? 'checked' : '' }} onchange="toggleScopeSelection()">
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

                    <!-- Affiliating University Quick Selector -->
                    @php
                        $affiliatingParents = $universities->where('type', 'university')->where('colleges_count', '>', 0)->sortBy('name');
                    @endphp
                    @if($affiliatingParents->isNotEmpty())
                        <div class="p-2.5 mb-3 bg-light rounded-3 border">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                <label for="parentUniQuickSelect" class="form-label mb-0 small fw-bold text-primary d-flex align-items-center gap-1">
                                    <i class="bi bi-diagram-3-fill"></i> Select by Affiliating University (Auto-selects all colleges):
                                </label>
                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.7rem;">
                                    {{ $affiliatingParents->count() }} Affiliating Universities Available
                                </span>
                            </div>
                            <div class="row g-2 align-items-center mt-1">
                                <div class="col-md-7">
                                    <select id="parentUniQuickSelect" class="form-select form-select-sm">
                                        <option value="">-- Choose Affiliating University... --</option>
                                        @foreach($affiliatingParents as $pu)
                                            <option value="{{ $pu->id }}" data-count="{{ $pu->colleges_count }}" data-name="{{ $pu->short_name ?: $pu->name }}">
                                                {{ $pu->name }} ({{ $pu->colleges_count }} Affiliated Colleges)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-5 d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-primary py-1 px-2.5 rounded-pill flex-grow-1" onclick="applyAffiliatingUniversitySelect(true)" style="font-size: 0.78rem;">
                                        <i class="bi bi-check2-circle me-1"></i> Auto-Select All Colleges
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill" onclick="applyAffiliatingUniversitySelect(false)" style="font-size: 0.78rem;" title="Deselect colleges under this university">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Selection Feedback Toast/Alert -->
                    <div id="selectionFeedbackMsg" class="alert alert-info alert-dismissible py-1.5 px-3 mb-2 small d-none align-items-center justify-content-between" role="alert">
                        <span id="selectionFeedbackText"></span>
                        <button type="button" class="btn-close py-2" onclick="this.parentElement.classList.add('d-none')" aria-label="Close"></button>
                    </div>

                    <!-- Search Input Bar -->
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="uniSearchInput" class="form-control border-start-0" placeholder="Search by university / college name, affiliating parent, code..." oninput="filterUniversities()">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearUniSearch()" title="Clear Search">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                            <small class="text-muted" style="font-size: 0.75rem;" id="uniFilterCount">Showing all {{ count($universities) }} institutions</small>
                            <small class="text-muted fw-semibold text-primary" style="font-size: 0.75rem;" id="uniSelectedCount">0 selected</small>
                        </div>
                    </div>

                    <div class="row g-2" id="uniCheckboxList" style="max-height: 280px; overflow-y: auto;">
                        @foreach($universities as $u)
                            @php
                                $typeLabel = '';
                                if($u->type === 'university') $typeLabel = 'University';
                                elseif($u->type === 'ini') $typeLabel = 'INI';
                                elseif($u->type === 'affiliated_college') $typeLabel = 'Affiliated College';
                                elseif($u->type === 'polytechnic_iti') $typeLabel = 'Polytechnic/ITI';

                                $parentText = '';
                                if($u->parent) {
                                    $parentText = $u->parent->name . ' ' . ($u->parent->short_name ?? '');
                                }
                            @endphp
                            <div class="col-md-6 uni-item" 
                                 data-search-text="{{ strtolower($u->name . ' ' . $u->short_name . ' ' . $typeLabel . ' ' . $parentText) }}"
                                 data-parent-id="{{ $u->parent_id ?? '' }}">
                                <div class="form-check p-2 rounded hover-bg-light border mb-1 h-100 d-flex align-items-start">
                                    <input class="form-check-input ms-0 me-2 mt-1 uni-checkbox" 
                                           type="checkbox" 
                                           name="university_ids[]" 
                                           value="{{ $u->id }}" 
                                           id="uni_{{ $u->id }}" 
                                           data-is-parent="{{ ($u->colleges_count > 0) ? '1' : '0' }}"
                                           data-parent-id="{{ $u->parent_id ?? '' }}"
                                           data-name="{{ $u->short_name ?: $u->name }}"
                                           data-colleges-count="{{ $u->colleges_count }}"
                                           {{ (in_array($u->id, old('university_ids', $selectedUniversityIds ?? []))) ? 'checked' : '' }} 
                                           onchange="handleUniCheckboxChange(this)">
                                    <label class="form-check-label small w-100 cursor-pointer" for="uni_{{ $u->id }}">
                                        <div class="d-flex align-items-center flex-wrap gap-1">
                                            <strong class="text-dark">{{ $u->short_name ?: $u->name }}</strong>
                                            @if($u->colleges_count > 0)
                                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;" title="Checking this will auto-select all {{ $u->colleges_count }} affiliated colleges">
                                                    <i class="bi bi-diagram-3 me-1"></i>{{ $u->colleges_count }} Colleges
                                                </span>
                                            @elseif($typeLabel)
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.65rem;">{{ $typeLabel }}</span>
                                            @endif
                                        </div>
                                        <span class="text-secondary d-block" style="font-size: 0.78rem;">{{ Str::limit($u->name, 45) }}</span>
                                        @if($u->parent)
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                <i class="bi bi-arrow-return-right text-primary me-1"></i>Affiliated to: {{ $u->parent->short_name ?: Str::limit($u->parent->name, 28) }}
                                            </small>
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
        updateSelectedCount();
    }

    function handleUniCheckboxChange(checkbox) {
        const isParent = checkbox.getAttribute('data-is-parent') === '1';
        const uniId = checkbox.value;
        const isChecked = checkbox.checked;

        if (isParent) {
            // Auto-select/deselect all colleges under this affiliating university
            const childCheckboxes = document.querySelectorAll('#uniCheckboxList .uni-checkbox[data-parent-id="' + uniId + '"]');
            childCheckboxes.forEach(cb => {
                cb.checked = isChecked;
            });

            const parentName = checkbox.getAttribute('data-name') || 'University';
            const count = childCheckboxes.length;
            if (count > 0) {
                showSelectionFeedback((isChecked ? 'Auto-selected ' : 'Deselected ') + parentName + ' and all ' + count + ' affiliated colleges.');
            }
        }
        updateSelectedCount();
    }

    function applyAffiliatingUniversitySelect(selectFlag) {
        const selectEl = document.getElementById('parentUniQuickSelect');
        const parentId = selectEl?.value;
        if (!parentId) {
            alert('Please choose an affiliating university from the dropdown first.');
            return;
        }

        // Check/uncheck parent university
        const parentCheckbox = document.getElementById('uni_' + parentId);
        if (parentCheckbox) {
            parentCheckbox.checked = selectFlag;
        }

        // Check/uncheck all child colleges
        const childCheckboxes = document.querySelectorAll('#uniCheckboxList .uni-checkbox[data-parent-id="' + parentId + '"]');
        childCheckboxes.forEach(cb => {
            cb.checked = selectFlag;
        });

        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const count = selectedOption.getAttribute('data-count') || childCheckboxes.length;
        const name = selectedOption.getAttribute('data-name') || 'University';
        
        showSelectionFeedback((selectFlag ? 'Auto-selected ' : 'Deselected ') + name + ' and all ' + count + ' affiliated colleges.');

        updateSelectedCount();
    }

    function showSelectionFeedback(msg) {
        const alertEl = document.getElementById('selectionFeedbackMsg');
        const textEl = document.getElementById('selectionFeedbackText');
        if (alertEl && textEl) {
            textEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> ' + msg;
            alertEl.classList.remove('d-none');
            alertEl.classList.add('d-flex');
            
            // Auto-hide after 5 seconds
            clearTimeout(window._feedbackTimeout);
            window._feedbackTimeout = setTimeout(() => {
                alertEl.classList.add('d-none');
                alertEl.classList.remove('d-flex');
            }, 5000);
        }
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
