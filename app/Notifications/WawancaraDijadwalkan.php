<?php

namespace App\Notifications;

use App\Models\Application;
use App\Models\ApplicationStage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WawancaraDijadwalkan extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Application $application,
        public readonly ApplicationStage $stage,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $this->application->loadMissing(['vacancy', 'candidate']);

        return [
            'application_id' => $this->application->id,
            'vacancy_id' => $this->application->vacancy_id,
            'judul_posisi' => $this->application->vacancy->judul_posisi,
            'nama_kandidat' => $this->application->candidate->nama_lengkap,
            'jadwal_interview' => $this->stage->jadwal_interview->toDateTimeString(),
            'lokasi_interview' => $this->stage->lokasi_interview,
        ];
    }
}
