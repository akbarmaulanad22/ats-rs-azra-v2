<?php

namespace Tests\Feature;

use App\Enums\VacancyStatus;
use App\Models\InterviewTemplate;
use App\Models\JobTemplate;
use App\Models\JobTemplateTest;
use App\Models\Question;
use App\Models\Stage;
use App\Models\VacancyTestSnapshot;
use App\Models\WorkflowTemplate;
use App\Models\WorkflowTemplateSnapshot;
use App\Services\VacancyPublisher;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyPublisherTest extends TestCase
{
    use RefreshDatabase;

    private function workflowTemplateWithStages(array $stageKeys = ['lamaran', 'tes_kompetensi', 'onboarding']): WorkflowTemplate
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);

        $template = WorkflowTemplate::factory()->create();
        Stage::whereIn('key', $stageKeys)->get()->each(function (Stage $stage, int $index) use ($template) {
            $template->stages()->attach($stage->id, ['position' => $index + 1]);
        });

        return $template->load('stages');
    }

    private function payload(): array
    {
        return [
            'jumlah_posisi' => 3,
            'tenggat_lamaran' => now()->addMonth()->format('Y-m-d'),
            'flyer_path' => 'flyers/test.jpg',
        ];
    }

    public function test_publish_snapshots_template_content_onto_vacancy(): void
    {
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowTemplateWithStages()->id,
        ]);

        $vacancy = app(VacancyPublisher::class)->publish($template, $this->payload());

        $this->assertSame($template->id, $vacancy->job_template_id);
        $this->assertSame($template->judul_posisi, $vacancy->judul_posisi);
        $this->assertSame($template->unit_id, $vacancy->unit_id);
        $this->assertSame($template->jenis_pekerjaan, $vacancy->jenis_pekerjaan);
        $this->assertSame($template->deskripsi_pekerjaan, $vacancy->deskripsi_pekerjaan);
        $this->assertSame($template->kualifikasi, $vacancy->kualifikasi);
        $this->assertSame(3, $vacancy->jumlah_posisi);
        $this->assertSame(VacancyStatus::Draft, $vacancy->status);
        $this->assertNotNull($vacancy->workflow_template_snapshot_id);
    }

    public function test_publish_freezes_a_fresh_workflow_snapshot(): void
    {
        $workflow = $this->workflowTemplateWithStages();
        $template = JobTemplate::factory()->create(['workflow_template_id' => $workflow->id]);

        $vacancy = app(VacancyPublisher::class)->publish($template, $this->payload());

        $snapshot = $vacancy->workflowTemplateSnapshot;
        $this->assertSame($workflow->id, $snapshot->workflow_template_id);
        $this->assertCount($workflow->stages->count(), $snapshot->stages);
    }

    public function test_per_period_kualifikasi_override_replaces_template_default(): void
    {
        $template = JobTemplate::factory()->create([
            'kualifikasi' => 'Default S1.',
            'workflow_template_id' => $this->workflowTemplateWithStages()->id,
        ]);

        $vacancy = app(VacancyPublisher::class)->publish($template, [
            ...$this->payload(),
            'kualifikasi' => 'Override D3.',
        ]);

        $this->assertSame('Override D3.', $vacancy->kualifikasi);
    }

    public function test_publish_clones_competency_test_and_freezes_snapshot(): void
    {
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowTemplateWithStages()->id,
        ]);
        $templateTest = JobTemplateTest::factory()->create([
            'job_template_id' => $template->id,
            'batas_waktu_menit' => 45,
        ]);
        $questions = Question::factory()->count(3)->create();
        $questions->each(fn (Question $q, int $i) => $templateTest->questions()->attach($q->id, ['urutan' => $i + 1]));

        $vacancy = app(VacancyPublisher::class)->publish($template, $this->payload());

        $vacancyTest = $vacancy->vacancyTest;
        $this->assertNotNull($vacancyTest);
        $this->assertSame(45, $vacancyTest->batas_waktu_menit);
        $this->assertSame($questions->pluck('id')->all(), $vacancyTest->questions->pluck('id')->all());

        $snapshot = VacancyTestSnapshot::where('vacancy_test_id', $vacancyTest->id)->first();
        $this->assertNotNull($snapshot);
        $this->assertCount(3, $snapshot->questions);
    }

    public function test_publish_copies_interview_templates_into_vacancy_pivot(): void
    {
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowTemplateWithStages()->id,
        ]);
        $interview = InterviewTemplate::factory()->create();
        $template->interviewTemplates()->attach($interview->id, ['stage_key' => 'wawancara_user']);

        $vacancy = app(VacancyPublisher::class)->publish($template, $this->payload());

        $this->assertCount(1, $vacancy->interviewTemplates);
        $this->assertSame('wawancara_user', $vacancy->interviewTemplates->first()->pivot->stage_key);
    }

    public function test_editing_template_after_publish_does_not_mutate_published_vacancy(): void
    {
        $template = JobTemplate::factory()->create([
            'judul_posisi' => 'Perawat ICU',
            'kualifikasi' => 'Original.',
            'workflow_template_id' => $this->workflowTemplateWithStages()->id,
        ]);
        $templateTest = JobTemplateTest::factory()->create([
            'job_template_id' => $template->id,
            'batas_waktu_menit' => 45,
        ]);
        $question = Question::factory()->create();
        $templateTest->questions()->attach($question->id, ['urutan' => 1]);

        $vacancy = app(VacancyPublisher::class)->publish($template, $this->payload());
        $snapshotMinutes = VacancyTestSnapshot::where('vacancy_test_id', $vacancy->vacancyTest->id)->value('batas_waktu_menit');

        $template->update(['judul_posisi' => 'CHANGED', 'kualifikasi' => 'CHANGED']);
        $templateTest->update(['batas_waktu_menit' => 120]);

        $vacancy->refresh();
        $this->assertSame('Perawat ICU', $vacancy->judul_posisi);
        $this->assertSame('Original.', $vacancy->kualifikasi);
        $this->assertSame(45, $vacancy->vacancyTest->batas_waktu_menit);
        $this->assertSame(45, $snapshotMinutes);
    }

    public function test_publish_is_atomic_rolls_back_on_failure(): void
    {
        $template = JobTemplate::factory()->create([
            'workflow_template_id' => $this->workflowTemplateWithStages()->id,
        ]);

        $payload = $this->payload();
        unset($payload['jumlah_posisi']);

        try {
            app(VacancyPublisher::class)->publish($template, $payload);
            $this->fail('Expected publish to throw on missing jumlah_posisi.');
        } catch (QueryException|\TypeError|\ErrorException $e) {
            // expected
        }

        $this->assertSame(0, $template->vacancies()->count());
        $this->assertSame(0, WorkflowTemplateSnapshot::count());
    }
}
