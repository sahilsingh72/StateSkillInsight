@extends('layouts.survey')

@section('title', 'Respondent Registration - ' . $category->name)

@section('content')
<div class="container" style="max-width: 750px;">
    <div class="survey-card">
        <div class="d-flex align-items-center gap-3 mb-4 border-bottom pb-3">
            <div class="rounded-circle p-3 text-white" style="background:var(--uni-primary);">
                <i class="bi {{ $category->icon ?? 'bi-person-badge' }} fs-4"></i>
            </div>
            <div>
                <span class="badge bg-light text-primary border mb-1">Category Registration</span>
                <h4 class="fw-bold text-dark mb-0">{{ $category->name }}</h4>
            </div>
        </div>

        <form action="{{ route('survey.start') }}" method="POST">
            @csrf
            <input type="hidden" name="category_code" value="{{ $category->code }}">

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Select Your University / College / Institute <span class="text-danger">*</span></label>
                    <select name="university_id" class="form-select border-primary" required>
                        <option value="">-- Select Your Institution / College --</option>
                        
                        @php $inis = $institutions->where('type', 'ini'); @endphp
                        @if($inis->count() > 0)
                            <optgroup label="Institutes of National Importance (IIT / NIT / IIM / AIIMS)">
                                @foreach($inis as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->short_name }})</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $unis = $institutions->where('type', 'university'); @endphp
                        @if($unis->count() > 0)
                            <optgroup label="Central & State Universities">
                                @foreach($unis as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }} ({{ $inst->short_name }})</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $autonomies = $institutions->where('type', 'autonomous_college'); @endphp
                        @if($autonomies->count() > 0)
                            <optgroup label="Autonomous Colleges">
                                @foreach($autonomies as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }} (Autonomous)</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $affiliateds = $institutions->where('type', 'affiliated_college'); @endphp
                        @if($affiliateds->count() > 0)
                            <optgroup label="Affiliated Colleges (Under Parent University)">
                                @foreach($affiliateds as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }} {{ $inst->parent ? '(Affiliated to '.$inst->parent->short_name.')' : '' }}</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $polytechnics = $institutions->where('type', 'polytechnic_iti'); @endphp
                        @if($polytechnics->count() > 0)
                            <optgroup label="Polytechnics & ITIs (Skill & Technical Institutes)">
                                @foreach($polytechnics as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }} (Polytechnic / ITI)</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address (Optional)</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mobile Number (Optional)</label>
                    <input type="tel" name="mobile" class="form-control" placeholder="+91 9876543210">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gender (Optional)</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other / Prefer not to say</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Academic Programme</label>
                    <select name="programme" class="form-select">
                        <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                        <option value="Electrical Engineering">Electrical Engineering</option>
                        <option value="Mechanical Engineering">Mechanical Engineering</option>
                        <option value="Civil Engineering">Civil Engineering</option>
                        <option value="MBA / Business Management">MBA / Business Management</option>
                        <option value="Other Discipline">Other Discipline</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department / Discipline</label>
                    <input type="text" name="department" class="form-control" placeholder="e.g. Dept of Computer Science">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Graduation / Admission Year</label>
                    <input type="text" name="graduation_year" class="form-control" placeholder="e.g. 2024">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Current Employment Status</label>
                    <select name="employment_status" class="form-select">
                        <option value="Employed Full-time">Employed Full-time</option>
                        <option value="Employed Part-time / Freelance">Employed Part-time / Freelance</option>
                        <option value="Actively Seeking Employment">Actively Seeking Employment</option>
                        <option value="Enrolled Student">Enrolled Student</option>
                        <option value="Discontinued Studies">Discontinued Studies</option>
                    </select>
                </div>
            </div>

            <!-- Consent Checkbox -->
            <div class="p-3 bg-light rounded-3 border mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="consent_given" id="consentCheck" required checked>
                    <label class="form-check-label text-dark small" for="consentCheck">
                        <strong>Consent:</strong> I agree to participate in this institutional research study. I understand that my responses will be used for curriculum improvement, skill mapping, and educational policy.
                    </label>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('survey.landing') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back
                </a>
                <button type="submit" class="btn btn-uni-primary px-4">
                    Start The Survey <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
