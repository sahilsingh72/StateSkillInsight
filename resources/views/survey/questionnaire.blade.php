@extends('layouts.survey')

@section('title', $category->name . ' - Survey Questionnaire')

@section('content')
<div class="container" style="max-width: 900px;">
    
@if($sections->isEmpty() || !$currentSection)
    <div class="card border-0 shadow-sm rounded-4 p-5 bg-white text-center my-4">
        <div class="rounded-circle p-3 text-warning bg-warning-subtle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-exclamation-circle fs-2"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">No Survey Questions Configured Yet</h4>
        <p class="text-secondary max-w-md mx-auto mb-4" style="max-width: 550px;">
            No survey questions have been assigned to <strong>{{ $university->name ?? 'this institution' }}</strong> yet.
            Please check back later or contact your institution administrator to set up questions.
        </p>
        <div>
            <a href="{{ route('survey.landing') }}" class="btn btn-outline-primary px-4 rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> Return to Main Page
            </a>
        </div>
    </div>
@else
    <!-- Progress Indicator Header -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold me-2">{{ $category->name }}</span>
                <span class="text-secondary small" id="currentSectionTitle">Section: {{ $currentSection->title }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-success border" id="autoSaveBadge">
                    <i class="bi bi-cloud-check me-1"></i> Auto-saved
                </span>
                <span class="fw-bold text-dark" id="progressPercentageText">{{ $respondentSurvey->completion_percentage }}%</span>
            </div>
        </div>
        <div class="progress" style="height: 10px; border-radius: 5px;">
            <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" id="progressBar" style="width: {{ $respondentSurvey->completion_percentage }}%;"></div>
        </div>
    </div>

    <!-- Section Navigation Pills -->
    <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
        @foreach($sections as $secIdx => $sec)
            <a href="{{ route('survey.take', ['token' => $respondent->token, 'section' => $sec->id]) }}" 
               class="btn btn-sm text-nowrap rounded-pill px-3 {{ $sec->id == $currentSection->id ? 'btn-primary' : 'btn-light border text-secondary' }}">
                Sec {{ $secIdx + 1 }}: {{ Str::limit($sec->title, 20) }}
            </a>
        @endforeach
    </div>

    <!-- Questions Form -->
    <form id="questionnaireForm">
        @csrf
        <input type="hidden" name="section_id" value="{{ $currentSection->id }}">

        <div class="survey-card">
            <h4 class="fw-bold text-dark mb-2">{{ $currentSection->title }}</h4>
            <p class="text-secondary small mb-4">{{ $currentSection->description }}</p>

            @foreach($currentSection->questions as $qIdx => $q)
                <div class="question-card p-4 rounded-4 border mb-4 bg-white question-block" id="q_block_{{ $q->id }}" data-question-id="{{ $q->id }}" data-required="{{ $q->is_required ? '1' : '0' }}" data-type="{{ $q->type }}">
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <span class="badge bg-light text-dark border rounded-circle fs-6 p-2" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;">
                            {{ $qIdx + 1 }}
                        </span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">
                                {{ $q->getTranslationText($locale) }}
                                @if($q->is_required)
                                    <span class="text-danger" title="Required Question">*</span>
                                    <span class="badge bg-danger-subtle text-danger border ms-1" style="font-size:0.65rem;">Required</span>
                                @endif
                            </h6>
                            @if($q->help_text)
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> {{ $q->help_text }}</small>
                            @endif
                        </div>
                    </div>

                    <!-- Question Input Renderer -->
                    <div class="ps-4">
                        @if($q->type === 'single_choice' || $q->type === 'likert' || $q->type === 'yes_no')
                            <div class="d-flex flex-column gap-2">
                                @foreach($q->options as $opt)
                                    @php
                                        $isChecked = isset($existingResponses[$q->id]) && $existingResponses[$q->id]->text_value === $opt->option_text;
                                    @endphp
                                    <div class="form-check p-3 rounded-3 border option-hover" style="cursor:pointer;">
                                        <input class="form-check-input q-input" type="radio" name="answers[{{ $q->id }}]" id="opt_{{ $opt->id }}" value="{{ $opt->value }}" {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="opt_{{ $opt->id }}" style="cursor:pointer;">
                                            {{ $opt->option_text }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                        @elseif($q->type === 'multiple_choice')
                            <div class="d-flex flex-column gap-2">
                                @foreach($q->options as $opt)
                                    @php
                                        $arrVals = isset($existingResponses[$q->id]) && is_array($existingResponses[$q->id]->json_value) ? $existingResponses[$q->id]->json_value : [];
                                        $isChecked = in_array($opt->option_text, $arrVals);
                                    @endphp
                                    <div class="form-check p-3 rounded-3 border">
                                        <input class="form-check-input q-input" type="checkbox" name="answers[{{ $q->id }}][]" id="opt_{{ $opt->id }}" value="{{ $opt->value }}" {{ $isChecked ? 'checked' : '' }}>
                                        <label class="form-check-label w-100" for="opt_{{ $opt->id }}">
                                            {{ $opt->option_text }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                        @elseif($q->type === 'rating')
                            <div class="d-flex gap-2">
                                @for($r = 1; $r <= 5; $r++)
                                    @php
                                        $isChecked = isset($existingResponses[$q->id]) && (int)$existingResponses[$q->id]->text_value === $r;
                                    @endphp
                                    <input type="radio" class="btn-check q-input" name="answers[{{ $q->id }}]" id="rate_{{ $q->id }}_{{ $r }}" value="{{ $r }}" {{ $isChecked ? 'checked' : '' }}>
                                    <label class="btn btn-outline-warning text-dark flex-fill py-3 fw-bold" for="rate_{{ $q->id }}_{{ $r }}">
                                        ★ {{ $r }}
                                    </label>
                                @endfor
                            </div>

                        @elseif($q->type === 'dropdown')
                            <select class="form-select form-select-lg q-input" name="answers[{{ $q->id }}]">
                                <option value="">Select an Option</option>
                                @foreach($q->options as $opt)
                                    @php
                                        $isSelected = isset($existingResponses[$q->id]) && $existingResponses[$q->id]->text_value === $opt->option_text;
                                    @endphp
                                    <option value="{{ $opt->value }}" {{ $isSelected ? 'selected' : '' }}>{{ $opt->option_text }}</option>
                                @endforeach
                            </select>

                        @elseif($q->type === 'short_text')
                            <input type="text" class="form-control form-control-lg q-input" name="answers[{{ $q->id }}]" value="{{ $existingResponses[$q->id]->text_value ?? '' }}" placeholder="Type your response here...">

                        @elseif($q->type === 'long_text')
                            <textarea class="form-control q-input" name="answers[{{ $q->id }}]" rows="4" placeholder="Write detailed answer...">{{ $existingResponses[$q->id]->text_value ?? '' }}</textarea>

                        @elseif($q->type === 'voice')
                            <div class="voice-recorder-box p-4 bg-light rounded-4 border text-center">
                                <div class="mb-3">
                                    <i class="bi bi-mic-fill fs-1 text-primary mb-2"></i>
                                    <p class="small text-secondary mb-0">Record your response using your device microphone.</p>
                                </div>
                                <div class="d-flex justify-content-center gap-2 mb-3">
                                    <button type="button" class="btn btn-danger px-4" id="startRec_{{ $q->id }}" onclick="startVoiceRecording({{ $q->id }})">
                                        <i class="bi bi-record-circle me-1"></i> Start Recording
                                    </button>
                                    <button type="button" class="btn btn-secondary px-4 d-none" id="stopRec_{{ $q->id }}" onclick="stopVoiceRecording({{ $q->id }})">
                                        <i class="bi bi-stop-circle me-1"></i> Stop
                                    </button>
                                </div>
                                <audio id="audioPreview_{{ $q->id }}" controls class="w-100 d-none mt-2"></audio>
                                <span class="badge bg-success {{ isset($existingResponses[$q->id]) ? '' : 'd-none' }} mt-2" id="uploadStatus_{{ $q->id }}"><i class="bi bi-check-all me-1"></i> Audio Uploaded</span>
                            </div>

                        @else
                            <input type="text" class="form-control q-input" name="answers[{{ $q->id }}]" value="{{ $existingResponses[$q->id]->text_value ?? '' }}">
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                @php
                    $prevSec = $sections->firstWhere('order', $currentSection->order - 1);
                    $nextSec = $sections->firstWhere('order', $currentSection->order + 1);
                @endphp

                @if($prevSec)
                    <button type="button" onclick="navigateSection('{{ route('survey.take', ['token' => $respondent->token, 'section' => $prevSec->id]) }}', false)" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Previous Section
                    </button>
                @else
                    <div></div>
                @endif

                @if($nextSec)
                    <button type="button" onclick="navigateSection('{{ route('survey.take', ['token' => $respondent->token, 'section' => $nextSec->id]) }}', true)" class="btn btn-uni-primary px-4 fw-bold">
                        Next Section <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                @else
                    <button type="button" onclick="navigateSection('{{ route('survey.review', ['token' => $respondent->token]) }}', true)" class="btn btn-success px-4 fw-bold">
                        Review & Submit <i class="bi bi-check-circle ms-1"></i>
                    </button>
                @endif
            </div>
        </div>
    </form>
@endif
</div>
@endsection

@push('scripts')
<script>
    let mediaRecorders = {};
    let audioChunks = {};

    // Auto save trigger on change
    document.querySelectorAll('.q-input').forEach(input => {
        input.addEventListener('change', function() {
            const block = this.closest('.question-block');
            if (block) {
                block.classList.remove('border-danger', 'bg-danger-subtle');
                const errEl = block.querySelector('.required-error-msg');
                if (errEl) errEl.classList.add('d-none');
            }
            triggerAutoSave();
        });
    });

    function validateSection() {
        let isValid = true;
        let firstUnansweredBlock = null;

        document.querySelectorAll('.question-block').forEach(block => {
            const isRequired = block.getAttribute('data-required') === '1';
            const qId = block.getAttribute('data-question-id');
            const qType = block.getAttribute('data-type');
            let errElement = block.querySelector('.required-error-msg');

            // Reset error state
            block.classList.remove('border-danger', 'bg-danger-subtle');
            if (errElement) errElement.classList.add('d-none');

            if (!isRequired) return;

            let answered = false;

            if (qType === 'single_choice' || qType === 'likert' || qType === 'yes_no' || qType === 'rating') {
                const checked = block.querySelector('input[type="radio"]:checked');
                if (checked && checked.value !== '') answered = true;
            } else if (qType === 'multiple_choice') {
                const checked = block.querySelectorAll('input[type="checkbox"]:checked');
                if (checked && checked.length > 0) answered = true;
            } else if (qType === 'dropdown') {
                const select = block.querySelector('select');
                if (select && select.value && select.value.trim() !== '') answered = true;
            } else if (qType === 'short_text' || qType === 'long_text') {
                const input = block.querySelector('input[type="text"], textarea');
                if (input && input.value && input.value.trim() !== '') answered = true;
            } else if (qType === 'voice') {
                const uploadStatus = document.getElementById(`uploadStatus_${qId}`);
                if (uploadStatus && !uploadStatus.classList.contains('d-none')) answered = true;
            } else {
                const anyInput = block.querySelector('.q-input');
                if (anyInput && anyInput.value && anyInput.value.trim() !== '') answered = true;
            }

            if (!answered) {
                isValid = false;
                block.classList.add('border-danger', 'bg-danger-subtle');
                
                if (!errElement) {
                    errElement = document.createElement('div');
                    errElement.className = 'required-error-msg text-danger small mt-2 fw-semibold';
                    errElement.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> This question is required. Please provide an answer before proceeding.';
                    block.querySelector('.ps-4').appendChild(errElement);
                } else {
                    errElement.classList.remove('d-none');
                }

                if (!firstUnansweredBlock) {
                    firstUnansweredBlock = block;
                }
            }
        });

        if (!isValid && firstUnansweredBlock) {
            firstUnansweredBlock.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return isValid;
    }

    function navigateSection(targetUrl, validate = true) {
        if (validate && !validateSection()) {
            return false;
        }

        const form = document.getElementById('questionnaireForm');
        if (!form) {
            window.location.href = targetUrl;
            return;
        }

        const formData = new FormData(form);

        fetch("{{ route('survey.autosave', $respondent->token) }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(() => {
            window.location.href = targetUrl;
        })
        .catch(() => {
            window.location.href = targetUrl;
        });
    }

    function triggerAutoSave() {
        const badge = document.getElementById('autoSaveBadge');
        badge.className = 'badge bg-warning text-dark border';
        badge.innerHTML = '<i class="bi bi-cloud-arrow-up me-1"></i> Saving...';

        const form = document.getElementById('questionnaireForm');
        const formData = new FormData(form);

        fetch("{{ route('survey.autosave', $respondent->token) }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                badge.className = 'badge bg-light text-success border';
                badge.innerHTML = `<i class="bi bi-cloud-check me-1"></i> Saved ${data.last_saved}`;
                document.getElementById('progressBar').style.width = data.completion_percentage + '%';
                document.getElementById('progressPercentageText').innerText = data.completion_percentage + '%';
            }
        });
    }

    // Voice Recorder Browser MediaRecorder Implementation
    function startVoiceRecording(qId) {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                const mediaRecorder = new MediaRecorder(stream);
                mediaRecorders[qId] = mediaRecorder;
                audioChunks[qId] = [];

                mediaRecorder.ondataavailable = e => {
                    if (e.data.size > 0) audioChunks[qId].push(e.data);
                };

                mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(audioChunks[qId], { type: 'audio/webm' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    const audioPrev = document.getElementById(`audioPreview_${qId}`);
                    audioPrev.src = audioUrl;
                    audioPrev.classList.remove('d-none');

                    uploadVoiceAudio(qId, audioBlob);
                };

                mediaRecorder.start();
                document.getElementById(`startRec_${qId}`).classList.add('d-none');
                document.getElementById(`stopRec_${qId}`).classList.remove('d-none');
            })
            .catch(err => alert("Microphone access permission required to record audio."));
    }

    function stopVoiceRecording(qId) {
        if (mediaRecorders[qId]) {
            mediaRecorders[qId].stop();
            document.getElementById(`stopRec_${qId}`).classList.add('d-none');
            document.getElementById(`startRec_${qId}`).classList.remove('d-none');
        }
    }

    function uploadVoiceAudio(qId, blob) {
        const formData = new FormData();
        formData.append('question_id', qId);
        formData.append('audio_file', blob, `voice_${qId}.webm`);

        fetch("{{ route('survey.voice_upload', $respondent->token) }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById(`uploadStatus_${qId}`).classList.remove('d-none');
            }
        });
    }
</script>
@endpush
