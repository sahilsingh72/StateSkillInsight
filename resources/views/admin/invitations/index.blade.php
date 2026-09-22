@extends('layouts.admin')

@section('title', 'Survey Invitations & Tokens')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Survey Invitations & Secure Tokens</h4>
        <p class="text-secondary small mb-0">Generate single or bulk token links (`/survey/start/{token}`) for respondents.</p>
    </div>
    <button class="btn btn-primary-custom btn-sm" data-bs-toggle="modal" data-bs-target="#newInviteModal">
        <i class="bi bi-plus-circle me-1"></i> New Invitation
    </button>
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Token Link</th>
                    <th>Sent At</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invitations as $inv)
                    <tr>
                        <td><strong>{{ $inv->name }}</strong></td>
                        <td>{{ $inv->email }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $inv->category_code }}</span></td>
                        <td><span class="badge bg-info text-dark">{{ strtoupper($inv->status) }}</span></td>
                        <td>
                            <code class="small" style="font-size:0.75rem;">/survey/start/{{ Str::limit($inv->token, 12) }}...</code>
                        </td>
                        <td>{{ $inv->sent_at ? $inv->sent_at->format('d M Y H:i') : $inv->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <form action="{{ route('admin.invitations.resend', $inv->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm" title="Resend Email Invitation">
                                    <i class="bi bi-send me-1"></i> Resend Mail
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newInviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.invitations.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Send Survey Invitation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Survey <span class="text-danger">*</span></label>
                        <select name="survey_id" class="form-select border-primary" required>
                            <option value="">-- Select Target Survey --</option>
                            @forelse($surveys as $s)
                                <option value="{{ $s->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $s->title }}</option>
                            @empty
                                <option value="" disabled>No Surveys Available</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Respondent Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Category</label>
                        <select name="category_code" class="form-select" required>
                            <option value="cat_1">Working Alumni</option>
                            <option value="cat_2">Job-Seeking Alumni</option>
                            <option value="cat_3">Current Students</option>
                            <option value="cat_4">Interrupted Students</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-custom">Generate Token Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
