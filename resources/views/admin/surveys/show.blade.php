@extends('layouts.admin')

@section('title', 'Survey Builder: ' . $survey->title)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-success mb-1">{{ strtoupper($survey->status) }}</span>
        <h4 class="fw-bold text-dark mb-0">{{ $survey->title }}</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.surveys.preview', $survey->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-play-circle me-1"></i> Preview Survey
        </a>
        <a href="{{ route('admin.surveys.index') }}" class="btn btn-light border btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>


@if(isset($errors) && $errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <ul class="mb-0 small">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Survey Settings Left Column -->
    <div class="col-md-4">
        <div class="card-custom p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-gear me-2 text-primary"></i> Survey Settings</h6>
            <div class="small mb-3">
                <strong>Target:</strong> {{ $survey->target_respondents ?? 'All Respondents' }}
            </div>
            <div class="small mb-3">
                <strong>Voice Answers Enabled:</strong> {{ $survey->enable_voice ? 'Yes' : 'No' }}
            </div>
            <div class="small mb-3">
                <strong>Allow Anonymous:</strong> {{ $survey->allow_anonymous ? 'Yes' : 'No' }}
            </div>
            <div class="small mb-3">
                <strong>Version:</strong> v{{ $survey->version }}
            </div>
            <div class="pt-3 border-top d-grid gap-2">
                <button type="button" class="btn btn-uni-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-folder-plus me-1"></i> Add New Category
                </button>
            </div>
        </div>
    </div>

    <!-- Survey Content / Categories & Sections Right Column -->
    <div class="col-md-8">
        <div class="card-custom p-4">
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-layers me-2 text-primary"></i> Configured Categories ({{ $survey->categories->count() }})</h6>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Category
                </button>
            </div>
            
            @if($survey->categories->isEmpty())
                <div class="p-5 text-center bg-light rounded-4 border">
                    <i class="bi bi-folder-plus display-4 text-secondary mb-3"></i>
                    <h5 class="fw-bold text-dark mb-2">No Categories Configured</h5>
                    <p class="text-secondary small mb-4">Start building your survey by adding your first category (e.g. Working Alumni, Students, General).</p>
                    <button type="button" class="btn btn-uni-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle me-1"></i> Add First Category
                    </button>
                </div>
            @else
                <div class="accordion" id="categoryAccordion">
                    @foreach($survey->categories as $cIdx => $cat)
                        <div class="accordion-item mb-3 border rounded-3 overflow-hidden shadow-sm">
                            <h2 class="accordion-header d-flex align-items-center bg-white pe-3">
                                <button class="accordion-button {{ $cIdx > 0 ? 'collapsed' : '' }} flex-grow-1" type="button" data-bs-toggle="collapse" data-bs-target="#cat_collapse_{{ $cat->id }}">
                                    <i class="bi {{ $cat->icon ?? 'bi-folder' }} me-2 text-primary"></i>
                                    <span class="fw-bold text-dark me-2">{{ $cat->name }}</span>
                                    <span class="badge bg-light text-secondary border me-2">{{ $cat->sections->count() }} Sections</span>
                                </button>
                            </h2>
                            <div id="cat_collapse_{{ $cat->id }}" class="accordion-collapse collapse {{ $cIdx == 0 ? 'show' : '' }}" data-bs-parent="#categoryAccordion">
                                <div class="accordion-body bg-light">
                                    @if($cat->description)
                                        <p class="small text-secondary mb-3">{{ $cat->description }}</p>
                                    @endif

                                    @if($cat->sections->isEmpty())
                                        <div class="p-3 text-center text-muted bg-white rounded border">
                                            <small class="d-block mb-2">No sections created in this category yet.</small>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="openAddSectionModal({{ $cat->id }}, '{{ addslashes($cat->name) }}')">
                                                <i class="bi bi-plus-lg me-1"></i> Create Section
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-3">
                                            @foreach($cat->sections as $sec)
                                                <div class="card border rounded-3 bg-white p-3 shadow-2xs">
                                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-task me-1 text-primary"></i> {{ $sec->title }}</h6>
                                                            @if($sec->description)
                                                                <small class="text-muted">{{ $sec->description }}</small>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-light text-secondary border me-1">{{ $sec->questions->count() }} Questions</span>
                                                            <a href="{{ route('admin.questions.create') }}?section_id={{ $sec->id }}" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-plus-lg me-1"></i> Add Question
                                                            </a>
                                                        </div>
                                                    </div>

                                                    <!-- Questions List inside Section -->
                                                    @if($sec->questions->isEmpty())
                                                        <p class="text-muted small mb-0 py-2 text-center italic">No questions in this section yet. Click "+ Add Question" above.</p>
                                                    @else
                                                        <div class="list-group list-group-flush">
                                                            @foreach($sec->questions as $qIdx => $q)
                                                                <div class="list-group-item px-0 py-2 d-flex justify-content-between align-items-center border-0">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <span class="badge bg-light text-dark border me-1">{{ $qIdx + 1 }}</span>
                                                                        <div>
                                                                            <span class="fw-semibold text-dark small">{{ Str::limit($q->question_text, 65) }}</span>
                                                                            <span class="badge bg-secondary-subtle text-secondary small ms-1">{{ $q->type }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="d-flex gap-1">
                                                                        <a href="{{ route('admin.questions.edit', $q->id) }}" class="btn btn-sm btn-light border py-0 px-2" title="Edit Question">
                                                                            <i class="bi bi-pencil"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Configure Categories Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.surveys.categories.sync', $survey->id) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-header-title fw-bold text-dark mb-0"><i class="bi bi-layers text-primary me-2"></i> Configure Categories for Survey</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="fw-bold text-dark">Select Existing Categories from Engine</span>
                            <p class="text-secondary small mb-0">Check categories to assign to this survey. Uncheck to deselect/remove from this survey.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllCategories(true)">
                                <i class="bi bi-check-all me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllCategories(false)">
                                <i class="bi bi-x-circle me-1"></i> Deselect All
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        @if(isset($masterCategories) && count($masterCategories) > 0)
                            @foreach($masterCategories as $masterCat)
                                @php $isSelected = $survey->categories->contains('id', $masterCat->id); @endphp
                                <div class="col-md-6">
                                    <label class="card h-100 p-3 cursor-pointer border rounded-3 transition-all {{ $isSelected ? 'border-primary bg-primary-subtle' : 'border-light-subtle bg-white' }}" style="cursor: pointer;">
                                        <div class="d-flex align-items-start gap-3">
                                            <input class="form-check-input flex-shrink-0 category-checkbox mt-1 fs-5" type="checkbox" name="category_ids[]" value="{{ $masterCat->id }}" {{ $isSelected ? 'checked' : '' }}>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="fw-bold text-dark">
                                                        <i class="bi {{ $masterCat->icon ?? 'bi-folder' }} text-primary me-1"></i> {{ $masterCat->name }}
                                                    </span>
                                                    <span class="badge bg-light text-secondary border">{{ $masterCat->code }}</span>
                                                </div>
                                                @if($masterCat->description)
                                                    <p class="small text-secondary mb-0">{{ Str::limit($masterCat->description, 75) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-4 text-muted">
                                No master categories found in Category Engine. Please create categories in <a href="{{ route('admin.categories.index') }}">Category Engine</a> first.
                            </div>
                        @endif
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-check-circle me-1"></i> Save Configured Categories</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.sections.store') }}" method="POST">
                @csrf
                <input type="hidden" name="category_id" id="modal_category_id" value="">
                <div class="modal-header border-bottom">
                    <h5 class="modal-header-title fw-bold text-dark mb-0"><i class="bi bi-list-plus text-primary me-2"></i> Add Section to <span id="modal_category_name" class="text-primary"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Section Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Demographics / Technical Skills" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Section guidelines or background information"></textarea>
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

@push('scripts')
<script>
    function selectAllCategories(select) {
        document.querySelectorAll('.category-checkbox').forEach(cb => {
            cb.checked = select;
        });
    }

    function openAddSectionModal(categoryId, categoryName) {
        document.getElementById('modal_category_id').value = categoryId;
        document.getElementById('modal_category_name').innerText = categoryName;
        const modal = new bootstrap.Modal(document.getElementById('addSectionModal'));
        modal.show();
    }
</script>
@endpush
@endsection
