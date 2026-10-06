<?php

namespace App\Mail;

use App\Models\RespondentSurvey;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SurveyCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $respondentSurvey;
    public $respondent;
    public $survey;
    public $category;
    public $university;

    public function __construct(RespondentSurvey $respondentSurvey)
    {
        $this->respondentSurvey = $respondentSurvey->loadMissing(['respondent.university.parent', 'survey.university', 'category']);
        $this->respondent = $this->respondentSurvey->respondent;
        $this->survey = $this->respondentSurvey->survey;
        $this->category = $this->respondentSurvey->category;
        $this->university = $this->respondent?->university ?? $this->survey?->university;
    }

    public function envelope(): Envelope
    {
        $surveyTitle = $this->survey ? $this->survey->title : 'State Skill Insight Survey';
        return new Envelope(
            subject: 'Thank You: Survey Completed Successfully (' . $surveyTitle . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.survey_completed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
