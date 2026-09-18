@extends('layouts.admin')

@section('title', 'Question Bank & Builder')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Reusable Question Bank (200+ Questions)</h4>
        <p class="text-secondary small mb-0">Search, filter by psychometric tag, question type, or category.</p>
    </div>
    <a href="{{ route('admin.questions.create') }}" class="btn btn-primary-custom btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Add New Question
    </a>
</div>

<!-- Filter Bar -->
<div class="card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="row g-2">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search question text..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="tag" class="form-select form-select-sm">
                <option value="">Filter by Tag (All)</option>
                <option value="technical" {{ request('tag') == 'technical' ? 'selected' : '' }}>Technical</option>
                <option value="practical" {{ request('tag') == 'practical' ? 'selected' : '' }}>Practical</option>
                <option value="ai" {{ request('tag') == 'ai' ? 'selected' : '' }}>AI & Digital</option>
                <option value="employability" {{ request('tag') == 'employability' ? 'selected' : '' }}>Employability</option>
                <option value="psychometric" {{ request('tag') == 'psychometric' ? 'selected' : '' }}>Psychometric</option>
                <option value="voice" {{ request('tag') == 'voice' ? 'selected' : '' }}>Voice Feedback</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select form-select-sm">
                <option value="">Filter by Type (All)</option>
                <option value="single_choice" {{ request('type') == 'single_choice' ? 'selected' : '' }}>Single Choice</option>
                <option value="likert" {{ request('type') == 'likert' ? 'selected' : '' }}>Likert Scale</option>
                <option value="rating" {{ request('type') == 'rating' ? 'selected' : '' }}>Rating</option>
                <option value="voice" {{ request('type') == 'voice' ? 'selected' : '' }}>Voice Answer</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-secondary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
        </div>
    </form>
</div>

<!-- Questions Table -->
<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Question Statement</th>
                    <th>Type</th>
                    <th>Category & Section</th>
                    <th>Psychometric Dimension</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($questions as $q)
                    <tr>
                        <td>{{ $q->id }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $q->question_text }}</div>
                            @if(!empty($q->tags))
                                @foreach($q->tags as $t)
                                    <span class="badge bg-light text-primary border" style="font-size:0.7rem;">#{{ $t }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td><span class="badge bg-info-subtle text-info border">{{ strtoupper($q->type) }}</span></td>
                        <td>
                            <small class="text-secondary">{{ $q->section->category->name ?? 'General' }}</small><br>
                            <small class="text-muted">{{ Str::limit($q->section->title ?? '', 20) }}</small>
                        </td>
                        <td>
                            <small class="text-primary fw-semibold">{{ $q->dimension->name ?? 'None' }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.questions.edit', $q->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $questions->links() }}
    </div>
</div>
@endsection
