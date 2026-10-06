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
                                <!-- Header with select all -->
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-1 mb-2 pt-1 border-top">
                                    <small class="text-muted" style="font-size: 0.75rem;" id="sec_uni_count_new">0 selected</small>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-outline-primary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('new', true)">Select All</button>
                                        <button type="button" class="btn btn-outline-secondary py-0 px-2 rounded-pill" style="font-size: 0.72rem;" onclick="selectAllSecUnis('new', false)">Deselect All</button>
                                    </div>
                                </div>

                                <!-- Search input -->
                                <div class="input-group input-group-sm mb-2">
                                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                                    <input type="text" id="sec_uni_search_new" class="form-control border-start-0" placeholder="Search institution by name, code..." oninput="filterSecUnis('new')">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearSecUniSearch('new')"><i class="bi bi-x-lg"></i></button>
                                </div>

                                <div class="row g-2 p-2 bg-white rounded-3 border overflow-auto" id="sec_uni_list_new" style="max-height: 180px;">
                                    @foreach($universities as $u)
                                        @php
                                            $typeLabel = '';
                                            if($u->type === 'university') $typeLabel = 'University';
                                            elseif($u->type === 'ini') $typeLabel = 'INI';
                                            elseif($u->type === 'affiliated_college') $typeLabel = 'Affiliated College';
                                            elseif($u->type === 'polytechnic_iti') $typeLabel = 'Polytechnic/ITI';
                                        @endphp
                                        <div class="col-md-6 sec-uni-item" data-search-text="{{ strtolower($u->name . ' ' . $u->short_name . ' ' . $typeLabel) }}">
                                            <div class="form-check p-1 rounded hover-bg-light border mb-0 h-100 d-flex align-items-center">
                                                <input class="form-check-input ms-0 me-2 sec-uni-checkbox" type="checkbox" name="university_ids[]" value="{{ $u->id }}" id="new_sec_uni_{{ $u->id }}" onchange="updateSecSelectedCount('new')">
                                                <label class="form-check-label small w-100 cursor-pointer" for="new_sec_uni_{{ $u->id }}">
                                                    <strong class="text-dark">{{ $u->short_name }}</strong> — <span class="text-secondary">{{ Str::limit($u->name, 30) }}</span>
                                                    @if($typeLabel)
                                                        <span class="badge bg-light text-secondary border ms-1" style="font-size: 0.6rem;">{{ $typeLabel }}</span>
                                                    @endif
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div id="sec_no_match_new" class="text-center py-2 text-muted small d-none">
                                    No matching institutions found.
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
});
</script>
@endpush
