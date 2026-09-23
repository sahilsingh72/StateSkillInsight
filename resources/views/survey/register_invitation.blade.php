@extends('layouts.survey')

@section('title', 'Complete Profile - ' . $category->name)

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

        <form action="{{ route('survey.complete_invitation_profile', ['token' => $invitation->token]) }}" method="POST">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Select Your University / College / Institute <span class="text-danger">*</span></label>
                    <select name="university_id" class="form-select border-primary" required>
                        <option value="">-- Select Your Institution / College --</option>
                        
                        @php $inis = $institutions->where('type', 'ini'); @endphp
                        @if($inis->count() > 0)
                            <optgroup label="Institutes of National Importance (IIT / NIT / IIM / AIIMS)">
                                @foreach($inis as $inst)
                                    <option value="{{ $inst->id }}" {{ ($selectedUniId == $inst->id) ? 'selected' : '' }}>{{ $inst->name }} ({{ $inst->short_name }})</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $unis = $institutions->where('type', 'university'); @endphp
                        @if($unis->count() > 0)
                            <optgroup label="Central & State Universities">
                                @foreach($unis as $inst)
                                    <option value="{{ $inst->id }}" {{ ($selectedUniId == $inst->id) ? 'selected' : '' }}>{{ $inst->name }} ({{ $inst->short_name }})</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $autonomies = $institutions->where('type', 'autonomous_college'); @endphp
                        @if($autonomies->count() > 0)
                            <optgroup label="Autonomous Colleges">
                                @foreach($autonomies as $inst)
                                    <option value="{{ $inst->id }}" {{ ($selectedUniId == $inst->id) ? 'selected' : '' }}>{{ $inst->name }} (Autonomous)</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $affiliateds = $institutions->where('type', 'affiliated_college'); @endphp
                        @if($affiliateds->count() > 0)
                            <optgroup label="Affiliated Colleges (Under Parent University)">
                                @foreach($affiliateds as $inst)
                                    <option value="{{ $inst->id }}" {{ ($selectedUniId == $inst->id) ? 'selected' : '' }}>{{ $inst->name }} {{ $inst->parent ? '(Affiliated to '.$inst->parent->short_name.')' : '' }}</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @php $polytechnics = $institutions->where('type', 'polytechnic_iti'); @endphp
                        @if($polytechnics->count() > 0)
                            <optgroup label="Polytechnics & ITIs (Skill & Technical Institutes)">
                                @foreach($polytechnics as $inst)
                                    <option value="{{ $inst->id }}" {{ ($selectedUniId == $inst->id) ? 'selected' : '' }}>{{ $inst->name }} (Polytechnic / ITI)</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control bg-light" value="{{ $invitation->name }}" readonly disabled>
                    <input type="hidden" name="name" value="{{ $invitation->name }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email Address (Optional)</label>
                    <input type="email" class="form-control bg-light" value="{{ $invitation->email }}" readonly disabled>
                    <input type="hidden" name="email" value="{{ $invitation->email }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Mobile Number (Optional)</label>
                    <input type="tel" name="mobile" class="form-control" placeholder="+91 9876543210" value="{{ old('mobile', $invitation->mobile) }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Gender (Optional)</label>
                    <select name="gender" class="form-select">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other / Prefer not to say</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Academic Programme <span class="text-danger">*</span></label>
                    <select name="programme" id="programme_select_inv" class="form-select border-primary" required>
                        <option value="">-- Select Institution First --</option>
                    </select>
                    <div id="other_programme_container_inv" class="mt-2" style="display:none;">
                        <input type="text" name="other_programme" id="other_programme_input_inv" class="form-control border-primary" placeholder="Specify your Academic Programme (e.g. B.Tech Artificial Intelligence)">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Department / Discipline</label>
                    <select name="department" id="department_select_inv" class="form-select border-primary">
                        <option value="">-- Select Programme First --</option>
                    </select>
                    <div id="other_department_container_inv" class="mt-2" style="display:none;">
                        <input type="text" name="other_department" id="other_department_input_inv" class="form-control border-primary" placeholder="Specify your Department / Discipline" value="{{ old('other_department') }}">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Graduation / Admission Year</label>
                    <input type="text" name="graduation_year" class="form-control" placeholder="e.g. 2024" value="{{ old('graduation_year') }}">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Current Employment Status</label>
                    <select name="employment_status" class="form-select">
                        <option value="Employed Full-time">Employed Full-time</option>
                        <option value="Employed Part-time / Freelance">Employed Part-time / Freelance</option>
                        <option value="Actively Seeking Employment">Actively Seeking Employment</option>
                        <option value="Currently Studying">Currently Studying</option>
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

            <div class="d-flex justify-content-end align-items-center">
                <button type="submit" class="btn btn-uni-primary px-4">
                    Start The Survey <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const uniSelect = document.querySelector('select[name="university_id"]');
    const progSelect = document.getElementById('programme_select_inv');
    const otherProgContainer = document.getElementById('other_programme_container_inv');
    const otherProgInput = document.getElementById('other_programme_input_inv');

    const deptSelect = document.getElementById('department_select_inv');
    const otherDeptContainer = document.getElementById('other_department_container_inv');
    const otherDeptInput = document.getElementById('other_department_input_inv');

    const defaultProg = @json(old('programme', $invitation->programme));
    const defaultDept = @json(old('department', $invitation->department));

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
            updateDepartments(uniSelect ? uniSelect.value : '', this.value);
        });
    }

    if (deptSelect) {
        deptSelect.addEventListener('change', checkOtherDepartment);
    }

    function updateDepartments(uniId, progVal) {
        if (!deptSelect) return;
        if (!uniId || !progVal) {
            deptSelect.innerHTML = '<option value="">-- Select Programme First --</option>';
            checkOtherDepartment();
            return;
        }

        deptSelect.innerHTML = '<option value="">Loading departments...</option>';

        fetch('/api/universities/' + uniId + '/departments?programme=' + encodeURIComponent(progVal))
            .then(response => response.json())
            .then(data => {
                deptSelect.innerHTML = '<option value="">-- Select Department / Discipline --</option>';
                let matched = false;
                if (data.departments && data.departments.length > 0) {
                    data.departments.forEach(dept => {
                        if (dept === 'Other') return;
                        const opt = document.createElement('option');
                        opt.value = dept;
                        opt.textContent = dept;
                        if (defaultDept && (defaultDept.toLowerCase() === dept.toLowerCase() || dept.toLowerCase().includes(defaultDept.toLowerCase()))) {
                            opt.selected = true;
                            matched = true;
                        }
                        deptSelect.appendChild(opt);
                    });
                }
                const otherOpt = document.createElement('option');
                otherOpt.value = 'Other';
                otherOpt.textContent = 'Other (Please specify)';
                if (defaultDept && !matched) {
                    otherOpt.selected = true;
                    matched = true;
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

    if (uniSelect && progSelect) {
        function updateProgrammes(uniId) {
            if (!uniId) {
                progSelect.innerHTML = '<option value="">-- Select Institution First --</option>';
                checkOtherProgramme();
                updateDepartments('', '');
                return;
            }
            
            progSelect.innerHTML = '<option value="">Loading offered programmes...</option>';
            
            fetch('/api/universities/' + uniId + '/programmes')
                .then(response => response.json())
                .then(data => {
                    progSelect.innerHTML = '<option value="">-- Select Academic Programme --</option>';
                    let matched = false;
                    if (data.programmes && data.programmes.length > 0) {
                        data.programmes.forEach(prog => {
                            if (prog === 'Other') return;
                            const opt = document.createElement('option');
                            opt.value = prog;
                            opt.textContent = prog;
                            if (defaultProg && (defaultProg.toLowerCase() === prog.toLowerCase() || prog.toLowerCase().includes(defaultProg.toLowerCase()))) {
                                opt.selected = true;
                                matched = true;
                            }
                            progSelect.appendChild(opt);
                        });
                    }
                    
                    const otherOpt = document.createElement('option');
                    otherOpt.value = 'Other';
                    otherOpt.textContent = 'Other (Please specify)';
                    if (defaultProg && !matched) {
                        otherOpt.selected = true;
                        matched = true;
                    }
                    progSelect.appendChild(otherOpt);

                    checkOtherProgramme();
                    updateDepartments(uniId, progSelect.value);
                })
                .catch(err => {
                    console.error('Error fetching programmes:', err);
                    progSelect.innerHTML = '<option value="">-- Select Academic Programme --</option>' +
                        '<option value="Other">Other (Please specify)</option>';
                    checkOtherProgramme();
                    updateDepartments(uniId, progSelect.value);
                });
        }

        uniSelect.addEventListener('change', function() {
            updateProgrammes(this.value);
        });

        if (uniSelect.value) {
            updateProgrammes(uniSelect.value);
        }
    }
});
</script>
@endpush
