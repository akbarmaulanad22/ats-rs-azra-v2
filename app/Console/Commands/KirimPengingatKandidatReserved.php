<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\EmailNotificationService;
use Illuminate\Console\Command;

class KirimPengingatKandidatReserved extends Command
{
    protected $signature = 'email:kirim-pengingat-reserved {--days=3 : Hari sebelum tenggat untuk mengirim pengingat}';

    protected $description = 'Kirim pengingat ke Admin HR untuk lowongan yang akan kadaluarsa dengan kandidat reserved';

    public function __construct(private readonly EmailNotificationService $emailNotificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $targetDate = now()->addDays($days)->toDateString();

        $vacancies = Vacancy::whereHas('applications', function ($query) {
            $query->whereHas('stages', function ($q) {
                $q->where('status', ApplicationStageStatus::Aktif->value);
            });
        })
            ->whereDate('tenggat_lamaran', $targetDate)
            ->get();

        if ($vacancies->isEmpty()) {
            $this->info("Tidak ada lowongan dengan kandidat reserved yang tenggat pada {$targetDate}.");

            return self::SUCCESS;
        }

        $hrAdmins = User::where('role', Role::HrAdmin->value)
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        if ($hrAdmins->isEmpty()) {
            $this->warn('Tidak ada Admin HR aktif dengan email ditemukan.');

            return self::SUCCESS;
        }

        foreach ($vacancies as $vacancy) {
            foreach ($hrAdmins as $hrAdmin) {
                $this->emailNotificationService->dispatch('pengingat_kandidat_reserved', $hrAdmin->email, [
                    'judul_lowongan' => $vacancy->judul_posisi,
                    'tanggal_tenggat' => $vacancy->tenggat_lamaran->format('d/m/Y'),
                ]);
            }

            $this->info("Pengingat terkirim untuk lowongan: {$vacancy->judul_posisi}");
        }

        return self::SUCCESS;
    }
}
