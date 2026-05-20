<?php

namespace App\Notifications;

use App\Models\OfferingLetter;
use Illuminate\Notifications\Notification;

class PenawaranDirespon extends Notification
{
    public function __construct(public readonly OfferingLetter $offeringLetter) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $this->offeringLetter->loadMissing(['application.vacancy', 'application.candidate']);

        return [
            'application_id' => $this->offeringLetter->application_id,
            'vacancy_id' => $this->offeringLetter->application->vacancy_id,
            'judul_posisi' => $this->offeringLetter->application->vacancy->judul_posisi,
            'nama_kandidat' => $this->offeringLetter->application->candidate->nama_lengkap,
            'status' => $this->offeringLetter->status->value,
            'responded_at' => $this->offeringLetter->responded_at->toDateTimeString(),
        ];
    }
}
