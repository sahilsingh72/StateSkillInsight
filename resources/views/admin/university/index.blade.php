@extends('layouts.admin')

@section('title', 'Institution Directory & Enrollment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Higher Education Institutions & Colleges Directory</h4>
        <p class="text-secondary small mb-0">Enroll and manage IITs/NITs, Central/State Universities, Autonomous Colleges, Affiliated Colleges, and Polytechnics/ITIs.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#newInstitutionModal" onclick="prepareEnrollModal('university')">
            <i class="bi bi-building-add me-1"></i> Enroll New Institution
        </button>
        <button class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkImportModal">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Bulk Enroll
        </button>
    </div>
</div>

<!-- Filter & Search Controls Bar -->
<div class="card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.university.index') }}" id="institutionFilterForm">
        <input type="hidden" name="per_page" id="perPageInput" value="{{ request('per_page', 10) }}">
        <div class="row g-2 align-items-center">
            <!-- Keyword Search -->
            <div class="col-md-4">
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
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold mb-1"><i class="bi bi-diagram-3 me-1"></i> Affiliating University</label>
                <select name="parent_id" class="form-select form-select-sm" onchange="document.getElementById('institutionFilterForm').submit()">
                    <option value="">All Affiliating University</option>
                    @foreach($parentUniversities as $pUni)
                        <option value="{{ $pUni->id }}" {{ request('parent_id') == $pUni->id ? 'selected' : '' }}>{{ $pUni->short_name }} - {{ Str::limit($pUni->name, 22) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Sort By -->
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold mb-1"><i class="bi bi-sort-down me-1"></i> Sort By</label>
                <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('institutionFilterForm').submit()">
                    <option value="name_asc" {{ request('sort', 'name_asc') === 'name_asc' ? 'selected' : '' }}>Name (A to Z)</option>
                    <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Name (Z to A)</option>
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Latest Enrolled</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest Enrolled</option>
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
                @if(request()->anyFilled(['search', 'type', 'parent_id', 'sort']))
                    <span class="badge bg-primary-subtle text-primary border px-2 py-1 small">
                        <i class="bi bi-funnel-fill me-1"></i> Filters Active
                    </span>
                @endif
            </div>

            <div class="d-flex gap-2">
                @if(request()->anyFilled(['search', 'type', 'parent_id', 'sort']))
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
                    <th style="width: 50px;">Sno</th>
                    <th>Institution Name</th>
                    <th>Code</th>
                    <th>Classification Type</th>
                    <th>Hierarchy / Relationship</th>
                    <th>Contact Info</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($institutions as $count => $inst)
                    <tr id="institution-row-{{ $inst->id }}">
                        <td class="text-muted fw-semibold">{{ ($institutions->firstItem() ?? 1) + $count }}</td>
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
                                    <a href="javascript:void(0)" 
                                       class="fw-semibold text-primary text-decoration-none d-block text-start small"
                                       onclick="showAffiliatedColleges({{ $inst->parent->id }}, '{{ addslashes($inst->parent->name) }}', '{{ addslashes($inst->parent->short_name) }}')"
                                       title="Click to view all colleges affiliated under {{ $inst->parent->name }}">
                                        <i class="bi bi-link-45deg me-1"></i>Affiliated under: {{ $inst->parent->name }}
                                    </a>
                                @else
                                    <small class="text-warning">Affiliated College (Affiliating Uni Pending)</small>
                                @endif
                            @elseif($inst->type === 'university')
                                <small class="text-muted fw-semibold d-block">Affiliating University</small>
                                @if($inst->colleges->count() > 0)
                                    <a href="javascript:void(0)" 
                                       class="fw-semibold text-primary text-decoration-none d-block text-start mt-1 small" 
                                       onclick="showAffiliatedColleges({{ $inst->id }}, '{{ addslashes($inst->name) }}', '{{ addslashes($inst->short_name) }}')"
                                       title="Click to view affiliated colleges under {{ $inst->name }}">
                                        <i class="bi bi-diagram-2 me-1"></i>{{ $inst->colleges->count() }} Affiliated {{ Str::plural('College', $inst->colleges->count()) }} Working Under
                                    </a>
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
                                        <i class="bi bi-plus-circle me-1"></i> Add College Under University
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
    <!-- Pagination & Entries Summary Footer -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
        <div class="d-flex align-items-center gap-3">
            <span class="text-muted small">
                Showing <strong class="text-dark">{{ $institutions->firstItem() ?? 0 }}</strong> to <strong class="text-dark">{{ $institutions->lastItem() ?? 0 }}</strong> of <strong class="text-dark">{{ $institutions->total() }}</strong> institutions
            </span>
            <div class="d-flex align-items-center gap-1">
                <label class="text-muted small mb-0 text-nowrap">Per page:</label>
                <select class="form-select form-select-sm" style="width: 75px;" onchange="document.getElementById('perPageInput').value = this.value; document.getElementById('institutionFilterForm').submit();">
                    <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page', 10) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </div>
        <div>
            {{ $institutions->links() }}
        </div>
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
                                <option value="affiliated_college">Affiliated College (Works Under Affiliating University)</option>
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
                                <label class="form-label fw-bold text-dark mb-1"><i class="bi bi-diagram-3 me-1 text-primary"></i> Affiliating University<span class="text-danger">*</span></label>
                                <select name="parent_id" id="parent_id_select" class="form-select border-primary">
                                    <option value="">-- Select Existing Affiliating University --</option>
                                    @foreach($parentUniversities as $pUni)
                                        <option value="{{ $pUni->id }}">{{ $pUni->name }} ({{ $pUni->short_name }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-2">
                                    This college will be registered as an affiliated college working under the selected Affiliating University.
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
                            <input type="text" name="state" class="form-control" value="Odisha">
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

<!-- Modal: Bulk Import Institutions -->
<div class="modal fade" id="bulkImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.university.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark">
                        <i class="bi bi-file-earmark-spreadsheet text-primary me-2"></i> Bulk Import Institutions
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-info-subtle d-flex align-items-start gap-3 mb-4">
                        <i class="bi bi-info-circle-fill fs-4 text-info flex-shrink-0"></i>
                        <div class="small">
                            <strong class="d-block mb-1 text-dark">Instructions for Bulk Import:</strong>
                            <ul class="mb-2 ps-3">
                                <li>Upload a <code>.xlsx</code> file containing the list of institutes to enroll in bulk.</li>
                                <li>Required columns: <strong>Name</strong>, <strong>Short Name</strong>.</li>
                                <li>In the <code>Type</code> column, choose any of the 5 exact classification options:
                                    <ol class="mt-1 mb-1 ps-3">
                                        <li><code>Institute of National Importance (IIT / NIT / IIM / AIIMS)</code></li>
                                        <li><code>Central / State University</code></li>
                                        <li><code>Autonomous College (Independent Academic Autonomy)</code></li>
                                        <li><code>Affiliated College (Works Under Affiliating University)</code></li>
                                        <li><code>Polytechnic & ITI (Technical / Skill Institute)</code></li>
                                    </ol>
                                </li>
                                <li>For affiliated colleges, specify <code>Affiliated Under (Short Name)</code> (e.g. <code>UU</code>, <code>BPUT</code>) to automatically link to the Affiliating university.</li>
                            </ul>
                            <a href="{{ route('admin.university.sample_csv') }}" class="btn btn-sm btn-outline-info fw-bold text-decoration-none">
                                <i class="bi bi-download me-1"></i> Download Pre-Formatted Excel Template (.xlsx)
                            </a>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Choose Spreadsheet / xlsx File to Upload <span class="text-danger">*</span></label>
                        <input type="file" name="import_file" class="form-control form-control-lg border-primary" accept=".xlsx, .xls, .csv, .txt" required>
                        <small class="text-muted d-block mt-1">Accepted formats: .xlsx(Max file size: 10MB)</small>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-table me-1 text-secondary"></i> Expected Header & Data Format:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered bg-white text-nowrap small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name *</th>
                                        <th>Short Name *</th>
                                        <th>Type</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Website</th>
                                        <th>State</th>
                                        <th>Affiliated Under (Short Name)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>IIT Bhubaneswar</td>
                                        <td>IIT BBS</td>
                                        <td>Institute of National Importance (IIT / NIT / IIM / AIIMS)</td>
                                        <td>contact@iitbbs.ac.in</td>
                                        <td>06742576000</td>
                                        <td>https://iitbbs.ac.in</td>
                                        <td>Odisha</td>
                                        <td><em>(leave blank)</em></td>
                                    </tr>
                                    <tr>
                                        <td>Utkal University</td>
                                        <td>UU</td>
                                        <td>Central / State University</td>
                                        <td>info@utkaluniversity.ac.in</td>
                                        <td>06742567382</td>
                                        <td>https://utkaluniversity.ac.in</td>
                                        <td>Odisha</td>
                                        <td><em>(leave blank)</em></td>
                                    </tr>
                                    <tr>
                                        <td>Bhubaneswar Inst. of Tech.</td>
                                        <td>BIT</td>
                                        <td>Affiliated College (Works Under Parent University)</td>
                                        <td>info@bit.edu.in</td>
                                        <td>06742500111</td>
                                        <td>https://bit.edu.in</td>
                                        <td>Odisha</td>
                                        <td>UU</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-upload me-1"></i> Upload & Import Institutions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: View Affiliated Colleges Working Under University -->
<div class="modal fade" id="affiliatedCollegesModal" tabindex="-1" aria-labelledby="affiliatedCollegesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom bg-light py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-diagram-3-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-dark mb-0" id="affiliatedCollegesModalLabel">
                                Affiliated Colleges Under <span id="affUniName" class="text-primary"></span>
                            </h5>
                            <span class="badge bg-primary text-white" id="affUniCode"></span>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i> Colleges and academic institutions officially affiliated & operating under this parent university &bull; 
                            <span id="affCollegesCountBadge" class="fw-semibold text-dark"></span>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle border mb-0" id="affiliatedCollegesTable">
                        <thead class="table-light">
                            <tr class="small text-uppercase text-secondary">
                                <th style="width: 45px;">Sno</th>
                                <th>College / Institution Name</th>
                                <th>Code</th>
                                <th>Contact Information</th>
                                <th>Location</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="affiliatedCollegesTableBody">
                            <!-- Populated dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div id="noAffiliatedCollegesAlert" class="text-center py-5 text-muted" style="display: none;">
                    <i class="bi bi-diagram-3 fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    <h6 class="fw-bold">No Affiliated Colleges Found</h6>
                    <p class="small text-secondary mb-0">No colleges are currently registered under this parent university.</p>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-3 px-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary-custom px-3" id="modalAddCollegeBtn">
                    <i class="bi bi-plus-circle me-1"></i> Enroll Affiliated College Under This University
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const affiliatedCollegesData = {
    @foreach($parentUniversities as $pUni)
        "{{ $pUni->id }}": @json($pUni->colleges),
    @endforeach
};

let currentAffUniId = null;
let currentAffUniName = '';

function showAffiliatedColleges(uniId, uniName, uniCode) {
    currentAffUniId = uniId;
    currentAffUniName = uniName;

    document.getElementById('affUniName').innerText = uniName;
    document.getElementById('affUniCode').innerText = uniCode;

    const colleges = (affiliatedCollegesData[uniId] || []).slice();
    colleges.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    const countBadge = document.getElementById('affCollegesCountBadge');
    countBadge.innerText = `${colleges.length} ${colleges.length === 1 ? 'College' : 'Colleges'} Found`;

    const tbody = document.getElementById('affiliatedCollegesTableBody');
    const tableEl = document.getElementById('affiliatedCollegesTable');
    const noAlert = document.getElementById('noAffiliatedCollegesAlert');
    tbody.innerHTML = '';

    if (!colleges || colleges.length === 0) {
        tableEl.style.display = 'none';
        noAlert.style.display = 'block';
    } else {
        tableEl.style.display = '';
        noAlert.style.display = 'none';

        colleges.forEach((c, index) => {
            const tr = document.createElement('tr');
            const editUrl = "{{ url('/university') }}/" + c.id;

            const contactDetails = [];
            if (c.email) {
                contactDetails.push(`<div><i class="bi bi-envelope me-1 text-muted"></i><a href="mailto:${c.email}" class="text-decoration-none">${c.email}</a></div>`);
            }
            if (c.phone) {
                contactDetails.push(`<div><i class="bi bi-telephone me-1 text-muted"></i>${c.phone}</div>`);
            }
            if (c.website) {
                contactDetails.push(`<div><i class="bi bi-globe me-1 text-muted"></i><a href="${c.website}" target="_blank" class="text-decoration-none text-truncate d-inline-block" style="max-width: 220px;">${c.website}</a></div>`);
            }
            const contactHtml = contactDetails.length > 0 ? contactDetails.join('') : '<span class="text-muted small">Not provided</span>';

            const locationDetails = [];
            if (c.address) {
                locationDetails.push(`<div class="small text-truncate" style="max-width: 200px;" title="${c.address}"><i class="bi bi-geo-alt me-1 text-muted"></i>${c.address}</div>`);
            }
            if (c.state) {
                locationDetails.push(`<span class="badge bg-light text-secondary border small mt-1">${c.state}</span>`);
            }
            const locationHtml = locationDetails.length > 0 ? locationDetails.join('') : '<span class="text-muted small">Not specified</span>';

            const statusBadge = c.is_active ? 
                '<span class="badge bg-success-subtle text-success border"><i class="bi bi-check-circle me-1"></i> Active</span>' :
                '<span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-pause-circle me-1"></i> Inactive</span>';

            tr.innerHTML = `
                <td class="text-muted small fw-semibold">${index + 1}</td>
                <td>
                    <div class="fw-bold text-dark">${c.name}</div>
                    ${c.tagline ? `<small class="text-muted">${c.tagline}</small>` : ''}
                </td>
                <td><span class="badge bg-light text-dark border fw-semibold">${c.short_name || 'N/A'}</span></td>
                <td><div class="small">${contactHtml}</div></td>
                <td>${locationHtml}</td>
                <td class="text-end">
                    <a href="${editUrl}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Bind Add College button in modal footer
    document.getElementById('modalAddCollegeBtn').onclick = function() {
        const affModal = bootstrap.Modal.getInstance(document.getElementById('affiliatedCollegesModal'));
        if (affModal) {
            affModal.hide();
        }
        addCollegeUnderUni(currentAffUniId, currentAffUniName);
    };

    const modal = new bootstrap.Modal(document.getElementById('affiliatedCollegesModal'));
    modal.show();
}

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
