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
        <a href="{{ route('admin.questions.index') }}" class="btn btn-light border btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Question Bank
        </a>
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
                            <label class="form-check-label fw-semibold" for="q_scope_global">
                                <i class="bi bi-globe me-1 text-primary"></i> Common / All Institutions (Global Question)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="scope_type" id="q_scope_specific" value="specific" 
                                   {{ old('scope_type', $currentScopeType ?? 'global') === 'specific' ? 'checked' : '' }}
                                   onchange="toggleQScope(this.value)">
                            <label class="form-check-label fw-semibold" for="q_scope_specific">
                                <i class="bi bi-building me-1 text-primary"></i> Specific Institution(s)
                            </label>
                        </div>
                    </div>

                    <div id="q_universities_container" class="{{ old('scope_type', $currentScopeType ?? 'global') === 'specific' ? '' : 'd-none' }}">
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
                                               {{ (in_array($u->id, old('university_ids', $selectedUniversityIds ?? []))) ? 'checked' : '' }}>
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
                    <small class="text-muted">This question is assigned to your institution: <strong>{{ auth()->user()->university->name }}</strong>.</small>
                </div>
            @endif

            <div class="mb-3">
                <label class="form-label fw-semibold">Question Statement <span class="text-danger">*</span></label>
                <textarea name="question_text" class="form-control" rows="3" placeholder="Enter the exact question statement..." required>{{ old('question_text', $question->question_text) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Help Text / Instructions</label>
                <input type="text" name="help_text" class="form-control" value="{{ old('help_text', $question->help_text) }}" placeholder="Optional helper text for respondent">
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Question Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
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

            <div class="form-check form-switch mb-4">
                <input class="form-check-input" type="checkbox" name="is_required" id="is_required_toggle" value="1" {{ $question->is_required ? 'checked' : '' }}>
                <label class="form-check-label fw-semibold" for="is_required_toggle">Required Question (Respondent cannot skip)</label>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Options (One per line for Choice/Dropdown types)</label>
                <textarea name="options_text" class="form-control" rows="5" placeholder="Option 1&#10;Option 2&#10;Option 3&#10;Option 4">{{ old('options_text', $question->options->pluck('option_text')->implode("\n")) }}</textarea>
                <small class="text-muted">Enter options on separate lines. Leave empty if question type is text or rating.</small>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-secondary px-4">Cancel</a>
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
    }
    function selectAllQUnis(checked) {
        document.querySelectorAll('.q-uni-checkbox').forEach(cb => cb.checked = checked);
    }
</script>
@endpush
