@extends('layouts.admin')

@section('title', 'Question Bank & Builder')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Question Bank</h4>
        <p class="text-secondary small mb-0">Search, filter by psychometric tag, question type, or category.</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createSectionModal">
            <i class="bi bi-folder-plus me-1"></i> Add New Section
        </button>
        <a href="{{ route('admin.questions.create') }}" class="btn btn-primary-custom btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add New Question
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="row g-2">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search question text..." value="{{ request('search') }}">
        </div>
        @if(auth()->check() && auth()->user()->isSuperAdmin())
            <div class="col-md-3">
                <select name="university_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Filter by Institution (All)</option>
                    <option value="global" {{ request('university_id') == 'global' ? 'selected' : '' }}>Global (All Institutions)</option>
                    @foreach($universities as $u)
                        <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->short_name }})</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="col-md-2">
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
        <div class="col-md-2">
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
                    <th>Sno</th>
                    <th>Question Statement</th>
                    <th>Institution Scope</th>
                    <th>Type</th>
                    <th>Category & Section</th>
                    <th>Psychometric Dimension</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($questions as $q)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-semibold text-dark">
                                {{ $q->question_text }}
                                @if($q->is_required)
                                    <span class="badge bg-danger-subtle text-danger border ms-1" style="font-size: 0.65rem;">Must to answer</span>
                                @else
                                    <span class="badge bg-light text-muted border ms-1" style="font-size: 0.65rem;">Optional</span>
                                @endif
                            </div>
                            @if(!empty($q->tags))
                                @foreach($q->tags as $t)
                                    <span class="badge bg-light text-primary border" style="font-size:0.7rem;">#{{ $t }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td>
                            @if($q->university_id && $q->university)
                                <span class="badge bg-primary-subtle text-primary border" title="Specific to {{ $q->university->name }}">
                                    <i class="bi bi-building me-1"></i> {{ $q->university->short_name }}
                                </span>
                            @elseif(isset($q->universities) && $q->universities->isNotEmpty())
                                <span class="badge bg-info-subtle text-info border" title="{{ $q->universities->pluck('name')->implode(', ') }}">
                                    <i class="bi bi-building me-1"></i> {{ $q->universities->count() }} Institutions
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">
                                    <i class="bi bi-globe me-1"></i> Common (All)
                                </span>
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
                            @if($q->canBeEditedBy(auth()->user()))
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.questions.edit', $q->id) }}" class="btn btn-light border" title="Edit Question">
                                        <i class="bi bi-pencil text-primary"></i>
                                    </a>
                                    <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-light border text-danger" title="Delete Question">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="badge bg-light text-secondary border py-1 px-2" title="Superadmin / Global Question (Read-Only)">
                                    <i class="bi bi-lock-fill me-1 text-warning"></i> Read-Only
                                </span>
                            @endif
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
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Select the category in which this new section will be created.</small>
                    </div>
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Institution Assignment (Optional)</label>
                            <select name="university_id" class="form-select">
                                <option value="">Common / All Institutions (Global)</option>
                                @foreach($universities as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->short_name }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Select an institution if this section is specific to a university/college, or leave as Common for all.</small>
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
