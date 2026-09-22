@extends('layouts.admin')

@section('title', 'Users & Role RBAC Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">User Accounts & Role Permissions (RBAC)</h4>
        <p class="text-secondary small mb-0">Manage system users, administrators, analysts, and operators.</p>
    </div>
    <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#newUserModal">
        <i class="bi bi-person-plus me-1"></i> Add User Account
    </button>
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>University</th>
                    <th>Status</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge bg-primary-subtle text-primary border">{{ strtoupper($u->roleRelation->display_name ?? $u->roleRelation->name ?? 'NO ROLE') }}</span></td>
                        <td>
                            @if($u->isSuperAdmin())
                                <span class="badge bg-primary text-white border"><i class="bi bi-shield-lock me-1"></i> Global System Admin (Software Apex)</span>
                            @else
                                <span class="fw-semibold text-secondary">{{ $u->university->name ?? 'Unassigned' }}</span>
                            @endif
                        </td>
                        <td><span class="badge bg-success">ACTIVE</span></td>
                        <td>{{ $u->created_at->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create System User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mobile Number</label>
                        <input type="text" name="mobile" class="form-control">
                    </div>
                    @if(auth()->user() && auth()->user()->isSuperAdmin())
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assign Institution / College</label>
                            <select name="university_id" class="form-select" required>
                                <option value="">-- Select Institution --</option>
                                @foreach($universities as $uni)
                                    <option value="{{ $uni->id }}">{{ $uni->name }} ({{ strtoupper($uni->type) }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Assigned Role</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">-- Select Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }} ({{ $role->name }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
