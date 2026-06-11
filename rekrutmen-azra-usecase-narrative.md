# Narasi Use Case — Sistem ATS Azra Hospital

Dokumen ini memuat narasi (use case narrative) untuk seluruh use case pada diagram
`rekrutmen-azra-usecase.puml`. Penomoran UC mengikuti urutan modul dan alur eksekusi
rekrutmen: Autentikasi & Umum → Manajemen Master → Pipeline Rekrutmen → Use Case Bersama
→ Portal Kandidat.

---

## Daftar Use Case

| ID | Nama Use Case | Deskripsi | Pelaku |
|---|---|---|---|
| UC-01 | Login | Autentikasi pengguna internal untuk masuk ke sistem. | Pengguna Internal |
| UC-02 | Ubah Password | Penggantian kata sandi akun sendiri. | Pengguna Internal |
| UC-03 | Lihat Notifikasi | Menampilkan daftar notifikasi sistem bagi pengguna. | Pengguna Internal |
| UC-04 | Lihat Profil Sendiri | Karyawan melihat data dirinya di direktori karyawan. | Karyawan |
| UC-05 | Kelola Unit | CRUD data unit/departemen organisasi. | HR Admin |
| UC-06 | Kelola Akun | CRUD akun pengguna internal beserta peran & status aktif. | HR Admin |
| UC-07 | Kelola Karyawan | CRUD data karyawan pada direktori karyawan. | HR Admin |
| UC-08 | Kelola Template Alur | CRUD template alur (tahapan) rekrutmen yang dapat dipakai ulang. | HR Admin |
| UC-09 | Kelola Template Wawancara | CRUD template kriteria penilaian & pertanyaan wawancara. | HR Admin |
| UC-10 | Kelola Bank Soal | CRUD template bank soal tes kompetensi. | HR Admin |
| UC-11 | Kelola Template Email | Menyunting template email notifikasi sistem. | HR Admin |
| UC-12 | Kelola Lowongan | CRUD lowongan beserta konfigurasi alur, tes, dan template wawancara. | HR Admin |
| UC-13 | Terapkan Template Alur | Menyalin template alur menjadi snapshot tahapan lowongan. | HR Admin |
| UC-14 | Lihat Pipeline | Menampilkan papan kandidat per tahap untuk satu lowongan. | HR Admin |
| UC-15 | Lihat Detail Kandidat | Menampilkan profil lengkap dan riwayat tahap satu kandidat. | HR Admin |
| UC-16 | Skrining CV HR | Keputusan penyaringan CV tahap HR (lulus/gagal/ditangguhkan). | HR Admin |
| UC-17 | Skrining CV User | Keputusan penyaringan CV tahap unit oleh Kepala Unit/Karyawan. | Unit Head, Karyawan |
| UC-18 | Kelola Tes Kompetensi | Konfigurasi soal & ambang tes kompetensi untuk satu lowongan. | HR Admin |
| UC-19 | Tinjau Jawaban Tes | Menilai jawaban esai dan memutuskan hasil tes kompetensi. | HR Admin |
| UC-20 | Lihat Hasil DiSC/MBTI | Menampilkan hasil tes kepribadian kandidat. | HR Admin |
| UC-21 | Wawancara User | Merekam hasil & keputusan wawancara tahap unit. | Unit Head, Karyawan |
| UC-22 | Wawancara Manajer HR | Merekam hasil & keputusan wawancara tahap Manajer HR. | HR Manager |
| UC-23 | Wawancara Direktur | Merekam hasil & keputusan wawancara tahap Direktur. | Direktur |
| UC-24 | Jadwalkan MCU | Menjadwalkan medical check-up bagi kandidat. | HR Admin |
| UC-25 | Keputusan MCU | Merekam hasil & keputusan MCU kandidat. | HR Admin |
| UC-26 | Kirim Surat Penawaran | Menyusun & mengirim surat penawaran kerja bertaut tertanda. | HR Admin |
| UC-27 | Proses Onboarding | Mengirim undangan onboarding dan menyelesaikan proses. | HR Admin |
| UC-28 | Kirim Notifikasi Email | Mengirim email notifikasi tahap (dipakai bersama use case lain). | Sistem |
| UC-29 | Tandai Reserved | Menangguhkan kandidat pada tahap aktif tanpa menolak. | HR Admin, Unit Head |
| UC-30 | Lihat Lowongan | Kandidat menelusuri portal karier & detail lowongan. | Kandidat |
| UC-31 | Lamar Lowongan | Kandidat mengisi wizard 8 langkah & mengirim lamaran. | Kandidat |
| UC-32 | Isi Data Pribadi | Pengisian & validasi data pribadi per langkah pada wizard lamaran. | Kandidat |
| UC-33 | Kerjakan Tes Kompetensi | Kandidat mengerjakan tes kompetensi via tautan tertoken. | Kandidat |
| UC-34 | Kerjakan Tes DiSC | Kandidat mengerjakan tes kepribadian DiSC via tautan tertoken. | Kandidat |
| UC-35 | Kerjakan Tes MBTI | Kandidat mengerjakan tes kepribadian MBTI via tautan tertoken. | Kandidat |
| UC-36 | Terima/Tolak Penawaran | Kandidat merespons surat penawaran via tautan tertanda. | Kandidat |
| UC-37 | Lihat Status Lamaran | Kandidat memantau status lamaran via tautan tertoken. | Kandidat |

---

# A. Autentikasi & Umum

## UC-01 — Login

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Login |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-01 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Kebutuhan keamanan akses sistem internal |
| **Pelaku Bisnis Primer** | Pengguna Internal (HR Admin, HR Manager, Kepala Unit, Direktur, Karyawan) |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin (pengelola akun) |
| **Deskripsi** | Use case ini mengautentikasi pengguna internal menggunakan email & kata sandi agar memperoleh sesi terotorisasi sesuai perannya. Mencakup verifikasi kredensial, pembatasan percobaan, dan pengalihan ke dashboard. |
| **Prakondisi** | Pengguna memiliki akun aktif yang dibuat HR Admin. |
| **Pemicu** | Pengguna membuka halaman login dan mengirim kredensial. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memasukkan email & kata sandi lalu menekan masuk.<br>2. **Sistem:** memverifikasi kredensial, membuat sesi, mengalihkan ke dashboard sesuai peran. |
| **Bagian Alternatif** | **E1 — Kredensial salah:** sistem menampilkan pesan gagal tanpa membuat sesi.<br>**E2 — Akun nonaktif:** sistem menolak masuk.<br>**E3 — Percobaan berlebih:** sistem membatasi sementara (rate limit). |
| **Kesimpulan** | Use case selesai saat sesi terbentuk dan pengguna berada di dashboard. |
| **Pasca Kondisi** | Sesi terotorisasi aktif; peran pengguna menentukan menu yang tersedia. |
| **Aturan Bisnis** | Hanya akun berstatus aktif yang dapat login; peran menentukan otorisasi seluruh sistem. |
| **Batasan dan Spesifikasi** | Pembatasan percobaan login (rate limit); kata sandi tersimpan ter-hash. |
| **Implementasi** | Rute `login` (middleware `guest`); guard `auth` melindungi seluruh area internal. |
| **Asumsi** | Akun & peran telah disiapkan HR Admin sebelumnya. |
| **Isu Terbuka** | Belum ada autentikasi dua faktor. |

## UC-02 — Ubah Password

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Ubah Password |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-02 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Kebutuhan keamanan akun mandiri |
| **Pelaku Bisnis Primer** | Pengguna Internal |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin |
| **Deskripsi** | Use case ini memungkinkan pengguna mengganti kata sandi akunnya sendiri dengan memverifikasi kata sandi lama dan menetapkan kata sandi baru yang memenuhi aturan keamanan. |
| **Prakondisi** | Pengguna sudah login. |
| **Pemicu** | Pengguna membuka halaman ubah password. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi kata sandi lama, kata sandi baru, dan konfirmasi.<br>2. **Sistem:** memvalidasi kata sandi lama & aturan kata sandi baru, menyimpan kata sandi ter-hash, menampilkan pesan sukses. |
| **Bagian Alternatif** | **E1 — Kata sandi lama salah:** sistem menolak perubahan.<br>**E2 — Konfirmasi tidak cocok / kata sandi lemah:** sistem menampilkan error validasi.<br>**E3 — Percobaan berlebih:** dibatasi (throttle 5/menit). |
| **Kesimpulan** | Use case selesai saat kata sandi berhasil diperbarui. |
| **Pasca Kondisi** | Kata sandi akun diperbarui; sesi tetap aktif. |
| **Aturan Bisnis** | Kata sandi baru harus memenuhi kebijakan kekuatan kata sandi. |
| **Batasan dan Spesifikasi** | Throttle `5,1` pada submit; kata sandi tersimpan ter-hash. |
| **Implementasi** | `PasswordChangeController@show/update`; rute `password.change`/`password.update`. |
| **Asumsi** | Pengguna mengingat kata sandi lama. |
| **Isu Terbuka** | Belum ada alur lupa password mandiri. |

## UC-03 — Lihat Notifikasi

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lihat Notifikasi |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-03 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Kebutuhan komunikasi kejadian pipeline ke pengguna internal |
| **Pelaku Bisnis Primer** | Pengguna Internal |
| **Pelaku Peserta Lain** | Sistem (penghasil notifikasi) |
| **Stakeholder yang Berminat** | HR Admin, HR Manager |
| **Deskripsi** | Use case ini menampilkan daftar notifikasi internal (mis. respons penawaran kandidat) sehingga pengguna mengetahui kejadian yang memerlukan tindak lanjut. |
| **Prakondisi** | Pengguna sudah login. |
| **Pemicu** | Pengguna membuka menu notifikasi. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka halaman notifikasi.<br>2. **Sistem:** menampilkan daftar notifikasi pengguna terurut terbaru. |
| **Bagian Alternatif** | **A1 — Tidak ada notifikasi:** sistem menampilkan keadaan kosong. |
| **Kesimpulan** | Use case selesai saat daftar notifikasi tampil. |
| **Pasca Kondisi** | Pengguna mengetahui kejadian terkini. |
| **Aturan Bisnis** | Notifikasi hanya milik pengguna bersangkutan/peran terkait (mis. respons penawaran ke HR Admin aktif). |
| **Batasan dan Spesifikasi** | Notifikasi disimpan via mekanisme notifikasi Laravel. |
| **Implementasi** | `NotifikasiController@index`; rute `notifikasi.index`; notifikasi `PenawaranDirespon`. |
| **Asumsi** | Kejadian pipeline memicu notifikasi yang relevan. |
| **Isu Terbuka** | Belum ada penanda baca/belum-baca yang ditampilkan eksplisit di dokumen ini. |

## UC-04 — Lihat Profil Sendiri

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lihat Profil Sendiri |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-04 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Rendah |
| **Sumber** | Direktori karyawan internal |
| **Pelaku Bisnis Primer** | Karyawan |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin |
| **Deskripsi** | Use case ini memungkinkan karyawan melihat data dirinya sendiri pada direktori karyawan, seperti unit, jabatan, dan informasi kepegawaian. |
| **Prakondisi** | Karyawan sudah login dan tertaut ke data karyawan. |
| **Pemicu** | Karyawan membuka profilnya. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka halaman profil.<br>2. **Sistem:** menampilkan data karyawan miliknya. |
| **Bagian Alternatif** | **E1 — Akun belum tertaut data karyawan:** sistem menampilkan keadaan kosong/keterbatasan akses. |
| **Kesimpulan** | Use case selesai saat data profil tampil. |
| **Pasca Kondisi** | Karyawan melihat datanya tanpa dapat menyunting data sensitif kepegawaian. |
| **Aturan Bisnis** | Karyawan hanya dapat melihat data dirinya, bukan karyawan lain. |
| **Batasan dan Spesifikasi** | Akses dibatasi peran `employee`. |
| **Implementasi** | Area karyawan/dashboard (`EmployeeController`/`DashboardController`). |
| **Asumsi** | Pemetaan akun↔karyawan sudah dilakukan HR Admin. |
| **Isu Terbuka** | Cakupan penyuntingan mandiri oleh karyawan belum didefinisikan. |

---

# B. Manajemen Master & Konfigurasi (HR Admin)

> UC-05 s.d. UC-11 berbagi pola CRUD yang serupa: daftar → form tambah/ubah → validasi →
> simpan → pesan sukses, dengan penolakan hapus bila data masih dipakai entitas lain.

## UC-05 — Kelola Unit

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Unit |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-05 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Struktur organisasi Azra Hospital |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Kepala Unit, HR Manager |
| **Deskripsi** | Use case ini mengelola data unit/departemen (tambah, ubah, hapus, cari) yang menjadi acuan penempatan lowongan, akun, dan karyawan. |
| **Prakondisi** | HR Admin sudah login. |
| **Pemicu** | HR Admin membuka menu Unit. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memilih tambah/ubah/hapus unit dan mengisi form.<br>2. **Sistem:** memvalidasi, menyimpan perubahan, menampilkan daftar terbaru & pesan sukses. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** sistem menampilkan error per-field.<br>**E2 — Hapus unit yang masih dipakai:** sistem menolak penghapusan. |
| **Kesimpulan** | Use case selesai saat data unit tersimpan/terbarui. |
| **Pasca Kondisi** | Data unit tersedia sebagai acuan modul lain. |
| **Aturan Bisnis** | Unit yang masih direferensikan lowongan/karyawan tidak dapat dihapus. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; tersedia endpoint pencarian unit. |
| **Implementasi** | `UnitController` (resource, kecuali `show`) + `UnitController@search`; rute `unit.*`. |
| **Asumsi** | Struktur unit relatif stabil. |
| **Isu Terbuka** | — |

## UC-06 — Kelola Akun

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Akun |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-06 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Kebutuhan kontrol akses berbasis peran |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Seluruh pengguna internal |
| **Deskripsi** | Use case ini mengelola akun pengguna internal: membuat, menyunting, menautkan ke data karyawan, menetapkan peran, dan mengaktif/nonaktifkan akun. |
| **Prakondisi** | HR Admin sudah login. |
| **Pemicu** | HR Admin membuka menu Akun. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuat/menyunting akun, memilih peran, menautkan karyawan.<br>2. **Sistem:** memvalidasi, menyimpan akun, menampilkan pesan sukses. |
| **Bagian Alternatif** | **A1 — Toggle aktif:** HR Admin menonaktifkan/mengaktifkan akun; sistem memperbarui status.<br>**E1 — Validasi gagal:** sistem menampilkan error. |
| **Kesimpulan** | Use case selesai saat akun tersimpan dengan peran & status yang benar. |
| **Pasca Kondisi** | Akun siap dipakai login sesuai peran; akun nonaktif tidak dapat login. |
| **Aturan Bisnis** | Peran menentukan otorisasi; akun dapat ditautkan ke satu karyawan; pencarian karyawan tersedia saat penautan. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; resource terbatas pada `index/create/store/edit/update` + toggle. |
| **Implementasi** | `AccountController` + `@searchAvailableEmployees` + `@toggleAktif`; rute `akun.*`. |
| **Asumsi** | Data karyawan telah ada saat penautan. |
| **Isu Terbuka** | — |

## UC-07 — Kelola Karyawan

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Karyawan |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-07 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Direktori karyawan / manajemen kepegawaian |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Kepala Unit, HR Manager |
| **Deskripsi** | Use case ini mengelola data karyawan pada direktori (tambah, lihat, ubah, hapus), termasuk penempatan unit dan jabatan. |
| **Prakondisi** | HR Admin sudah login. |
| **Pemicu** | HR Admin membuka menu Karyawan. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** menambah/menyunting/menghapus data karyawan.<br>2. **Sistem:** memvalidasi, menyimpan, menampilkan daftar & pesan sukses. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** sistem menampilkan error per-field.<br>**E2 — Hapus karyawan tertaut akun:** sistem menjaga konsistensi relasi. |
| **Kesimpulan** | Use case selesai saat data karyawan tersimpan. |
| **Pasca Kondisi** | Direktori karyawan termutakhirkan. |
| **Aturan Bisnis** | Karyawan dapat ditautkan ke akun pengguna; penempatan mengacu ke unit. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; resource penuh. |
| **Implementasi** | `EmployeeController` (resource); rute `karyawan.*`. |
| **Asumsi** | Data unit tersedia. |
| **Isu Terbuka** | — |

## UC-08 — Kelola Template Alur

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Template Alur |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-08 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Kebutuhan standarisasi tahapan rekrutmen |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Manager |
| **Deskripsi** | Use case ini mengelola template alur rekrutmen, yaitu susunan tahapan (skrining, tes, wawancara, MCU, penawaran, onboarding) berikut urutannya, agar dapat dipakai ulang pada banyak lowongan. |
| **Prakondisi** | HR Admin sudah login; daftar tahapan master tersedia. |
| **Pemicu** | HR Admin membuka menu Template Alur. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** menyusun template dengan memilih & mengurutkan tahapan.<br>2. **Sistem:** memvalidasi posisi tahapan, menyimpan template, menampilkan pesan sukses. |
| **Bagian Alternatif** | **E1 — Tahapan terkunci pertama/terakhir dilanggar:** sistem mempertahankan tahap awal (Lamaran) & akhir (Onboarding).<br>**E2 — Validasi gagal:** sistem menampilkan error. |
| **Kesimpulan** | Use case selesai saat template alur tersimpan. |
| **Pasca Kondisi** | Template alur tersedia untuk diterapkan ke lowongan (lihat UC-13). |
| **Aturan Bisnis** | Tahap `Lamaran` selalu pertama (`is_locked_first`) dan `Onboarding` selalu terakhir (`is_locked_last`); urutan tahap disimpan via posisi. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; tersedia endpoint pencarian template. |
| **Implementasi** | `WorkflowTemplateController` (resource kecuali `show`) + `@search`; rute `template-alur.*`; model `Stage`, `WorkflowTemplate`. |
| **Asumsi** | Daftar tahapan master telah di-seed (`StageSeeder`). |
| **Isu Terbuka** | — |

## UC-09 — Kelola Template Wawancara

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Template Wawancara |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-09 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Standarisasi penilaian wawancara |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Kepala Unit, HR Manager, Direktur (pewawancara) |
| **Deskripsi** | Use case ini mengelola template wawancara berisi kriteria penilaian (rating) dan/atau pertanyaan kesiapan kerja yang nantinya dipakai pewawancara saat merekam hasil wawancara. |
| **Prakondisi** | HR Admin sudah login. |
| **Pemicu** | HR Admin membuka menu Template Wawancara. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** menambah/menyunting item template (kriteria/pertanyaan).<br>2. **Sistem:** memvalidasi, menyimpan template, menampilkan pesan sukses. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** sistem menampilkan error per-field. |
| **Kesimpulan** | Use case selesai saat template wawancara tersimpan. |
| **Pasca Kondisi** | Template tersedia untuk ditautkan ke lowongan & dipakai pada wawancara. |
| **Aturan Bisnis** | Item template menentukan kriteria rating dan pertanyaan kesiapan pada form wawancara. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; resource kecuali `show`. |
| **Implementasi** | `InterviewTemplateController` (resource); model `InterviewTemplate`, `InterviewTemplateItem`; rute `template-wawancara.*`. |
| **Asumsi** | Kriteria penilaian disepakati HR. |
| **Isu Terbuka** | — |

## UC-10 — Kelola Bank Soal

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Bank Soal |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-10 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Kebutuhan tes kompetensi terstandar |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Kepala Unit |
| **Deskripsi** | Use case ini mengelola template bank soal tes kompetensi (pertanyaan pilihan ganda & esai beserta opsi dan poin) yang dapat dipakai untuk menyusun tes pada lowongan. |
| **Prakondisi** | HR Admin sudah login. |
| **Pemicu** | HR Admin membuka menu Template Bank Soal. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** menambah/menyunting soal beserta opsi & poin.<br>2. **Sistem:** memvalidasi, menyimpan template bank soal, menampilkan pesan sukses. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** sistem menampilkan error per-field. |
| **Kesimpulan** | Use case selesai saat bank soal tersimpan. |
| **Pasca Kondisi** | Bank soal tersedia untuk konfigurasi tes lowongan (UC-18). |
| **Aturan Bisnis** | Soal esai memiliki nilai poin maksimum; soal pilihan ganda memiliki kunci jawaban. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; resource kecuali `show`. |
| **Implementasi** | `QuestionBankTemplateController` (resource); model `QuestionBankTemplate`, `Question`, `QuestionOption`; rute `template-bank-soal.*`. |
| **Asumsi** | Materi soal disiapkan HR/unit terkait. |
| **Isu Terbuka** | — |

## UC-11 — Kelola Template Email

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Template Email |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-11 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Standarisasi komunikasi email ke kandidat |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Kandidat (penerima email) |
| **Deskripsi** | Use case ini menyunting template email notifikasi sistem (mis. tes tersedia, lolos skrining, surat penawaran, undangan onboarding) berikut variabel placeholder-nya. |
| **Prakondisi** | HR Admin sudah login; template email telah di-seed. |
| **Pemicu** | HR Admin membuka pengaturan Template Email. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memilih template, menyunting subjek/isi, menyimpan.<br>2. **Sistem:** memvalidasi placeholder, menyimpan template, menampilkan pesan sukses. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** sistem menampilkan error. |
| **Kesimpulan** | Use case selesai saat template email tersimpan. |
| **Pasca Kondisi** | Template dipakai oleh layanan notifikasi pada seluruh tahap pipeline. |
| **Aturan Bisnis** | Placeholder yang dipakai harus sesuai konteks tahap pengirimannya. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; hanya `index/edit/update` (tanpa buat/hapus). |
| **Implementasi** | `EmailTemplateController@index/edit/update`; model `EmailTemplate`; rute `template-email.*`; `EmailTemplateSeeder`. |
| **Asumsi** | Daftar jenis email sudah ditentukan sistem. |
| **Isu Terbuka** | — |

## UC-12 — Kelola Lowongan

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Lowongan |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-12 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Kebutuhan pengadaan tenaga kerja per unit |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Kepala Unit, HR Manager, Kandidat |
| **Deskripsi** | Use case ini mengelola lowongan (tambah, ubah, hapus, publikasi) beserta atribut posisi, unit, tenggat, status, dan konfigurasi turunannya (alur tahapan, tes kompetensi, template wawancara). |
| **Prakondisi** | HR Admin sudah login; data unit & template alur tersedia. |
| **Pemicu** | HR Admin membuka menu Lowongan. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi data lowongan (posisi, unit, deskripsi, tenggat) & memilih status publikasi.<br>2. **Sistem:** memvalidasi, menyimpan lowongan, menerapkan snapshot alur, menampilkan pesan sukses. |
| **Bagian Alternatif** | **A1 — Terapkan Template Alur** *(«extend» UC-13)*: saat membuat lowongan, sistem menyalin template alur menjadi snapshot tahapan.<br>**E1 — Validasi gagal:** sistem menampilkan error.<br>**E2 — Hapus lowongan dengan lamaran berjalan:** sistem menjaga konsistensi data pipeline. |
| **Kesimpulan** | Use case selesai saat lowongan tersimpan dengan konfigurasi tahapnya. |
| **Pasca Kondisi** | Lowongan Published tampil di portal karier & menerima lamaran; pipeline siap memproses kandidat. |
| **Aturan Bisnis** | Lamaran hanya diterima saat status `Published` dan dalam tenggat; setiap lowongan memiliki snapshot alur sendiri agar perubahan template tidak mengubah lowongan berjalan. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; resource kecuali `show`; konfigurasi tes & template wawancara dilakukan pada sub-halaman lowongan. |
| **Implementasi** | `VacancyController` (resource); `VacancyTestController`, `VacancyInterviewTemplateController`; model `Vacancy`, `WorkflowTemplateSnapshot`; rute `lowongan.*`. |
| **Asumsi** | Template alur telah disiapkan (UC-08). |
| **Isu Terbuka** | — |

## UC-13 — Terapkan Template Alur

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Terapkan Template Alur |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-13 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Konfigurasi pipeline per lowongan |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Manager |
| **Deskripsi** | Use case ini menyalin sebuah template alur menjadi snapshot tahapan milik lowongan, sehingga tahapan pipeline lowongan terkunci pada konfigurasi saat itu meski template induk berubah kemudian. Merupakan perluasan («extend») dari Kelola Lowongan. |
| **Prakondisi** | HR Admin sedang membuat/menyunting lowongan; template alur tersedia. |
| **Pemicu** | HR Admin memilih template alur untuk lowongan. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memilih template alur pada form lowongan.<br>2. **Sistem:** menyalin tahapan template menjadi `WorkflowTemplateSnapshot` + `...SnapshotStage` milik lowongan. |
| **Bagian Alternatif** | **E1 — Template tidak dipilih:** sistem memakai/menuntut konfigurasi alur valid sebelum publikasi. |
| **Kesimpulan** | Use case selesai saat snapshot tahapan lowongan terbentuk. |
| **Pasca Kondisi** | Lamaran baru pada lowongan ini memperoleh tahapan dari snapshot tersebut. |
| **Aturan Bisnis** | Snapshot bersifat imutabel terhadap perubahan template induk; tahap terkunci awal/akhir tetap dipertahankan. |
| **Batasan dan Spesifikasi** | Dijalankan dalam konteks pembuatan/penyuntingan lowongan. |
| **Implementasi** | `VacancyController` + model `WorkflowTemplateSnapshot`, `WorkflowTemplateSnapshotStage`. |
| **Asumsi** | Template alur final saat diterapkan. |
| **Isu Terbuka** | Perubahan alur untuk lowongan berjalan belum didefinisikan. |

---

# C. Pipeline Rekrutmen

## UC-14 — Lihat Pipeline

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lihat Pipeline |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-14 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Pemantauan proses seleksi |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Kepala Unit, HR Manager, Direktur (sesuai tahap) |
| **Stakeholder yang Berminat** | Seluruh pewawancara |
| **Deskripsi** | Use case ini menampilkan papan pipeline satu lowongan: daftar kandidat dikelompokkan per tahap beserta statusnya, sebagai titik masuk seluruh aksi keputusan tahap. |
| **Prakondisi** | Pengguna terotorisasi sudah login; lowongan memiliki lamaran. |
| **Pemicu** | Pengguna membuka pipeline sebuah lowongan. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka halaman pipeline lowongan.<br>2. **Sistem:** menampilkan kandidat per tahap beserta status (Menunggu/Aktif/Ditangguhkan/Selesai/Gagal). |
| **Bagian Alternatif** | **A1 — Buka detail kandidat** *(«include» UC-15)*: sistem menampilkan profil & riwayat tahap.<br>**A2 — Belum ada kandidat:** sistem menampilkan keadaan kosong. |
| **Kesimpulan** | Use case selesai saat papan pipeline tampil. |
| **Pasca Kondisi** | Pengguna dapat memilih kandidat untuk diproses pada tahapnya. |
| **Aturan Bisnis** | Tahap aktif satu kandidat ditentukan status tahapnya; aksi keputusan hanya pada tahap Aktif/Ditangguhkan. |
| **Batasan dan Spesifikasi** | Akses dibatasi peran; `scopeBindings` memastikan lamaran milik lowongan. |
| **Implementasi** | `VacancyPipelineController@index/showApplication`; rute `lowongan.pipeline*`. |
| **Asumsi** | Tahapan kandidat dibentuk saat melamar. |
| **Isu Terbuka** | — |

## UC-15 — Lihat Detail Kandidat

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lihat Detail Kandidat |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-15 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Kebutuhan evaluasi kandidat |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Kepala Unit, HR Manager, Direktur |
| **Stakeholder yang Berminat** | Seluruh pewawancara |
| **Deskripsi** | Use case ini menampilkan profil lengkap kandidat (data pribadi, pendidikan, pengalaman, CV, hasil tes & wawancara) dan riwayat tahapnya; menjadi bagian yang disertakan («include») oleh Lihat Pipeline dan aksi keputusan tahap. Mendukung ekspor PDF profil. |
| **Prakondisi** | Pengguna terotorisasi; lamaran ada pada lowongan. |
| **Pemicu** | Pengguna memilih kandidat dari pipeline. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memilih kandidat.<br>2. **Sistem:** menampilkan profil lengkap, berkas, hasil tahap, dan opsi ekspor PDF. |
| **Bagian Alternatif** | **A1 — Ekspor PDF:** sistem menghasilkan PDF profil kandidat.<br>**E1 — Lamaran bukan milik lowongan:** sistem menampilkan 404. |
| **Kesimpulan** | Use case selesai saat detail kandidat tampil. |
| **Pasca Kondisi** | Pengguna memperoleh informasi untuk mengambil keputusan tahap. |
| **Aturan Bisnis** | Akses dibatasi peran & kepemilikan lowongan/unit. |
| **Batasan dan Spesifikasi** | `scopeBindings`; ekspor PDF tersedia. |
| **Implementasi** | `VacancyPipelineController@showApplication/exportPdf`, `CandidateExportController@profile`; rute `lowongan.pipeline.show*`, `lowongan.kandidat.pdf`. |
| **Asumsi** | Data kandidat lengkap saat melamar. |
| **Isu Terbuka** | — |

## UC-16 — Skrining CV HR

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Skrining CV HR |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-16 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap penyaringan CV oleh HR |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | Kepala Unit, HR Manager, Kandidat |
| **Deskripsi** | Use case ini merekam keputusan penyaringan CV pada tahap HR — meloloskan, menggagalkan, atau menangguhkan kandidat — beserta catatan, lalu memindahkan kandidat ke tahap berikutnya bila lulus. |
| **Prakondisi** | HR Admin login; lamaran pada tahap `Skrining CV HR` berstatus Aktif/Ditangguhkan. |
| **Pemicu** | HR Admin menekan tombol keputusan skrining pada detail kandidat. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** meninjau CV *(«include» UC-15)*, mengisi catatan, memilih **Lulus**, menyimpan.<br>2. **Sistem:** menyimpan catatan & peninjau, menandai tahap Selesai, mengaktifkan tahap berikutnya.<br>3. **Sistem:** mengirim email "lolos skrining CV" *(«include» UC-28)* & kembali ke pipeline. |
| **Bagian Alternatif** | **A1 — Gagal:** tahap ditandai Gagal, email penolakan, pipeline berhenti.<br>**A2 — Ditangguhkan** *(«extend» UC-29)*: tahap ditandai Ditangguhkan.<br>**E1 — Tahap sudah diputus:** pesan "Keputusan tidak dapat diberikan untuk tahap ini."<br>**E2 — Tes Kompetensi belum dikonfigurasi:** transaksi dibatalkan, pesan "Konfigurasi tes kompetensi harus dibuat terlebih dahulu." |
| **Kesimpulan** | Use case selesai saat keputusan tersimpan & kandidat dipindahkan sesuai keputusan. |
| **Pasca Kondisi** | Keputusan & catatan tercatat; status kandidat berubah; notifikasi terkirim. |
| **Aturan Bisnis** | Keputusan hanya pada tahap Aktif/Ditangguhkan; HR Admin → `skrining_cv_hr`; bila tahap berikut Tes Kompetensi, konfigurasi tes wajib ada. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`decide`); operasi transaksional atomik. |
| **Implementasi** | `CvScreeningController@decide` → `ApplicationPipelineService` (`advance`/`fail`/`reserve`); rute `lowongan.skrining.keputusan`. |
| **Asumsi** | CV telah diunggah saat melamar; alur memuat tahap skrining HR. |
| **Isu Terbuka** | Belum ada audit perubahan keputusan untuk tahap yang ditangguhkan lalu diputus ulang. |

## UC-17 — Skrining CV User

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Skrining CV User |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-17 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap penyaringan CV oleh unit pengguna |
| **Pelaku Bisnis Primer** | Kepala Unit, Karyawan |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | HR Admin, Kandidat |
| **Deskripsi** | Use case ini merekam keputusan penyaringan CV pada tahap unit (`skrining_cv_user`) oleh Kepala Unit atau Karyawan unit terkait, dengan keputusan lulus/gagal/ditangguhkan beserta catatan. |
| **Prakondisi** | Kepala Unit/Karyawan login; lamaran pada tahap `Skrining CV User` berstatus Aktif/Ditangguhkan. |
| **Pemicu** | Pelaku menekan tombol keputusan skrining pada detail kandidat. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** meninjau CV, mengisi catatan, memilih keputusan, menyimpan.<br>2. **Sistem:** menyimpan catatan & peninjau, memproses keputusan, kembali ke pipeline.<br>3. **Sistem:** mengirim email transisi tahap ke kandidat *(«include» UC-28)*. |
| **Bagian Alternatif** | **A1 — Gagal:** tahap ditandai Gagal, email penolakan, pipeline berhenti.<br>**A2 — Ditangguhkan** *(«extend» UC-29)*.<br>**E1 — Tahap sudah diputus:** pesan penolakan keputusan. |
| **Kesimpulan** | Use case selesai saat keputusan unit tersimpan. |
| **Pasca Kondisi** | Status kandidat berubah sesuai keputusan; notifikasi terkirim. |
| **Aturan Bisnis** | Peran Kepala Unit/Karyawan diarahkan ke `skrining_cv_user`; akses dibatasi unit terkait. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`decide`); transaksional atomik. |
| **Implementasi** | `CvScreeningController@decide` (resolusi peran → `skrining_cv_user`); rute `lowongan.skrining.keputusan`. |
| **Asumsi** | Lowongan menyertakan tahap skrining unit. |
| **Isu Terbuka** | — |

## UC-18 — Kelola Tes Kompetensi

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kelola Tes Kompetensi |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-18 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Konfigurasi tes per lowongan |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | Kepala Unit, Kandidat |
| **Deskripsi** | Use case ini menyusun tes kompetensi untuk satu lowongan: memilih/menyusun soal (dari bank soal), menetapkan poin/ambang, lalu menyimpannya sebagai snapshot agar tes yang dikerjakan kandidat tidak berubah meski bank soal diubah. |
| **Prakondisi** | HR Admin login; lowongan ada; bank soal tersedia. |
| **Pemicu** | HR Admin membuka konfigurasi tes pada lowongan. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** menyusun soal & parameter tes lalu menyimpan.<br>2. **Sistem:** memvalidasi, menyimpan konfigurasi & membuat snapshot tes, menampilkan pesan sukses. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** sistem menampilkan error.<br>**E2 — Belum dikonfigurasi saat kandidat akan masuk tahap tes:** pipeline menolak memajukan kandidat (lihat UC-16 E2). |
| **Kesimpulan** | Use case selesai saat konfigurasi tes & snapshot tersimpan. |
| **Pasca Kondisi** | Saat kandidat masuk tahap `tes_kompetensi`, sistem menerbitkan tautan tes dari snapshot. |
| **Aturan Bisnis** | Tes yang dikerjakan kandidat mengacu snapshot, bukan bank soal langsung; konfigurasi wajib ada sebelum kandidat dimajukan ke tahap tes. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin. |
| **Implementasi** | `VacancyTestController@show/save`; model `VacancyTest`, `VacancyTestSnapshot`, `VacancyTestSnapshotQuestion/Option`; rute `lowongan.tes.*`. |
| **Asumsi** | Bank soal telah disiapkan (UC-10). |
| **Isu Terbuka** | — |

## UC-19 — Tinjau Jawaban Tes

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Tinjau Jawaban Tes |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-19 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Penilaian hasil tes kompetensi |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | Kepala Unit, Kandidat |
| **Deskripsi** | Use case ini menilai jawaban tes kompetensi kandidat — memberi skor pada jawaban esai (pilihan ganda dinilai otomatis), menjumlahkan total skor saat semua jawaban dinilai — lalu mengambil keputusan lulus/gagal/ditangguhkan. |
| **Prakondisi** | HR Admin login; kandidat telah mengirim jawaban tes; tahap `tes_kompetensi` Aktif. |
| **Pemicu** | HR Admin membuka ulasan jawaban tes kandidat. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memberi skor tiap jawaban esai.<br>2. **Sistem:** menyimpan skor & menandai ditinjau; bila semua jawaban ditinjau, menghitung total skor.<br>3. **Pelaku:** memilih keputusan & menyimpan.<br>4. **Sistem:** memproses keputusan (advance/fail/reserve) & kembali ke pipeline. |
| **Bagian Alternatif** | **E1 — Skor di luar rentang poin soal:** validasi menolak (`max:nilai_poin`).<br>**E2 — Keputusan sebelum semua jawaban dinilai:** pesan "Semua jawaban harus dinilai sebelum mengambil keputusan."<br>**A1 — Gagal/Ditangguhkan:** sesuai keputusan. |
| **Kesimpulan** | Use case selesai saat keputusan tes tersimpan. |
| **Pasca Kondisi** | Total skor & keputusan tercatat; kandidat dipindahkan sesuai keputusan; notifikasi terkirim. |
| **Aturan Bisnis** | Hanya jawaban esai dinilai manual; keputusan mensyaratkan seluruh jawaban telah dinilai. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`reviewEssay`, `decide`); validasi rentang skor; transaksional. |
| **Implementasi** | `TestReviewController@scoreEssay/decide` → `ApplicationPipelineService`; rute `lowongan.tes.ulasan.*`. |
| **Asumsi** | Kandidat menyelesaikan tes via tautan tertoken (UC-33). |
| **Isu Terbuka** | — |

## UC-20 — Lihat Hasil DiSC/MBTI

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lihat Hasil DiSC/MBTI |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-20 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Asesmen kepribadian kandidat |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Kepala Unit, HR Manager, Direktur |
| **Stakeholder yang Berminat** | Pewawancara |
| **Deskripsi** | Use case ini menampilkan hasil tes kepribadian DiSC dan/atau MBTI kandidat (profil tipe & skor) sebagai bahan pertimbangan pada tahap wawancara dan keputusan seleksi. |
| **Prakondisi** | Kandidat telah menyelesaikan tes DiSC/MBTI; pengguna terotorisasi. |
| **Pemicu** | Pengguna membuka detail kandidat yang memuat hasil kepribadian. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka detail kandidat.<br>2. **Sistem:** menampilkan hasil DiSC/MBTI yang telah dihitung. |
| **Bagian Alternatif** | **A1 — Tes belum dikerjakan:** sistem menampilkan status belum tersedia. |
| **Kesimpulan** | Use case selesai saat hasil kepribadian tampil. |
| **Pasca Kondisi** | Pewawancara memperoleh gambaran kepribadian kandidat. |
| **Aturan Bisnis** | Hasil dihitung dari jawaban submission DiSC/MBTI kandidat. |
| **Batasan dan Spesifikasi** | Akses dibatasi peran; bagian dari halaman detail kandidat. |
| **Implementasi** | `VacancyPipelineController@showApplication`; model `DiscResult`, `MbtiResult`, `DiscSubmission`, `MbtiSubmission`. |
| **Asumsi** | Tahap tes DiSC/MBTI termasuk dalam alur lowongan. |
| **Isu Terbuka** | — |

## UC-21 — Wawancara User

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Wawancara User |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-21 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap wawancara unit |
| **Pelaku Bisnis Primer** | Kepala Unit, Karyawan (pewawancara yang ditugaskan) |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | HR Admin, Kandidat |
| **Deskripsi** | Use case ini merekam hasil wawancara tahap unit: nilai per kriteria, jawaban kesiapan kerja, catatan, dan keputusan lulus/gagal/ditangguhkan; hanya pewawancara yang ditugaskan yang boleh merekam. |
| **Prakondisi** | Pewawancara login & ditugaskan; tahap `wawancara_user` Aktif/Ditangguhkan; hasil belum direkam. |
| **Pemicu** | Pewawancara membuka form keputusan wawancara. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi rating kriteria, jawaban kesiapan, catatan, memilih keputusan, menyimpan.<br>2. **Sistem:** menyimpan `InterviewResult` + rating + jawaban kesiapan, memproses keputusan, kembali ke pipeline.<br>3. **Sistem:** mengirim email transisi tahap *(«include» UC-28)*. |
| **Bagian Alternatif** | **E1 — Hasil sudah direkam:** pesan "Hasil wawancara sudah direkam sebelumnya."<br>**E2 — Bukan pewawancara yang ditugaskan:** sistem menolak (403).<br>**E3 — Tahap sudah diputus:** pesan penolakan.<br>**A1 — Gagal/Ditangguhkan:** sesuai keputusan. |
| **Kesimpulan** | Use case selesai saat hasil & keputusan wawancara tersimpan. |
| **Pasca Kondisi** | Hasil wawancara tercatat; kandidat dipindahkan sesuai keputusan; keputusan dicatat ke log. |
| **Aturan Bisnis** | Satu hasil per tahap wawancara; pewawancara `wawancara_user` harus sesuai `interviewer_id`. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`decideInterview`); transaksional; pencatatan log. |
| **Implementasi** | `InterviewController@decide` (resolusi peran → `wawancara_user`) + `InterviewScheduleController`; rute `lowongan.wawancara.*`. |
| **Asumsi** | Pewawancara telah dijadwalkan/ditugaskan. |
| **Isu Terbuka** | — |

## UC-22 — Wawancara Manajer HR

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Wawancara Manajer HR |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-22 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap wawancara manajerial |
| **Pelaku Bisnis Primer** | HR Manager |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | HR Admin, Kandidat |
| **Deskripsi** | Use case ini merekam hasil & keputusan wawancara tahap Manajer HR (`wawancara_manajer_hr`) berupa rating, jawaban kesiapan, catatan, dan keputusan lulus/gagal/ditangguhkan. |
| **Prakondisi** | HR Manager login; tahap `wawancara_manajer_hr` Aktif/Ditangguhkan; hasil belum direkam. |
| **Pemicu** | HR Manager membuka form keputusan wawancara. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi penilaian & keputusan, menyimpan.<br>2. **Sistem:** menyimpan hasil, memproses keputusan, kembali ke pipeline & mengirim notifikasi. |
| **Bagian Alternatif** | **E1 — Hasil sudah direkam:** pesan penolakan.<br>**E2 — Tahap sudah diputus:** pesan penolakan.<br>**A1 — Gagal/Ditangguhkan:** sesuai keputusan. |
| **Kesimpulan** | Use case selesai saat hasil wawancara manajer tersimpan. |
| **Pasca Kondisi** | Hasil tercatat; kandidat dipindahkan sesuai keputusan. |
| **Aturan Bisnis** | Peran HR Manager diarahkan ke `wawancara_manajer_hr`; satu hasil per tahap. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`decideInterview`); transaksional. |
| **Implementasi** | `InterviewController@decide` (resolusi peran → `wawancara_manajer_hr`); rute `lowongan.wawancara.keputusan`. |
| **Asumsi** | Tahap wawancara manajer termasuk dalam alur lowongan. |
| **Isu Terbuka** | — |

## UC-23 — Wawancara Direktur

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Wawancara Direktur |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-23 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap wawancara direksi |
| **Pelaku Bisnis Primer** | Direktur |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | HR Admin, Kandidat |
| **Deskripsi** | Use case ini merekam hasil & keputusan wawancara tahap Direktur (`wawancara_direktur`), umumnya tahap wawancara final sebelum penawaran. |
| **Prakondisi** | Direktur login; tahap `wawancara_direktur` Aktif/Ditangguhkan; hasil belum direkam. |
| **Pemicu** | Direktur membuka form keputusan wawancara. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi penilaian & keputusan, menyimpan.<br>2. **Sistem:** menyimpan hasil, memproses keputusan, kembali ke pipeline & mengirim notifikasi. |
| **Bagian Alternatif** | **E1 — Hasil sudah direkam:** pesan penolakan.<br>**E2 — Tahap sudah diputus:** pesan penolakan.<br>**A1 — Gagal/Ditangguhkan:** sesuai keputusan. |
| **Kesimpulan** | Use case selesai saat hasil wawancara direktur tersimpan. |
| **Pasca Kondisi** | Hasil tercatat; kandidat dipindahkan sesuai keputusan. |
| **Aturan Bisnis** | Peran Direktur diarahkan ke `wawancara_direktur`; satu hasil per tahap. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`decideInterview`); transaksional. |
| **Implementasi** | `InterviewController@decide` (resolusi peran → `wawancara_direktur`); rute `lowongan.wawancara.keputusan`. |
| **Asumsi** | Tahap wawancara direktur termasuk dalam alur lowongan. |
| **Isu Terbuka** | — |

## UC-24 — Jadwalkan MCU

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Jadwalkan MCU |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-24 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Tahap pemeriksaan kesehatan (medical check-up) |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email, Kandidat |
| **Stakeholder yang Berminat** | Kandidat |
| **Deskripsi** | Use case ini menjadwalkan MCU bagi kandidat pada tahap `mcu` (waktu & lokasi) dan menginformasikannya, sebelum hasil MCU direkam. |
| **Prakondisi** | HR Admin login; kandidat berada pada tahap `mcu`. |
| **Pemicu** | HR Admin membuka form jadwal MCU. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi jadwal & lokasi MCU, menyimpan.<br>2. **Sistem:** menyimpan jadwal pada tahap MCU & menampilkan pesan sukses. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** sistem menampilkan error. |
| **Kesimpulan** | Use case selesai saat jadwal MCU tersimpan. |
| **Pasca Kondisi** | Jadwal MCU tercatat; kandidat dapat diinformasikan; siap untuk perekaman hasil (UC-25). |
| **Aturan Bisnis** | Penjadwalan dilakukan pada tahap MCU sebelum keputusan. |
| **Batasan dan Spesifikasi** | Akses peran HR Admin; `scopeBindings`. |
| **Implementasi** | `McuScheduleController@store`; rute `lowongan.mcu.jadwal`. |
| **Asumsi** | Tahap MCU termasuk dalam alur lowongan. |
| **Isu Terbuka** | — |

## UC-25 — Keputusan MCU

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Keputusan MCU |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-25 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Hasil pemeriksaan kesehatan |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | Kandidat |
| **Deskripsi** | Use case ini merekam hasil MCU kandidat — lulus, tidak lulus, atau ditangguhkan — beserta dokumen hasil & catatan, lalu memajukan kandidat ke onboarding bila lulus. |
| **Prakondisi** | HR Admin login; tahap `mcu` Aktif/Ditangguhkan; hasil MCU belum direkam. |
| **Pemicu** | HR Admin membuka form keputusan MCU. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memilih keputusan, mengunggah dokumen (opsional), mengisi catatan, menyimpan.<br>2. **Sistem:** menyimpan `McuResult` + dokumen, memproses keputusan, kembali ke pipeline. |
| **Bagian Alternatif** | **A1 — Tidak lulus:** tahap Gagal, kandidat ditolak.<br>**A2 — Ditangguhkan:** tahap Ditangguhkan.<br>**E1 — MCU sudah diproses:** pesan "MCU sudah selesai diproses."<br>**E2 — Hasil sudah direkam:** pesan penolakan.<br>**E3 — Galat saat simpan:** dokumen yang terlanjur diunggah dihapus (rollback). |
| **Kesimpulan** | Use case selesai saat hasil MCU tersimpan. |
| **Pasca Kondisi** | Hasil & dokumen MCU tercatat; kandidat dipindahkan (onboarding/ditolak/ditangguhkan). |
| **Aturan Bisnis** | Keputusan: `Lulus`→advance, `TidakLulus`→fail, `Ditangguhkan`→reserve; satu hasil MCU per kandidat. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`manageMcu`); unggah dokumen ke disk `public`; transaksional dengan kompensasi berkas. |
| **Implementasi** | `McuController@store`; enum `McuStatus`; rute `lowongan.mcu.keputusan`. |
| **Asumsi** | Jadwal MCU telah dibuat (UC-24). |
| **Isu Terbuka** | — |

## UC-26 — Kirim Surat Penawaran

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kirim Surat Penawaran |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-26 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap penawaran kerja |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email, Kandidat |
| **Stakeholder yang Berminat** | Direktur, Kandidat |
| **Deskripsi** | Use case ini menyusun surat penawaran kerja (jabatan, gaji, tanggal mulai, catatan) dan mengirimkannya ke kandidat melalui email berisi tautan terima/tolak bertanda-tangan (signed) yang berlaku 7 hari. |
| **Prakondisi** | HR Admin login; tahap `surat_penawaran` Aktif/Ditangguhkan; penawaran belum pernah dikirim. |
| **Pemicu** | HR Admin membuka form surat penawaran. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi detail penawaran & mengirim.<br>2. **Sistem:** menyimpan/memperbarui surat penawaran (status `pending`), membuat tautan tertanda terima & tolak (kedaluwarsa 7 hari).<br>3. **Sistem:** mengirim email penawaran *(«include» UC-28)* & menandai `sent_at`. |
| **Bagian Alternatif** | **E1 — Penawaran sudah dikirim:** pesan "Surat penawaran sudah pernah dikirim."<br>**E2 — Gagal kirim email:** pesan "Gagal mengirim email penawaran. Silakan coba lagi." (status tidak ditandai terkirim). |
| **Kesimpulan** | Use case selesai saat surat penawaran terkirim ke kandidat. |
| **Pasca Kondisi** | Surat penawaran tercatat & terkirim; kandidat dapat merespons (UC-36). |
| **Aturan Bisnis** | Satu surat penawaran per lamaran; tautan respons tertanda & kedaluwarsa 7 hari; pengiriman ulang ditolak bila sudah terkirim. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`manageOffering`); `temporarySignedRoute`. |
| **Implementasi** | `OfferingLetterController@send`; model `OfferingLetter`; rute `lowongan.surat-penawaran.kirim`. |
| **Asumsi** | Kandidat lulus seluruh tahap sebelumnya. |
| **Isu Terbuka** | — |

## UC-27 — Proses Onboarding

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Proses Onboarding |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-27 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap akhir penerimaan kandidat |
| **Pelaku Bisnis Primer** | HR Admin |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email, Kandidat |
| **Stakeholder yang Berminat** | Kepala Unit, Karyawan baru |
| **Deskripsi** | Use case ini mengirim undangan onboarding (tanggal bergabung & catatan) ke kandidat yang menerima penawaran, lalu menandai onboarding selesai sebagai tahap terakhir pipeline. |
| **Prakondisi** | HR Admin login; tahap `onboarding` Aktif/Ditangguhkan. |
| **Pemicu** | HR Admin membuka aksi onboarding kandidat. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi tanggal bergabung & catatan, mengirim undangan.<br>2. **Sistem:** menyimpan data onboarding, mengirim email undangan *(«include» UC-28)*.<br>3. **Pelaku:** menandai onboarding selesai.<br>4. **Sistem:** menyelesaikan tahap (advance) & menutup pipeline. |
| **Bagian Alternatif** | **E1 — Tahap onboarding sudah selesai:** pesan "Tahap onboarding sudah selesai." |
| **Kesimpulan** | Use case selesai saat onboarding ditandai selesai. |
| **Pasca Kondisi** | Kandidat resmi bergabung; seluruh tahap pipeline berstatus Selesai. |
| **Aturan Bisnis** | Onboarding adalah tahap terakhir (`is_locked_last`); undangan dikirim sebelum penyelesaian. |
| **Batasan dan Spesifikasi** | Otorisasi Policy (`manageOnboarding`). |
| **Implementasi** | `OnboardingController@sendInvitation/complete`; model `OnboardingResult`; rute `lowongan.onboarding.*`. |
| **Asumsi** | Kandidat telah menerima penawaran (UC-36). |
| **Isu Terbuka** | Penautan otomatis kandidat onboarded ke direktori karyawan belum didefinisikan di dokumen ini. |

---

# D. Use Case Bersama

## UC-28 — Kirim Notifikasi Email

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kirim Notifikasi Email |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-28 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Kebutuhan komunikasi otomatis ke kandidat |
| **Pelaku Bisnis Primer** | Sistem |
| **Pelaku Peserta Lain** | Kandidat (penerima) |
| **Stakeholder yang Berminat** | HR Admin |
| **Deskripsi** | Use case bersama ini mengirim email notifikasi berbasis template ke kandidat pada berbagai tahap (tes tersedia, lolos skrining, transisi tahap, penolakan, surat penawaran, undangan onboarding). Disertakan («include») oleh banyak use case pipeline. |
| **Prakondisi** | Terdapat template email yang sesuai; alamat email kandidat valid. |
| **Pemicu** | Use case pemanggil memicu pengiriman saat kejadian tahap terjadi. |
| **Bagian Khas Suatu Event** | 1. **Sistem (pemanggil):** memicu pengiriman dengan jenis & data konteks.<br>2. **Sistem:** menyusun email dari template & mengirim ke kandidat. |
| **Bagian Alternatif** | **E1 — Pengiriman gagal:** kegagalan dilaporkan (`report`) tanpa menggagalkan transaksi tahap utama (kecuali pada surat penawaran yang menampilkan error). |
| **Kesimpulan** | Use case selesai saat email terkirim atau kegagalan tercatat. |
| **Pasca Kondisi** | Kandidat menerima informasi tahap; kegagalan tidak membatalkan kemajuan pipeline (best-effort). |
| **Aturan Bisnis** | Isi email mengikuti template (UC-11) & variabel konteks tahap. |
| **Batasan dan Spesifikasi** | Pengiriman best-effort dengan penanganan galat; bergantung konfigurasi mailer. |
| **Implementasi** | `EmailNotificationService@dispatch`; dipanggil dari `ApplicationPipelineService`, `OfferingLetterController`, `OnboardingController`. |
| **Asumsi** | Layanan email terkonfigurasi. |
| **Isu Terbuka** | Belum ada antrian/retry terjadwal untuk email gagal. |

## UC-29 — Tandai Reserved

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Tandai Reserved |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-29 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Kebutuhan menahan kandidat potensial |
| **Pelaku Bisnis Primer** | HR Admin, Kepala Unit |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Manager, Direktur |
| **Deskripsi** | Use case perluasan («extend») ini menangguhkan kandidat pada tahap aktifnya (status Ditangguhkan) tanpa meloloskan atau menolak, sehingga kandidat dapat diproses kembali kemudian. Dipakai pada tahap skrining & wawancara. |
| **Prakondisi** | Terdapat tahap berstatus Aktif pada lamaran. |
| **Pemicu** | Pelaku memilih keputusan "Ditangguhkan" pada aksi tahap. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** memilih keputusan Ditangguhkan.<br>2. **Sistem:** menandai tahap aktif menjadi Ditangguhkan. |
| **Bagian Alternatif** | **E1 — Tidak ada tahap aktif:** pesan "Tidak ada tahap aktif yang dapat ditangguhkan." |
| **Kesimpulan** | Use case selesai saat tahap ditandai Ditangguhkan. |
| **Pasca Kondisi** | Kandidat tertahan pada tahap; tahap Ditangguhkan tetap dapat diputus ulang (advanceable). |
| **Aturan Bisnis** | Status Ditangguhkan tetap dapat dilanjutkan/digagalkan kemudian; berlaku pada skrining & wawancara. |
| **Batasan dan Spesifikasi** | Dipanggil dari aksi keputusan tahap; transaksional. |
| **Implementasi** | `ApplicationPipelineService@reserve`; dipanggil dari controller skrining/tes/wawancara/MCU. |
| **Asumsi** | Pelaku berwenang atas tahap tersebut. |
| **Isu Terbuka** | Belum ada batas waktu/expiry untuk status Ditangguhkan. |

---

# E. Portal Kandidat

## UC-30 — Lihat Lowongan

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lihat Lowongan |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-30 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Portal karier publik |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin |
| **Deskripsi** | Use case ini memungkinkan kandidat menelusuri daftar lowongan yang dipublikasikan dan membuka detailnya (deskripsi, unit, tenggat) sebagai pintu masuk untuk melamar. |
| **Prakondisi** | Terdapat lowongan berstatus Published. |
| **Pemicu** | Kandidat membuka portal karier. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka daftar/detail lowongan.<br>2. **Sistem:** menampilkan lowongan Published & detailnya. |
| **Bagian Alternatif** | **E1 — Lowongan tidak Published/kedaluwarsa:** sistem tidak menampilkan / 404 pada akses langsung. |
| **Kesimpulan** | Use case selesai saat lowongan/detail tampil. |
| **Pasca Kondisi** | Kandidat dapat melanjutkan ke pelamaran (UC-31). |
| **Aturan Bisnis** | Hanya lowongan Published dalam tenggat yang dapat dilamar. |
| **Batasan dan Spesifikasi** | Rate limit `public-browse`. |
| **Implementasi** | `CareerController@index/show`; rute `karier.index`, `karier.show`. |
| **Asumsi** | Lowongan dipublikasikan HR Admin. |
| **Isu Terbuka** | — |

## UC-31 — Lamar Lowongan

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lamar Lowongan |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-31 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Portal karier — kanal masuk kandidat ke pipeline |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | Sistem Notifikasi Email |
| **Stakeholder yang Berminat** | HR Admin, Unit pemilik lowongan |
| **Deskripsi** | Use case ini memandu kandidat mengisi formulir lamaran 8 langkah (data pribadi, pendidikan, pengalaman, dll.) dengan validasi per langkah, mengunggah CV, lalu mengirim lamaran sehingga terbentuk data kandidat, lamaran, dan tahapan pipeline. |
| **Prakondisi** | Lowongan Published & dalam tenggat. |
| **Pemicu** | Kandidat menekan "Lamar" pada detail lowongan. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka halaman lamar & mengisi tiap langkah.<br>2. **Sistem:** memvalidasi data langkah via AJAX tanpa menyimpan *(«include» UC-32)*; langkah 1 memeriksa email belum melamar.<br>3. **Pelaku:** mengunggah CV (& STR/SIP bila tenaga kesehatan) pada langkah 8 lalu mengirim.<br>4. **Sistem:** menyimpan kandidat, lamaran, & tahapan dari snapshot alur, membuat token, mengalihkan ke konfirmasi. |
| **Bagian Alternatif** | **E1 — Validasi langkah gagal:** error per-field (HTTP 422), tetap di langkah.<br>**E2 — Email sudah melamar:** pesan "Anda sudah pernah melamar lowongan ini."<br>**E3 — Lowongan tidak tersedia/kedaluwarsa:** 404. |
| **Kesimpulan** | Use case selesai saat lamaran tersimpan & kandidat menerima tautan konfirmasi. |
| **Pasca Kondisi** | Data kandidat & lamaran tersimpan; tahapan pipeline terbentuk; token & tautan status diterbitkan. |
| **Aturan Bisnis** | Satu email satu lamaran per lowongan; lamaran hanya selama Published & dalam tenggat. |
| **Batasan dan Spesifikasi** | Rate limit `public-submit` (submit) & `public-browse` (wizard); unggah CV/STR sesuai jenis & ukuran; 8 langkah. |
| **Implementasi** | `ApplicationController@create/store`, `ValidateApplicationStepController`, `ApplicationService@store`; rute `karier.lamar*`. |
| **Asumsi** | Kandidat memiliki email valid & CV digital. |
| **Isu Terbuka** | Wizard belum menyimpan draf; data hilang bila sesi tertutup sebelum submit. |

## UC-32 — Isi Data Pribadi

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Isi Data Pribadi |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-32 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Formulir lamaran multi-langkah |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin |
| **Deskripsi** | Use case ini merepresentasikan pengisian dan validasi data pribadi serta data pendukung kandidat per langkah wizard (identitas, keluarga, pendidikan, pengalaman, bahasa, dll.); disertakan («include») oleh Lamar Lowongan dan memvalidasi tiap langkah tanpa menyimpan permanen. |
| **Prakondisi** | Kandidat berada dalam wizard lamaran lowongan Published. |
| **Pemicu** | Kandidat berpindah antar langkah pada wizard. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengisi field langkah aktif & menekan lanjut.<br>2. **Sistem:** memvalidasi aturan langkah tersebut (`rulesForStep`) via AJAX & mengembalikan ok/gagal. |
| **Bagian Alternatif** | **E1 — Validasi gagal:** error per-field (HTTP 422), kandidat tetap di langkah.<br>**E2 — Langkah di luar 1–8:** 422.<br>**E3 — Email (langkah 1) sudah melamar:** error pada field email. |
| **Kesimpulan** | Use case selesai saat seluruh langkah lolos validasi & kandidat siap submit. |
| **Pasca Kondisi** | Data langkah tervalidasi; persistensi terjadi saat submit final (UC-31). |
| **Aturan Bisnis** | Validasi per langkah mencerminkan aturan `StoreApplicationRequest` yang dipersempit per langkah; pemeriksaan duplikasi email pada langkah 1. |
| **Batasan dan Spesifikasi** | Endpoint validasi tidak mempersistensi data; rate limit `public-browse`. |
| **Implementasi** | `ValidateApplicationStepController`, `StoreApplicationRequest@rulesForStep`; rute `karier.lamar.validate`. |
| **Asumsi** | Front-end memanggil validasi di tiap transisi langkah. |
| **Isu Terbuka** | — |

## UC-33 — Kerjakan Tes Kompetensi

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kerjakan Tes Kompetensi |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-33 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Tahap tes kompetensi |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin, Kepala Unit |
| **Deskripsi** | Use case ini memungkinkan kandidat mengerjakan tes kompetensi melalui tautan tertoken yang dikirim via email saat memasuki tahap tes; jawaban disimpan untuk ditinjau HR (UC-19). |
| **Prakondisi** | Kandidat masuk tahap `tes_kompetensi`; tautan tertoken valid & belum dikerjakan. |
| **Pemicu** | Kandidat membuka tautan tes dari email. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka tautan tes & mengerjakan soal.<br>2. **Sistem:** menampilkan soal dari snapshot tes.<br>3. **Pelaku:** mengirim jawaban.<br>4. **Sistem:** menyimpan jawaban pada submission. |
| **Bagian Alternatif** | **E1 — Token tidak valid:** sistem menolak akses.<br>**E2 — Tes sudah dikerjakan:** sistem menampilkan status sudah dikirim. |
| **Kesimpulan** | Use case selesai saat jawaban tes terkirim. |
| **Pasca Kondisi** | Jawaban tersimpan & siap ditinjau HR. |
| **Aturan Bisnis** | Tes mengacu snapshot tes lowongan; satu pengiriman per submission. |
| **Batasan dan Spesifikasi** | Rate limit `token-access` (buka) & `public-submit` (kirim). |
| **Implementasi** | `TestController@show/submit`; model `TestSubmission`, `TestAnswer`; rute `tes.show`, `tes.submit`. |
| **Asumsi** | Konfigurasi tes telah dibuat (UC-18). |
| **Isu Terbuka** | — |

## UC-34 — Kerjakan Tes DiSC

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kerjakan Tes DiSC |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-34 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Tahap asesmen kepribadian DiSC |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin, Pewawancara |
| **Deskripsi** | Use case ini memungkinkan kandidat mengerjakan tes kepribadian DiSC via tautan tertoken; jawaban diolah menjadi hasil DiSC yang dapat dilihat HR (UC-20). |
| **Prakondisi** | Kandidat masuk tahap `tes_disc`; tautan tertoken valid & belum dikerjakan. |
| **Pemicu** | Kandidat membuka tautan tes DiSC. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengerjakan & mengirim tes DiSC.<br>2. **Sistem:** menyimpan jawaban & menghitung hasil DiSC. |
| **Bagian Alternatif** | **E1 — Token tidak valid:** ditolak.<br>**E2 — Sudah dikerjakan:** ditampilkan status sudah dikirim. |
| **Kesimpulan** | Use case selesai saat tes DiSC terkirim & hasil terhitung. |
| **Pasca Kondisi** | Hasil DiSC tersedia bagi HR/pewawancara. |
| **Aturan Bisnis** | Satu submission per kandidat; hasil dihitung dari pola jawaban. |
| **Batasan dan Spesifikasi** | Rate limit `token-access` & `public-submit`. |
| **Implementasi** | `DiscTestController@show/submit`; model `DiscSubmission`, `DiscAnswer`, `DiscResult`; rute `tes-disc.*`. |
| **Asumsi** | Tahap DiSC termasuk dalam alur lowongan. |
| **Isu Terbuka** | — |

## UC-35 — Kerjakan Tes MBTI

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Kerjakan Tes MBTI |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-35 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Tahap asesmen kepribadian MBTI |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin, Pewawancara |
| **Deskripsi** | Use case ini memungkinkan kandidat mengerjakan tes kepribadian MBTI via tautan tertoken; jawaban diolah menjadi tipe MBTI yang dapat dilihat HR (UC-20). |
| **Prakondisi** | Kandidat masuk tahap `tes_mbti`; tautan tertoken valid & belum dikerjakan. |
| **Pemicu** | Kandidat membuka tautan tes MBTI. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** mengerjakan & mengirim tes MBTI.<br>2. **Sistem:** menyimpan jawaban & menghitung tipe MBTI. |
| **Bagian Alternatif** | **E1 — Token tidak valid:** ditolak.<br>**E2 — Sudah dikerjakan:** ditampilkan status sudah dikirim. |
| **Kesimpulan** | Use case selesai saat tes MBTI terkirim & hasil terhitung. |
| **Pasca Kondisi** | Hasil MBTI tersedia bagi HR/pewawancara. |
| **Aturan Bisnis** | Satu submission per kandidat; tipe dihitung dari pola jawaban. |
| **Batasan dan Spesifikasi** | Rate limit `token-access` & `public-submit`. |
| **Implementasi** | `MbtiTestController@show/submit`; model `MbtiSubmission`, `MbtiAnswer`, `MbtiResult`; rute `tes-mbti.*`. |
| **Asumsi** | Tahap MBTI termasuk dalam alur lowongan. |
| **Isu Terbuka** | — |

## UC-36 — Terima/Tolak Penawaran

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Terima/Tolak Penawaran |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-36 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Tinggi |
| **Sumber** | Respons kandidat atas penawaran kerja |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | Sistem Notifikasi (ke HR Admin) |
| **Stakeholder yang Berminat** | HR Admin |
| **Deskripsi** | Use case ini memungkinkan kandidat menerima atau menolak surat penawaran melalui tautan tertanda (signed) berlaku 7 hari; penerimaan memajukan kandidat ke MCU, penolakan menggagalkan tahap penawaran. HR Admin diberi tahu atas respons. |
| **Prakondisi** | Surat penawaran terkirim & belum direspons; tautan tertanda valid. |
| **Pemicu** | Kandidat membuka tautan terima/tolak dari email. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka tautan & memilih terima atau tolak (mengisi alasan bila menolak).<br>2. **Sistem:** menandai status penawaran (Accepted/Rejected) & waktu respons.<br>3a. **Terima:** sistem memajukan kandidat (advance) ke tahap berikutnya.<br>3b. **Tolak:** sistem menandai tahap `surat_penawaran` Gagal.<br>4. **Sistem:** memberi tahu HR Admin aktif *(notifikasi PenawaranDirespon)*. |
| **Bagian Alternatif** | **E1 — Sudah direspons:** sistem menampilkan halaman "sudah direspons".<br>**E2 — Tautan tidak tertanda/kedaluwarsa:** akses ditolak (middleware `signed`). |
| **Kesimpulan** | Use case selesai saat respons kandidat tercatat. |
| **Pasca Kondisi** | Status penawaran final; pipeline maju (terima) atau berhenti pada penawaran (tolak); HR Admin ternotifikasi. |
| **Aturan Bisnis** | Respons hanya sekali (`isResponded`); tautan tertanda kedaluwarsa 7 hari; terima→advance, tolak→tahap Gagal. |
| **Batasan dan Spesifikasi** | Middleware `signed` + rate limit `signed-access`; transaksional. |
| **Implementasi** | `OfferingResponseController@showAcceptForm/accept/showRejectForm/reject`; notifikasi `PenawaranDirespon`; rute `offering.accept*`, `offering.reject*`. |
| **Asumsi** | Surat penawaran telah dikirim (UC-26). |
| **Isu Terbuka** | — |

## UC-37 — Lihat Status Lamaran

| Atribut | Keterangan |
|---|---|
| **Nama Use Case** | Lihat Status Lamaran |
| **Versi** | 1.0.0 |
| **ID Use Case** | UC-37 |
| **Tipe Use Case** | Analisis Sistem |
| **Prioritas** | Sedang |
| **Sumber** | Transparansi proses bagi kandidat |
| **Pelaku Bisnis Primer** | Kandidat (tanpa akun) |
| **Pelaku Peserta Lain** | — |
| **Stakeholder yang Berminat** | HR Admin |
| **Deskripsi** | Use case ini memungkinkan kandidat memantau kemajuan lamarannya (tahap-tahap & statusnya) melalui tautan tertoken yang diterima saat melamar/transisi tahap. |
| **Prakondisi** | Lamaran ada; kandidat memiliki token lamaran. |
| **Pemicu** | Kandidat membuka tautan status. |
| **Bagian Khas Suatu Event** | 1. **Pelaku:** membuka tautan status lamaran.<br>2. **Sistem:** menampilkan lamaran, lowongan, & tahapan beserta statusnya. |
| **Bagian Alternatif** | **E1 — Token tidak ditemukan:** 404. |
| **Kesimpulan** | Use case selesai saat status lamaran tampil. |
| **Pasca Kondisi** | Kandidat mengetahui posisi lamarannya pada pipeline. |
| **Aturan Bisnis** | Akses berbasis token unik lamaran (tanpa akun). |
| **Batasan dan Spesifikasi** | Rate limit `token-access`. |
| **Implementasi** | `CandidateStatusController@show` (& `ApplicationController@confirmation`); rute `karier.lamaran.status`, `karier.lamaran.konfirmasi`. |
| **Asumsi** | Kandidat menyimpan tautan status dari konfirmasi/email. |
| **Isu Terbuka** | — |
