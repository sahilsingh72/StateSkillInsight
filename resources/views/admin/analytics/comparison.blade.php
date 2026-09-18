@extends('layouts.admin')

@section('title', 'Category Comparison Matrix')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Education–Employment Continuum Category Comparison</h4>
        <p class="text-secondary small mb-0">Side-by-side metric matrix across Working, Job-Seeking, Current Students, and Interrupted Learners.</p>
    </div>
</div>

<div class="card-custom p-4 mb-4">
    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="text-start">Research Dimension</th>
                    <th>Working Alumni (Cat 1)</th>
                    <th>Job-Seeking Alumni (Cat 2)</th>
                    <th>Current Students (Cat 3)</th>
                    <th>Interrupted Students (Cat 4)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($matrix['dimensions'] as $idx => $dim)
                    <tr>
                        <td class="text-start fw-bold text-dark">{{ $dim }}</td>
                        <td><span class="badge bg-primary fs-6 px-3 py-2">{{ $matrix['cat_1'][$idx] }} / 100</span></td>
                        <td><span class="badge bg-teal text-white fs-6 px-3 py-2" style="background:var(--uni-secondary);">{{ $matrix['cat_2'][$idx] }} / 100</span></td>
                        <td><span class="badge bg-warning text-dark fs-6 px-3 py-2">{{ $matrix['cat_3'][$idx] }} / 100</span></td>
                        <td><span class="badge bg-danger fs-6 px-3 py-2">{{ $matrix['cat_4'][$idx] }} / 100</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
