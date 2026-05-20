<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'lamaran_diterima',
                'deskripsi' => 'Dikirim ke kandidat saat lamaran berhasil diterima',
                'subjek' => 'Konfirmasi Penerimaan Lamaran — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nKami telah menerima lamaran Anda untuk posisi {judul_lowongan} di RS Azra.\n\nAnda dapat memantau status lamaran Anda melalui tautan berikut:\n{link_status}\n\nTerima kasih atas minat Anda bergabung bersama kami.\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'transisi_tahap',
                'deskripsi' => 'Dikirim ke kandidat saat berpindah ke tahap berikutnya',
                'subjek' => 'Pembaruan Status Lamaran — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nKami ingin memberitahu bahwa status lamaran Anda untuk posisi {judul_lowongan} telah diperbarui.\n\nAnda dapat melihat status terkini melalui tautan berikut:\n{link_status}\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'lolos_skrining_cv',
                'deskripsi' => 'Dikirim ke kandidat yang lolos skrining CV, mengundang ke tahap selanjutnya',
                'subjek' => 'Selamat! Anda Lolos Skrining CV — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nSelamat! Anda telah lolos tahap skrining CV untuk posisi {judul_lowongan} di RS Azra.\n\nKami akan menghubungi Anda kembali mengenai tahap selanjutnya dalam proses rekrutmen.\n\nPantau terus status lamaran Anda di:\n{link_status}\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'tes_tersedia',
                'deskripsi' => 'Dikirim ke kandidat saat tes (kompetensi/DiSC/MBTI) tersedia',
                'subjek' => 'Tes Tersedia untuk Lamaran Anda — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nTes untuk posisi {judul_lowongan} kini tersedia untuk Anda.\n\nSilakan akses tes melalui tautan berikut:\n{link_tes}\n\nPastikan Anda mengerjakan tes sebelum batas waktu yang telah ditentukan.\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'wawancara_dijadwalkan',
                'deskripsi' => 'Dikirim ke kandidat saat wawancara dijadwalkan',
                'subjek' => 'Undangan Wawancara — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nAnda diundang untuk mengikuti wawancara untuk posisi {judul_lowongan} di RS Azra.\n\nDetail wawancara:\nTanggal & Waktu: {tanggal_interview}\nLokasi: {lokasi_interview}\n\nMohon hadir tepat waktu. Jika ada kendala, segera hubungi tim HR kami.\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'kandidat_ditolak',
                'deskripsi' => 'Dikirim ke kandidat yang tidak lolos (termasuk auto-reject reserved)',
                'subjek' => 'Hasil Seleksi Lamaran — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nTerima kasih atas minat dan waktu yang telah Anda berikan dalam proses rekrutmen untuk posisi {judul_lowongan} di RS Azra.\n\nSetelah melalui proses seleksi, kami dengan menyesal memberitahu bahwa Anda tidak dapat kami proses lebih lanjut pada tahap ini.\n\nKami mengapresiasi partisipasi Anda dan mengucapkan terima kasih atas kepercayaan Anda kepada RS Azra.\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'surat_penawaran',
                'deskripsi' => 'Dikirim ke kandidat bersama surat penawaran kerja',
                'subjek' => 'Surat Penawaran Kerja — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nSelamat! Kami dengan bangga menawarkan posisi {jabatan_ditawarkan} di RS Azra kepada Anda.\n\nDetail Penawaran:\nPosisi: {jabatan_ditawarkan}\nGaji: {gaji}\nTanggal Mulai: {tanggal_mulai}\n\nSilakan klik salah satu tautan berikut untuk merespon penawaran ini:\n\nTerima Penawaran:\n{link_terima}\n\nTolak Penawaran:\n{link_tolak}\n\nTautan ini berlaku selama 7 hari. Jika ada pertanyaan, jangan ragu untuk menghubungi tim HR kami.\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'instruksi_mcu',
                'deskripsi' => 'Dikirim ke kandidat dengan instruksi pemeriksaan kesehatan (MCU)',
                'subjek' => 'Instruksi Medical Check-Up (MCU) — {judul_lowongan}',
                'isi' => "Yth. {nama_kandidat},\n\nSebagai bagian dari proses rekrutmen untuk posisi {judul_lowongan}, Anda diwajibkan mengikuti pemeriksaan kesehatan (MCU).\n\nDetail MCU:\nTanggal & Waktu: {jadwal_mcu}\nLokasi: {lokasi_mcu}\n\nMohon hadir tepat waktu. Jika ada kendala, segera hubungi tim HR kami.\n\nPantau status lamaran Anda di:\n{link_status}\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'undangan_onboarding',
                'deskripsi' => 'Dikirim ke kandidat yang diterima dengan informasi onboarding',
                'subjek' => 'Selamat Datang di RS Azra — Informasi Onboarding',
                'isi' => "Yth. {nama_kandidat},\n\nSelamat bergabung dengan keluarga besar RS Azra!\n\nOnboarding Anda dijadwalkan pada: {tanggal_onboarding}\n\nMohon hadir sesuai jadwal dan siapkan dokumen-dokumen yang diperlukan. Detail lebih lanjut akan disampaikan oleh tim HR.\n\nSalam,\nTim HR RS Azra",
            ],
            [
                'key' => 'tindakan_diperlukan',
                'deskripsi' => 'Dikirim ke pengguna internal (Kepala Unit, Manajer HR, Direktur) saat tindakan diperlukan',
                'subjek' => 'Tindakan Diperlukan — {judul_lowongan}',
                'isi' => "Yth. {nama_penerima},\n\nTerdapat kandidat yang memerlukan tindakan Anda untuk posisi {judul_lowongan}.\n\nSilakan masuk ke sistem untuk meninjau dan mengambil tindakan yang diperlukan.\n\nSalam,\nSistem Rekrutmen RS Azra",
            ],
        ];

        foreach ($templates as $template) {
            $key = $template['key'];
            unset($template['key']);
            EmailTemplate::unguarded(fn () => EmailTemplate::firstOrCreate(['key' => $key], ['key' => $key, ...$template]));
        }
    }
}
