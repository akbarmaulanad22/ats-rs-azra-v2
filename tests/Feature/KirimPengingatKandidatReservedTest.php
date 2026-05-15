<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Mail\TemplatedMail;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class KirimPengingatKandidatReservedTest extends TestCase
{
    use RefreshDatabase;

    private function seedTemplate(): void
    {
        EmailTemplate::unguarded(fn () => EmailTemplate::create([
            'key' => 'pengingat_kandidat_reserved',
            'deskripsi' => 'Pengingat reserved',
            'subjek' => 'Pengingat: {judul_lowongan}',
            'isi' => 'Lowongan {judul_lowongan} tenggat {tanggal_tenggat}.',
        ]));
    }

    public function test_sends_reminder_to_hr_admins_for_vacancies_expiring_in_n_days(): void
    {
        Mail::fake();
        $this->seedTemplate();

        $targetDate = now()->addDays(3)->toDateString();
        $hrAdmin = User::factory()->create([
            'role' => Role::HrAdmin,
            'is_active' => true,
            'email' => 'admin@rsazra.id',
        ]);

        $vacancy = Vacancy::factory()->create(['tenggat_lamaran' => $targetDate]);
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        ApplicationStage::factory()->aktif()->create(['application_id' => $application->id]);

        $this->artisan('email:kirim-pengingat-reserved')->assertSuccessful();

        Mail::assertQueued(TemplatedMail::class, fn ($mail) => $mail->hasTo('admin@rsazra.id')
            && $mail->key === 'pengingat_kandidat_reserved');
    }

    public function test_no_email_sent_when_no_reserved_candidates(): void
    {
        Mail::fake();
        $this->seedTemplate();

        $targetDate = now()->addDays(3)->toDateString();
        User::factory()->create(['role' => Role::HrAdmin, 'is_active' => true, 'email' => 'admin@rsazra.id']);

        $vacancy = Vacancy::factory()->create(['tenggat_lamaran' => $targetDate]);
        Application::factory()->create(['vacancy_id' => $vacancy->id]);

        $this->artisan('email:kirim-pengingat-reserved')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_no_email_sent_when_no_vacancy_on_target_date(): void
    {
        Mail::fake();
        $this->seedTemplate();

        User::factory()->create(['role' => Role::HrAdmin, 'is_active' => true]);
        Vacancy::factory()->create(['tenggat_lamaran' => now()->addDays(10)->toDateString()]);

        $this->artisan('email:kirim-pengingat-reserved')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_custom_days_option_works(): void
    {
        Mail::fake();
        $this->seedTemplate();

        $targetDate = now()->addDays(5)->toDateString();
        $hrAdmin = User::factory()->create(['role' => Role::HrAdmin, 'is_active' => true, 'email' => 'admin@rsazra.id']);

        $vacancy = Vacancy::factory()->create(['tenggat_lamaran' => $targetDate]);
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        ApplicationStage::factory()->aktif()->create(['application_id' => $application->id]);

        $this->artisan('email:kirim-pengingat-reserved --days=5')->assertSuccessful();

        Mail::assertQueued(TemplatedMail::class, fn ($mail) => $mail->hasTo('admin@rsazra.id'));
    }

    public function test_sends_to_multiple_hr_admins(): void
    {
        Mail::fake();
        $this->seedTemplate();

        $targetDate = now()->addDays(3)->toDateString();
        User::factory()->create(['role' => Role::HrAdmin, 'is_active' => true, 'email' => 'admin1@rsazra.id']);
        User::factory()->create(['role' => Role::HrAdmin, 'is_active' => true, 'email' => 'admin2@rsazra.id']);

        $vacancy = Vacancy::factory()->create(['tenggat_lamaran' => $targetDate]);
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        ApplicationStage::factory()->aktif()->create(['application_id' => $application->id]);

        $this->artisan('email:kirim-pengingat-reserved')->assertSuccessful();

        Mail::assertQueued(TemplatedMail::class, fn ($mail) => $mail->hasTo('admin1@rsazra.id'));
        Mail::assertQueued(TemplatedMail::class, fn ($mail) => $mail->hasTo('admin2@rsazra.id'));
    }
}
