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
                <label class="form-label fw-semibold">Target Category & Section <span class="text-danger">*</span></label>
                <select name="section_id" class="form-select" required>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ (isset($selectedSectionId) && $selectedSectionId == $sec->id) ? 'selected' : '' }}>
                            {{ $sec->category->name ?? 'Category' }} — {{ $sec->title }}
                        </option>
                    @endforeach
                </select>
            </div>

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
@endsection
