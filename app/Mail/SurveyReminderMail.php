<?php

namespace App\Mail;

use App\Models\RespondentSurvey;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SurveyReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $respondentSurvey;
    public $respondent;
    public $survey;
    public $category;
    public $university;
    public $resumeUrl;
    public $percentage;

    public function __construct(RespondentSurvey $respondentSurvey)
    {
        $this->respondentSurvey = $respondentSurvey->loadMissing(['respondent.university.parent', 'survey.university', 'category', 'currentSection']);
        $this->respondent = $this->respondentSurvey->respondent;
        $this->survey = $this->respondentSurvey->survey;
        $this->category = $this->respondentSurvey->category;
        $this->university = $this->respondent?->university ?? $this->survey?->university;
        $this->percentage = round((float)$this->respondentSurvey->completion_percentage, 1);

        $targetSectionId = $this->respondentSurvey->current_section_id;
        $this->resumeUrl = route('survey.take', array_filter([
            'token' => $this->respondent->token,
            'section' => $targetSectionId,
        ]));
    }

    public function envelope(): Envelope
    {
        $surveyTitle = $this->survey ? $this->survey->title : 'State Skill Insight Survey';
        return new Envelope(
            subject: 'Reminder: Complete Your Survey Session (' . $this->percentage . '% Completed) - ' . $surveyTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.survey_reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
