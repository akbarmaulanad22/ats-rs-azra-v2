<?php

namespace Tests\Feature;

use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCareerPageTest extends TestCase
{
    use RefreshDatabase;

    private function seedStages(): void
    {
        $this->artisan('db:seed', ['--class' => 'StageSeeder']);
    }

    // ── Career index ──────────────────────────────────────────────────────────

    public function test_public_career_page_is_accessible_without_login(): void
    {
        $response = $this->get(route('karier.index'));

        $response->assertStatus(200);
        $response->assertViewIs('career.index');
    }

    public function test_career_page_shows_published_non_expired_vacancies(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create(['judul_posisi' => 'Perawat ICU']);

        $response = $this->get(route('karier.index'));

        $response->assertSee('Perawat ICU');
    }

    public function test_career_page_hides_draft_vacancies(): void
    {
        $this->seedStages();
        Vacancy::factory()->create([
            'judul_posisi' => 'Dokter Tersembunyi',
            'status' => VacancyStatus::Draft,
        ]);

        $response = $this->get(route('karier.index'));

        $response->assertDontSee('Dokter Tersembunyi');
    }

    public function test_career_page_hides_closed_vacancies(): void
    {
        $this->seedStages();
        Vacancy::factory()->closed()->create(['judul_posisi' => 'Posisi Ditutup']);

        $response = $this->get(route('karier.index'));

        $response->assertDontSee('Posisi Ditutup');
    }

    public function test_career_page_hides_expired_published_vacancies(): void
    {
        $this->seedStages();
        Vacancy::factory()->expired()->create(['judul_posisi' => 'Posisi Kedaluwarsa']);

        $response = $this->get(route('karier.index'));

        $response->assertDontSee('Posisi Kedaluwarsa');
    }

    // ── Career show ───────────────────────────────────────────────────────────

    public function test_vacancy_detail_page_accessible_for_published_vacancy(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create(['judul_posisi' => 'Bidan Senior']);

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(200);
        $response->assertViewIs('career.show');
        $response->assertSee('Bidan Senior');
    }

    public function test_vacancy_detail_shows_apply_button(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->published()->create();

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertSee('Lamar Sekarang');
    }

    public function test_draft_vacancy_returns_404_on_detail_page(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->create(['status' => VacancyStatus::Draft]);

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(404);
    }

    public function test_closed_vacancy_returns_404_on_detail_page(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->closed()->create();

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(404);
    }

    public function test_expired_vacancy_returns_404_on_detail_page(): void
    {
        $this->seedStages();
        $vacancy = Vacancy::factory()->expired()->create();

        $response = $this->get(route('karier.show', $vacancy));

        $response->assertStatus(404);
    }
}
