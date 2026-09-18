@extends('layouts.admin')

@section('title', 'Category Engine Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Database-Driven Category Engine</h4>
        <p class="text-secondary small mb-0">Add, rename, reorder, or edit research categories dynamically.</p>
    </div>
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
                <div class="small text-muted mb-2"><strong>Eligibility:</strong> {{ $cat->eligibility }}</div>
                <div class="small text-muted mb-3"><strong>Estimated Duration:</strong> {{ $cat->estimated_minutes }} mins</div>
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
                                <label class="form-label fw-semibold">Category Name</label>
                                <input type="text" name="name" class="form-control" value="{{ $cat->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="3">{{ $cat->description }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Eligibility Statement</label>
                                <input type="text" name="eligibility" class="form-control" value="{{ $cat->eligibility }}">
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
@endsection
