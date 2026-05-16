<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStageStatus;
use App\Enums\Role;
use App\Enums\VacancyStatus;
use App\Models\User;
use App\Models\Vacancy;
use App\Notifications\PengingatKandidatReserved;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class KirimPengingatKandidatReserved extends Command
{
    protected $signature = 'notifikasi:pengingat-kandidat-reserved {--hari=3 : Jumlah hari sebelum tenggat}';

    protected $description = 'Kirim notifikasi pengingat ke HR Admin untuk lowongan dengan kandidat ditangguhkan yang mendekati tenggat';

    public function handle(): int
    {
        $hari = (int) $this->option('hari');

        $lowonganList = Vacancy::query()
            ->where('status', VacancyStatus::Published)
            ->whereBetween('tenggat_lamaran', [now()->toDateString(), now()->addDays($hari)->toDateString()])
            ->whereHas('applications', fn ($q) => $q->whereHas('stages', fn ($sq) => $sq->where('status', ApplicationStageStatus::Reserved)))
            ->get();

        if ($lowonganList->isEmpty()) {
            $this->info('Tidak ada lowongan yang perlu dikirim notifikasi.');

            return Command::SUCCESS;
        }

        $hrAdmins = User::where('role', Role::HrAdmin)
            ->where('is_active', true)
            ->get();

        if ($hrAdmins->isEmpty()) {
            $this->warn('Tidak ada HR Admin aktif.');

            return Command::SUCCESS;
        }

        $terkirim = 0;

        foreach ($lowonganList as $lowongan) {
            $sudahDikirim = DB::table('notifications')
                ->where('type', PengingatKandidatReserved::class)
                ->where('data->vacancy_id', $lowongan->id)
                ->exists();

            if ($sudahDikirim) {
                continue;
            }

            Notification::send($hrAdmins, new PengingatKandidatReserved($lowongan));
            $terkirim++;
        }

        $this->info("Notifikasi terkirim: {$terkirim} lowongan ke {$hrAdmins->count()} HR Admin.");

        return Command::SUCCESS;
    }
}
