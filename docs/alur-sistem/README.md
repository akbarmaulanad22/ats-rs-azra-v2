# Dokumentasi Alur Sistem — ATS Rumah Sakit Azra

Dokumen ini menjelaskan alur kerja **Applicant Tracking System (ATS)** Rumah Sakit Azra secara visual, lengkap dengan tangkapan layar dari setiap langkah utama: dari kandidat melamar lewat halaman karier publik, melewati seluruh tahap seleksi, sampai diterima dan onboarding.

> **Versi PDF.** Dokumen tersedia sebagai PDF dengan deskripsi tiap layar (satu langkah per halaman):
> - **Terbaru:** [`Dokumentasi-Alur-Sistem-ATS-v2.pdf`](Dokumentasi-Alur-Sistem-ATS-v2.pdf) — 48 layar, termasuk varian "terisi" tiap tahap + alur callback lengkap.
> - Versi awal: [`Dokumentasi-Alur-Sistem-ATS.pdf`](Dokumentasi-Alur-Sistem-ATS.pdf) — 38 layar.
>
> Bangun ulang dengan `node scripts/build-pdf.mjs` (menulis versi v2). Data varian terisi dibuat oleh `php artisan db:seed --class=DemoPipelineCaseSeeder`.

> **Catatan data demo.** Seluruh tangkapan layar diambil dari basis data pengembangan yang diisi seeder `DummyCandidateSeeder`. Lowongan **"Koordinator Medis (Demo)"** sengaja memiliki satu kandidat aktif di setiap tahap pipeline, sehingga tiap tahap dapat ditampilkan. Nama dan data kandidat adalah data palsu (faker). Cara membuat ulang ada di [Lampiran](#lampiran--membuat-ulang-data--tangkapan-layar).

## Daftar Isi

- [Peran Pengguna](#peran-pengguna)
- [Modul Sistem](#modul-sistem)
- [Tahapan Pipeline (Template Koordinator)](#tahapan-pipeline-template-koordinator)
- [A. Autentikasi & Dashboard per Peran](#a-autentikasi--dashboard-per-peran)
- [B. Alur Kandidat (Publik, Tanpa Akun)](#b-alur-kandidat-publik-tanpa-akun)
- [C. Pipeline Rekrutmen — Tahap demi Tahap](#c-pipeline-rekrutmen--tahap-demi-tahap)
- [D. Konfigurasi Template](#d-konfigurasi-template)
- [E. Manajemen Karyawan & Akun](#e-manajemen-karyawan--akun)
- [Lampiran — Membuat Ulang Data & Tangkapan Layar](#lampiran--membuat-ulang-data--tangkapan-layar)

## Peran Pengguna

Sistem mengenal 4 peran internal + kandidat (tanpa akun).

| Peran | Akun demo | Akses utama |
| --- | --- | --- |
| **HR Admin** | `admin` | Akses penuh: lowongan, pipeline, karyawan, akun, pengaturan |
| **Kepala Unit** | `kepala_unit` | Skrining CV & wawancara untuk **unitnya** |
| **Manajer HR** | `hr_manager` | Wawancara Manajer HR |
| **Direktur** | `direktur` | Wawancara final (alur Koordinator) |
| **Karyawan** | `staff_demo` | Profil sendiri + skrining/wawancara sisi unit bila `unit_id` cocok dengan lowongan |
| **Kandidat** | — | Tanpa akun, akses lewat **tautan bertoken** |

> Semua akun demo memakai kata sandi `password`.

## Modul Sistem

1. **ATS (Pipeline Rekrutmen)** — lamaran, skrining CV, tes, wawancara, surat penawaran, MCU, onboarding.
2. **Direktori Karyawan** — data karyawan berdiri sendiri (tidak terhubung otomatis ke ATS).
3. **Manajemen Akun** — akun login untuk seluruh karyawan internal.

## Tahapan Pipeline (Template Koordinator)

Alur kerja bersifat semi-configurable (tahap bisa diaktif/nonaktifkan & diurut ulang per template). Template **Koordinator** memakai seluruh tahap, dengan **peran penanggung jawab** keputusan tiap tahap sebagai berikut:

| # | Tahap | Penanggung jawab keputusan |
| --- | --- | --- |
| 1 | Lamaran | — (otomatis saat kandidat melamar) |
| 2 | Skrining CV HR | HR Admin |
| 3 | Skrining CV User | Kepala Unit / Karyawan unit terkait |
| 4 | Tes Kompetensi | HR Admin (review esai) |
| 5 | Wawancara User | Kepala Unit / Karyawan unit terkait |
| 6 | Wawancara Manajer HR | Manajer HR |
| 7 | Wawancara Direktur | Direktur |
| 8 | Tes DiSC | Otomatis (informasional) |
| 9 | Tes MBTI | Otomatis (informasional) |
| 10 | Surat Penawaran | HR Admin |
| 11 | MCU | HR Admin |
| 12 | Onboarding | HR Admin |

Setiap tahap memiliki status: **Pending** (belum dijalani), **Aktif** (sedang berjalan), **Selesai** (lulus), **Gagal**, atau **Cadangan/Reserved**. Pergerakan hanya maju, tidak bisa mundur.

---

## A. Autentikasi & Dashboard per Peran

### A.1 Halaman Masuk

Pengguna internal masuk dengan nama pengguna + kata sandi. Kandidat tidak perlu login.

![Halaman masuk](screenshots/01-login.png)

### A.2 Dashboard HR Admin

HR Admin melihat metrik rekrutmen seluruh organisasi (semua unit): corong pipeline, tingkat lulus/gagal per tahap, hambatan tahap, dan waktu rekrutmen.

![Dashboard HR Admin](screenshots/02-dashboard-hr-admin.png)

### A.3 Dashboard Kepala Unit

Dashboard **role-scoped**: hanya menampilkan data unit yang bersangkutan (mis. IGD). Menu samping pun terbatas pada Beranda & Lowongan Kerja.

![Dashboard Kepala Unit](screenshots/03-dashboard-unit-head.png)

### A.4 Dashboard Manajer HR

![Dashboard Manajer HR](screenshots/04-dashboard-hr-manager.png)

### A.5 Dashboard Direktur

![Dashboard Direktur](screenshots/05-dashboard-direktur.png)

### A.6 Dashboard Karyawan

Karyawan yang `unit_id`-nya cocok melihat tampilan unit-scoped yang sama dengan Kepala Unit (mis. IGD).

![Dashboard Karyawan](screenshots/06-dashboard-employee.png)

---

## B. Alur Kandidat (Publik, Tanpa Akun)

### B.1 Halaman Karier — Daftar Lowongan

Kandidat membuka daftar lowongan yang sedang dibuka tanpa login.

![Daftar lowongan karier](screenshots/10-karier-list.png)

### B.2 Detail Lowongan

Deskripsi pekerjaan, kualifikasi, dan tenggat lamaran. Kandidat menekan tombol lamar.

![Detail lowongan](screenshots/11-karier-detail.png)

### B.3 Formulir Lamaran

Formulir data pribadi bertahap (multi-langkah) dengan validasi per bagian. Kandidat mengunggah CV dan mengisi data diri.

![Formulir lamaran](screenshots/12-lamar-form.png)

### B.4 Konfirmasi Lamaran

Setelah submit, kandidat menerima halaman konfirmasi berisi tautan status bertoken.

![Konfirmasi lamaran](screenshots/13-lamar-konfirmasi.png)

### B.5 Status Kandidat (Tautan Bertoken)

Kandidat memantau posisi lamarannya melalui tautan read-only `lamaran/{token}` — tanpa perlu akun.

![Status kandidat](screenshots/14-status-kandidat.png)

### B.6 Tes Kompetensi (Tautan Bertoken)

Kandidat mengerjakan tes via tautan `tes/{token}` yang dikirim lewat email. Soal pilihan ganda (auto-skor) + esai (review manual), dengan timer.

![Tes kompetensi kandidat](screenshots/15-tes-kompetensi.png)

### B.7 Tes DiSC (Tautan Bertoken)

Tes kepribadian DiSC: kandidat memilih kata yang **paling** dan **paling tidak** menggambarkan dirinya per kelompok.

![Tes DiSC kandidat](screenshots/16-tes-disc.png)

### B.8 Tes MBTI (Tautan Bertoken)

Tes MBTI dengan pertanyaan pilihan, auto-skor.

![Tes MBTI kandidat](screenshots/17-tes-mbti.png)

### B.9 Respons Surat Penawaran (Tautan Bertoken Bertanda Tangan)

Kandidat menerima/menolak penawaran lewat tautan email **signed** (`penawaran/{id}/terima`). Halaman menampilkan ringkasan penawaran (posisi, gaji, tanggal mulai) + tombol konfirmasi.

![Respons penawaran](screenshots/18-penawaran-terima.png)

---

## C. Pipeline Rekrutmen — Tahap demi Tahap

### C.0 Titik Awal — Template Lowongan

Sebelum pipeline berjalan, HR menerbitkan lowongan dari **Template Lowongan** (deskripsi & kualifikasi siap pakai). Pengelolaan template selengkapnya di [D.2](#d2-template-lowongan).

![Template lowongan](screenshots/51-template-lowongan-list.png)

### C.1 Daftar Lowongan (Internal)

Titik awal pengelolaan setiap proses rekrutmen oleh tim internal.

![Daftar lowongan internal](screenshots/20-lowongan-list.png)

### C.2 Papan Pipeline

Menampilkan seluruh kandidat sebuah lowongan beserta posisi tahapnya. Setiap baris kandidat menunjukkan status tiap tahap.

![Papan pipeline](screenshots/21-pipeline-board.png)

> Tangkapan layar tahap berikut diambil **sebagai peran penanggung jawab** masing-masing, sehingga form aksi yang sebenarnya (penjadwalan, penilaian, keputusan Lulus/Gagal/Cadangan) terlihat. Peran lain hanya melihat tampilan baca-saja.

### C.3 Tahap 1 — Lamaran

Data lamaran awal kandidat begitu masuk pipeline.

![Tahap Lamaran](screenshots/22-pipeline-lamaran.png)

### C.4 Tahap 2 — Skrining CV HR _(HR Admin)_

HR Admin meninjau berkas dan memberi keputusan **Lulus / Tunda / Gagal** beserta catatan.

![Skrining CV HR](screenshots/23-pipeline-skrining-cv-hr.png)

### C.5 Tahap 3 — Skrining CV User _(Kepala Unit)_

Kepala unit terkait melakukan skrining sisi pengguna untuk unitnya.

![Skrining CV User](screenshots/24-pipeline-skrining-cv-user.png)

### C.6 Tahap 4 — Tes Kompetensi _(HR Admin)_

Tes domain (pilihan ganda auto-skor + esai review manual). Setelah kandidat mengerjakan, HR Admin meninjau jawaban dan memutuskan. Pada data demo kandidat belum mengerjakan, sehingga panel menampilkan status _"belum menyelesaikan tes"_.

![Tes Kompetensi — belum dikerjakan](screenshots/25-pipeline-tes-kompetensi.png)

**Kasus: kandidat telah mengisi tes.** Panel menampilkan Total Skor, rincian jawaban (pilihan ganda ditandai benar/salah, esai dinilai manual), lalu form Keputusan (**Loloskan / Tangguhkan / Tolak**).

![Tes Kompetensi — terisi](screenshots/25b-tes-kompetensi-terisi.png)

### C.7 Tahap 5 — Wawancara User _(Kepala Unit)_

Saat jadwal belum ditetapkan, muncul form **Jadwalkan Wawancara** (tanggal, lokasi, pemilihan pewawancara).

![Wawancara User — jadwal](screenshots/26-pipeline-wawancara-user.png)

**Kasus: jadwal ditetapkan & form penilaian.** Muncul **Penilaian Wawancara** — skala 1–5 per kriteria + pertanyaan kesiapan, lalu keputusan + catatan.

![Wawancara User — penilaian](screenshots/26b-wawancara-user-penilaian.png)

### C.8 Tahap 6 — Wawancara Manajer HR _(Manajer HR)_

Form penjadwalan oleh Manajer HR.

![Wawancara Manajer HR — jadwal](screenshots/27-pipeline-wawancara-manajer-hr.png)

**Kasus: form penilaian** (kriteria + kesiapan + keputusan) sebagai Manajer HR.

![Wawancara Manajer HR — penilaian](screenshots/27b-wawancara-manajer-hr-penilaian.png)

### C.9 Tahap 7 — Wawancara Direktur _(Direktur)_

Direktur menjadwalkan wawancara final.

![Wawancara Direktur — jadwal](screenshots/28-pipeline-wawancara-direktur.png)

**Kasus: form penilaian** wawancara final sebagai Direktur.

![Wawancara Direktur — penilaian](screenshots/28b-wawancara-direktur-penilaian.png)

### C.10 Tahap 8 — Tes DiSC

Tes kepribadian DiSC (auto-skor, informasional, tidak terlihat oleh kandidat). Saat belum dikerjakan, panel menampilkan status _"belum menyelesaikan tes"_.

![Tes DiSC — belum dikerjakan](screenshots/29-pipeline-tes-disc.png)

**Kasus: kandidat selesai.** Panel **Hasil Tes DiSC** menampilkan skor D/I/S/C + Tipe Primer & Sekunder.

![Tes DiSC — selesai](screenshots/29b-tes-disc-selesai.png)

### C.11 Tahap 9 — Tes MBTI

Sama seperti DiSC: auto-skor & informasional. Saat belum dikerjakan masih kosong.

![Tes MBTI — belum dikerjakan](screenshots/30-pipeline-tes-mbti.png)

**Kasus: kandidat selesai.** Panel hasil MBTI menampilkan tipe kepribadian + kekuatan tiap dimensi.

![Tes MBTI — selesai](screenshots/30b-tes-mbti-selesai.png)

### C.12 Tahap 10 — Surat Penawaran _(HR Admin)_

HR Admin menyusun & mengirim surat penawaran via email.

![Surat Penawaran](screenshots/31-pipeline-surat-penawaran.png)

### C.13 Tahap 11 — MCU _(HR Admin)_

Saat jadwal belum ditetapkan, muncul form **Jadwalkan MCU** (tanggal & lokasi).

![MCU — jadwal](screenshots/32-pipeline-mcu.png)

**Kasus: HR Admin input hasil MCU.** Setelah jadwal ditetapkan, HR Admin mengunggah dokumen hasil MCU (PDF, maks. 5 MB) dan menetapkan keputusan (**Lulus / Ditangguhkan / Tidak Lulus**) + catatan.

![MCU — input hasil](screenshots/32b-mcu-input.png)

### C.14 Tahap 12 — Onboarding / Selesai

Kandidat yang lulus seluruh tahap; onboarding sebagai tahap akhir.

![Onboarding selesai](screenshots/33-pipeline-selesai.png)

### C.15 Panggil Kembali Kandidat (Callback)

Kandidat yang gagal di periode sebelumnya (template lowongan sama) dapat dipanggil kembali. Saat belum ada kandidat gagal, daftar kosong.

![Panggil kembali — kosong](screenshots/34-callback.png)

**Kasus: alur callback lengkap (gagal → diundang → melamar kembali).** Daftar menampilkan kandidat gagal beserta badge status: **A** belum diundang (eligible), **B** _"Sudah diundang"_, **C** _"Sudah melamar"_. HR memilih kandidat lalu **Kirim Undangan** (email undangan melamar kembali terkirim).

![Panggil kembali — terisi](screenshots/34b-callback-terisi.png)

Kandidat **C** yang diundang kemudian melamar ke periode/lowongan baru dan masuk kembali ke pipeline (tahap Lamaran) — menutup siklus callback.

![Panggil kembali — melamar kembali](screenshots/34c-callback-melamar-kembali.png)

---

## D. Konfigurasi Template

### D.1 Template Alur Kerja

Mendefinisikan tahap mana yang aktif dan urutannya (mesin alur semi-configurable).

![Template alur kerja](screenshots/50-template-alur-list.png)

### D.2 Template Lowongan

Template deskripsi & kualifikasi lowongan yang dapat diterbitkan menjadi vacancy.

![Template lowongan](screenshots/51-template-lowongan-list.png)

### D.3 Template Wawancara

Kriteria penilaian wawancara terstruktur (default global, bisa dioverride per lowongan).

![Template wawancara](screenshots/52-template-wawancara-list.png)

### D.4 Template Bank Soal

Bank soal tes kompetensi per departemen.

![Template bank soal](screenshots/53-template-bank-soal-list.png)

### D.5 Template Email

Template email global dengan placeholder, terkirim otomatis pada transisi tahap.

![Template email](screenshots/54-template-email-list.png)

---

## E. Manajemen Karyawan & Akun

### E.1 Direktori Karyawan

Data karyawan: NIP, Nama, Unit, Posisi, Profesi, Jabatan.

![Direktori karyawan](screenshots/60-karyawan-list.png)

### E.2 Manajemen Akun

Setiap karyawan memperoleh akun login; kata sandi default diganti saat login pertama.

![Manajemen akun](screenshots/61-akun-list.png)

### E.3 Unit / Departemen

![Daftar unit](screenshots/62-unit-list.png)

---

## Lampiran — Membuat Ulang Data & Tangkapan Layar

Prasyarat: aplikasi berjalan di `http://127.0.0.1:8000` dan aset sudah dibangun (`npm run build`).

1. **Isi data demo** (membuat akun `kepala_unit` + lowongan "Koordinator Medis (Demo)" berisi kandidat di setiap tahap):

   ```bash
   php artisan db:seed --class=DummyCandidateSeeder
   ```

2. **Akun demo lain** (`hr_manager`, `direktur`, `staff_demo`) dan reset kata sandi `admin` dibuat manual saat penyusunan dokumen ini. Semua memakai kata sandi `password`.

3. **Ambil ulang tangkapan layar** dengan driver Playwright:

   ```bash
   npm i -D playwright          # sekali saja
   npx playwright install chromium
   node scripts/screenshot-flows.mjs all     # semua section
   # atau per-section: auth | candidate | tokens | pipeline | templates | management
   # atau slice ringkas: node scripts/screenshot-flows.mjs slice
   ```

   Token & ID dinamis (lowongan, lamaran) dibaca dari `scripts/_fixtures.json`. Buat ulang fixtures bila data di-seed ulang.

### Halaman bertoken (B.6–B.9)

`DummyCandidateSeeder` tidak membuat baris submission/offering, jadi data untuk halaman tes publik & penawaran dibuat manual (sekali) saat penyusunan dokumen:

- `MbtiQuestion` di-seed: `php artisan db:seed --class=MbtiQuestionSeeder` (DiSC sudah ada).
- `TestSubmission` (+`VacancyTestSnapshot` berisi soal), `DiscSubmission`, `MbtiSubmission`, dan `OfferingLetter` (status `pending`) dibuat lewat tinker untuk kandidat demo di tahap terkait. Token hasilnya disimpan di blok `tokens` pada `scripts/_fixtures.json`.
- Route penawaran memakai middleware `signed`, jadi `offering_url` di fixtures adalah URL **bertanda tangan** (`URL::signedRoute` dengan root `http://127.0.0.1:8000`). Regenerasi bila kedaluwarsa/host berubah.

Lalu jalankan section khusus:

```bash
node scripts/screenshot-flows.mjs tokens
```
