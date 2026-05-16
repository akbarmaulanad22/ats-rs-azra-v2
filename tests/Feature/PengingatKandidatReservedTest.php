<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\User;
use App\Models\Vacancy;
use App\Notifications\PengingatKandidatReserved;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PengingatKandidatReservedTest extends TestCase
{
    use RefreshDatabase;

    private function vacancyApproachingDeadline(int $daysFromNow = 2): Vacancy
    {
        return Vacancy::factory()->published()->create([
            'tenggat_lamaran' => now()->addDays($daysFromNow)->toDateString(),
        ]);
    }

    private function attachReservedStage(Vacancy $vacancy): void
    {
        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        ApplicationStage::factory()->reserved()->create(['application_id' => $application->id]);
    }

    public function test_sends_notification_to_active_hr_admins(): void
    {
        Notification::fake();

        $vacancy = $this->vacancyApproachingDeadline();
        $this->attachReservedStage($vacancy);

        $hrAdmin1 = User::factory()->hrAdmin()->create();
        $hrAdmin2 = User::factory()->hrAdmin()->create();

        $this->artisan('notifikasi:pengingat-kandidat-reserved')->assertSuccessful();

        Notification::assertSentTo($hrAdmin1, PengingatKandidatReserved::class, function ($notification) use ($vacancy) {
            return $notification->vacancy->id === $vacancy->id;
        });

        Notification::assertSentTo($hrAdmin2, PengingatKandidatReserved::class, function ($notification) use ($vacancy) {
            return $notification->vacancy->id === $vacancy->id;
        });
    }

    public function test_no_notification_when_no_vacancy_approaching(): void
    {
        Notification::fake();

        Vacancy::factory()->published()->create([
            'tenggat_lamaran' => now()->addDays(10)->toDateString(),
        ]);

        User::factory()->hrAdmin()->create();

        $this->artisan('notifikasi:pengingat-kandidat-reserved')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_no_notification_when_no_reserved_candidates(): void
    {
        Notification::fake();

        $vacancy = $this->vacancyApproachingDeadline();

        $application = Application::factory()->create(['vacancy_id' => $vacancy->id]);
        ApplicationStage::factory()->aktif()->create(['application_id' => $application->id]);

        User::factory()->hrAdmin()->create();

        $this->artisan('notifikasi:pengingat-kandidat-reserved')->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_inactive_hr_admin_not_notified(): void
    {
        Notification::fake();

        $vacancy = $this->vacancyApproachingDeadline();
        $this->attachReservedStage($vacancy);

        $inactive = User::factory()->hrAdmin()->inactive()->create();

        $this->artisan('notifikasi:pengingat-kandidat-reserved')->assertSuccessful();

        Notification::assertNotSentTo($inactive, PengingatKandidatReserved::class);
    }

    public function test_respects_custom_hari_option(): void
    {
        Notification::fake();

        $vacancy = Vacancy::factory()->published()->create([
            'tenggat_lamaran' => now()->addDays(5)->toDateString(),
        ]);
        $this->attachReservedStage($vacancy);

        $hrAdmin = User::factory()->hrAdmin()->create();

        $this->artisan('notifikasi:pengingat-kandidat-reserved', ['--hari' => 7])->assertSuccessful();

        Notification::assertSentTo($hrAdmin, PengingatKandidatReserved::class);
    }

    public function test_does_not_send_duplicate_notification_for_same_vacancy(): void
    {
        Notification::fake();

        $vacancy = $this->vacancyApproachingDeadline();
        $this->attachReservedStage($vacancy);

        $hrAdmin = User::factory()->hrAdmin()->create();

        // Simulate a previously sent notification for this vacancy
        DB::table('notifications')->insert([
            'id' => Str::uuid(),
            'type' => PengingatKandidatReserved::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $hrAdmin->id,
            'data' => json_encode(['vacancy_id' => $vacancy->id]),
            'read_at' => null,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $this->artisan('notifikasi:pengingat-kandidat-reserved')->assertSuccessful();

        Notification::assertNotSentTo($hrAdmin, PengingatKandidatReserved::class);
    }

    public function test_notification_data_contains_vacancy_info(): void
    {
        Notification::fake();

        $vacancy = $this->vacancyApproachingDeadline();
        $this->attachReservedStage($vacancy);

        $hrAdmin = User::factory()->hrAdmin()->create();

        $this->artisan('notifikasi:pengingat-kandidat-reserved')->assertSuccessful();

        Notification::assertSentTo($hrAdmin, PengingatKandidatReserved::class, function ($notification) use ($vacancy) {
            $data = $notification->toArray($notification->vacancy);

            return $data['vacancy_id'] === $vacancy->id
                && $data['judul_posisi'] === $vacancy->judul_posisi
                && $data['tenggat_lamaran'] === $vacancy->tenggat_lamaran->toDateString();
        });
    }
}
