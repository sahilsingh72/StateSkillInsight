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

        @if(session('resume_data'))
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #fffcf0 0%, #fef3c7 100%); border: 2px solid #f59e0b !important;">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-warning text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-warning text-dark fw-bold">Incomplete Survey Found</span>
                                    <span class="badge bg-white text-secondary border">{{ session('resume_data')['category'] }}</span>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Welcome back, {{ session('resume_data')['name'] }}!</h5>
                                <p class="text-secondary small mb-2">
                                    You have an incomplete survey registered with <strong>{{ session('resume_data')['email'] }}</strong>.
                                </p>
                                @if(isset(session('resume_data')['percentage']))
                                    <div class="d-flex align-items-center gap-2" style="max-width: 280px;">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ session('resume_data')['percentage'] }}%"></div>
                                        </div>
                                        <span class="small text-muted fw-semibold">{{ round(session('resume_data')['percentage']) }}% completed</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($errors->any() && !session('resume_data'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <strong>Unable to Start Survey:</strong>
                </div>
                <ul class="mb-0 ps-3 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('survey.start') }}" method="POST">
            @csrf
            <input type="hidden" name="category_code" value="{{ $category->code }}">

            <div class="row g-3 mb-4">
                <!-- Step 1: Select Institution / University -->
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Select Your University / Institute <span class="text-danger">*</span></label>
                    <select name="institution_id" id="institution_select" class="form-select border-primary @error('institution_id') is-invalid @enderror @error('university_id') is-invalid @enderror" required>
                        <option value="">-- Search & Select University / Institute --</option>
                        
                        @php $unis = $institutions->where('type', 'university'); @endphp
                        @if($unis->count() > 0)
                            <optgroup label="Central & State Universities">
                                @foreach($unis as $inst)
                                    <option value="{{ $inst->id }}" 
                                            data-has-colleges="{{ $inst->colleges->count() > 0 ? '1' : '0' }}"
                                            {{ old('institution_id', old('university_id')) == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->name }} ({{ $inst->short_name }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $inis = $institutions->where('type', 'ini'); @endphp
                        @if($inis->count() > 0)
                            <optgroup label="Institutes of National Importance (IIT / NIT / IIM / AIIMS)">
                                @foreach($inis as $inst)
                                    <option value="{{ $inst->id }}" 
                                            data-has-colleges="0"
                                            {{ old('institution_id', old('university_id')) == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->name }} ({{ $inst->short_name }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $polytechnics = $institutions->where('type', 'polytechnic_iti'); @endphp
                        @if($polytechnics->count() > 0)
                            <optgroup label="Polytechnics & ITIs (Skill & Technical Institutes)">
                                @foreach($polytechnics as $inst)
                                    <option value="{{ $inst->id }}" 
                                            data-has-colleges="0"
                                            {{ old('institution_id', old('university_id')) == $inst->id ? 'selected' : '' }}>
                                        {{ $inst->name }} (Polytechnic / ITI)
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    @error('institution_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @error('university_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Step 2: Conditional College / Campus Dropdown -->
                <div class="col-md-12" id="college_container" style="display: none;">
                    <label class="form-label fw-semibold">Select College / Campus <span class="text-danger">*</span></label>
                    <select name="college_id" id="college_select" class="form-select border-primary @error('college_id') is-invalid @enderror">
                        <option value="main_campus">University Main Campus / University Departments</option>
                    </select>
                    <small class="text-muted d-block mt-1">
                        Select your specific affiliated college, or choose <strong>"University Main Campus"</strong> if you study directly in university departments.
                    </small>
                    @error('college_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter your full name" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="name@example.com" value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                            @if(session('resume_data'))
                                <div class="mt-2">
                                    <a href="{{ session('resume_data')['url'] }}" class="btn btn-sm btn-warning text-dark fw-bold px-3 py-1 rounded-pill">
                                        <i class="bi bi-arrow-right-circle-fill me-1"></i> Continue to Remaining Survey
                                    </a>
                                </div>
                            @endif
                        </div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mobile Number (Optional)</label>
                    <input type="tel" name="mobile" class="form-control @error('mobile') is-invalid @enderror" placeholder="+91 9876543210" value="{{ old('mobile') }}">
                    @error('mobile')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gender (Optional)</label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other / Prefer not to say</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Academic Programme <span class="text-danger">*</span></label>
                    <select name="programme" id="programme_select" class="form-select border-primary @error('programme') is-invalid @enderror" required>
                        <option value="">-- Select Institution First --</option>
                    </select>
                    <div id="other_programme_container" class="mt-2" style="display:none;">
                        <input type="text" name="other_programme" id="other_programme_input" class="form-control border-primary" placeholder="Specify your Academic Programme" value="{{ old('other_programme') }}">
                    </div>
                    @error('programme')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department / Discipline</label>
                    <select name="department" id="department_select" class="form-select border-primary @error('department') is-invalid @enderror">
                        <option value="">-- Select Programme First --</option>
                    </select>
                    <div id="other_department_container" class="mt-2" style="display:none;">
                        <input type="text" name="other_department" id="other_department_input" class="form-control border-primary" placeholder="Specify your Department / Discipline" value="{{ old('other_department') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Graduation Year</label>
                    <input type="text" name="graduation_year" class="form-control @error('graduation_year') is-invalid @enderror" placeholder="e.g. 2024" value="{{ old('graduation_year') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Current Employment Status</label>
                    <select name="employment_status" class="form-select @error('employment_status') is-invalid @enderror">
                        <option value="Employed Full-time" {{ old('employment_status') == 'Employed Full-time' ? 'selected' : '' }}>Employed Full-time</option>
                        <option value="Employed Part-time / Freelance" {{ old('employment_status') == 'Employed Part-time / Freelance' ? 'selected' : '' }}>Employed Part-time / Freelance</option>
                        <option value="Actively Seeking Employment" {{ old('employment_status') == 'Actively Seeking Employment' ? 'selected' : '' }}>Actively Seeking Employment</option>
                        <option value="Currently Studying" {{ old('employment_status') == 'Currently Studying' ? 'selected' : '' }}>Currently Studying</option>
                        <option value="Discontinued Studies" {{ old('employment_status') == 'Discontinued Studies' ? 'selected' : '' }}>Discontinued Studies</option>
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

<!-- Instructions Popup Modal (750px width container) -->
<div class="modal fade" id="surveyInstructionsModal" tabindex="-1" aria-labelledby="surveyInstructionsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 750px; width: 95%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="surveyInstructionsModalLabel">Important Survey Instructions</h5>
                        <small class="text-secondary">Please review these essential guidelines before proceeding to registration</small>
                    </div>
                </div>
            </div>

            <!-- Modal Body with Guidelines -->
            <div class="modal-body p-4 bg-white">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded-3 bg-light border d-flex align-items-start gap-3">
                        <div class="text-primary fs-5 mt-1 flex-shrink-0">
                            <i class="bi bi-person-check-fill"></i>
                        </div>
                        <div class="text-dark fw-semibold" style="font-size: 0.98rem; line-height: 1.6;">
                            Only one response is allowed per survey.
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border d-flex align-items-start gap-3">
                        <div class="text-primary fs-5 mt-1 flex-shrink-0">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <div class="text-dark fw-semibold" style="font-size: 0.98rem; line-height: 1.6;">
                            Before submitting the survey, you can review and modify your information as needed.
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border d-flex align-items-start gap-3">
                        <div class="text-danger fs-5 mt-1 flex-shrink-0">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <div class="text-dark fw-semibold" style="font-size: 0.98rem; line-height: 1.6;">
                            Once the survey is finally submitted, no further modifications will be allowed.
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-light border d-flex align-items-start gap-3">
                        <div class="text-warning-emphasis fs-5 mt-1 flex-shrink-0">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div class="text-dark fw-semibold" style="font-size: 0.98rem; line-height: 1.6;">
                            If required, you may take breaks while completing the survey. However, please ensure that the survey is completed and submitted on the same day.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer with OK button -->
            <div class="modal-footer border-top bg-light px-4 py-3 justify-content-end">
                <button type="button" class="btn btn-primary px-5 py-2 fw-bold rounded-pill shadow-sm" data-bs-dismiss="modal">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- TomSelect CSS -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    /* Styling TomSelect dropdowns with visible border */
    .form-select.ts-wrapper,
    .ts-wrapper.form-select {
        border: 1.5px solid #3b82f6 !important;
        border-radius: 0.5rem !important;
        background-color: #ffffff !important;
        box-shadow: none !important;
        min-height: 44px !important;
        padding: 0 2.25rem 0 0.5rem !important;
        display: flex !important;
        align-items: center !important;
        position: relative !important;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
    }
    .form-select.ts-wrapper.focus,
    .form-select.ts-wrapper.input-active,
    .ts-wrapper.focus {
        border-color: #1d4ed8 !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2) !important;
    }
    .ts-wrapper .ts-control,
    .form-select.ts-wrapper .ts-control {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0.5rem 0.25rem !important;
        font-size: 0.95rem !important;
        width: 100% !important;
        min-height: 40px !important;
        display: flex !important;
        align-items: center !important;
    }
    .ts-control input {
        font-size: 0.95rem !important;
    }
    .ts-control .item {
        color: #1e293b !important;
        font-weight: 500 !important;
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
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #475569 !important;
        padding: 8px 14px !important;
        background-color: #f8fafc !important;
        border-bottom: 1px solid #f1f5f9 !important;
    }
    .ts-dropdown .option {
        padding: 9px 14px !important;
        font-size: 0.935rem !important;
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

@push('scripts')
<!-- TomSelect JS -->
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const institutionSelect = document.getElementById('institution_select');
    const collegeContainer = document.getElementById('college_container');
    const collegeSelect = document.getElementById('college_select');

    const progSelect = document.getElementById('programme_select');
    const otherProgContainer = document.getElementById('other_programme_container');
    const otherProgInput = document.getElementById('other_programme_input');

    const deptSelect = document.getElementById('department_select');
    const otherDeptContainer = document.getElementById('other_department_container');
    const otherDeptInput = document.getElementById('other_department_input');

    const savedOldCollege = "{{ old('college_id') }}";
    const savedOldProg = "{{ old('programme') }}";
    const savedOldDept = "{{ old('department') }}";

    // Initialize TomSelect on University and College dropdowns
    let instTomSelect = null;
    let collegeTomSelect = null;

    if (institutionSelect) {
        instTomSelect = new TomSelect('#institution_select', {
            create: false,
            maxItems: 1,
            placeholder: '-- Search & Select University / Institute --',
            allowEmptyOption: true,
            highlight: true,
            sortField: { field: '$order' },
            searchField: ['text']
        });
    }

    if (collegeSelect) {
        collegeTomSelect = new TomSelect('#college_select', {
            create: false,
            maxItems: 1,
            placeholder: '-- Search & Select College / Campus --',
            allowEmptyOption: true,
            highlight: true,
            sortField: { field: '$order' },
            searchField: ['text']
        });
    }

    function getActiveInstitutionId() {
        const colVal = collegeTomSelect ? collegeTomSelect.getValue() : (collegeSelect ? collegeSelect.value : '');
        if (collegeContainer.style.display !== 'none' && colVal && colVal !== 'main_campus') {
            return colVal;
        }
        return instTomSelect ? instTomSelect.getValue() : (institutionSelect ? institutionSelect.value : '');
    }

    function checkOtherProgramme() {
        if (progSelect && progSelect.value === 'Other') {
            otherProgContainer.style.display = 'block';
            otherProgInput.setAttribute('required', 'required');
            otherProgInput.focus();
        } else if (otherProgContainer && otherProgInput) {
            otherProgContainer.style.display = 'none';
            otherProgInput.removeAttribute('required');
        }
    }

    function checkOtherDepartment() {
        if (deptSelect && deptSelect.value === 'Other') {
            otherDeptContainer.style.display = 'block';
            otherDeptInput.setAttribute('required', 'required');
            otherDeptInput.focus();
        } else if (otherDeptContainer && otherDeptInput) {
            otherDeptContainer.style.display = 'none';
            otherDeptInput.removeAttribute('required');
        }
    }

    if (progSelect) {
        progSelect.addEventListener('change', function() {
            checkOtherProgramme();
            updateDepartments(getActiveInstitutionId(), this.value);
        });
    }

    if (deptSelect) {
        deptSelect.addEventListener('change', checkOtherDepartment);
    }

    function updateDepartments(instId, progVal, preselectDept = '') {
        if (!deptSelect) return;
        if (!instId || !progVal) {
            deptSelect.innerHTML = '<option value="">-- Select Programme First --</option>';
            checkOtherDepartment();
            return;
        }

        deptSelect.innerHTML = '<option value="">Loading departments...</option>';

        fetch('/api/universities/' + instId + '/departments?programme=' + encodeURIComponent(progVal))
            .then(response => response.json())
            .then(data => {
                deptSelect.innerHTML = '<option value="">-- Select Department / Discipline --</option>';
                if (data.departments && data.departments.length > 0) {
                    data.departments.forEach(dept => {
                        if (dept === 'Other') return;
                        const opt = document.createElement('option');
                        opt.value = dept;
                        opt.textContent = dept;
                        if (preselectDept && preselectDept === dept) {
                            opt.selected = true;
                        }
                        deptSelect.appendChild(opt);
                    });
                }
                const otherOpt = document.createElement('option');
                otherOpt.value = 'Other';
                otherOpt.textContent = 'Other (Please specify)';
                if (preselectDept && preselectDept === 'Other') {
                    otherOpt.selected = true;
                }
                deptSelect.appendChild(otherOpt);

                checkOtherDepartment();
            })
            .catch(err => {
                console.error('Error fetching departments:', err);
                deptSelect.innerHTML = '<option value="">-- Select Department / Discipline --</option>' +
                    '<option value="Other">Other (Please specify)</option>';
                checkOtherDepartment();
            });
    }

    function updateProgrammes(instId, preselectProg = '', preselectDept = '') {
        if (!progSelect) return;
        if (!instId) {
            progSelect.innerHTML = '<option value="">-- Select Institution First --</option>';
            checkOtherProgramme();
            updateDepartments('', '');
            return;
        }
        
        progSelect.innerHTML = '<option value="">Loading offered programmes...</option>';
        
        fetch('/api/universities/' + instId + '/programmes')
            .then(response => response.json())
            .then(data => {
                progSelect.innerHTML = '<option value="">-- Select Academic Programme --</option>';
                if (data.programmes && data.programmes.length > 0) {
                    data.programmes.forEach(prog => {
                        if (prog === 'Other') return;
                        const opt = document.createElement('option');
                        opt.value = prog;
                        opt.textContent = prog;
                        if (preselectProg && preselectProg === prog) {
                            opt.selected = true;
                        }
                        progSelect.appendChild(opt);
                    });
                }
                const otherOpt = document.createElement('option');
                otherOpt.value = 'Other';
                otherOpt.textContent = 'Other (Please specify)';
                if (preselectProg && preselectProg === 'Other') {
                    otherOpt.selected = true;
                }
                progSelect.appendChild(otherOpt);

                checkOtherProgramme();
                updateDepartments(instId, progSelect.value, preselectDept);
            })
            .catch(err => {
                console.error('Error fetching programmes:', err);
                progSelect.innerHTML = '<option value="">-- Select Academic Programme --</option>' +
                    '<option value="Other">Other (Please specify)</option>';
                checkOtherProgramme();
                updateDepartments(instId, progSelect.value, preselectDept);
            });
    }

    function handleInstitutionChange(isInit = false) {
        const instId = instTomSelect ? instTomSelect.getValue() : (institutionSelect ? institutionSelect.value : '');
        if (!instId) {
            collegeContainer.style.display = 'none';
            if (collegeSelect) collegeSelect.removeAttribute('required');
            updateProgrammes('');
            return;
        }

        const opt = institutionSelect ? institutionSelect.querySelector(`option[value="${instId}"]`) : null;
        const hasColleges = opt ? opt.getAttribute('data-has-colleges') === '1' : false;

        if (hasColleges) {
            collegeContainer.style.display = 'block';

            if (collegeTomSelect) {
                collegeTomSelect.clear();
                collegeTomSelect.clearOptions();
                collegeTomSelect.clearOptionGroups();
                collegeTomSelect.addOption({
                    value: 'main_campus',
                    text: 'University Main Campus / University Departments'
                });
                collegeTomSelect.setValue('main_campus');
            } else if (collegeSelect) {
                collegeSelect.innerHTML = '<option value="">Loading affiliated colleges...</option>';
            }

            fetch('/api/universities/' + instId + '/colleges')
                .then(res => res.json())
                .then(data => {
                    if (collegeTomSelect) {
                        collegeTomSelect.clearOptions();
                        collegeTomSelect.clearOptionGroups();
                        collegeTomSelect.addOption({
                            value: 'main_campus',
                            text: 'University Main Campus / University Departments'
                        });

                        if (data.colleges && data.colleges.length > 0) {
                            collegeTomSelect.addOptionGroup('affiliated', { label: 'Affiliated Colleges' });
                            data.colleges.forEach(col => {
                                collegeTomSelect.addOption({
                                    value: String(col.id),
                                    text: col.name,
                                    optgroup: 'affiliated'
                                });
                            });
                        }
                        collegeTomSelect.refreshOptions(false);

                        if (isInit && savedOldCollege) {
                            collegeTomSelect.setValue(String(savedOldCollege));
                        } else {
                            collegeTomSelect.setValue('main_campus');
                        }
                    } else if (collegeSelect) {
                        collegeSelect.innerHTML = '<option value="main_campus">University Main Campus / University Departments</option>';
                        if (data.colleges && data.colleges.length > 0) {
                            const optgroup = document.createElement('optgroup');
                            optgroup.label = "Affiliated Colleges";
                            data.colleges.forEach(col => {
                                const optEl = document.createElement('option');
                                optEl.value = col.id;
                                optEl.textContent = col.name;
                                if (isInit && savedOldCollege && (savedOldCollege == col.id || savedOldCollege === col.name)) {
                                    optEl.selected = true;
                                }
                                optgroup.appendChild(optEl);
                            });
                            collegeSelect.appendChild(optgroup);
                        }
                    }

                    const activeId = getActiveInstitutionId();
                    updateProgrammes(activeId, isInit ? savedOldProg : '', isInit ? savedOldDept : '');
                })
                .catch(err => {
                    console.error('Error loading colleges:', err);
                    if (collegeTomSelect) {
                        collegeTomSelect.setValue('main_campus');
                    } else if (collegeSelect) {
                        collegeSelect.innerHTML = '<option value="main_campus">University Main Campus / University Departments</option>';
                    }
                    updateProgrammes(instId, isInit ? savedOldProg : '', isInit ? savedOldDept : '');
                });
        } else {
            collegeContainer.style.display = 'none';
            if (collegeTomSelect) {
                collegeTomSelect.setValue('main_campus');
            } else if (collegeSelect) {
                collegeSelect.value = 'main_campus';
            }
            updateProgrammes(instId, isInit ? savedOldProg : '', isInit ? savedOldDept : '');
        }
    }

    if (instTomSelect) {
        instTomSelect.on('change', function() {
            handleInstitutionChange(false);
        });
    } else if (institutionSelect) {
        institutionSelect.addEventListener('change', function() {
            handleInstitutionChange(false);
        });
    }

    if (collegeTomSelect) {
        collegeTomSelect.on('change', function() {
            const activeId = getActiveInstitutionId();
            updateProgrammes(activeId);
        });
    } else if (collegeSelect) {
        collegeSelect.addEventListener('change', function() {
            const activeId = getActiveInstitutionId();
            updateProgrammes(activeId);
        });
    }

    // Initialize institution on page load
    const initialInstVal = instTomSelect ? instTomSelect.getValue() : (institutionSelect ? institutionSelect.value : '');
    if (initialInstVal) {
        handleInstitutionChange(true);
    }

    @if(!$errors->any() && !session('resume_data') && empty(old('_token')))
    // Auto-open Instructions Modal ONLY on fresh arrival from landing page
    const instructionsModalEl = document.getElementById('surveyInstructionsModal');
    if (instructionsModalEl) {
        const instructionsModal = new bootstrap.Modal(instructionsModalEl, {
            backdrop: 'static',
            keyboard: false
        });
        instructionsModal.show();
    }
    @endif
});
</script>
@endpush
