@extends('layouts.admin')

@section('title', 'Institution Directory & Enrollment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Higher Education Institutions & Colleges Directory</h4>
        <p class="text-secondary small mb-0">Enroll and manage IITs/NITs, Central/State Universities, Autonomous Colleges, Affiliated Colleges, and Polytechnics/ITIs.</p>
    </div>
    <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#newInstitutionModal" onclick="prepareEnrollModal('university')">
        <i class="bi bi-building-add me-1"></i> Enroll New Institution
    </button>
</div>

<!-- Filter & Search Controls Bar -->
<div class="card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.university.index') }}" id="institutionFilterForm">
        <div class="row g-2 align-items-center">
            <!-- Keyword Search -->
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1"><i class="bi bi-search me-1"></i> Search Institution</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-building"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Name, code, or email..." value="{{ request('search') }}">
                </div>
            </div>

            <!-- Institution Type / Classification Filter -->
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1"><i class="bi bi-funnel me-1"></i> Institution Type</label>
                <select name="type" class="form-select form-select-sm" onchange="document.getElementById('institutionFilterForm').submit()">
                    <option value="">All Classification Types</option>
                    <option value="ini" {{ request('type') === 'ini' ? 'selected' : '' }}>IIT / NIT / IIM / AIIMS (INI)</option>
                    <option value="university" {{ request('type') === 'university' ? 'selected' : '' }}>Central / State University</option>
                    <option value="autonomous_college" {{ request('type') === 'autonomous_college' ? 'selected' : '' }}>Autonomous College</option>
                    <option value="affiliated_college" {{ request('type') === 'affiliated_college' ? 'selected' : '' }}>Affiliated College</option>
                    <option value="polytechnic_iti" {{ request('type') === 'polytechnic_iti' ? 'selected' : '' }}>Polytechnic & ITI</option>
                </select>
            </div>

            <!-- Parent University Filter -->
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1"><i class="bi bi-diagram-3 me-1"></i> Parent University</label>
                <select name="parent_id" class="form-select form-select-sm" onchange="document.getElementById('institutionFilterForm').submit()">
                    <option value="">All Parent Unis</option>
                    @foreach($parentUniversities as $pUni)
                        <option value="{{ $pUni->id }}" {{ request('parent_id') == $pUni->id ? 'selected' : '' }}>{{ $pUni->short_name }} - {{ Str::limit($pUni->name, 22) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- State Filter -->
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1"><i class="bi bi-geo-alt me-1"></i> State / Region</label>
                <select name="state" class="form-select form-select-sm" onchange="document.getElementById('institutionFilterForm').submit()">
                    <option value="">All States</option>
                    @foreach($states as $st)
                        <option value="{{ $st }}" {{ request('state') === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1"><i class="bi bi-sort-down me-1"></i> Sort By</label>
                <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('institutionFilterForm').submit()">
                    <option value="latest" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Latest Enrolled</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest Enrolled</option>
                    <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name (A to Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z to A)</option>
                    <option value="type" {{ request('sort') === 'type' ? 'selected' : '' }}>Classification Type</option>
                </select>
            </div>
        </div>

        <!-- Action Row: Apply, Reset, Active Badges -->
        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border px-2 py-1 small">
                    <i class="bi bi-building me-1"></i> {{ $institutions->total() }} {{ Str::plural('Institution', $institutions->total()) }} Found
                </span>
                @if(request()->anyFilled(['search', 'type', 'parent_id', 'state', 'sort']))
                    <span class="badge bg-primary-subtle text-primary border px-2 py-1 small">
                        <i class="bi bi-funnel-fill me-1"></i> Filters Active
                    </span>
                @endif
            </div>

            <div class="d-flex gap-2">
                @if(request()->anyFilled(['search', 'type', 'parent_id', 'state', 'sort']))
                    <a href="{{ route('admin.university.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i> Reset Filters
                    </a>
                @endif
                <button type="submit" class="btn btn-sm btn-primary-custom px-3">
                    <i class="bi bi-filter me-1"></i> Apply Filters
                </button>
            </div>
        </div>
    </form>
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Institution Name</th>
                    <th>Code</th>
                    <th>Classification Type</th>
                    <th>Hierarchy / Relationship</th>
                    <th>Contact Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($institutions as $inst)
                    <tr>
                        <td>
                            <strong>{{ $inst->name }}</strong>
                            @if($inst->tagline)<br><small class="text-muted">{{ $inst->tagline }}</small>@endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $inst->short_name }}</span></td>
                        <td>
                            @if($inst->type === 'ini')
                                <span class="badge bg-danger text-white border"><i class="bi bi-award-fill me-1"></i> IIT / NIT / IIM (INI)</span>
                            @elseif($inst->type === 'university')
                                <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-bank me-1"></i> CENTRAL / STATE UNIVERSITY</span>
                            @elseif($inst->type === 'autonomous_college')
                                <span class="badge bg-purple-subtle text-purple border" style="background:#f3e8ff; color:#7e22ce;"><i class="bi bi-shield-check me-1"></i> AUTONOMOUS COLLEGE</span>
                            @elseif($inst->type === 'polytechnic_iti')
                                <span class="badge bg-success-subtle text-success border"><i class="bi bi-tools me-1"></i> POLYTECHNIC & ITI</span>
                            @else
                                <span class="badge bg-info-subtle text-info border"><i class="bi bi-diagram-3 me-1"></i> AFFILIATED COLLEGE</span>
                            @endif
                        </td>
                        <td>
                            @if($inst->type === 'affiliated_college')
                                @if($inst->parent)
                                    <small class="fw-semibold text-primary d-block"><i class="bi bi-link-45deg me-1"></i> Affiliated under: {{ $inst->parent->name }}</small>
                                @else
                                    <small class="text-warning">Affiliated College (Parent Uni Pending)</small>
                                @endif
                            @elseif($inst->type === 'university')
                                <small class="text-dark fw-semibold d-block">Parent University</small>
                                @if($inst->colleges->count() > 0)
                                    <span class="badge bg-light text-secondary border mt-1">
                                        <i class="bi bi-diagram-3 me-1"></i> {{ $inst->colleges->count() }} Affiliated {{ Str::plural('College', $inst->colleges->count()) }} Working Under
                                    </span>
                                @else
                                    <small class="text-muted">No affiliated colleges enrolled yet under this university</small>
                                @endif
                            @elseif($inst->type === 'ini')
                                <small class="text-muted">Institute of National Importance (Autonomous)</small>
                            @elseif($inst->type === 'polytechnic_iti')
                                <small class="text-muted">Diploma & Vocational Skill Center</small>
                            @else
                                <small class="text-muted">Autonomous Degree Granting Institution</small>
                            @endif
                        </td>
                        <td>
                            <small class="d-block">{{ $inst->email ?? 'No email' }}</small>
                            <small class="text-muted">{{ $inst->phone ?? '' }}</small>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($inst->type === 'university')
                                    <button class="btn btn-sm btn-outline-primary" onclick="addCollegeUnderUni({{ $inst->id }}, '{{ addslashes($inst->name) }}')" title="Add Affiliated College Working Under This University">
                                        <i class="bi bi-plus-circle me-1"></i> + Add College Under Uni
                                    </button>
                                @endif
                                <a href="{{ route('admin.university.edit', $inst->id) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $institutions->links() }}
    </div>
</div>

<!-- Modal: Enroll Institution -->
<div class="modal fade" id="newInstitutionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.university.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-header-title fw-bold text-dark mb-0"><i class="bi bi-building-add text-primary me-2"></i> Enroll Higher Education Institution / College</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Institution Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="inst_name_input" class="form-control" placeholder="e.g., Indian Institute of Technology, Utkal University, or Govt Engineering College" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Code / Short Name <span class="text-danger">*</span></label>
                            <input type="text" name="short_name" id="inst_short_name_input" class="form-control" placeholder="e.g., IIT, UU, or GEC" required>
                        </div>

                        <!-- Institute Classification Type Selection -->
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Select Institution Type / Classification <span class="text-danger">*</span></label>
                            <select name="type" id="institution_type_select" class="form-select border-primary" required onchange="onTypeChange(this.value)">
                                <option value="ini">Institute of National Importance (IIT / NIT / IIM / AIIMS)</option>
                                <option value="university" selected>Central / State University</option>
                                <option value="autonomous_college">Autonomous College (Independent Academic Autonomy)</option>
                                <option value="affiliated_college">Affiliated College (Works Under Parent University)</option>
                                <option value="polytechnic_iti">Polytechnic & ITI (Technical / Vocational Skill Institute)</option>
                            </select>
                        </div>

                        <!-- Dynamic Type Guidance Note -->
                        <div class="col-md-12">
                            <div id="type_info_box" class="alert alert-info py-2 px-3 mb-0 small rounded-3">
                                <i class="bi bi-info-circle me-1"></i> <span id="type_info_text">Central & State Universities grant academic degrees and manage constituent/affiliated colleges.</span>
                            </div>
                        </div>

                        <!-- Parent University Selection (Active when Affiliated College is selected) -->
                        <div class="col-md-12" id="parent_uni_container" style="display: none;">
                            <div class="p-3 bg-light rounded-3 border border-primary-subtle">
                                <label class="form-label fw-bold text-dark mb-1"><i class="bi bi-diagram-3 me-1 text-primary"></i> Parent University (Affiliating University) <span class="text-danger">*</span></label>
                                <select name="parent_id" id="parent_id_select" class="form-select border-primary">
                                    <option value="">-- Select Existing Parent University --</option>
                                    @foreach($parentUniversities as $pUni)
                                        <option value="{{ $pUni->id }}">{{ $pUni->name }} ({{ $pUni->short_name }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-2">
                                    This college will be registered as an affiliated college working under the selected Parent University.
                                </small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Tagline / Mission Statement</label>
                            <input type="text" name="tagline" class="form-control" placeholder="e.g., Excellence in Science, Engineering & Technological Research">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Email</label>
                            <input type="email" name="email" class="form-control" placeholder="contact@institution.edu.in">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 9876543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Website URL</label>
                            <input type="url" name="website" class="form-control" placeholder="https://">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">State / Territory</label>
                            <input type="text" name="state" class="form-control" value="State Region">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom px-4"><i class="bi bi-check-circle me-1"></i> Enroll Institution</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function onTypeChange(type) {
    const parentContainer = document.getElementById('parent_uni_container');
    const parentSelect = document.getElementById('parent_id_select');
    const infoText = document.getElementById('type_info_text');

    if (type === 'affiliated_college') {
        parentContainer.style.display = 'block';
        parentSelect.required = true;
        infoText.innerText = "Affiliated Colleges operate academically under a Central or State Parent University. Select the parent university under which this college works.";
    } else {
        parentContainer.style.display = 'none';
        parentSelect.required = false;
        if (type === 'ini') {
            infoText.innerText = "Institutes of National Importance (IIT / NIT / IIM / AIIMS) are premier apex autonomous institutes established by Parliament.";
        } else if (type === 'university') {
            infoText.innerText = "Central & State Universities grant academic degrees and manage constituent/affiliated colleges working under them.";
        } else if (type === 'autonomous_college') {
            infoText.innerText = "Autonomous Colleges have academic independence to design syllabus and conduct exams independently.";
        } else if (type === 'polytechnic_iti') {
            infoText.innerText = "Polytechnic & ITI institutes offer technical diploma and practical vocational skill training.";
        }
    }
}

function prepareEnrollModal(defaultType = 'university') {
    const select = document.getElementById('institution_type_select');
    select.value = defaultType;
    onTypeChange(defaultType);
}

function addCollegeUnderUni(uniId, uniName) {
    const select = document.getElementById('institution_type_select');
    select.value = 'affiliated_college';
    onTypeChange('affiliated_college');
    const parentSelect = document.getElementById('parent_id_select');
    parentSelect.value = uniId;
    
    const modal = new bootstrap.Modal(document.getElementById('newInstitutionModal'));
    modal.show();
}
</script>
@endsection
