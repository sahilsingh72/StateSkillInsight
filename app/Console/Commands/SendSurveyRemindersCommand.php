<?php

namespace App\Console\Commands;

use App\Mail\SurveyReminderMail;
use App\Models\RespondentSurvey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSurveyRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'survey:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automatic email reminders to respondents who started a survey 15+ minutes ago and have not reached 100% completion.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffTime = now()->subMinutes(15);

        $incompleteSurveys = RespondentSurvey::where('status', '!=', 'completed')
            ->where('completion_percentage', '<', 100)
            ->whereNull('reminder_sent_at')
            ->where('created_at', '<=', $cutoffTime)
            ->whereHas('respondent', function ($q) {
                $q->whereNotNull('email')->where('email', '!=', '');
            })
            ->with(['respondent', 'survey', 'category', 'currentSection'])
            ->get();

        $this->info("Found {$incompleteSurveys->count()} incomplete survey session(s) pending 15-minute reminders.");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($incompleteSurveys as $survey) {
            $email = $survey->respondent?->email;

            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($email)->send(new SurveyReminderMail($survey));
                    $survey->update([
                        'reminder_sent_at' => now(),
                    ]);
                    $this->line("Sent reminder to: {$email} ({$survey->completion_percentage}% completed)");
                    Log::info("Sent incomplete survey reminder to: {$email} (Survey ID: {$survey->id}, Progress: {$survey->completion_percentage}%)");
                    $sentCount++;
                } catch (\Throwable $e) {
                    $this->error("Failed to send reminder to {$email}: " . $e->getMessage());
                    Log::error("Failed to send incomplete survey reminder to {$email}: " . $e->getMessage());
                    $failedCount++;
                }
            }
        }

        $this->info("Completed. Sent: {$sentCount}, Failed: {$failedCount}.");
        return 0;
    }
}
