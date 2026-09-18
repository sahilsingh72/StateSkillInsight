@extends('layouts.admin')

@section('title', 'Voice Response Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Open-Ended Voice Response Manager</h4>
        <p class="text-secondary small mb-0">Listen to audio recordings submitted by respondents across open feedback questions.</p>
    </div>
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Respondent</th>
                    <th>Question</th>
                    <th>Category</th>
                    <th>Audio Player</th>
                    <th>Recorded Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($voiceResponses as $v)
                    <tr>
                        <td>
                            <strong>{{ $v->response->respondentSurvey->respondent->name ?? 'Respondent' }}</strong><br>
                            <small class="text-muted">{{ $v->response->respondentSurvey->respondent->email ?? '' }}</small>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark">{{ Str::limit($v->response->question->question_text ?? '', 50) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $v->response->respondentSurvey->category->name ?? 'Category' }}</span>
                        </td>
                        <td>
                            @if($v->file_path)
                                <audio controls style="height:36px; max-width:240px;">
                                    <source src="{{ Storage::url($v->file_path) }}" type="{{ $v->mime_type }}">
                                </audio>
                            @else
                                <span class="text-muted small">No file</span>
                            @endif
                        </td>
                        <td>{{ $v->created_at->format('d M Y, H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
