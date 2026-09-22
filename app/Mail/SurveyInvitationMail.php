<?php

namespace App\Mail;

use App\Models\SurveyInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SurveyInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $surveyUrl;

    public function __construct(SurveyInvitation $invitation)
    {
        $this->invitation = $invitation->loadMissing('survey');
        $this->surveyUrl = url('/survey/start/' . $invitation->token);
    }

    public function envelope(): Envelope
    {
        $surveyTitle = $this->invitation->survey ? $this->invitation->survey->title : 'State Skill Insight Survey';
        return new Envelope(
            subject: 'Survey Invitation: ' . $surveyTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.survey_invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
