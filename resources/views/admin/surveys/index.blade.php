@extends('layouts.admin')

@section('title', 'Survey Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Survey Campaigns</h4>
        <p class="text-secondary small mb-0">Manage survey title, status, versioning, and structure.</p>
    </div>
    <a href="{{ route('admin.surveys.create') }}" class="btn btn-primary-custom btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Create New Survey
    </a>
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($surveys as $s)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $s->title }}</div>
                            <small class="text-muted">{{ Str::limit($s->description, 60) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-success">{{ strtoupper($s->status) }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $s->categories->count() }} Categories</span>
                        </td>
                        <td>v{{ $s->version }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.surveys.show', $s->id) }}" class="btn btn-light border"><i class="bi bi-eye"></i> Manage</a>
                                <a href="{{ route('admin.surveys.preview', $s->id) }}" class="btn btn-light border" target="_blank"><i class="bi bi-play-circle"></i> Preview</a>
                                <form action="{{ route('admin.surveys.clone', $s->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-light border" title="Clone Survey"><i class="bi bi-copy"></i> Clone</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
