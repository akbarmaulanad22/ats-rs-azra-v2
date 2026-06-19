<?php

namespace App\Services;

use App\Enums\VacancyStatus;
use App\Models\JobTemplate;
use App\Models\Vacancy;
use App\Models\VacancyTest;
use App\Models\VacancyTestSnapshot;
use App\Models\WorkflowTemplateSnapshot;
use Illuminate\Support\Facades\DB;

class VacancyPublisher
{
    /**
     * Publish a Vacancy from a JobTemplate, snapshotting the template's content
     * and pipeline config so later template edits never alter this period.
     *
     * @param  array{jumlah_posisi:int, tenggat_lamaran:string, flyer_path:string, status?:VacancyStatus|string, kualifikasi?:string|null}  $attributes
     */
    public function publish(JobTemplate $jobTemplate, array $attributes): Vacancy
    {
        $jobTemplate->loadMissing([
            'workflowTemplate.stages',
            'jobTemplateTest.questions',
            'interviewTemplates',
        ]);

        return DB::transaction(function () use ($jobTemplate, $attributes): Vacancy {
            $snapshot = WorkflowTemplateSnapshot::createFromTemplate($jobTemplate->workflowTemplate);

            $vacancy = Vacancy::create([
                'job_template_id' => $jobTemplate->id,
                'judul_posisi' => $jobTemplate->judul_posisi,
                'unit_id' => $jobTemplate->unit_id,
                'workflow_template_snapshot_id' => $snapshot->id,
                'jenis_pekerjaan' => $jobTemplate->jenis_pekerjaan,
                'deskripsi_pekerjaan' => $jobTemplate->deskripsi_pekerjaan,
                'kualifikasi' => $attributes['kualifikasi'] ?? $jobTemplate->kualifikasi,
                'flyer_path' => $attributes['flyer_path'],
                'jumlah_posisi' => $attributes['jumlah_posisi'],
                'tenggat_lamaran' => $attributes['tenggat_lamaran'],
                'status' => $attributes['status'] ?? VacancyStatus::Draft,
            ]);

            if ($jobTemplate->jobTemplateTest) {
                $this->cloneTest($jobTemplate, $vacancy);
            }

            $jobTemplate->interviewTemplates->each(function ($template) use ($vacancy) {
                $vacancy->interviewTemplates()->attach($template->id, [
                    'stage_key' => $template->pivot->stage_key,
                ]);
            });

            return $vacancy;
        });
    }

    /**
     * Clone the template's default competency test onto the Vacancy and freeze
     * its snapshot so candidates sit a fixed test for this period.
     */
    private function cloneTest(JobTemplate $jobTemplate, Vacancy $vacancy): void
    {
        $templateTest = $jobTemplate->jobTemplateTest;

        $vacancyTest = VacancyTest::create([
            'vacancy_id' => $vacancy->id,
            'batas_waktu_menit' => $templateTest->batas_waktu_menit,
        ]);

        $sync = $templateTest->questions->mapWithKeys(fn ($question) => [
            $question->id => ['urutan' => $question->pivot->urutan],
        ])->all();

        $vacancyTest->questions()->sync($sync);

        VacancyTestSnapshot::createFromVacancyTest($vacancyTest->fresh());
    }
}
