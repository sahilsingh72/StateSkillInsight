@extends('layouts.admin')

@section('title', 'Respondent Management')

@push('styles')
<!-- TomSelect CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    /* Compact TomSelect for Admin Filter Bar */
    .ts-wrapper.form-select-sm,
    .form-select-sm.ts-wrapper {
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
        min-height: 31px !important;
        padding: 0 1.75rem 0 0.25rem !important;
        display: flex !important;
        align-items: center !important;
    }
    .ts-wrapper.focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15) !important;
    }
    .ts-wrapper .ts-control {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0.15rem 0.35rem !important;
        font-size: 0.825rem !important;
        width: 100% !important;
        min-height: 28px !important;
        display: flex !important;
        align-items: center !important;
    }
    .ts-control input {
        font-size: 0.825rem !important;
    }
    .ts-control .item {
        color: #1e293b !important;
        font-weight: 500 !important;
        font-size: 0.825rem !important;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        border: 1px solid #cbd5e1 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.08) !important;
        margin-top: 4px !important;
        overflow: hidden !important;
        z-index: 1050 !important;
    }
    .ts-dropdown .optgroup-header {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        color: #475569 !important;
        padding: 6px 12px !important;
        background-color: #f8fafc !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .ts-dropdown .option {
        padding: 7px 12px !important;
        font-size: 0.835rem !important;
        color: #1e293b !important;
        border-bottom: 1px solid #f8fafc !important;
    }
    .ts-dropdown .option.active {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        font-weight: 600 !important;
    }
    .ts-dropdown .highlight {
        background-color: #fef08a !important;
        color: #0f172a !important;
        font-weight: 700 !important;
        padding: 0 2px !important;
        border-radius: 2px !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Respondent Profiles</h4>
        <p class="text-secondary small mb-0">Browse and inspect individual respondent records across categories.</p>
    </div>
    <a href="{{ route('admin.exports.csv', request()->query()) }}" class="btn btn-primary-custom btn-sm">
        <i class="bi bi-download me-1"></i> Export CSV
    </a>
</div>

<div class="card-custom p-3 mb-4">
    <form method="GET" action="{{ route('admin.respondents.index') }}" id="respondentFilterForm" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search name, email, mobile, ID..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="category_code" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <option value="cat_1" {{ request('category_code') == 'cat_1' ? 'selected' : '' }}>Working Alumni</option>
                <option value="cat_2" {{ request('category_code') == 'cat_2' ? 'selected' : '' }}>Job-Seeking Alumni</option>
                <option value="cat_3" {{ request('category_code') == 'cat_3' ? 'selected' : '' }}>Current Students</option>
                <option value="cat_4" {{ request('category_code') == 'cat_4' ? 'selected' : '' }}>Interrupted Students</option>
            </select>
        </div>
        @if(isset($universities) && $universities->count() > 0)
            <div class="col-md-4">
                <select name="university_id" id="respondent_uni_filter" class="form-select form-select-sm">
                    <option value="">All Universities / Colleges</option>
                    @php
                        $inis = $universities->where('type', 'ini');
                        $unis = $universities->where('type', 'university');
                        $colleges = $universities->where('type', 'affiliated_college');
                        $polytechnics = $universities->where('type', 'polytechnic_iti');
                    @endphp

                    @if($inis->count() > 0)
                        <optgroup label="Institutes of National Importance (IIT / NIT / IIM / AIIMS)">
                            @foreach($inis as $u)
                                <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->short_name }} — {{ $u->name }} (INI)
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                    @if($unis->count() > 0)
                        <optgroup label="Universities">
                            @foreach($unis as $u)
                                <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->short_name }} — {{ $u->name }} (University)
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                    @if($colleges->count() > 0)
                        <optgroup label="Affiliated Colleges">
                            @foreach($colleges as $u)
                                @php
                                    $parentAffil = $u->parent ? ' [Affiliated to ' . ($u->parent->short_name ?? $u->parent->name) . ']' : '';
                                @endphp
                                <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->short_name }} — {{ $u->name }}{{ $parentAffil }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif

                    @if($polytechnics->count() > 0)
                        <optgroup label="Polytechnics & ITIs">
                            @foreach($polytechnics as $u)
                                <option value="{{ $u->id }}" {{ request('university_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->short_name }} — {{ $u->name }} (Polytechnic / ITI)
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>
        @endif
        <div class="col-auto ms-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-secondary"><i class="bi bi-filter me-1"></i> Apply Filter</button>
            @if(request()->hasAny(['search', 'category_code', 'university_id']))
                <a href="{{ route('admin.respondents.index') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
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
                @forelse($respondents as $r)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $r->name }}</div>
                            <small class="text-muted">{{ $r->email ?? $r->mobile }}</small>
                            @if($r->university_student_alumni_id)
                                <div class="text-muted" style="font-size: 0.72rem;">ID: {{ $r->university_student_alumni_id }}</div>
                            @endif
                        </td>
                        @if(auth()->check() && auth()->user()->isSuperAdmin())
                            <td>
                                @if($r->university)
                                    <span class="badge bg-primary-subtle text-primary border" title="{{ $r->university->name }}">
                                        <i class="bi bi-building me-1"></i> {{ $r->university->short_name ?? $r->university->name }}
                                    </span>
                                    @if($r->university->parent)
                                        <div class="small text-muted" style="font-size: 0.7rem;" title="Affiliated to {{ $r->university->parent->name }}">
                                            Affil: {{ $r->university->parent->short_name ?? $r->university->parent->name }}
                                        </div>
                                    @endif
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
                @empty
                    <tr>
                        <td colspan="{{ auth()->check() && auth()->user()->isSuperAdmin() ? 9 : 8 }}" class="text-center py-4 text-muted">
                            <i class="bi bi-people fs-4 d-block mb-1"></i>
                            No respondents matching your search criteria found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $respondents->links() }}
    </div>
</div>
@endsection

@push('scripts')
<!-- TomSelect JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const uniSelect = document.getElementById('respondent_uni_filter');
    if (uniSelect) {
        let isInit = true;
        const ts = new TomSelect('#respondent_uni_filter', {
            create: false,
            maxItems: 1,
            placeholder: 'Search university / college...',
            allowEmptyOption: true,
            highlight: true,
            openOnFocus: true,
            sortField: { field: '$order' },
            searchField: ['text']
        });

        ts.on('change', function(val) {
            if (!isInit) {
                document.getElementById('respondentFilterForm').submit();
            }
        });
        isInit = false;
    }
});
</script>
@endpush


