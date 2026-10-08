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
        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkQuestionImportModal">
            <i class="bi bi-file-earmark-excel me-1"></i> Bulk Question Import
        </button>
        <a href="{{ route('admin.questions.create') }}" class="btn btn-primary-custom btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Add New Question
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.questions.index') }}" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search question text..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <select name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Filter by Section (All)</option>
                @foreach($sections as $sec)
                    <option value="{{ $sec->id }}" {{ request('section_id') == $sec->id ? 'selected' : '' }}>
                        {{ $sec->title }} ({{ $sec->category->name ?? 'General' }})
                    </option>
                @endforeach
            </select>
        </div>

        @if(auth()->check() && auth()->user()->isSuperAdmin())
            <div class="col-md-2">
                <select name="university_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Institution (All)</option>
                    <option value="global" {{ request('university_id') == 'global' ? 'selected' : '' }}>Global (Common)</option>
                    @foreach($universities as $u)
                        <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>{{ $u->short_name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-md-2">
            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Type (All)</option>
                <option value="single_choice" {{ request('type') == 'single_choice' ? 'selected' : '' }}>Single Choice</option>
                <option value="multiple_choice" {{ request('type') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                <option value="likert" {{ request('type') == 'likert' ? 'selected' : '' }}>Likert Scale</option>
                <option value="rating" {{ request('type') == 'rating' ? 'selected' : '' }}>Rating</option>
                <option value="dropdown" {{ request('type') == 'dropdown' ? 'selected' : '' }}>Dropdown</option>
                <option value="short_text" {{ request('type') == 'short_text' ? 'selected' : '' }}>Short Text</option>
                <option value="long_text" {{ request('type') == 'long_text' ? 'selected' : '' }}>Long Text</option>
                <option value="voice" {{ request('type') == 'voice' ? 'selected' : '' }}>Voice Answer</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="tag" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Tag (All)</option>
                <option value="technical" {{ request('tag') == 'technical' ? 'selected' : '' }}>Technical</option>
                <option value="practical" {{ request('tag') == 'practical' ? 'selected' : '' }}>Practical</option>
                <option value="ai" {{ request('tag') == 'ai' ? 'selected' : '' }}>AI & Digital</option>
                <option value="employability" {{ request('tag') == 'employability' ? 'selected' : '' }}>Employability</option>
                <option value="psychometric" {{ request('tag') == 'psychometric' ? 'selected' : '' }}>Psychometric</option>
                <option value="voice" {{ request('tag') == 'voice' ? 'selected' : '' }}>Voice Feedback</option>
            </select>
        </div>

        <div class="col-auto ms-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-secondary"><i class="bi bi-filter me-1"></i> Filter</button>
            @if(request()->hasAny(['search', 'section_id', 'category_id', 'university_id', 'type', 'tag']))
                <a href="{{ route('admin.questions.index') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
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
                        <td class="text-muted fw-semibold">{{ ($questions->firstItem() ?? 1) + $loop->index }}</td>
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
                                    <i class="bi bi-building me-1"></i> {{ $q->universities->pluck('short_name')->implode(', ') }}
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
                                <div class="d-flex align-items-center gap-2">
                                    <!-- Enable / Disable Switch -->
                                    <div class="form-check form-switch m-0" title="{{ $q->is_active ? 'Active (Click to Disable)' : 'Disabled (Click to Enable)' }}">
                                        <input class="form-check-input question-toggle-btn" 
                                               type="checkbox" 
                                               role="switch"
                                               id="q_toggle_{{ $q->id }}"
                                               data-id="{{ $q->id }}"
                                               data-url="{{ route('admin.questions.toggle_status', $q->id) }}"
                                               {{ $q->is_active ? 'checked' : '' }}
                                               style="cursor: pointer; width: 2.2em; height: 1.15em;">
                                    </div>
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
                                </div>
                            @else
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $q->is_active ? 'bg-success-subtle text-success border-success' : 'bg-secondary-subtle text-secondary' }} border py-1 px-2" style="font-size: 0.72rem;">
                                        <i class="bi {{ $q->is_active ? 'bi-check-circle-fill' : 'bi-dash-circle' }} me-1"></i> {{ $q->is_active ? 'Active' : 'Disabled' }}
                                    </span>
                                    <span class="badge bg-light text-secondary border py-1 px-2" title="Superadmin / Global Question (Read-Only)">
                                        <i class="bi bi-lock-fill me-1 text-warning"></i> Read-Only
                                    </span>
                                </div>
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
                                <!-- Affiliating University Quick Selector -->
                                @php
                                    $affiliatingParents = $universities->where('type', 'university')->where('colleges_count', '>', 0)->sortBy('name');
                                @endphp
                                @if($affiliatingParents->isNotEmpty())
                                    <div class="p-2 mb-2 bg-white rounded-3 border">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                                            <label for="parent_uni_quick_select_new" class="form-label mb-0 small fw-bold text-primary d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                <i class="bi bi-diagram-3-fill"></i> Select by Affiliating University:
                                            </label>
                                            <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;">
                                                {{ $affiliatingParents->count() }} Affiliating Available
                                            </span>
                                        </div>
                                        <div class="row g-1 align-items-center">
                                            <div class="col-sm-7">
                                                <select id="parent_uni_quick_select_new" class="form-select form-select-sm" style="font-size: 0.75rem;">
                                                    <option value="">-- Choose Affiliating Univ... --</option>
                                                    @foreach($affiliatingParents as $pu)
                                                        <option value="{{ $pu->id }}" data-count="{{ $pu->colleges_count }}" data-name="{{ $pu->short_name ?: $pu->name }}">
                                                            {{ $pu->name }} ({{ $pu->colleges_count }} Colleges)
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-sm-5 d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-primary py-1 px-2 rounded-pill flex-grow-1" onclick="applySecAffiliatingUniSelect('new', true)" style="font-size: 0.72rem;">
                                                    <i class="bi bi-check2-circle me-1"></i> Auto-Select All
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-1.5 rounded-pill" onclick="applySecAffiliatingUniSelect('new', false)" style="font-size: 0.72rem;" title="Deselect colleges under this university">
                                                    <i class="bi bi-dash-circle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Feedback Alert -->
                                <div id="sec_feedback_msg_new" class="alert alert-info alert-dismissible py-1 px-2 mb-2 small d-none align-items-center justify-content-between" style="font-size: 0.75rem;" role="alert">
                                    <span id="sec_feedback_text_new"></span>
                                    <button type="button" class="btn-close py-1" style="font-size: 0.65rem;" onclick="this.parentElement.classList.add('d-none')" aria-label="Close"></button>
                                </div>

                                <!-- Header with select all and counts -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-2 pt-1 border-top">
                                    <small class="text-muted" style="font-size: 0.75rem;" id="sec_uni_filter_count_new">Showing all {{ count($universities) }} institutions</small>
                                    <div class="d-flex gap-1 align-items-center">
                                        <small class="text-muted fw-semibold text-primary me-1" style="font-size: 0.75rem;" id="sec_uni_count_new">0 selected</small>
                                        <button type="button" class="btn btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('new', true)">Select All</button>
                                        <button type="button" class="btn btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('new', false)">Deselect All</button>
                                    </div>
                                </div>

                                <!-- Search input -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" id="sec_uni_search_new" class="form-control border-start-0" placeholder="Search by name, affiliating parent, code..." oninput="filterSecUnis('new')">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearSecUniSearch('new')"><i class="bi bi-x-lg"></i></button>
                                </div>

                                <div class="row g-2 p-2 bg-white rounded-3 border overflow-auto" id="sec_uni_list_new" style="max-height: 200px;">
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
                                        <div class="col-md-6 sec-uni-item" 
                                             data-search-text="{{ strtolower($u->name . ' ' . $u->short_name . ' ' . $typeLabel . ' ' . $parentText) }}"
                                             data-parent-id="{{ $u->parent_id ?? '' }}">
                                            <div class="form-check p-1.5 rounded hover-bg-light border mb-0 h-100 d-flex align-items-start">
                                                <input class="form-check-input ms-0 me-2 mt-1 sec-uni-checkbox" 
                                                       type="checkbox" 
                                                       name="university_ids[]" 
                                                       value="{{ $u->id }}" 
                                                       id="sec_uni_new_{{ $u->id }}"
                                                       data-is-parent="{{ ($u->colleges_count > 0) ? '1' : '0' }}"
                                                       data-parent-id="{{ $u->parent_id ?? '' }}"
                                                       data-name="{{ $u->short_name ?: $u->name }}"
                                                       data-colleges-count="{{ $u->colleges_count }}"
                                                       onchange="handleSecUniCheckboxChange('new', this)">
                                                <label class="form-check-label small w-100 cursor-pointer" for="sec_uni_new_{{ $u->id }}">
                                                    <div class="d-flex align-items-center flex-wrap gap-1">
                                                        <strong class="text-dark">{{ $u->short_name ?: $u->name }}</strong>
                                                        @if($u->colleges_count > 0)
                                                            <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.6rem;" title="Checking this will auto-select all {{ $u->colleges_count }} affiliated colleges">
                                                                <i class="bi bi-diagram-3 me-1"></i>{{ $u->colleges_count }} Colleges
                                                            </span>
                                                        @elseif($typeLabel)
                                                            <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.6rem;">{{ $typeLabel }}</span>
                                                        @endif
                                                    </div>
                                                    <span class="text-secondary d-block" style="font-size: 0.75rem;">{{ Str::limit($u->name, 35) }}</span>
                                                    @if($u->parent)
                                                        <small class="text-muted d-block" style="font-size: 0.68rem;">
                                                            <i class="bi bi-arrow-return-right text-primary me-1"></i>Affiliated to: {{ $u->parent->short_name ?: Str::limit($u->parent->name, 22) }}
                                                        </small>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="sec_no_match_new" class="text-center py-2 text-muted small d-none">
                                    <i class="bi bi-search me-1"></i> No matching institutions found.
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

<!-- Modal: Bulk Question Import -->
<div class="modal fade" id="bulkQuestionImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.questions.bulk_import') }}" method="POST" enctype="multipart/form-data" id="bulkQuestionImportForm">
                @csrf
                <div class="modal-header border-bottom bg-light">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-file-earmark-excel-fill text-success fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0">Bulk Question Import</h5>
                            <small class="text-muted">Upload an Excel/CSV spreadsheet to batch create questions</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- Step 1: Download Template -->
                    <div class="card border border-success border-opacity-25 bg-success bg-opacity-10 rounded-3 p-3 mb-3">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-download text-success me-1"></i> Step 1: Download Sample Excel Template</h6>
                                <p class="text-secondary small mb-0">
                                    Pre-populated with all required columns, interactive category & question type dropdowns, and realistic sample data.
                                </p>
                            </div>
                            <div>
                                <a href="{{ route('admin.questions.download_sample_excel') }}" class="btn btn-success btn-sm px-3 text-nowrap shadow-sm">
                                    <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Download Template (.xlsx)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Role Scope Notice -->
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <div class="alert alert-primary d-flex align-items-center py-2 px-3 mb-3 rounded-3 border-primary border-opacity-25">
                            <i class="bi bi-globe-americas fs-4 text-primary me-2"></i>
                            <div class="small">
                                <strong>Super Admin Scope:</strong> Imported questions will be automatically assigned as <strong>Common / Global Questions</strong> accessible to all universities & colleges state-wide.
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info d-flex align-items-center py-2 px-3 mb-3 rounded-3 border-info border-opacity-25">
                            <i class="bi bi-building fs-4 text-info me-2"></i>
                            <div class="small">
                                <strong>University Scope:</strong> Imported questions will be automatically assigned exclusively to <strong>{{ auth()->user()->university->name ?? 'your institution' }}</strong>.
                            </div>
                        </div>
                    @endif

                    <!-- Step 2: Upload File -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Step 2: Choose Excel / CSV File to Import <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text small text-muted">
                            Supported formats: <code>.xlsx</code>, <code>.xls</code>, <code>.csv</code> (Maximum file size: 10MB).
                        </div>
                    </div>

                    <!-- Guidelines Accordion / Helper -->
                    <div class="card bg-light border-0 rounded-3 p-3">
                        <h6 class="fw-semibold text-dark mb-2 small"><i class="bi bi-info-circle text-primary me-1"></i> Spreadsheet Column Guidelines:</h6>
                        <div class="row g-2 small text-secondary">
                            <div class="col-md-6">
                                <strong>Target Category:</strong> Select from dropdown list (e.g. <code>cat_1: Working Alumni</code>).
                            </div>
                            <div class="col-md-6">
                                <strong>Section Title:</strong> Dependent dropdown — automatically filters to show only sections belonging to the selected Target Category.
                            </div>
                            <div class="col-md-6">
                                <strong>Question Statement:</strong> The question text / prompt (Required).
                            </div>
                            <div class="col-md-6">
                                <strong>Question Type:</strong> Select from dropdown (Single Choice, Likert, Rating, Text, Voice, etc.).
                            </div>
                            <div class="col-md-6">
                                <strong>Options:</strong> Separated by semicolon (<code>;</code>) for Choice and Dropdown types.
                            </div>
                            <div class="col-md-6">
                                <strong>Psychometric Dimension:</strong> Select from dropdown list or <code>None</code>.
                            </div>
                            <div class="col-md-6">
                                <strong>Must to Answer:</strong> <code>Yes</code> or <code>No</code> (Default: No).
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> <code>Active</code> or <code>Disabled</code> (Default: Active).
                            </div>
                            <div class="col-12">
                                <strong>Tags:</strong> Comma-separated tags (e.g. <code>technical, practical, ai</code>).
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-light">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4" id="btnSubmitImport">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Start Bulk Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

    function handleSecUniCheckboxChange(secId, checkbox) {
        const isParent = checkbox.getAttribute('data-is-parent') === '1';
        const uniId = checkbox.value;
        const isChecked = checkbox.checked;

        if (isParent) {
            // Auto-select/deselect all colleges under this affiliating university
            const childCheckboxes = document.querySelectorAll('#sec_uni_list_' + secId + ' .sec-uni-checkbox[data-parent-id="' + uniId + '"]');
            childCheckboxes.forEach(cb => {
                cb.checked = isChecked;
            });

            const parentName = checkbox.getAttribute('data-name') || 'University';
            const count = childCheckboxes.length;
            if (count > 0) {
                showSecSelectionFeedback(secId, (isChecked ? 'Auto-selected ' : 'Deselected ') + parentName + ' and all ' + count + ' affiliated colleges.');
            }
        }
        updateSecSelectedCount(secId);
    }

    function applySecAffiliatingUniSelect(secId, selectFlag) {
        const selectEl = document.getElementById('parent_uni_quick_select_' + secId);
        const parentId = selectEl?.value;
        if (!parentId) {
            alert('Please choose an affiliating university from the dropdown first.');
            return;
        }

        // Check/uncheck parent university checkbox
        const parentCheckbox = document.getElementById('sec_uni_' + secId + '_' + parentId);
        if (parentCheckbox) {
            parentCheckbox.checked = selectFlag;
        }

        // Check/uncheck all child colleges
        const childCheckboxes = document.querySelectorAll('#sec_uni_list_' + secId + ' .sec-uni-checkbox[data-parent-id="' + parentId + '"]');
        childCheckboxes.forEach(cb => {
            cb.checked = selectFlag;
        });

        const selectedOption = selectEl.options[selectEl.selectedIndex];
        const count = selectedOption.getAttribute('data-count') || childCheckboxes.length;
        const name = selectedOption.getAttribute('data-name') || 'University';

        showSecSelectionFeedback(secId, (selectFlag ? 'Auto-selected ' : 'Deselected ') + name + ' and all ' + count + ' affiliated colleges.');

        updateSecSelectedCount(secId);
    }

    function showSecSelectionFeedback(secId, msg) {
        const alertEl = document.getElementById('sec_feedback_msg_' + secId);
        const textEl = document.getElementById('sec_feedback_text_' + secId);
        if (alertEl && textEl) {
            textEl.innerHTML = '<i class="bi bi-info-circle me-1"></i> ' + msg;
            alertEl.classList.remove('d-none');
            alertEl.classList.add('d-flex');

            if (!window._secFeedbackTimeouts) window._secFeedbackTimeouts = {};
            clearTimeout(window._secFeedbackTimeouts[secId]);
            window._secFeedbackTimeouts[secId] = setTimeout(() => {
                alertEl.classList.add('d-none');
                alertEl.classList.remove('d-flex');
            }, 5000);
        }
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

        const filterCount = document.getElementById('sec_uni_filter_count_' + secId);
        if (filterCount) {
            filterCount.innerText = query ? `Showing ${visibleCount} of ${items.length} institutions` : `Showing all ${items.length} institutions`;
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

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.question-toggle-btn').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            const isChecked = this.checked;
            const url = this.dataset.url;
            const toggleEl = this;
            
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Network response was not ok');
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    toggleEl.checked = data.is_active;
                    toggleEl.closest('.form-switch').title = data.is_active ? 'Active (Click to Disable)' : 'Disabled (Click to Enable)';
                } else {
                    toggleEl.checked = !isChecked;
                    alert(data.message || 'Could not update status');
                }
            })
            .catch(err => {
                toggleEl.checked = !isChecked;
                alert('An error occurred while updating question status.');
            });
        });
    });

    const importForm = document.getElementById('bulkQuestionImportForm');
    if (importForm) {
        importForm.addEventListener('submit', function () {
            const btn = document.getElementById('btnSubmitImport');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Importing Questions...';
            }
        });
    }
});
</script>
@endpush
