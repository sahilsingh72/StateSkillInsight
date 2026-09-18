<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Survey;
use App\Models\SurveyCategory;
use App\Models\SurveySection;
use Illuminate\Support\Str;

class SurveyService
{
    /**
     * Deep clone a survey including categories, sections, questions, and options.
     */
    public function cloneSurvey(Survey $originalSurvey, string $newTitle): Survey
    {
        $newSurvey = $originalSurvey->replicate();
        $newSurvey->title = $newTitle;
        $newSurvey->status = 'draft';
        $newSurvey->version = (float)$originalSurvey->version + 0.1;
        $newSurvey->save();

        foreach ($originalSurvey->categories as $cat) {
            $newCat = $cat->replicate();
            $newCat->survey_id = $newSurvey->id;
            $newCat->save();

            foreach ($cat->sections as $sec) {
                $newSec = $sec->replicate();
                $newSec->category_id = $newCat->id;
                $newSec->save();

                foreach ($sec->questions as $q) {
                    $newQ = $q->replicate();
                    $newQ->section_id = $newSec->id;
                    $newQ->save();

                    foreach ($q->options as $opt) {
                        $newOpt = $opt->replicate();
                        $newOpt->question_id = $newQ->id;
                        $newOpt->save();
                    }
                }
            }
        }

        return $newSurvey;
    }
}
