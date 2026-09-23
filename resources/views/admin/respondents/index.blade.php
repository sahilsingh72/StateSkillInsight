@extends('layouts.admin')

@section('title', 'Respondent Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Respondent Profiles</h4>
        <p class="text-secondary small mb-0">Browse and inspect individual respondent records across categories.</p>
    </div>
    <a href="{{ route('admin.exports.csv') }}" class="btn btn-primary-custom btn-sm"><i class="bi bi-download me-1"></i> Export CSV</a>
</div>

<div class="card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.respondents.index') }}" class="row g-2">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, or Student ID..." value="{{ request('search') }}">
        </div>
        <div class="col-md-4">
            <select name="category_code" class="form-select form-select-sm">
                <option value="">All Categories</option>
                <option value="cat_1" {{ request('category_code') == 'cat_1' ? 'selected' : '' }}>Working Alumni</option>
                <option value="cat_2" {{ request('category_code') == 'cat_2' ? 'selected' : '' }}>Job-Seeking Alumni</option>
                <option value="cat_3" {{ request('category_code') == 'cat_3' ? 'selected' : '' }}>Current Students</option>
                <option value="cat_4" {{ request('category_code') == 'cat_4' ? 'selected' : '' }}>Interrupted Students</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-sm btn-secondary w-100"><i class="bi bi-filter me-1"></i> Apply Filter</button>
        </div>
    </form>
</div>

<div class="card-custom p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Respondent Name</th>
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <th>Organisation</th>
                    @endif
                    <th>Survey</th>
                    <th>Category</th>
                    <th>Programme & Dept</th>
                    <th>Grad Year</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($respondents as $r)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $r->name }}</div>
                            <small class="text-muted">{{ $r->email ?? $r->mobile }}</small>
                        </td>
                        @if(auth()->check() && auth()->user()->isSuperAdmin())
                            <td>
                                @if($r->university)
                                    <span class="badge bg-primary-subtle text-primary border" title="{{ $r->university->name }}">
                                        <i class="bi bi-building me-1"></i> {{ $r->university->short_name ?? $r->university->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border">N/A</span>
                                @endif
                            </td>
                        @endif
                        <td>
                            @php
                                $surveyObj = $r->respondentSurveys->first()?->survey;
                                $surveyTitle = $surveyObj?->title ?? 'State Skill Insight Survey';
                                $words = preg_split('/\s+/', trim($surveyTitle));
                                $acronym = '';
                                foreach ($words as $w) {
                                    $cleanWord = preg_replace('/[^a-zA-Z]/', '', $w);
                                    if (!empty($cleanWord)) {
                                        $acronym .= strtoupper(mb_substr($cleanWord, 0, 1));
                                    }
                                }
                            @endphp
                            <span class="badge bg-info-subtle text-info border" title="{{ $surveyTitle }}">
                                <i class="bi bi-file-earmark-text me-1"></i> {{ $acronym }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $r->category_code }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold small text-dark">{{ $r->programme }}</div>
                            <small class="text-muted">{{ $r->department }}</small>
                        </td>
                        <td>{{ $r->graduation_year }}</td>
                        <td>{{ $r->current_city }}, {{ $r->country }}</td>
                        <td>{{ $r->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye"></i> View Profile</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $respondents->links() }}
    </div>
</div>
@endsection
