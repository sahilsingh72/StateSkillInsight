@extends('layouts.admin')

@section('title', 'System Audit Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">System Security & Operation Audit Logs</h4>
        <p class="text-secondary small mb-0">Track admin login, survey publishing, export downloads, and system configuration edits.</p>
    </div>
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Action</th>
                    <th>User</th>
                    <th>Entity Type</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $l)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $l->action }}</span></td>
                        <td>{{ $l->user->name ?? 'System' }}</td>
                        <td>{{ $l->entity_type }} #{{ $l->entity_id }}</td>
                        <td><code>{{ $l->ip_address }}</code></td>
                        <td>{{ $l->created_at->format('d M Y, H:i:s') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
