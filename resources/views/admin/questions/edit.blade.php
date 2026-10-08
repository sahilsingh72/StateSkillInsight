@extends('layouts.admin')

@section('title', 'Edit Question #' . $question->id)

@section('content')
<div class="container-fluid" style="max-width: 850px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold mb-1">
                Question #{{ $question->id }}
            </span>
            <h4 class="fw-bold text-dark mb-0">Edit Question Details</h4>
        </div>
        @if(request('return_to') === 'section_modal' || old('return_to') === 'section_modal')
            <a href="{{ route('admin.sections.index', ['open_section_modal' => request('section_id', old('section_id_return', $question->section_id))]) }}" class="btn btn-light border btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Section
            </a>
        @else
            <a href="{{ route('admin.questions.index') }}" class="btn btn-light border btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Question Bank
            </a>
        @endif
    </div>

    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger rounded-3 mb-4">
            <ul class="mb-0 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-custom p-4">
        <form action="{{ route('admin.questions.update', $question->id) }}" method="POST">
            @csrf
            @method('PUT')

            @if(request('return_to') === 'section_modal' || old('return_to') === 'section_modal')
                <input type="hidden" name="return_to" value="section_modal">
                <input type="hidden" name="section_id_return" value="{{ request('section_id', old('section_id_return', $question->section_id)) }}">
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Target Category & Section <span class="text-danger">*</span></label>
                <select name="section_id" class="form-select" required>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ $question->section_id == $sec->id ? 'selected' : '' }}>
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
                                   {{ old('scope_type', $currentScopeType ?? 'global') === 'global' ? 'checked' : '' }}
                                   onchange="toggleQScope(this.value)">
                            <label class="form-check-label fw-semibold cursor-pointer" for="q_scope_global">
                                <i class="bi bi-globe me-1 text-primary"></i> Common / All Institutions (Global Question)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope_type" id="q_scope_specific" value="specific" 
                                   {{ old('scope_type', $currentScopeType ?? 'global') === 'specific' ? 'checked' : '' }}
                                   onchange="toggleQScope(this.value)">
                            <label class="form-check-label fw-semibold cursor-pointer" for="q_scope_specific">
                                <i class="bi bi-building me-1 text-primary"></i> Specific Institution(s)
                            </label>
                        </div>
                    </div>

                    <div id="q_universities_container" class="{{ old('scope_type', $currentScopeType ?? 'global') === 'specific' ? '' : 'd-none' }}">
                        <!-- Affiliating University Quick Selector -->
                        @php
                            $affiliatingParents = $universities->where('type', 'university')->where('colleges_count', '>', 0)->sortBy('name');
                        @endphp
                        @if($affiliatingParents->isNotEmpty())
                            <div class="p-2.5 mb-3 bg-white rounded-3 border">
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
                                    <label for="qParentUniQuickSelect" class="form-label mb-0 small fw-bold text-primary d-flex align-items-center gap-1">
                                        <i class="bi bi-diagram-3-fill"></i> Select by Affiliating University (Auto-selects all colleges):
                                    </label>
                                    <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.7rem;">
                                        {{ $affiliatingParents->count() }} Affiliating Universities Available
                                    </span>
                                </div>
                                <div class="row g-2 align-items-center mt-1">
                                    <div class="col-md-7">
                                        <select id="qParentUniQuickSelect" class="form-select form-select-sm">
                                            <option value="">-- Choose Affiliating University... --</option>
                                            @foreach($affiliatingParents as $pu)
                                                <option value="{{ $pu->id }}" data-count="{{ $pu->colleges_count }}" data-name="{{ $pu->short_name ?: $pu->name }}">
                                                    {{ $pu->name }} ({{ $pu->colleges_count }} Affiliated Colleges)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5 d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-primary py-1 px-2.5 rounded-pill flex-grow-1" onclick="applyQAffiliatingUniversitySelect(true)" style="font-size: 0.78rem;">
                                            <i class="bi bi-check2-circle me-1"></i> Auto-Select All Colleges
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill" onclick="applyQAffiliatingUniversitySelect(false)" style="font-size: 0.78rem;" title="Deselect colleges under this university">
                                            <i class="bi bi-dash-circle"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Selection Feedback Toast/Alert -->
                        <div id="qSelectionFeedbackMsg" class="alert alert-info alert-dismissible py-1.5 px-3 mb-2 small d-none align-items-center justify-content-between" role="alert">
                            <span id="qSelectionFeedbackText"></span>
                            <button type="button" class="btn-close py-2" onclick="this.parentElement.classList.add('d-none')" aria-label="Close"></button>
                        </div>

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
                                <input type="text" id="qUniSearchInput" class="form-control border-start-0" placeholder="Search by university / college name, affiliating parent, code..." oninput="filterQUnis()">
                                <button class="btn btn-outline-secondary" type="button" onclick="clearQUniSearch()" title="Clear Search">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                <small class="text-muted" style="font-size: 0.75rem;" id="qUniFilterCount">Showing all {{ count($universities) }} institutions</small>
                                <small class="text-muted fw-semibold text-primary" style="font-size: 0.75rem;" id="qUniSelectedCount">0 selected</small>
                            </div>
                        </div>

                        <div class="row g-2 p-2 bg-white rounded-3 border overflow-auto" id="qUniCheckboxList" style="max-height: 250px;">
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
                                <div class="col-md-6 q-uni-item" 
                                     data-search-text="{{ strtolower($u->name . ' ' . $u->short_name . ' ' . $typeLabel . ' ' . $parentText) }}"
                                     data-parent-id="{{ $u->parent_id ?? '' }}">
                                    <div class="form-check p-2 rounded hover-bg-light border mb-0 h-100 d-flex align-items-start">
                                        <input class="form-check-input ms-0 me-2 mt-1 q-uni-checkbox" 
                                               type="checkbox" 
                                               name="university_ids[]" 
                                               value="{{ $u->id }}" 
                                               id="q_uni_{{ $u->id }}"
                                               data-is-parent="{{ ($u->colleges_count > 0) ? '1' : '0' }}"
                                               data-parent-id="{{ $u->parent_id ?? '' }}"
                                               data-name="{{ $u->short_name ?: $u->name }}"
                                               data-colleges-count="{{ $u->colleges_count }}"
                                               {{ (in_array($u->id, old('university_ids', $selectedUniversityIds ?? []))) ? 'checked' : '' }} 
                                               onchange="handleQUniCheckboxChange(this)">
                                        <label class="form-check-label small w-100 cursor-pointer" for="q_uni_{{ $u->id }}">
                                            <div class="d-flex align-items-center flex-wrap gap-1">
                                                <strong class="text-dark">{{ $u->short_name ?: $u->name }}</strong>
                                                @if($u->colleges_count > 0)
                                                    <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;" title="Checking this will auto-select all {{ $u->colleges_count }} affiliated colleges">
                                                        <i class="bi bi-diagram-3 me-1"></i>{{ $u->colleges_count }} Colleges
                                                    </span>
                                                @elseif($typeLabel)
                                                    <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.65rem;">{{ $typeLabel }}</span>
                                                @endif
                                            </div>
                                            <span class="text-secondary d-block" style="font-size: 0.78rem;">{{ Str::limit($u->name, 40) }}</span>
                                            @if($u->parent)
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                    <i class="bi bi-arrow-return-right text-primary me-1"></i>Affiliated to: {{ $u->parent->short_name ?: Str::limit($u->parent->name, 25) }}
                                                </small>
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
                    <small class="text-muted">This question is assigned to your institution: <strong>{{ auth()->user()->university->name }}</strong>.</small>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Question Statement <span class="text-danger">*</span></label>
                <textarea name="question_text" class="form-control @error('question_text') is-invalid @enderror" rows="3" placeholder="Enter the exact question statement..." required>{{ old('question_text', $question->question_text) }}</textarea>
                @error('question_text')
                    <div class="invalid-feedback fw-semibold">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Help Text / Instructions</label>
                <input type="text" name="help_text" class="form-control" value="{{ old('help_text', $question->help_text) }}" placeholder="Optional helper text for respondent">
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Question Type <span class="text-danger">*</span></label>
                    <select name="type" id="questionTypeSelectEdit" class="form-select" required>
                        <option value="single_choice" {{ $question->type == 'single_choice' ? 'selected' : '' }}>Single Choice (Radio)</option>
                        <option value="multiple_choice" {{ $question->type == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice (Checkboxes)</option>
                        <option value="dropdown" {{ $question->type == 'dropdown' ? 'selected' : '' }}>Dropdown Select</option>
                        <option value="likert" {{ $question->type == 'likert' ? 'selected' : '' }}>Likert Scale (1-5)</option>
                        <option value="rating" {{ $question->type == 'rating' ? 'selected' : '' }}>Rating (1-5 Stars)</option>
                        <option value="short_text" {{ $question->type == 'short_text' ? 'selected' : '' }}>Short Text</option>
                        <option value="long_text" {{ $question->type == 'long_text' ? 'selected' : '' }}>Long Text</option>
                        <option value="voice" {{ $question->type == 'voice' ? 'selected' : '' }}>Voice Answer (Web Audio API)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Psychometric Dimension Assignment</label>
                    <select name="dimension_id" class="form-select">
                        <option value="">None (General Question)</option>
                        @foreach($dimensions as $d)
                            <option value="{{ $d->id }}" {{ $question->dimension_id == $d->id ? 'selected' : '' }}>
                                {{ $d->name }} ({{ $d->category_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border h-100">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_required" id="is_required_toggle" value="1" {{ old('is_required', $question->is_required) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="is_required_toggle">
                            Must to answer <span class="badge bg-danger ms-1">Required</span>
                        </label>
                        <small class="text-muted d-block mt-1">If enabled, respondents must answer this question before submitting.</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch p-3 bg-light rounded-3 border h-100">
                        <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="is_active_toggle" value="1" {{ old('is_active', $question->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold text-dark" for="is_active_toggle">
                            Status <span class="badge bg-success ms-1">Active / Enabled</span>
                        </label>
                        <small class="text-muted d-block mt-1">If disabled, this question will be hidden from respondents in surveys.</small>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label fw-semibold mb-0">Options (One per line for Choice/Dropdown/Likert types)</label>
                    <small class="text-muted">Auto-filled for Likert Scale</small>
                </div>
                <textarea name="options_text" id="optionsTextareaEdit" class="form-control" rows="5" placeholder="Option 1&#10;Option 2&#10;Option 3&#10;Option 4">{{ old('options_text', $question->options->pluck('option_text')->implode("\n")) }}</textarea>
                <small class="text-muted d-block mt-1">Enter options on separate lines. For Likert scale, default 5 points (Strongly Disagree to Strongly Agree) are assigned.</small>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                @if(request('return_to') === 'section_modal' || old('return_to') === 'section_modal')
                    <a href="{{ route('admin.sections.index', ['open_section_modal' => request('section_id', old('section_id_return', $question->section_id))]) }}" class="btn btn-outline-secondary px-4">Cancel</a>
                @else
                    <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
                @endif
                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-check-circle me-1"></i> Update Question
                </button>
            </div>
        </form>
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

    function handleQUniCheckboxChange(checkbox) {
        const isParent = checkbox.getAttribute('data-is-parent') === '1';
        const uniId = checkbox.value;
        const isChecked = checkbox.checked;

        if (isParent) {
            // Auto-select/deselect all colleges under this affiliating university
            const childCheckboxes = document.querySelectorAll('#qUniCheckboxList .q-uni-checkbox[data-parent-id="' + uniId + '"]');
            childCheckboxes.forEach(cb => {
                cb.checked = isChecked;
            });

            const parentName = checkbox.getAttribute('data-name') || 'University';
            const count = childCheckboxes.length;
            if (count > 0) {
                showQSelectionFeedback((isChecked ? 'Auto-selected ' : 'Deselected ') + parentName + ' and all ' + count + ' affiliated colleges.');
            }
        }
        updateQSelectedCount();
    }

    function applyQAffiliatingUniversitySelect(selectFlag) {
        const selectEl = document.getElementById('qParentUniQuickSelect');
        const parentId = selectEl?.value;
        if (!parentId) {
            alert('Please choose an affiliating university from the dropdown first.');
            return;
        }

        // Check/uncheck parent university checkbox
        const parentCheckbox = document.getElementById('q_uni_' + parentId);
        if (parentCheckbox) {
            parentCheckbox.checked = selectFlag;
        }

        // Check/uncheck all child colleges
        const childCheckboxes = document.querySelectorAll('#qUniCheckboxList .q-uni-checkbox[data-parent-id="' + parentId + '"]');
        childCheckboxes.forEach(cb => {
            cb.checked = selectFlag;
        });

        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const count = selectedOption.getAttribute('data-count') || childCheckboxes.length;
        const name = selectedOption.getAttribute('data-name') || 'University';

        showQSelectionFeedback((selectFlag ? 'Auto-selected ' : 'Deselected ') + name + ' and all ' + count + ' affiliated colleges.');

        updateQSelectedCount();
    }

    function showQSelectionFeedback(msg) {
        const alertEl = document.getElementById('qSelectionFeedbackMsg');
        const textEl = document.getElementById('qSelectionFeedbackText');
        if (alertEl && textEl) {
            textEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> ' + msg;
            alertEl.classList.remove('d-none');
            alertEl.classList.add('d-flex');

            clearTimeout(window._qFeedbackTimeout);
            window._qFeedbackTimeout = setTimeout(() => {
                alertEl.classList.add('d-none');
                alertEl.classList.remove('d-flex');
            }, 5000);
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

    document.addEventListener('DOMContentLoaded', () => {
        updateQSelectedCount();

        const LIKERT_DEFAULT_OPTIONS = "Strongly Disagree\nDisagree\nNeutral\nAgree\nStrongly Agree";
        const typeSelect = document.getElementById('questionTypeSelectEdit');
        const optionsArea = document.getElementById('optionsTextareaEdit');

        if (typeSelect && optionsArea) {
            typeSelect.addEventListener('change', function () {
                if (this.value === 'likert') {
                    if (!optionsArea.value.trim()) {
                        optionsArea.value = LIKERT_DEFAULT_OPTIONS;
                    }
                }
            });
        }
    });
</script>
@endpush
