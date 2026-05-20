<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('email_templates')
            ->where('key', 'surat_penawaran')
            ->update([
                'isi' => "Yth. {nama_kandidat},\n\nSelamat! Kami dengan bangga menawarkan posisi {jabatan_ditawarkan} di RS Azra kepada Anda.\n\nDetail Penawaran:\nPosisi: {jabatan_ditawarkan}\nGaji: {gaji}\nTanggal Mulai: {tanggal_mulai}\n\nSilakan klik salah satu tautan berikut untuk merespon penawaran ini:\n\nTerima Penawaran:\n{link_terima}\n\nTolak Penawaran:\n{link_tolak}\n\nTautan ini berlaku selama 7 hari. Jika ada pertanyaan, jangan ragu untuk menghubungi tim HR kami.\n\nSalam,\nTim HR RS Azra",
            ]);
    }

    public function down(): void
    {
        DB::table('email_templates')
            ->where('key', 'surat_penawaran')
            ->update([
                'isi' => "Yth. {nama_kandidat},\n\nSelamat! Kami dengan bangga menawarkan posisi {jabatan_ditawarkan} di RS Azra kepada Anda.\n\nDetail Penawaran:\nPosisi: {jabatan_ditawarkan}\nGaji: {gaji}\nTanggal Mulai: {tanggal_mulai}\n\nJika ada pertanyaan, jangan ragu untuk menghubungi tim HR kami.\n\nSalam,\nTim HR RS Azra",
            ]);
    }
};
