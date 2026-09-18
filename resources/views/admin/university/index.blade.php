@extends('layouts.admin')

@section('title', 'Institution Directory & Enrollment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Higher Education Institutions & Colleges</h4>
        <p class="text-secondary small mb-0">Enroll and manage Universities, Affiliated Colleges, and Autonomous Colleges.</p>
    </div>
    <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#newInstitutionModal">
        <i class="bi bi-building-add me-1"></i> Enroll New Institution
    </button>
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Institution Name</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Affiliated Parent University</th>
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
                            @if($inst->type === 'university')
                                <span class="badge bg-primary-subtle text-primary border"><i class="bi bi-bank me-1"></i> UNIVERSITY</span>
                            @elseif($inst->type === 'autonomous_college')
                                <span class="badge bg-purple-subtle text-purple border" style="background:#f3e8ff; color:#7e22ce;"><i class="bi bi-award me-1"></i> AUTONOMOUS COLLEGE</span>
                            @else
                                <span class="badge bg-info-subtle text-info border"><i class="bi bi-diagram-3 me-1"></i> AFFILIATED COLLEGE</span>
                            @endif
                        </td>
                        <td>
                            @if($inst->type === 'affiliated_college' && $inst->parent)
                                <small class="fw-semibold text-secondary"><i class="bi bi-link-45deg me-1"></i> {{ $inst->parent->name }}</small>
                            @elseif($inst->type === 'autonomous_college')
                                <small class="text-muted">Independent / Non-Dependent</small>
                            @else
                                <small class="text-muted">Parent University ({{ $inst->colleges->count() }} Affiliated Colleges)</small>
                            @endif
                        </td>
                        <td>
                            <small class="d-block">{{ $inst->email ?? 'No email' }}</small>
                            <small class="text-muted">{{ $inst->phone ?? '' }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.university.edit', $inst->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-pencil me-1"></i> Branding & Edit
                            </a>
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
        <div class="modal-content">
            <form action="{{ route('admin.university.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Enroll Higher Education Institution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Institution Full Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., State Technical University or St. Xavier Autonomous College" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Code / Short Name</label>
                            <input type="text" name="short_name" class="form-control" placeholder="e.g., STU or SXAC" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Institution Classification</label>
                            <select name="type" id="institution_type_select" class="form-select" required onchange="toggleParentUniField(this.value)">
                                <option value="university">State / Central University</option>
                                <option value="autonomous_college">Autonomous College (Independent / Non-Dependent)</option>
                                <option value="affiliated_college">Affiliated College (Under University)</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="parent_uni_container" style="display: none;">
                            <label class="form-label fw-semibold">Parent University</label>
                            <select name="parent_id" class="form-select">
                                <option value="">-- Select Parent University --</option>
                                @foreach($parentUniversities as $pUni)
                                    <option value="{{ $pUni->id }}">{{ $pUni->name }} ({{ $pUni->short_name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Tagline / Mission Statement</label>
                            <input type="text" name="tagline" class="form-control" placeholder="e.g., Excellence in Science, Commerce & Technological Research">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Official Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact Phone</label>
                            <input type="text" name="phone" class="form-control">
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
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Enroll Institution</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleParentUniField(type) {
    const parentContainer = document.getElementById('parent_uni_container');
    if (type === 'affiliated_college') {
        parentContainer.style.display = 'block';
    } else {
        parentContainer.style.display = 'none';
    }
}
</script>
@endsection
