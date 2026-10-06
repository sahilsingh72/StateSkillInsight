@extends('layouts.admin')

@section('title', 'Add Question to Question Bank')

@section('content')
<div class="container-fluid" style="max-width: 850px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark mb-0">Add Question to Bank</h4>
        <a href="{{ route('admin.questions.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
    </div>

    <div class="card-custom p-4">
        <form action="{{ route('admin.questions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-semibold mb-0">Target Category & Section <span class="text-danger">*</span></label>
                    <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#createSectionModal">
                        <i class="bi bi-plus-circle me-1"></i> Create New Section
                    </button>
                </div>
                <select name="section_id" class="form-select" required>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ (isset($selectedSectionId) && $selectedSectionId == $sec->id) ? 'selected' : '' }}>
                            {{ $sec->category->name ?? 'Category' }} — {{ $sec->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <div class="mb-4 p-3 bg-light rounded-3 border">
                    <label class="form-label fw-bold text-dark mb-2">Target Institution Scope / Assignment</label>
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope_type" id="q_scope_global" value="global" 
                                   {{ old('scope_type', 'global') === 'global' ? 'checked' : '' }}
                                   onchange="toggleQScope(this.value)">
                            <label class="form-check-label fw-semibold cursor-pointer" for="q_scope_global">
                                <i class="bi bi-globe me-1 text-primary"></i> Common / All Institutions (Global Question)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope_type" id="q_scope_specific" value="specific" 
                                   {{ old('scope_type') === 'specific' ? 'checked' : '' }}
                                   onchange="toggleQScope(this.value)">
                            <label class="form-check-label fw-semibold cursor-pointer" for="q_scope_specific">
                                <i class="bi bi-building me-1 text-primary"></i> Specific Institution(s)
                            </label>
                        </div>
                    </div>

                    <div id="q_universities_container" class="{{ old('scope_type') === 'specific' ? '' : 'd-none' }}">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                            <div>
                                <small class="text-muted fw-semibold d-block">
                                    <i class="bi bi-check2-square me-1 text-primary"></i> Select Target Institution(s) <span class="text-danger">*</span>
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-pill" onclick="selectAllQUnis(true)" style="font-size: 0.78rem;">
                                    <i class="bi bi-check-all me-1"></i> Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill" onclick="selectAllQUnis(false)" style="font-size: 0.78rem;">
                                    <i class="bi bi-x-lg me-1"></i> Deselect All
                                </button>
                            </div>
                        </div>

                        <!-- Search Input Bar -->
                        <div class="mb-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-muted">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text" id="qUniSearchInput" class="form-control border-start-0" placeholder="Search by university / college name, code..." oninput="filterQUnis()">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearQUniSearch()" title="Clear Search">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                <small class="text-muted" style="font-size: 0.75rem;" id="qUniFilterCount">Showing all {{ count($universities) }} institutions</small>
                                <small class="text-muted" style="font-size: 0.75rem;" id="qUniSelectedCount">0 selected</small>
                            </div>
                        </div>

                        <div class="row g-2 p-2 bg-white rounded-3 border overflow-auto" id="qUniCheckboxList" style="max-height: 220px;">
                            @foreach($universities as $u)
                                @php
                                    $typeLabel = '';
                                    if($u->type === 'university') $typeLabel = 'University';
                                    elseif($u->type === 'ini') $typeLabel = 'INI';
                                    elseif($u->type === 'affiliated_college') $typeLabel = 'Affiliated College';
                                    elseif($u->type === 'polytechnic_iti') $typeLabel = 'Polytechnic/ITI';
                                @endphp
                                <div class="col-md-6 q-uni-item" data-search-text="{{ strtolower($u->name . ' ' . $u->short_name . ' ' . $typeLabel) }}">
                                    <div class="form-check p-2 rounded hover-bg-light border mb-0 h-100 d-flex align-items-center">
                                        <input class="form-check-input ms-0 me-2 q-uni-checkbox" type="checkbox" name="university_ids[]" value="{{ $u->id }}" id="q_uni_{{ $u->id }}"
                                               {{ (is_array(old('university_ids')) && in_array($u->id, old('university_ids'))) ? 'checked' : '' }} onchange="updateQSelectedCount()">
                                        <label class="form-check-label small w-100 cursor-pointer" for="q_uni_{{ $u->id }}">
                                            <strong class="text-dark">{{ $u->short_name }}</strong> — <span class="text-secondary">{{ Str::limit($u->name, 35) }}</span>
                                            @if($typeLabel)
                                                <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem;">{{ $typeLabel }}</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div id="qNoUniMatchMsg" class="text-center py-3 text-muted small d-none">
                            <i class="bi bi-search me-1"></i> No universities or colleges matching your search.
                        </div>
                        <small class="text-muted d-block mt-2">This question will be shown only to respondents from the selected institution(s).</small>
                    </div>
                </div>
            @elseif(auth()->check() && auth()->user()->university)
                <input type="hidden" name="scope_type" value="specific">
                <input type="hidden" name="university_ids[]" value="{{ auth()->user()->university_id }}">
                <div class="mb-3 p-3 bg-light rounded-3 border">
                    <small class="fw-semibold text-primary d-block mb-1"><i class="bi bi-building me-1"></i> Institution-Specific Question Assignment</small>
                    <small class="text-muted">This question will be created under <strong>{{ auth()->user()->university->name }}</strong>.</small>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Question Statement <span class="text-danger">*</span></label>
                <textarea name="question_text" class="form-control" rows="3" placeholder="Enter the exact question statement..." required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Help Text / Instructions</label>
                <input type="text" name="help_text" class="form-control" placeholder="Optional helper text for respondent">
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Question Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="single_choice">Single Choice (Radio)</option>
                        <option value="multiple_choice">Multiple Choice (Checkboxes)</option>
                        <option value="dropdown">Dropdown Select</option>
                        <option value="likert">Likert Scale (1-5)</option>
                        <option value="rating">Rating (1-5 Stars)</option>
                        <option value="short_text">Short Text</option>
                        <option value="long_text">Long Text</option>
                        <option value="voice">Voice Answer (Web Audio API)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Psychometric Dimension Assignment</label>
                    <select name="dimension_id" class="form-select">
                        <option value="">None (General Question)</option>
                        @foreach($dimensions as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->category_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border h-100">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_required" id="is_required_toggle" value="1" {{ old('is_required', '1') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="is_required_toggle">
                            Must to answer <span class="badge bg-danger ms-1">Required</span>
                        </label>
                        <small class="text-muted d-block mt-1">If enabled, respondents must answer this question before submitting.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border h-100">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="is_active_toggle" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="is_active_toggle">
                            Status <span class="badge bg-success ms-1">Active / Enabled</span>
                        </label>
                        <small class="text-muted d-block mt-1">If disabled, this question will be hidden from respondents in surveys.</small>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Options (One per line for Choice/Dropdown types)</label>
                <textarea name="options[]" class="form-control" rows="4" placeholder="Option 1&#10;Option 2&#10;Option 3&#10;Option 4"></textarea>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-check-circle me-1"></i> Save Question</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Create New Section in Category -->
<div class="modal fade" id="createSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.sections.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-folder-plus text-primary me-2"></i> Add New Section to Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose Target Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select border-primary" required>
                            <option value="">-- Select Category --</option>
                            @if(isset($categories))
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                                @endforeach
                            @endif
                        </select>
                        <small class="text-muted d-block mt-1">Select the category in which this new section will be created.</small>
                    </div>
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <label class="form-label fw-bold text-dark mb-2">Target Institution Scope</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="scope_type" id="new_sec_scope_g" value="global" checked onchange="toggleSecScope('new', this.value)">
                                    <label class="form-check-label small fw-semibold cursor-pointer" for="new_sec_scope_g">Common / All Institutions (Global)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="scope_type" id="new_sec_scope_s" value="specific" onchange="toggleSecScope('new', this.value)">
                                    <label class="form-check-label small fw-semibold cursor-pointer" for="new_sec_scope_s">Specific Institution(s)</label>
                                </div>
                            </div>

                            <div id="sec_unis_container_new" class="d-none">
                                <!-- Header with select all -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-2 pt-1 border-top">
                                    <small class="text-muted" style="font-size: 0.75rem;" id="sec_uni_count_new">0 selected</small>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('new', true)">Select All</button>
                                        <button type="button" class="btn btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('new', false)">Deselect All</button>
                                    </div>
                                </div>

                                <!-- Search input -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" id="sec_uni_search_new" class="form-control border-start-0" placeholder="Search institution by name, code..." oninput="filterSecUnis('new')">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearSecUniSearch('new')"><i class="bi bi-x-lg"></i></button>
                                </div>

                                <div class="row g-2 p-2 bg-white rounded-3 border overflow-auto" id="sec_uni_list_new" style="max-height: 180px;">
                                    @if(isset($universities))
                                        @foreach($universities as $u)
                                            @php
                                                $typeLabel = '';
                                                if($u->type === 'university') $typeLabel = 'University';
                                                elseif($u->type === 'ini') $typeLabel = 'INI';
                                                elseif($u->type === 'affiliated_college') $typeLabel = 'Affiliated College';
                                                elseif($u->type === 'polytechnic_iti') $typeLabel = 'Polytechnic/ITI';
                                            @endphp
                                            <div class="col-md-6 sec-uni-item" data-search-text="{{ strtolower($u->name . ' ' . $u->short_name . ' ' . $typeLabel) }}">
                                                <div class="form-check p-1 rounded hover-bg-light border mb-0 h-100 d-flex align-items-center">
                                                    <input class="form-check-input ms-0 me-2 sec-uni-checkbox" type="checkbox" name="university_ids[]" value="{{ $u->id }}" id="new_sec_uni_{{ $u->id }}" onchange="updateSecSelectedCount('new')">
                                                    <label class="form-check-label small w-100 cursor-pointer" for="new_sec_uni_{{ $u->id }}">
                                                        <strong class="text-dark">{{ $u->short_name }}</strong> — <span class="text-secondary">{{ Str::limit($u->name, 30) }}</span>
                                                        @if($typeLabel)
                                                            <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.6rem;">{{ $typeLabel }}</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div id="sec_no_match_new" class="text-center py-2 text-muted small d-none">
                                    No matching institutions found.
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Section Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Technical Skills / Practical Experience" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Section guidelines or scope"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-check-circle me-1"></i> Save Section</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleQScope(value) {
        const container = document.getElementById('q_universities_container');
        if (container) {
            if (value === 'specific') {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        }
        updateQSelectedCount();
    }

    function filterQUnis() {
        const query = (document.getElementById('qUniSearchInput')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('#qUniCheckboxList .q-uni-item');
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

        const noMatch = document.getElementById('qNoUniMatchMsg');
        if (noMatch) {
            noMatch.classList.toggle('d-none', visibleCount > 0);
        }

        const filterCount = document.getElementById('qUniFilterCount');
        if (filterCount) {
            filterCount.innerText = query ? `Showing ${visibleCount} of ${items.length} institutions` : `Showing all ${items.length} institutions`;
        }
    }

    function clearQUniSearch() {
        const input = document.getElementById('qUniSearchInput');
        if (input) {
            input.value = '';
            filterQUnis();
            input.focus();
        }
    }

    function selectAllQUnis(checked) {
        const visibleItems = document.querySelectorAll('#qUniCheckboxList .q-uni-item:not(.d-none) .q-uni-checkbox');
        visibleItems.forEach(cb => {
            cb.checked = checked;
        });
        updateQSelectedCount();
    }

    function updateQSelectedCount() {
        const totalChecked = document.querySelectorAll('#qUniCheckboxList .q-uni-checkbox:checked').length;
        const countEl = document.getElementById('qUniSelectedCount');
        if (countEl) {
            countEl.innerText = `${totalChecked} selected`;
        }
    }

    // Section modal handlers
    function toggleSecScope(secId, value) {
        const container = document.getElementById('sec_unis_container_' + secId);
        if (container) {
            if (value === 'specific') {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        }
        updateSecSelectedCount(secId);
    }

    function filterSecUnis(secId) {
        const query = (document.getElementById('sec_uni_search_' + secId)?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('#sec_uni_list_' + secId + ' .sec-uni-item');
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

        const noMatch = document.getElementById('sec_no_match_' + secId);
        if (noMatch) {
            noMatch.classList.toggle('d-none', visibleCount > 0);
        }
    }

    function clearSecUniSearch(secId) {
        const input = document.getElementById('sec_uni_search_' + secId);
        if (input) {
            input.value = '';
            filterSecUnis(secId);
            input.focus();
        }
    }

    function selectAllSecUnis(secId, check) {
        const visibleCheckboxes = document.querySelectorAll('#sec_uni_list_' + secId + ' .sec-uni-item:not(.d-none) .sec-uni-checkbox');
        visibleCheckboxes.forEach(cb => {
            cb.checked = check;
        });
        updateSecSelectedCount(secId);
    }

    function updateSecSelectedCount(secId) {
        const checked = document.querySelectorAll('#sec_uni_list_' + secId + ' .sec-uni-checkbox:checked').length;
        const countEl = document.getElementById('sec_uni_count_' + secId);
        if (countEl) {
            countEl.innerText = `${checked} selected`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateQSelectedCount();
    });
</script>
@endpush
