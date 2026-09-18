<?php

namespace App\Services;

use App\Models\CompositeIndex;
use App\Models\PsychometricDimension;
use App\Models\RespondentScore;
use App\Models\RespondentSurvey;

class ScoringService
{
    /**
     * Calculate and persist scores for a respondent survey.
     */
    public function calculateAndPersistScores(RespondentSurvey $respondentSurvey): array
    {
        $respondentSurvey->load(['category.questions.options', 'responses.selectedOptions']);
        $responses = $respondentSurvey->responses;
        $category = $respondentSurvey->category;
        $survey = $respondentSurvey->survey;

        // 1. Calculate overall score
        $totalScore = 0;
        $scoredCount = 0;

        foreach ($responses as $response) {
            if ($response->score !== null) {
                $totalScore += $response->score;
                $scoredCount++;
            }
        }

        $overallPercent = ($scoredCount > 0) ? min(100, max(0, round(($totalScore / ($scoredCount * 20)) * 100, 2))) : 75.00;

        $interpretationBand = match (true) {
            $overallPercent >= 80 => 'High Readiness',
            $overallPercent >= 60 => 'Moderate Readiness',
            default => 'Intervention Needed',
        };

        // 2. Clear old scores and save overall
        RespondentScore::where('respondent_survey_id', $respondentSurvey->id)->delete();

        $overallScoreRecord = RespondentScore::create([
            'respondent_survey_id' => $respondentSurvey->id,
            'score' => $overallPercent,
            'max_possible' => 100.00,
            'interpretation_band' => $interpretationBand,
            'calculated_at' => now(),
        ]);

        // 3. Calculate Psychometric Dimension Scores
        $dimensions = PsychometricDimension::where('survey_id', $survey->id)
            ->where('category_code', $category->code)
            ->get();

        $dimensionScores = [];
        foreach ($dimensions as $dim) {
            $dimQuestions = $category->questions->where('dimension_id', $dim->id);
            $dimTotal = 0;
            $dimCount = 0;

            foreach ($dimQuestions as $q) {
                $resp = $responses->firstWhere('question_id', $q->id);
                if ($resp && $resp->score !== null) {
                    $dimTotal += $resp->score;
                    $dimCount++;
                }
            }

            $dimVal = ($dimCount > 0) ? round(($dimTotal / ($dimCount * 20)) * 100, 2) : $overallPercent;
            $dimVal = min(100, max(0, $dimVal));

            RespondentScore::create([
                'respondent_survey_id' => $respondentSurvey->id,
                'dimension_id' => $dim->id,
                'score' => $dimVal,
                'max_possible' => 100.00,
                'interpretation_band' => ($dimVal >= 75) ? 'Proficient' : (($dimVal >= 55) ? 'Developing' : 'Critical Gap'),
                'calculated_at' => now(),
            ]);

            $dimensionScores[$dim->code] = $dimVal;
        }

        // 4. Calculate Composite GRI Index
        $griIndex = CompositeIndex::where('survey_id', $survey->id)->where('code', 'GRI')->first();
        if ($griIndex) {
            RespondentScore::create([
                'respondent_survey_id' => $respondentSurvey->id,
                'composite_index_id' => $griIndex->id,
                'score' => $overallPercent,
                'max_possible' => 100.00,
                'interpretation_band' => $interpretationBand,
                'calculated_at' => now(),
            ]);
        }

        return [
            'overall_score' => $overallPercent,
            'interpretation_band' => $interpretationBand,
            'dimensions' => $dimensionScores,
        ];
    }
}
