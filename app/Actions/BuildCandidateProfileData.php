<?php

namespace App\Actions;

use App\Models\Application;

class BuildCandidateProfileData
{
    public function execute(Application $application): Application
    {
        $application->load([
            'vacancy.unit',
            'candidate.siblings',
            'candidate.spouses',
            'candidate.children',
            'candidate.formalEducations',
            'candidate.achievements',
            'candidate.informalEducations',
            'candidate.languageSkills',
            'candidate.organizationExperiences',
            'candidate.workExperiences',
            'stages',
            'testSubmission.answers',
            'discSubmission.result',
            'mbtiSubmission.result',
            'interviewResults.ratings',
            'interviewResults.interviewer',
            'interviewResults.applicationStage',
            'offeringLetter',
            'mcuResult',
            'onboardingResult',
            'references',
            'socialMediaAccounts',
        ]);

        return $application;
    }
}
