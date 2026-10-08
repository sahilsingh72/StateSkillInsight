@extends('layouts.admin')

@section('title', 'Sections Management')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-folder2-open me-2 text-primary"></i> Sections Management</h4>
        <p class="text-secondary small mb-0">Manage research category sections and institution assignments.</p>
    </div>
    <button type="button" class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#createSectionModal">
        <i class="bi bi-plus-lg me-1"></i> Create New Section
    </button>
</div>

<!-- Filters & Search Bar -->
<div class="card-custom p-3 mb-4">
    <form action="{{ route('admin.sections.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search sections by title..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if(auth()->check() && auth()->user()->isSuperAdmin())
            <div class="col-md-3">
                <select name="university_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Institutions (Scope)</option>
                    <option value="global" {{ request('university_id') === 'global' ? 'selected' : '' }}>Common / Global Only</option>
                    @foreach($universities as $u)
                        <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-auto ms-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            @if(request()->hasAny(['search', 'category_id', 'university_id']))
                <a href="{{ route('admin.sections.index') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Sections Table -->
<div class="card-custom overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-secondary small text-uppercase fw-bold">
                <tr>
                    <th style="width: 50px;">Sno</th>
                    <th>Section Title & Details</th>
                    <th>Category</th>
                    <th>Institution Scope</th>
                    <th>Questions</th>
                    <th class="text-end" style="width: 130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sections as $sec)
                    @php
                        $user = auth()->user();
                        $isMultiUni = $sec->universities->count() > 1;
                        $isGlobal = is_null($sec->university_id) && $sec->universities->isEmpty();
                        $isExclusiveToUser = $sec->university_id === $user?->university_id && $sec->universities->count() <= 1;
                        $canManage = $user && ($user->isSuperAdmin() || (!$isGlobal && !$isMultiUni && $isExclusiveToUser));
                    @endphp
                    <tr>
                        <td class="fw-semibold text-muted">{{ ($sections->firstItem() ?? 1) + $loop->index }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $sec->title }}</div>
                            @if($sec->description)
                                <small class="text-secondary d-block">{{ Str::limit($sec->description, 80) }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border">
                                <i class="bi bi-folder me-1"></i> {{ $sec->category->name ?? 'General' }}
                            </span>
                        </td>
                        <td>
                            @if($user && $user->isSuperAdmin())
                                @if($sec->university_id && $sec->university)
                                    <span class="badge bg-primary-subtle text-primary border" title="Assigned to {{ $sec->university->name }}">
                                        <i class="bi bi-building me-1"></i> {{ $sec->university->short_name }}
                                    </span>
                                @elseif($sec->universities->isNotEmpty())
                                    <span class="badge bg-info-subtle text-info border" title="{{ $sec->universities->pluck('name')->implode(', ') }}">
                                        <i class="bi bi-building me-1"></i> {{ $sec->universities->count() }} Institutions
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <i class="bi bi-globe me-1"></i> Common (All)
                                    </span>
                                @endif
                            @else
                                @if($isGlobal)
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <i class="bi bi-globe me-1"></i> Common (All)
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border" title="{{ $user?->university->name ?? 'Your Institution' }}">
                                        <i class="bi bi-building me-1"></i> {{ $user?->university->short_name ?? 'Your Institution' }}
                                    </span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-light border text-primary fw-semibold px-2 py-1 shadow-sm d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#sectionQuestionsModal_{{ $sec->id }}" title="Click to view questions in this section">
                                <i class="bi bi-question-circle text-primary"></i>
                                <span>{{ $sec->questions_count }} Questions</span>
                            </button>
                        </td>
                        <td class="text-end">
                            @if($canManage)
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editSectionModal_{{ $sec->id }}" title="Edit Section">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#deleteSectionModal_{{ $sec->id }}" title="Delete Section">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="Locked: Shared across multiple institutions or Global">
                                    <i class="bi bi-lock-fill me-1"></i> Locked
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-5 text-center text-muted">
                            <i class="bi bi-folder2-open fs-2 d-block mb-2 text-secondary"></i>
                            No sections found. Click "+ Create New Section" above to add your first section.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sections->hasPages())
        <div class="p-3 border-top bg-light">
            {{ $sections->links() }}
        </div>
    @endif
</div>

<!-- All Section Modals (Placed Outside Table to Prevent Layout Glitches) -->
@foreach($sections as $sec)
    @php
        $user = auth()->user();
        $isMultiUni = $sec->universities->count() > 1;
        $isGlobal = is_null($sec->university_id) && $sec->universities->isEmpty();
        $isExclusiveToUser = $sec->university_id === $user?->university_id && $sec->universities->count() <= 1;
        $canManage = $user && ($user->isSuperAdmin() || (!$isGlobal && !$isMultiUni && $isExclusiveToUser));
    @endphp

    <!-- View Section Questions Modal -->
    <div class="modal fade text-start" id="sectionQuestionsModal_{{ $sec->id }}" tabindex="-1" aria-labelledby="sectionQuestionsModalLabel_{{ $sec->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom bg-light px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-primary-subtle text-primary p-2 rounded-3">
                            <i class="bi bi-card-list fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark mb-0" id="sectionQuestionsModalLabel_{{ $sec->id }}">
                                Questions in "{{ $sec->title }}"
                            </h5>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="badge bg-secondary-subtle text-secondary border" style="font-size: 0.75rem;">
                                    <i class="bi bi-folder2-open me-1"></i> {{ $sec->category->name ?? 'Category' }}
                                </span>
                                <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.75rem;">
                                    {{ $sec->questions->count() }} Total Questions
                                </span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    @if($sec->questions->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-question-circle fs-1 text-secondary opacity-50 d-block mb-2"></i>
                            <h6 class="fw-semibold text-secondary">No Questions Found</h6>
                            <p class="small text-muted mb-0">There are currently no questions configured under this section.</p>
                        </div>
                    @else
                        <div class="table-responsive border rounded-3 bg-white">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 55px;">Sno</th>
                                        <th>Question Statement</th>
                                        <th>Institution Scope</th>
                                        <th>Type</th>
                                        <th>Category & Section</th>
                                        <th>Psychometric Dimension</th>
                                        <th class="text-end pe-3" style="width: 110px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sec->questions as $q)
                                        <tr>
                                            <td class="text-muted fw-semibold ps-3">{{ $loop->iteration }}</td>
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
                                                    <div class="mt-1">
                                                        @foreach($q->tags as $t)
                                                            <span class="badge bg-light text-primary border" style="font-size:0.7rem;">#{{ $t }}</span>
                                                        @endforeach
                                                    </div>
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
                                                <small class="text-secondary">{{ $q->section->category->name ?? ($sec->category->name ?? 'General') }}</small><br>
                                                <small class="text-muted">{{ Str::limit($q->section->title ?? $sec->title, 20) }}</small>
                                            </td>
                                            <td>
                                                <small class="text-primary fw-semibold">{{ $q->dimension->name ?? 'None' }}</small>
                                            </td>
                                            <td class="text-end pe-3">
                                                @if($q->canBeEditedBy(auth()->user()))
                                                    <div class="d-inline-flex align-items-center gap-2">
                                                        <!-- Enable / Disable Switch -->
                                                        <div class="form-check form-switch m-0" title="{{ $q->is_active ? 'Active (Click to Disable)' : 'Disabled (Click to Enable)' }}">
                                                            <input class="form-check-input question-toggle-btn" 
                                                                   type="checkbox" 
                                                                   role="switch"
                                                                   id="q_modal_toggle_{{ $q->id }}"
                                                                   data-id="{{ $q->id }}"
                                                                   data-url="{{ route('admin.questions.toggle_status', $q->id) }}"
                                                                   {{ $q->is_active ? 'checked' : '' }}
                                                                   style="cursor: pointer; width: 2.2em; height: 1.15em;">
                                                        </div>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="{{ route('admin.questions.edit', ['question' => $q->id, 'return_to' => 'section_modal', 'section_id' => $sec->id]) }}" class="btn btn-light border" title="Edit Question">
                                                                <i class="bi bi-pencil text-primary"></i>
                                                            </a>
                                                            <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="return_to" value="section_modal">
                                                                <input type="hidden" name="section_id" value="{{ $sec->id }}">
                                                                <button type="submit" class="btn btn-light border text-danger" title="Delete Question">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="d-inline-flex align-items-center gap-2">
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
                    @endif
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3 d-flex justify-content-between">
                    <div class="small text-muted">
                        Showing <strong>{{ $sec->questions->count() }}</strong> question(s) in this section
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    @if($canManage)
        <div class="modal fade" id="editSectionModal_{{ $sec->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content rounded-4 border-0 shadow">
                    <form action="{{ route('admin.sections.update', $sec->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Section</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Target Category <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-select" required>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $sec->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Section Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title', $sec->title) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ old('description', $sec->description) }}</textarea>
                            </div>

                            @if(auth()->check() && auth()->user()->isSuperAdmin())
                                @php
                                    $secUniIds = $sec->universities->pluck('id')->toArray();
                                    if ($sec->university_id && !in_array($sec->university_id, $secUniIds)) {
                                        $secUniIds[] = $sec->university_id;
                                    }
                                    $secScopeType = (!empty($secUniIds) || $sec->university_id) ? 'specific' : 'global';
                                @endphp
                                <div class="mb-3 p-3 bg-light rounded-3 border">
                                    <label class="form-label fw-bold text-dark mb-2">Target Institution Scope</label>
                                    <div class="d-flex gap-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="scope_type" id="sec_scope_g_{{ $sec->id }}" value="global" 
                                                   {{ $secScopeType === 'global' ? 'checked' : '' }}
                                                   onchange="toggleSecScope('{{ $sec->id }}', this.value)">
                                            <label class="form-check-label small fw-semibold cursor-pointer" for="sec_scope_g_{{ $sec->id }}">Common / All</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="scope_type" id="sec_scope_s_{{ $sec->id }}" value="specific" 
                                                   {{ $secScopeType === 'specific' ? 'checked' : '' }}
                                                   onchange="toggleSecScope('{{ $sec->id }}', this.value)">
                                            <label class="form-check-label small fw-semibold cursor-pointer" for="sec_scope_s_{{ $sec->id }}">Specific Institution(s)</label>
                                        </div>
                                    </div>

                                    <div id="sec_unis_container_{{ $sec->id }}" class="{{ $secScopeType === 'specific' ? '' : 'd-none' }}">
                                        <!-- Affiliating University Quick Selector -->
                                        @php
                                            $affiliatingParents = $universities->where('type', 'university')->where('colleges_count', '>', 0)->sortBy('name');
                                        @endphp
                                        @if($affiliatingParents->isNotEmpty())
                                            <div class="p-2 mb-2 bg-white rounded-3 border">
                                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-1 mb-1">
                                                    <label for="parent_uni_quick_select_{{ $sec->id }}" class="form-label mb-0 small fw-bold text-primary d-flex align-items-center gap-1" style="font-size: 0.75rem;">
                                                        <i class="bi bi-diagram-3-fill"></i> Select by Affiliating University:
                                                    </label>
                                                    <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.65rem;">
                                                        {{ $affiliatingParents->count() }} Affiliating Available
                                                    </span>
                                                </div>
                                                <div class="row g-1 align-items-center">
                                                    <div class="col-sm-7">
                                                        <select id="parent_uni_quick_select_{{ $sec->id }}" class="form-select form-select-sm" style="font-size: 0.75rem;">
                                                            <option value="">-- Choose Affiliating Univ... --</option>
                                                            @foreach($affiliatingParents as $pu)
                                                                <option value="{{ $pu->id }}" data-count="{{ $pu->colleges_count }}" data-name="{{ $pu->short_name ?: $pu->name }}">
                                                                    {{ $pu->name }} ({{ $pu->colleges_count }} Colleges)
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-5 d-flex gap-1">
                                                        <button type="button" class="btn btn-sm btn-primary py-1 px-2 rounded-pill flex-grow-1" onclick="applySecAffiliatingUniSelect('{{ $sec->id }}', true)" style="font-size: 0.72rem;">
                                                            <i class="bi bi-check2-circle me-1"></i> Auto-Select All
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-1.5 rounded-pill" onclick="applySecAffiliatingUniSelect('{{ $sec->id }}', false)" style="font-size: 0.72rem;" title="Deselect colleges under this university">
                                                            <i class="bi bi-dash-circle"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Feedback Alert -->
                                        <div id="sec_feedback_msg_{{ $sec->id }}" class="alert alert-info alert-dismissible py-1 px-2 mb-2 small d-none align-items-center justify-content-between" style="font-size: 0.75rem;" role="alert">
                                            <span id="sec_feedback_text_{{ $sec->id }}"></span>
                                            <button type="button" class="btn-close py-1" style="font-size: 0.65rem;" onclick="this.parentElement.classList.add('d-none')" aria-label="Close"></button>
                                        </div>

                                        <!-- Header with select all and counts -->
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-2 pt-1 border-top">
                                            <small class="text-muted" style="font-size: 0.75rem;" id="sec_uni_filter_count_{{ $sec->id }}">Showing all {{ count($universities) }} institutions</small>
                                            <div class="d-flex gap-1 align-items-center">
                                                <small class="text-muted fw-semibold text-primary me-1" style="font-size: 0.75rem;" id="sec_uni_count_{{ $sec->id }}">{{ count($secUniIds) }} selected</small>
                                                <button type="button" class="btn btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('{{ $sec->id }}', true)">Select All</button>
                                                <button type="button" class="btn btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('{{ $sec->id }}', false)">Deselect All</button>
                                            </div>
                                        </div>

                                        <!-- Search input -->
                                        <div class="input-group input-group-sm mb-2">
                                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                            <input type="text" id="sec_uni_search_{{ $sec->id }}" class="form-control border-start-0" placeholder="Search by name, affiliating parent, code..." oninput="filterSecUnis('{{ $sec->id }}')">
                                            <button class="btn btn-outline-secondary" type="button" onclick="clearSecUniSearch('{{ $sec->id }}')"><i class="bi bi-x-lg"></i></button>
                                        </div>

                                        <div class="row g-2 p-2 bg-white rounded-3 border overflow-auto" id="sec_uni_list_{{ $sec->id }}" style="max-height: 200px;">
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
                                                               id="sec_uni_{{ $sec->id }}_{{ $u->id }}"
                                                               data-is-parent="{{ ($u->colleges_count > 0) ? '1' : '0' }}"
                                                               data-parent-id="{{ $u->parent_id ?? '' }}"
                                                               data-name="{{ $u->short_name ?: $u->name }}"
                                                               data-colleges-count="{{ $u->colleges_count }}"
                                                               {{ in_array($u->id, $secUniIds) ? 'checked' : '' }} 
                                                               onchange="handleSecUniCheckboxChange('{{ $sec->id }}', this)">
                                                        <label class="form-check-label small w-100 cursor-pointer" for="sec_uni_{{ $sec->id }}_{{ $u->id }}">
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
                                        <div id="sec_no_match_{{ $sec->id }}" class="text-center py-2 text-muted small d-none">
                                            <i class="bi bi-search me-1"></i> No matching institutions found.
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer border-top px-4 py-3">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-check-circle me-1"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Section Modal -->
        <div class="modal fade" id="deleteSectionModal_{{ $sec->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-body p-4 text-center">
                        <div class="rounded-circle bg-danger-subtle text-danger d-inline-flex align-items-center justify-content-center p-3 mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Delete Section?</h5>
                        <p class="text-secondary small mb-4">
                            Are you sure you want to delete section <strong>"{{ $sec->title }}"</strong>?<br>
                            This action cannot be undone. Questions attached to this section will remain in the bank.
                        </p>
                        <form action="{{ route('admin.sections.destroy', $sec->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger px-4 fw-bold"><i class="bi bi-trash me-1"></i> Yes, Delete Section</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- Modal: Create New Section -->
<div class="modal fade" id="createSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.sections.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-folder-plus text-primary me-2"></i> Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose Target Category <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Section Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Profile & Academic Background" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Optional guidelines or section scope"></textarea>
                    </div>

                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <div class="mb-3 p-3 bg-light rounded-3 border">
                            <label class="form-label fw-bold text-dark mb-2">Target Institution Scope</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="scope_type" id="new_sec_scope_g" value="global" checked onchange="toggleSecScope('new', this.value)">
                                    <label class="form-check-label small fw-semibold cursor-pointer" for="new_sec_scope_g">Common / All</label>
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

    document.addEventListener("DOMContentLoaded", function() {
        @if(request('open_section_modal'))
            const openSecId = '{{ request('open_section_modal') }}';
            const targetModalEl = document.getElementById('sectionQuestionsModal_' + openSecId);
            if (targetModalEl) {
                const myModal = new bootstrap.Modal(targetModalEl);
                myModal.show();
            }
        @endif

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
    });
</script>
@endpush
