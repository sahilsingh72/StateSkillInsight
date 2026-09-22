@extends('layouts.survey')

@section('title', 'Preview: ' . $survey->title)

@push('styles')
<style>
    .preview-mode-bar {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #f8fafc;
        border-bottom: 2px solid #3b82f6;
    }
    .question-card {
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .question-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .option-hover:hover {
        background-color: #f1f5f9;
    }
</style>
@endpush

@section('content')
<!-- Sticky Preview Banner -->
<div class="preview-mode-bar sticky-top py-2 px-3 shadow-sm mb-4">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark fw-bold text-uppercase px-2 py-1">
                <i class="bi bi-eye-fill me-1"></i> Preview Mode
            </span>
            <small class="text-light">
                Viewing survey <strong>"{{ $survey->title }}"</strong>. Data submitted in preview mode will not be stored.
            </small>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(isset($isSuperAdmin) && $isSuperAdmin)
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-building me-1"></i> Institution: 
                        {{ request('university_id') === 'global' ? 'Global / Common Only' : ($university ? $university->name : 'All Institutions (Unfiltered)') }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item {{ !request()->filled('university_id') && !$targetUniId ? 'active' : '' }}"
                               href="{{ route('admin.surveys.preview', ['survey' => $survey->id, 'category_id' => request('category_id')]) }}">
                                <i class="bi bi-globe me-2 text-primary"></i> All Institutions (Unfiltered)
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request('university_id') === 'global' ? 'active' : '' }}"
                               href="{{ route('admin.surveys.preview', ['survey' => $survey->id, 'category_id' => request('category_id'), 'university_id' => 'global']) }}">
                                <i class="bi bi-shield-check me-2 text-success"></i> Global / Common Only
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        @foreach($universities as $uniItem)
                            <li>
                                <a class="dropdown-item {{ $targetUniId == $uniItem->id && request('university_id') !== 'global' ? 'active' : '' }}" 
                                   href="{{ route('admin.surveys.preview', ['survey' => $survey->id, 'category_id' => request('category_id'), 'university_id' => $uniItem->id]) }}">
                                    <i class="bi bi-building me-2 text-secondary"></i> {{ $uniItem->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-1 fs-6">
                    <i class="bi bi-building me-1"></i> {{ $university ? $university->name : 'Assigned Institution' }}
                </span>
            @endif

            @if(isset($categories) && $categories->count() > 1)
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-diagram-3 me-1"></i> Category: {{ $category->name ?? 'Select Category' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        @foreach($categories as $catItem)
                            <li>
                                <a class="dropdown-item {{ $category && $category->id == $catItem->id ? 'active' : '' }}" 
                                   href="{{ route('admin.surveys.preview', array_filter(['survey' => $survey->id, 'category_id' => $catItem->id, 'university_id' => request('university_id')])) }}">
                                    {{ $catItem->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-sm btn-primary">
                <i class="bi bi-arrow-left me-1"></i> Return to Builder
            </a>
        </div>
    </div>
</div>

<div class="container" style="max-width: 900px;">

    <!-- Survey Header Info Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold mb-2">{{ $category->name ?? 'General Category' }}</span>
                <h3 class="fw-bold text-dark mb-1">{{ $survey->title }}</h3>
                @if($survey->subtitle)
                    <p class="text-muted mb-2 fs-6">{{ $survey->subtitle }}</p>
                @endif
            </div>
            <span class="badge bg-success-subtle text-success border border-success-subtle fw-semibold px-3 py-2">
                {{ strtoupper($survey->status) }}
            </span>
        </div>

        @if($survey->description)
            <p class="text-secondary small mb-3">{{ $survey->description }}</p>
        @endif

        <div class="d-flex flex-wrap gap-4 pt-3 border-top text-muted small">
            <div><i class="bi bi-people me-1 text-primary"></i> Target: <strong>{{ $survey->target_respondents ?? 'All Users' }}</strong></div>
            <div><i class="bi bi-mic me-1 text-primary"></i> Voice Enabled: <strong>{{ $survey->enable_voice ? 'Yes' : 'No' }}</strong></div>
            <div><i class="bi bi-shield-check me-1 text-primary"></i> Anonymous: <strong>{{ $survey->allow_anonymous ? 'Yes' : 'No' }}</strong></div>
        </div>
    </div>

    @if(!$category || $sections->isEmpty())
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <i class="bi bi-exclamation-triangle text-warning display-4 mb-3"></i>
            <h5 class="fw-bold text-dark">No Questions Found</h5>
            <p class="text-secondary small mb-3">This survey or selected category does not have any sections or questions configured yet.</p>
            <div>
                <a href="{{ route('admin.surveys.show', $survey->id) }}" class="btn btn-uni-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Add Questions in Survey Builder
                </a>
            </div>
        </div>
    @else
        <!-- Section Navigation Pills -->
        <div class="d-flex gap-2 mb-4 overflow-auto pb-2" id="sectionNavPills">
            @foreach($sections as $secIdx => $sec)
                <button type="button" 
                        class="btn btn-sm text-nowrap rounded-pill px-3 section-tab-btn {{ $secIdx === 0 ? 'btn-primary' : 'btn-light border text-secondary' }}"
                        data-target="section_card_{{ $sec->id }}"
                        onclick="switchPreviewSection('section_card_{{ $sec->id }}', this)">
                    Sec {{ $secIdx + 1 }}: {{ Str::limit($sec->title, 25) }}
                </button>
            @endforeach
        </div>

        <!-- Preview Form -->
        <form id="previewForm" onsubmit="event.preventDefault(); handlePreviewSubmit();">
            @foreach($sections as $secIdx => $sec)
                <div class="survey-card section-card-container {{ $secIdx > 0 ? 'd-none' : '' }}" id="section_card_{{ $sec->id }}">
                    <div class="border-bottom pb-3 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="fw-bold text-dark mb-1">{{ $sec->title }}</h4>
                            <span class="badge bg-light text-secondary border">Section {{ $secIdx + 1 }} of {{ $sections->count() }}</span>
                        </div>
                        @if($sec->description)
                            <p class="text-secondary small mb-0">{{ $sec->description }}</p>
                        @endif
                    </div>

                    @forelse($sec->questions as $qIdx => $q)
                        <div class="question-card p-4 rounded-4 border mb-4 bg-white" id="q_preview_{{ $q->id }}">
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle fs-6 p-2" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;">
                                    {{ $qIdx + 1 }}
                                </span>
                                <div class="flex-grow-1">
                                    <h6 class="fw-bold text-dark mb-1">
                                        {{ $q->question_text }}
                                        @if($q->is_required) <span class="text-danger">*</span> @endif
                                    </h6>
                                    @if($q->help_text)
                                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> {{ $q->help_text }}</small>
                                    @endif
                                </div>
                            </div>

                            <!-- Input Renderer for Preview -->
                            <div class="ps-4">
                                @if(in_array($q->type, ['single_choice', 'likert', 'yes_no']))
                                    <div class="d-flex flex-column gap-2">
                                        @forelse($q->options as $opt)
                                            <div class="form-check p-3 rounded-3 border option-hover" style="cursor:pointer;">
                                                <input class="form-check-input" type="radio" name="preview_answers[{{ $q->id }}]" id="prev_opt_{{ $opt->id }}" value="{{ $opt->value }}">
                                                <label class="form-check-label w-100" for="prev_opt_{{ $opt->id }}" style="cursor:pointer;">
                                                    {{ $opt->option_text }}
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-muted small italic">No options defined for this question.</p>
                                        @endforelse
                                    </div>

                                @elseif($q->type === 'multiple_choice')
                                    <div class="d-flex flex-column gap-2">
                                        @forelse($q->options as $opt)
                                            <div class="form-check p-3 rounded-3 border option-hover" style="cursor:pointer;">
                                                <input class="form-check-input" type="checkbox" name="preview_answers[{{ $q->id }}][]" id="prev_opt_{{ $opt->id }}" value="{{ $opt->value }}">
                                                <label class="form-check-label w-100" for="prev_opt_{{ $opt->id }}" style="cursor:pointer;">
                                                    {{ $opt->option_text }}
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-muted small italic">No options defined for this question.</p>
                                        @endforelse
                                    </div>

                                @elseif($q->type === 'rating')
                                    <div class="d-flex gap-2">
                                        @for($r = 1; $r <= 5; $r++)
                                            <input type="radio" class="btn-check" name="preview_answers[{{ $q->id }}]" id="prev_rate_{{ $q->id }}_{{ $r }}" value="{{ $r }}">
                                            <label class="btn btn-outline-warning text-dark flex-fill py-3 fw-bold" for="prev_rate_{{ $q->id }}_{{ $r }}">
                                                ★ {{ $r }}
                                            </label>
                                        @endfor
                                    </div>

                                @elseif($q->type === 'dropdown')
                                    <select class="form-select form-select-lg" name="preview_answers[{{ $q->id }}]">
                                        <option value="">Select an Option</option>
                                        @foreach($q->options as $opt)
                                            <option value="{{ $opt->value }}">{{ $opt->option_text }}</option>
                                        @endforeach
                                    </select>

                                @elseif($q->type === 'short_text')
                                    <input type="text" class="form-control form-control-lg" name="preview_answers[{{ $q->id }}]" placeholder="Type your response here...">

                                @elseif($q->type === 'long_text')
                                    <textarea class="form-control" name="preview_answers[{{ $q->id }}]" rows="4" placeholder="Write detailed answer..."></textarea>

                                @elseif($q->type === 'voice')
                                    <div class="voice-recorder-box p-4 bg-light rounded-4 border text-center">
                                        <i class="bi bi-mic-fill fs-1 text-primary mb-2"></i>
                                        <p class="small text-secondary mb-2">Voice Input Preview Mode</p>
                                        <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="alert('Voice recording test in preview mode: Audio input simulates successfully.')">
                                            <i class="bi bi-record-circle me-1"></i> Test Mic Input
                                        </button>
                                    </div>

                                @else
                                    <input type="text" class="form-control" name="preview_answers[{{ $q->id }}]" placeholder="Your answer...">
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No questions added to this section yet.
                        </div>
                    @endforelse

                    <!-- Section Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        @if($secIdx > 0)
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="switchPreviewSectionIndex({{ $secIdx - 1 }})">
                                <i class="bi bi-arrow-left me-1"></i> Previous Section
                            </button>
                        @else
                            <div></div>
                        @endif

                        @if($secIdx < $sections->count() - 1)
                            <button type="button" class="btn btn-uni-primary px-4" onclick="switchPreviewSectionIndex({{ $secIdx + 1 }})">
                                Next Section <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        @else
                            <button type="submit" class="btn btn-success px-4 fw-bold">
                                <i class="bi bi-check-circle me-1"></i> Test Complete Survey
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function switchPreviewSection(targetId, btnElement) {
        document.querySelectorAll('.section-card-container').forEach(card => {
            card.classList.add('d-none');
        });
        const targetCard = document.getElementById(targetId);
        if (targetCard) {
            targetCard.classList.remove('d-none');
        }

        document.querySelectorAll('.section-tab-btn').forEach(btn => {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-light', 'border', 'text-secondary');
        });
        if (btnElement) {
            btnElement.classList.remove('btn-light', 'border', 'text-secondary');
            btnElement.classList.add('btn-primary');
        }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function switchPreviewSectionIndex(index) {
        const buttons = document.querySelectorAll('.section-tab-btn');
        if (buttons[index]) {
            buttons[index].click();
        }
    }

    function handlePreviewSubmit() {
        alert('Preview Test Completed successfully! (No response data was saved as this is in Admin Preview mode)');
    }
</script>
@endpush
