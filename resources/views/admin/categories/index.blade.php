@extends('layouts.admin')

@section('title', 'Category Engine Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Database-Driven Category Engine</h4>
        <p class="text-secondary small mb-0">Define, edit, and maintain master research categories dynamically.</p>
    </div>
    <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-folder-plus me-1"></i> Create New Category
    </button>
</div>

<div class="row g-4">
    @foreach($categories as $cat)
        <div class="col-md-6">
            <div class="card-custom p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle p-2 bg-light text-primary">
                            <i class="bi {{ $cat->icon ?? 'bi-person-badge' }} fs-5"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0">{{ $cat->name }}</h6>
                            <span class="badge bg-light text-secondary border">{{ $cat->code }}</span>
                        </div>
                    </div>
                    <span class="badge bg-success">Active</span>
                </div>
                <p class="small text-secondary mb-3">{{ $cat->description }}</p>
                <div class="small text-muted mb-3"><strong>Eligibility:</strong> {{ $cat->eligibility }}</div>
                <div class="d-flex justify-content-end">
                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCatModal_{{ $cat->id }}"><i class="bi bi-pencil me-1"></i> Edit Category</button>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editCatModal_{{ $cat->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.categories.update', $cat->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit {{ $cat->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Code</label>
                                <input type="text" name="code" class="form-control" value="{{ $cat->code }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ $cat->description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Eligibility Statement</label>
                                <input type="text" name="eligibility" class="form-control" value="{{ $cat->eligibility }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Icon Identifier</label>
                                <select name="icon" class="form-select">
                                    <option value="bi-briefcase" {{ ($cat->icon ?? '') == 'bi-briefcase' ? 'selected' : '' }}>bi-briefcase (Working)</option>
                                    <option value="bi-person-vcard" {{ ($cat->icon ?? '') == 'bi-person-vcard' || ($cat->icon ?? '') == 'bi-person-search' ? 'selected' : '' }}>bi-person-vcard (Job-Seeking / Resume)</option>
                                    <option value="bi-person-workspace" {{ ($cat->icon ?? '') == 'bi-person-workspace' ? 'selected' : '' }}>bi-person-workspace (Workplace)</option>
                                    <option value="bi-mortarboard" {{ ($cat->icon ?? '') == 'bi-mortarboard' ? 'selected' : '' }}>bi-mortarboard (Current Students)</option>
                                    <option value="bi-arrow-counterclockwise" {{ ($cat->icon ?? '') == 'bi-arrow-counterclockwise' ? 'selected' : '' }}>bi-arrow-counterclockwise (Interrupted)</option>
                                    <option value="bi-person-badge" {{ ($cat->icon ?? '') == 'bi-person-badge' ? 'selected' : '' }}>bi-person-badge (General)</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary-custom">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Modal: Create New Category in Engine -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus text-primary me-2"></i> Create New Research Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Working Alumni or Technical Graduates" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Code (Optional)</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. cat_working_alumni (Auto-generated if blank)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Target audience focus or research intent"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Eligibility Statement</label>
                        <input type="text" name="eligibility" class="form-control" placeholder="e.g. Alumni employed in industry for 1+ years">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Icon Identifier</label>
                        <select name="icon" class="form-select">
                            <option value="bi-briefcase">bi-briefcase (Working)</option>
                            <option value="bi-person-vcard">bi-person-vcard (Job-Seeking / Resume)</option>
                            <option value="bi-person-workspace">bi-person-workspace (Workplace)</option>
                            <option value="bi-mortarboard">bi-mortarboard (Current Students)</option>
                            <option value="bi-arrow-counterclockwise">bi-arrow-counterclockwise (Interrupted)</option>
                            <option value="bi-person-badge">bi-person-badge (General)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom"><i class="bi bi-check-circle me-1"></i> Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
