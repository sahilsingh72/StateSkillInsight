<?php

namespace App\Jobs;

use App\Mail\SurveyReminderMail;
use App\Models\RespondentSurvey;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendIncompleteSurveyReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $respondentSurveyId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $respondentSurveyId)
    {
        $this->respondentSurveyId = $respondentSurveyId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $survey = RespondentSurvey::with(['respondent', 'survey', 'category', 'currentSection'])
            ->find($this->respondentSurveyId);

        if (!$survey) {
            return;
        }

        // Check if survey is still incomplete and reminder not sent yet
        if ($survey->status !== 'completed' && (float)$survey->completion_percentage < 100.0 && is_null($survey->reminder_sent_at)) {
            $email = $survey->respondent?->email;

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($email)->send(new SurveyReminderMail($survey));
                    $survey->update([
                        'reminder_sent_at' => now(),
                    ]);
                    Log::info("Sent incomplete survey reminder to: {$email} (Survey ID: {$survey->id}, Progress: {$survey->completion_percentage}%)");
                } catch (\Throwable $e) {
                    Log::error("Failed to send incomplete survey reminder to {$email}: " . $e->getMessage());
                }
            }
        }
    }
}
