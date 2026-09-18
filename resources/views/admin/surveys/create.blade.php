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
                    <label class="form-label fw-semibold">Target Respondents</label>
                    <input type="text" name="target_respondents" class="form-control" value="All Students & Alumni">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Estimated Completion Time (mins)</label>
                    <input type="number" name="estimated_completion_time" class="form-control" value="15" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft">Draft</option>
                        <option value="published" selected>Published</option>
                        <option value="paused">Paused</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-check-circle me-1"></i> Create Survey Campaign</button>
            </div>
        </form>
    </div>
</div>
@endsection
