@extends('layouts.admin')

@section('title', 'Survey Management')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Survey Campaigns</h4>
        <p class="text-secondary small mb-0">Manage survey title, status, versioning, and structure.</p>
    </div>
    @if(auth()->check() && auth()->user()->isSuperAdmin())
        <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary-custom btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Create New Survey
        </a>
    @endif
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Survey Title</th>
                    <th>Status</th>
                    <th>Categories</th>
                    <th>Version</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($surveys as $s)
                    @php
                        $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
                        $assignedUnis = $s->universities;
                        $isGlobalSurvey = is_null($s->university_id) && $assignedUnis->isEmpty();
                        $userUniId = auth()->user()?->university_id;
                        
                        $canModify = $isSuperAdmin || ($s->university_id && $s->university_id == $userUniId) || ($assignedUnis->contains('id', $userUniId));
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">
                                @if($isGlobalSurvey)
                                    <span class="badge bg-info-subtle text-primary border me-1" style="font-size:0.7rem;" title="Available to all Universities & Colleges"><i class="bi bi-globe me-1"></i> Global (All)</span>
                                @elseif($assignedUnis->isNotEmpty())
                                    <span class="badge bg-success-subtle text-success border me-1" style="font-size:0.7rem;" title="{{ $assignedUnis->pluck('name')->join(', ') }}">
                                        <i class="bi bi-building me-1"></i> {{ $assignedUnis->count() }} Institutions ({{ $assignedUnis->pluck('short_name')->take(2)->join(', ') }}{{ $assignedUnis->count() > 2 ? '...' : '' }})
                                    </span>
                                @elseif($s->university)
                                    <span class="badge bg-primary-subtle text-primary border me-1" style="font-size:0.7rem;">
                                        <i class="bi bi-building me-1"></i> {{ $s->university->short_name }}
                                    </span>
                                @endif
                                {{ $s->title }}
                            </div>
                            <small class="text-muted">{{ Str::limit($s->description, 60) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ strtoupper($s->status) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $s->categories->count() }} Categories</span>
                        </td>
                        <td>v{{ $s->version }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @if($canModify)
                                    <a href="{{ route('admin.surveys.show', $s->id) }}" class="btn btn-light border" title="Manage Sections & Questions"><i class="bi bi-gear"></i> Manage</a>
                                    <a href="{{ route('admin.surveys.preview', $s->id) }}" class="btn btn-light border" target="_blank" title="Preview Survey"><i class="bi bi-eye"></i> Preview</a>
                                    <button type="button" class="btn btn-outline-primary" title="Edit Survey" data-bs-toggle="modal" data-bs-target="#editConfirmModal" data-survey-title="{{ $s->title }}" data-edit-url="{{ route('admin.surveys.edit', $s->id) }}">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.surveys.clone', $s->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to clone &quot;{{ addslashes($s->title) }}&quot;?');">
                                        @csrf
                                        <button type="submit" class="btn btn-light border" title="Clone Survey"><i class="bi bi-copy"></i> Clone</button>
                                    </form>
                                    <button type="button" class="btn btn-outline-danger" title="Delete Survey" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal" data-survey-title="{{ $s->title }}" data-delete-url="{{ route('admin.surveys.destroy', $s->id) }}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                @else
                                    <a href="{{ route('admin.surveys.preview', $s->id) }}" class="btn btn-light border" target="_blank" title="Preview Survey"><i class="bi bi-eye"></i> Preview</a>
                                    <span class="badge bg-light text-secondary border px-2 py-1 align-self-center ms-1" title="Created by Super Admin (Read-Only)">
                                        <i class="bi bi-lock-fill me-1"></i> Read Only
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Stylish Edit Confirmation Modal -->
<div class="modal fade" id="editConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background:#eff6ff; color:#1e40af; width: 48px; height: 48px;">
                        <i class="bi bi-pencil-square fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Survey Campaign</h5>
                        <small class="text-secondary">Confirm before modifying survey campaign</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-secondary mb-0">Are you sure you want to edit the survey campaign <strong id="editModalSurveyTitle" class="text-dark"></strong>?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="editModalConfirmBtn" class="btn btn-primary-custom px-4"><i class="bi bi-pencil me-1"></i> Proceed to Edit</a>
            </div>
        </div>
    </div>
</div>

<!-- Stylish Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 d-flex align-items-center justify-content-center" style="background:#fef2f2; color:#dc2626; width: 48px; height: 48px;">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Delete Survey Campaign</h5>
                        <small class="text-secondary">This action is permanent and cannot be undone</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="text-secondary mb-3">Are you sure you want to delete <strong id="deleteModalSurveyTitle" class="text-dark"></strong>?</p>
                <div class="p-3 rounded-3 border small" style="background:#fef2f2; border-color:#fecaca !important; color:#991b1b;">
                    <i class="bi bi-info-circle me-1"></i> Deleting this survey campaign will remove its configuration and settings permanently.
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteModalForm" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger px-4"><i class="bi bi-trash me-1"></i> Confirm Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Edit Modal dynamic setup
        const editModal = document.getElementById('editConfirmModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const title = button.getAttribute('data-survey-title');
                const editUrl = button.getAttribute('data-edit-url');
                
                document.getElementById('editModalSurveyTitle').textContent = `"${title}"`;
                document.getElementById('editModalConfirmBtn').setAttribute('href', editUrl);
            });
        }

        // Delete Modal dynamic setup
        const deleteModal = document.getElementById('deleteConfirmModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const title = button.getAttribute('data-survey-title');
                const deleteUrl = button.getAttribute('data-delete-url');
                
                document.getElementById('deleteModalSurveyTitle').textContent = `"${title}"`;
                document.getElementById('deleteModalForm').setAttribute('action', deleteUrl);
            });
        }
    });
</script>
@endpush
@endsection
