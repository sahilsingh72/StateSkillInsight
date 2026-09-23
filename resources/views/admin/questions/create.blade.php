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
                            <label class="form-check-label fw-semibold" for="q_scope_global">
                                <i class="bi bi-globe me-1 text-primary"></i> Common / All Institutions (Global Question)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope_type" id="q_scope_specific" value="specific" 
                                   {{ old('scope_type') === 'specific' ? 'checked' : '' }}
                                   onchange="toggleQScope(this.value)">
                            <label class="form-check-label fw-semibold" for="q_scope_specific">
                                <i class="bi bi-building me-1 text-primary"></i> Specific Institution(s)
                            </label>
                        </div>
                    </div>

                    <div id="q_universities_container" class="{{ old('scope_type') === 'specific' ? '' : 'd-none' }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-semibold small mb-0">Select Target Institution(s) <span class="text-danger">*</span></label>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-xs" onclick="selectAllQUnis(true)">Select All</button>
                                <button type="button" class="btn btn-outline-secondary py-0 px-2 btn-xs" onclick="selectAllQUnis(false)">Deselect All</button>
                            </div>
                        </div>
                        <div class="row g-2 p-3 bg-white rounded-3 border overflow-auto" style="max-height: 200px;">
                            @foreach($universities as $u)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input q-uni-checkbox" type="checkbox" name="university_ids[]" value="{{ $u->id }}" id="q_uni_{{ $u->id }}"
                                               {{ (is_array(old('university_ids')) && in_array($u->id, old('university_ids'))) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="q_uni_{{ $u->id }}">
                                            {{ $u->name }} <span class="text-muted">({{ $u->short_name }})</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-muted d-block mt-1">This question will be shown only to respondents from the selected institution(s).</small>
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

            <div class="form-check form-switch mb-4 p-3 bg-light rounded-3 border">
                <input class="form-check-input ms-0 me-2" type="checkbox" name="is_required" id="is_required_toggle" value="1" {{ old('is_required', '1') ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold text-dark" for="is_required_toggle">
                    Must to answer <span class="badge bg-danger ms-1">Required</span>
                </label>
                <small class="text-muted d-block mt-1">If enabled, respondents must answer this question before proceeding or submitting.</small>
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
    }
    function selectAllQUnis(checked) {
        document.querySelectorAll('.q-uni-checkbox').forEach(cb => cb.checked = checked);
    }
</script>
@endpush
