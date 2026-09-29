@extends('layouts.survey')

@section('title', $category->name . ' - Survey Questionnaire')

@push('styles')
<style>
    /* Voice Recorder Custom UI */
    .voice-recorder-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .voice-mic-icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
        display: flex;
        align-items: center;
        justify-content: center;
        animation: idlePulse 2.5s infinite ease-in-out;
    }

    @keyframes idlePulse {
        0%, 100% { transform: scale(1); box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35); }
        50% { transform: scale(1.06); box-shadow: 0 6px 20px rgba(239, 68, 68, 0.55); }
    }

    /* Live Studio Box with Neon Audio Catch Waveform (Matching Reference Image) */
    .voice-studio-box {
        background: radial-gradient(ellipse at center, #0f172a 0%, #060913 100%);
        border: 1px solid rgba(139, 92, 246, 0.35);
        box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.6), 0 0 20px rgba(139, 92, 246, 0.2);
    }

    .waveform-canvas-container {
        background: #070a14;
        border: 1px solid rgba(148, 163, 184, 0.15);
        border-radius: 12px;
        box-shadow: inset 0 2px 10px rgba(0, 0, 0, 0.7);
        min-height: 95px;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .pulse-rec-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #ef4444;
        box-shadow: 0 0 10px #ef4444;
        animation: recDotBlink 1s infinite alternate;
        display: inline-block;
    }

    @keyframes recDotBlink {
        0% { opacity: 0.3; transform: scale(0.85); }
        100% { opacity: 1; transform: scale(1.25); }
    }
</style>
@endpush

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
                            @php
                                $existingResp = $existingResponses[$q->id] ?? null;
                                $existingVoice = $existingResp?->voiceResponse;
                                $existingAudioUrl = $existingVoice ? Storage::url($existingVoice->file_path) : null;
                                $hasAudio = !empty($existingAudioUrl) || ($existingResp && Str::contains($existingResp->text_value ?? '', 'Voice Recording'));
                            @endphp
                            <div class="voice-recorder-card p-3 p-md-4 rounded-4 border bg-light position-relative" id="voiceCard_{{ $q->id }}">
                                
                                <!-- State 1: Idle / Initial Start State -->
                                <div id="voiceIdleState_{{ $q->id }}" class="{{ $hasAudio ? 'd-none' : '' }} text-center py-2">
                                    <div class="voice-mic-icon-wrapper mb-3 mx-auto">
                                        <i class="bi bi-mic-fill fs-2 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Voice Answer Recording</h6>
                                    <p class="small text-secondary mb-3" style="max-width: 480px; margin: 0 auto;">
                                        Click start to record your voice answer using your microphone. You can review, retry, and re-record anytime before submitting.
                                    </p>
                                    <button type="button" class="btn btn-danger px-4 py-2 rounded-pill shadow-sm" id="startRecBtn_{{ $q->id }}" onclick="startVoiceRecording({{ $q->id }})">
                                        <i class="bi bi-mic-fill me-1"></i> Start Recording
                                    </button>
                                </div>

                                <!-- State 2: Active Recording Studio with Audio Catch Waveform Animation -->
                                <div id="voiceActiveState_{{ $q->id }}" class="d-none voice-studio-box p-3 p-md-4 rounded-4 shadow-lg text-center position-relative">
                                    <!-- Recording Status & Live Timer Header -->
                                    <div class="d-flex justify-content-between align-items-center mb-2 text-white-50 small px-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="pulse-rec-dot"></span>
                                            <span class="text-white fw-semibold small">RECORDING VOICE...</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="bi bi-stopwatch text-warning"></i>
                                            <span class="font-monospace text-white fw-bold fs-6" id="recTimer_{{ $q->id }}">00:00</span>
                                        </div>
                                    </div>

                                    <!-- Waveform Visualizer Canvas (Audio Catch Animation matching user reference image) -->
                                    <div class="waveform-canvas-container p-2 mb-3">
                                        <canvas id="voiceCanvas_{{ $q->id }}" class="w-100" height="90" style="max-height: 90px;"></canvas>
                                    </div>

                                    <!-- Recording Controls -->
                                    <div class="d-flex justify-content-center align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-light btn-sm px-3 rounded-pill" onclick="cancelVoiceRecording({{ $q->id }})">
                                            <i class="bi bi-x-circle me-1"></i> Cancel
                                        </button>
                                        <button type="button" class="btn btn-danger px-4 py-2 rounded-pill fw-bold shadow-sm" id="stopRecBtn_{{ $q->id }}" onclick="stopVoiceRecording({{ $q->id }})">
                                            <i class="bi bi-stop-circle-fill me-1"></i> Stop Recording
                                        </button>
                                    </div>
                                </div>

                                <!-- State 3: Completed Preview with Retry / Record Again Button -->
                                <div id="voicePreviewState_{{ $q->id }}" class="{{ $hasAudio ? '' : 'd-none' }} p-3 bg-white rounded-3 border">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                                <i class="bi bi-check-lg fw-bold fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small" id="uploadStatusText_{{ $q->id }}">Voice Answer Recorded</div>
                                                <small class="text-secondary" id="recDurationLabel_{{ $q->id }}">{{ $existingVoice ? 'Saved (' . $existingVoice->duration_seconds . 's)' : 'Audio uploaded & ready' }}</small>
                                            </div>
                                        </div>
                                        
                                        <!-- Retry Button to Record Again -->
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3 rounded-pill fw-semibold" id="retryRecBtn_{{ $q->id }}" onclick="retryVoiceRecording({{ $q->id }})" title="Discard current recording and record again">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i> Record Again (Retry)
                                        </button>
                                    </div>

                                    <!-- Audio Player Preview -->
                                    <audio id="audioPreview_{{ $q->id }}" controls class="w-100" src="{{ $existingAudioUrl ?? '' }}" style="outline: none; border-radius: 20px;"></audio>
                                </div>

                                <!-- Hidden input storing audio reference for form autosave and validations -->
                                <input type="hidden" class="q-input voice-recorded-flag" name="answers[{{ $q->id }}]" id="voiceRecordedInput_{{ $q->id }}" value="{{ $hasAudio ? ($existingResp->text_value ?? '1') : '' }}">
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
                    $sectionsList = $sections->values();
                    $currentIdx = $sectionsList->search(fn($sec) => $sec->id == $currentSection->id);
                    $prevSec = ($currentIdx !== false && $currentIdx > 0) ? $sectionsList->get($currentIdx - 1) : null;
                    $nextSec = ($currentIdx !== false && $currentIdx < $sectionsList->count() - 1) ? $sectionsList->get($currentIdx + 1) : null;
                @endphp

                @if($prevSec)
                    <button type="button" onclick="navigateSection('{{ route('survey.take', ['token' => $respondent->token, 'section' => $prevSec->id]) }}', false)" class="btn btn-outline-secondary px-4">
                        <i class="bi bi-arrow-left me-1"></i> Previous
                    </button>
                @else
                    <div></div>
                @endif

                @if($nextSec)
                    <button type="button" onclick="navigateSection('{{ route('survey.take', ['token' => $respondent->token, 'section' => $nextSec->id]) }}', true)" class="btn btn-uni-primary px-4 fw-bold">
                        Next <i class="bi bi-arrow-right ms-1"></i>
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
    let audioStreams = {};
    let audioContexts = {};
    let analyserNodes = {};
    let animationFrames = {};
    let timerIntervals = {};
    let recStartTimes = {};

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
                const voiceInput = document.getElementById(`voiceRecordedInput_${qId}`);
                if (voiceInput && voiceInput.value && voiceInput.value.trim() !== '') answered = true;
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
        if (!badge) return;
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

    // ==========================================
    // Advanced Voice Recording & Audio Waveform Visualizer
    // ==========================================
    function startVoiceRecording(qId) {
        navigator.mediaDevices.getUserMedia({ audio: true })
            .then(stream => {
                audioStreams[qId] = stream;

                // MediaRecorder Setup
                const mediaRecorder = new MediaRecorder(stream);
                mediaRecorders[qId] = mediaRecorder;
                audioChunks[qId] = [];

                mediaRecorder.ondataavailable = e => {
                    if (e.data.size > 0) audioChunks[qId].push(e.data);
                };

                mediaRecorder.onstop = () => {
                    const elapsedSec = Math.round((Date.now() - (recStartTimes[qId] || Date.now())) / 1000) || 1;
                    const audioBlob = new Blob(audioChunks[qId], { type: 'audio/webm' });
                    const audioUrl = URL.createObjectURL(audioBlob);
                    
                    const audioPrev = document.getElementById(`audioPreview_${qId}`);
                    if (audioPrev) {
                        audioPrev.src = audioUrl;
                    }

                    const durationLabel = document.getElementById(`recDurationLabel_${qId}`);
                    if (durationLabel) {
                        durationLabel.innerText = `Duration: ${elapsedSec}s · Uploading...`;
                    }

                    uploadVoiceAudio(qId, audioBlob, elapsedSec);
                };

                mediaRecorder.start();
                recStartTimes[qId] = Date.now();

                // UI Transition: Show active recording studio box
                document.getElementById(`voiceIdleState_${qId}`)?.classList.add('d-none');
                document.getElementById(`voicePreviewState_${qId}`)?.classList.add('d-none');
                document.getElementById(`voiceActiveState_${qId}`)?.classList.remove('d-none');

                // Start Live Timer
                const timerEl = document.getElementById(`recTimer_${qId}`);
                if (timerEl) {
                    timerEl.innerText = '00:00';
                    clearInterval(timerIntervals[qId]);
                    timerIntervals[qId] = setInterval(() => {
                        const totalSec = Math.floor((Date.now() - recStartTimes[qId]) / 1000);
                        const mins = String(Math.floor(totalSec / 60)).padStart(2, '0');
                        const secs = String(totalSec % 60).padStart(2, '0');
                        timerEl.innerText = `${mins}:${secs}`;
                    }, 500);
                }

                // Setup AudioContext & Real-Time Waveform Visualizer
                startWaveformVisualizer(qId, stream);
            })
            .catch(err => {
                console.error("Microphone access error:", err);
                alert("Microphone access permission is required to record your voice answer.");
            });
    }

    function stopVoiceRecording(qId) {
        cleanupVoiceStreamAndVisualizer(qId);

        if (mediaRecorders[qId] && mediaRecorders[qId].state !== 'inactive') {
            mediaRecorders[qId].stop();
        }

        document.getElementById(`voiceActiveState_${qId}`)?.classList.add('d-none');
        document.getElementById(`voicePreviewState_${qId}`)?.classList.remove('d-none');
    }

    function cancelVoiceRecording(qId) {
        cleanupVoiceStreamAndVisualizer(qId);

        if (mediaRecorders[qId] && mediaRecorders[qId].state !== 'inactive') {
            mediaRecorders[qId].ondataavailable = null;
            mediaRecorders[qId].onstop = null;
            mediaRecorders[qId].stop();
        }

        document.getElementById(`voiceActiveState_${qId}`)?.classList.add('d-none');

        const hasExisting = document.getElementById(`voiceRecordedInput_${qId}`)?.value;
        if (hasExisting) {
            document.getElementById(`voicePreviewState_${qId}`)?.classList.remove('d-none');
        } else {
            document.getElementById(`voiceIdleState_${qId}`)?.classList.remove('d-none');
        }
    }

    function retryVoiceRecording(qId) {
        // Clear previous audio
        const audioPrev = document.getElementById(`audioPreview_${qId}`);
        if (audioPrev) {
            audioPrev.pause();
            audioPrev.removeAttribute('src');
            audioPrev.load();
        }

        // Reset input flag and UI
        const recordedInput = document.getElementById(`voiceRecordedInput_${qId}`);
        if (recordedInput) {
            recordedInput.value = '';
        }

        document.getElementById(`voicePreviewState_${qId}`)?.classList.add('d-none');
        
        // Immediately start a fresh recording session
        startVoiceRecording(qId);
    }

    function cleanupVoiceStreamAndVisualizer(qId) {
        // Stop timer
        if (timerIntervals[qId]) {
            clearInterval(timerIntervals[qId]);
            delete timerIntervals[qId];
        }

        // Cancel animation loop
        if (animationFrames[qId]) {
            cancelAnimationFrame(animationFrames[qId]);
            delete animationFrames[qId];
        }

        // Close AudioContext
        if (audioContexts[qId]) {
            try { audioContexts[qId].close(); } catch(e) {}
            delete audioContexts[qId];
        }

        // Stop microphone stream tracks
        if (audioStreams[qId]) {
            audioStreams[qId].getTracks().forEach(track => track.stop());
            delete audioStreams[qId];
        }
    }

    function startWaveformVisualizer(qId, stream) {
        const canvas = document.getElementById(`voiceCanvas_${qId}`);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        
        let analyser = null;
        let dataArray = null;

        if (AudioContextClass) {
            try {
                const audioCtx = new AudioContextClass();
                audioContexts[qId] = audioCtx;

                const source = audioCtx.createMediaStreamSource(stream);
                analyser = audioCtx.createAnalyser();
                analyser.fftSize = 64; // Produces 32 frequency bins
                analyser.smoothingTimeConstant = 0.8;
                source.connect(analyser);

                analyserNodes[qId] = analyser;
                dataArray = new Uint8Array(analyser.frequencyBinCount);
            } catch(e) {
                console.warn("Web Audio API not fully available, falling back to simulated wave physics:", e);
            }
        }

        // Resize canvas for sharp rendering on retina displays
        const rect = canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        canvas.width = (rect.width || 560) * dpr;
        canvas.height = 90 * dpr;

        let phase = 0;
        const totalBars = 52; // Total audio equalizer bars across width

        function renderFrame() {
            animationFrames[qId] = requestAnimationFrame(renderFrame);

            const width = canvas.width;
            const height = canvas.height;
            const centerY = height / 2;

            ctx.clearRect(0, 0, width, height);

            // Get live audio data if available
            let avgVolume = 0;
            if (analyser && dataArray) {
                analyser.getByteFrequencyData(dataArray);
                let sum = 0;
                for (let i = 0; i < dataArray.length; i++) {
                    sum += dataArray[i];
                }
                avgVolume = sum / dataArray.length;
            }

            // Create stunning vertical gradient (Violet/Purple to Electric Indigo to Sky Cyan)
            const gradient = ctx.createLinearGradient(0, centerY - 38 * dpr, 0, centerY + 38 * dpr);
            gradient.addColorStop(0.0, '#d8b4fe'); // Light violet top
            gradient.addColorStop(0.25, '#a855f7'); // Vibrant Purple
            gradient.addColorStop(0.55, '#818cf8'); // Electric Blue / Indigo
            gradient.addColorStop(0.85, '#38bdf8'); // Sky Cyan
            gradient.addColorStop(1.0, '#06b6d4'); // Deep Cyan bottom

            ctx.fillStyle = gradient;

            const barWidth = Math.max(3 * dpr, (width / totalBars) * 0.46);
            const barSpacing = width / totalBars;
            const halfBars = totalBars / 2;

            phase += 0.08;

            for (let i = 0; i < totalBars; i++) {
                // Distance from center (0 at middle, 1 at wings)
                const distFromCenter = Math.abs(i - halfBars) / halfBars;
                
                // Base minimal height for horizontal resting line (as in reference image)
                const baselineHeight = 3.5 * dpr;

                // Center weighting (bell curve): peak at center, tapering down to edges
                const centerWeight = Math.pow(1 - distFromCenter, 2.2);

                let barHeight = baselineHeight;

                if (avgVolume > 2 && analyser && dataArray) {
                    // Map bar to frequency bin
                    const freqIdx = Math.floor((1 - distFromCenter) * (dataArray.length - 1));
                    const freqVal = dataArray[freqIdx] || avgVolume;
                    const dynamicBoost = (freqVal / 255) * 75 * dpr;
                    barHeight = baselineHeight + dynamicBoost * centerWeight;
                } else {
                    // Smooth subtle idle listening wave
                    const idleSine = Math.sin(phase + i * 0.28) * (1.5 * dpr);
                    barHeight = baselineHeight + Math.max(0, idleSine * centerWeight * 4);
                }

                // Add slight organic breathing to center bars
                const organicPulse = Math.sin(phase * 1.5 + i * 0.4) * (2 * dpr) * centerWeight;
                barHeight = Math.max(baselineHeight, barHeight + organicPulse);

                // Coordinates for centered vertical rounded bar
                const x = i * barSpacing + (barSpacing - barWidth) / 2;
                const topY = centerY - barHeight / 2;
                const radius = barWidth / 2;

                // Draw rounded pill bar
                ctx.beginPath();
                if (typeof ctx.roundRect === 'function') {
                    ctx.roundRect(x, topY, barWidth, barHeight, radius);
                } else {
                    ctx.rect(x, topY, barWidth, barHeight);
                }
                ctx.fill();
            }
        }

        renderFrame();
    }

    function uploadVoiceAudio(qId, blob, duration) {
        const formData = new FormData();
        formData.append('question_id', qId);
        formData.append('audio_file', blob, `voice_${qId}.webm`);
        formData.append('duration', duration);

        const uploadText = document.getElementById(`uploadStatusText_${qId}`);
        if (uploadText) {
            uploadText.innerText = 'Uploading voice answer...';
        }

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
                if (uploadText) {
                    uploadText.innerText = 'Voice Answer Recorded & Saved';
                }
                const durationLabel = document.getElementById(`recDurationLabel_${qId}`);
                if (durationLabel) {
                    durationLabel.innerText = `Duration: ${duration}s · Uploaded successfully`;
                }

                const recordedInput = document.getElementById(`voiceRecordedInput_${qId}`);
                if (recordedInput) {
                    recordedInput.value = `[Voice Recording Uploaded: ${duration}s]`;
                }

                // Clear error highlight on question block if any
                const block = document.querySelector(`[data-question-id="${qId}"]`);
                if (block) {
                    block.classList.remove('border-danger', 'bg-danger-subtle');
                    const errEl = block.querySelector('.required-error-msg');
                    if (errEl) errEl.classList.add('d-none');
                }

                triggerAutoSave();
            }
        })
        .catch(err => {
            console.error("Upload failed:", err);
            if (uploadText) {
                uploadText.innerText = 'Audio recorded (upload pending)';
            }
        });
    }
</script>
@endpush
