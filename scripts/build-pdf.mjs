// Build a PDF of the system-flow documentation from the captured screenshots.
//
// Usage:  node scripts/build-pdf.mjs
// Output: docs/alur-sistem/Dokumentasi-Alur-Sistem-ATS-v2.pdf  (v1 is kept)
//
// Renders a self-contained HTML (one screenshot per fixed-height page, with an
// Indonesian description) then prints it to PDF via Playwright/Chromium. Each
// page block is a fixed A4 content-height box with a single break-after, so
// pages never split or leave blanks; tall full-page screenshots are contained
// (letterboxed) inside the box.

import { chromium } from 'playwright';
import { writeFileSync, rmSync } from 'node:fs';
import { fileURLToPath, pathToFileURL } from 'node:url';
import { dirname, resolve } from 'node:path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(__dirname, '..');
const docDir = resolve(root, 'docs/alur-sistem');

const sections = [
  {
    id: 'A',
    title: 'Autentikasi & Dashboard per Peran',
    intro:
      'Sistem mengenal empat peran internal (HR Admin, Kepala Unit, Manajer HR, Direktur) plus Karyawan, serta Kandidat yang mengakses tanpa akun. Dashboard bersifat role-scoped: peran unit hanya melihat data unitnya.',
    shots: [
      { f: '01-login.png', t: 'Halaman Masuk', role: 'Publik / internal', d: 'Pintu masuk pengguna internal. Form kredensial (nama pengguna + kata sandi) dengan opsi "ingat saya". Panel kiri menampilkan branding RS Azra. Kandidat tidak melewati halaman ini — mereka memakai tautan bertoken.' },
      { f: '02-dashboard-hr-admin.png', t: 'Dashboard HR Admin (tingkat organisasi)', role: 'HR Admin', d: 'Metrik rekrutmen seluruh unit: kartu ringkasan (Total Lamaran, Dalam Proses, Diterima, Lowongan Aktif), Corong Pipeline kumulatif per tahap, tabel Tingkat Lulus/Gagal per tahap, Hambatan Tahap (rata-rata hari), dan Waktu Rekrutmen. Dilengkapi filter rentang tanggal & lowongan.' },
      { f: '03-dashboard-unit-head.png', t: 'Dashboard Kepala Unit (scoped: IGD)', role: 'Kepala Unit', d: 'Tampilan dan metrik identik HR Admin, tetapi seluruh data dibatasi pada unit pengguna — perhatikan judul "Dashboard Rekrutmen — IGD". Menu samping menyusut hanya menjadi Beranda & Lowongan Kerja.' },
      { f: '04-dashboard-hr-manager.png', t: 'Dashboard Manajer HR', role: 'Manajer HR', d: 'Ringkasan rekrutmen tingkat organisasi (lintas unit), sesuai cakupan Manajer HR yang menangani tahap Wawancara Manajer HR.' },
      { f: '05-dashboard-direktur.png', t: 'Dashboard Direktur', role: 'Direktur', d: 'Ringkasan rekrutmen tingkat organisasi untuk mendukung tahap Wawancara Direktur (wawancara final pada alur Koordinator).' },
      { f: '06-dashboard-employee.png', t: 'Dashboard Karyawan', role: 'Karyawan', d: 'Karyawan yang unit-nya cocok dengan lowongan melihat tampilan unit-scoped yang sama dengan Kepala Unit (mis. IGD) dan dapat menangani skrining/wawancara sisi unit.' },
    ],
  },
  {
    id: 'B',
    title: 'Alur Kandidat (Publik, Tanpa Akun)',
    intro:
      'Kandidat tidak memiliki akun. Mereka melamar lewat halaman karier publik dan menjalani tahap-tahap berbasis tautan bertoken yang dikirim via email.',
    shots: [
      { f: '10-karier-list.png', t: 'Halaman Karier — Daftar Lowongan', role: 'Kandidat (publik)', d: 'Landing rekrutmen: headline, statistik ringkas, kotak pencarian, filter (Bidang/Departemen & Jenis Pekerjaan), serta grid kartu lowongan yang sedang dibuka. Tanpa login. (Kartu flyer kosong karena data demo tidak memiliki gambar flyer.)' },
      { f: '11-karier-detail.png', t: 'Detail Lowongan', role: 'Kandidat (publik)', d: 'Menampilkan judul posisi, Deskripsi Pekerjaan, dan Kualifikasi, dengan panel "Detail Posisi" (jenis pekerjaan, jumlah posisi, tenggat) dan tombol "Lamar Sekarang".' },
      { f: '12-lamar-form.png', t: 'Formulir Lamaran', role: 'Kandidat (publik)', d: 'Form data pribadi multi-langkah dengan indikator langkah. Bagian "Identitas Diri" (NIK, tempat/tanggal lahir, jenis kelamin, agama, dll), kontak darurat, dan unggah CV. Validasi dilakukan per langkah.' },
      { f: '13-lamar-konfirmasi.png', t: 'Konfirmasi Lamaran', role: 'Kandidat (publik)', d: 'Halaman setelah submit: konfirmasi bahwa lamaran terkirim, beserta tautan status bertoken untuk dipantau kandidat.' },
      { f: '14-status-kandidat.png', t: 'Status Kandidat (Tautan Bertoken)', role: 'Kandidat (publik)', d: 'Halaman read-only di alamat lamaran/{token} yang menampilkan posisi dilamar dan pelacak tahap lamaran — tanpa perlu akun.' },
      { f: '15-tes-kompetensi.png', t: 'Tes Kompetensi (Tautan Bertoken)', role: 'Kandidat (publik)', d: 'Halaman pengerjaan tes via tes/{token}. Berisi soal pilihan ganda (auto-skor) dan esai (review manual), dengan timer hitung mundur dan tombol "Kirim Jawaban".' },
      { f: '16-tes-disc.png', t: 'Tes DiSC (Tautan Bertoken)', role: 'Kandidat (publik)', d: 'Untuk tiap kelompok kata, kandidat memilih kata yang PALING dan PALING TIDAK menggambarkan dirinya. Auto-skor, hasil bersifat informasional dan tidak terlihat oleh kandidat.' },
      { f: '17-tes-mbti.png', t: 'Tes MBTI (Tautan Bertoken)', role: 'Kandidat (publik)', d: 'Rangkaian pertanyaan dua pilihan; auto-skor, informasional.' },
      { f: '18-penawaran-terima.png', t: 'Respons Surat Penawaran (Tautan Bertanda Tangan)', role: 'Kandidat (publik)', d: 'Halaman terima/tolak penawaran via tautan email signed (penawaran/{id}/terima). Menampilkan ringkasan penawaran (posisi, gaji, tanggal mulai) dan tombol "Konfirmasi Penerimaan".' },
    ],
  },
  {
    id: 'C',
    title: 'Pipeline Rekrutmen — Tahap demi Tahap',
    intro:
      'Tiap tahap detail ditangkap sebagai peran yang berwenang atas tahap tersebut, sehingga form aksi yang sebenarnya (keputusan, penjadwalan, pengiriman) terlihat. Untuk tahap dengan data, ditampilkan juga varian "terisi" tepat setelah varian kosongnya. Status tahap: Pending, Aktif, Selesai, Gagal, atau Cadangan; pergerakan hanya maju.',
    shots: [
      { f: '51-template-lowongan-list.png', t: 'Titik Awal — Template Lowongan', role: 'HR Admin', d: 'Sebelum pipeline berjalan, HR menerbitkan lowongan dari Template Lowongan (deskripsi & kualifikasi siap pakai) menjadi lowongan nyata. Pengelolaan template selengkapnya ada di Bagian D.2.' },
      { f: '20-lowongan-list.png', t: 'Daftar Lowongan (Internal)', role: 'HR Admin', d: 'Tabel lowongan (Posisi, Unit, Jenis, Tenggat, Status) dengan aksi per baris (pipeline, salin tautan, edit, hapus) dan tombol "Terbitkan dari Template". Titik awal pengelolaan rekrutmen.' },
      { f: '21-pipeline-board.png', t: 'Papan Pipeline', role: 'HR Admin', d: 'Menampilkan seluruh kandidat sebuah lowongan beserta status tiap tahap per baris. Dari sini kandidat dibuka untuk diproses.' },
      { f: '22-pipeline-lamaran.png', t: 'Tahap 1 — Lamaran', role: 'HR Admin', d: 'Detail kandidat begitu masuk pipeline: pelacak tahap di atas, data pribadi, kontak darurat, data keluarga, pendidikan, dan data lamaran/CV.' },
      { f: '23-pipeline-skrining-cv-hr.png', t: 'Tahap 2 — Skrining CV HR', role: 'HR Admin', d: 'Panel "Keputusan Skrining" dengan opsi Lulus / Tunda / Gagal + catatan, di samping data lengkap kandidat. Bukti form keputusan asli muncul bagi peran pemilik tahap.' },
      { f: '24-pipeline-skrining-cv-user.png', t: 'Tahap 3 — Skrining CV User', role: 'Kepala Unit', d: 'Skrining sisi unit oleh kepala unit terkait, dengan form keputusan yang sama (Lulus/Tunda/Gagal). Diakses sebagai akun Kepala Unit.' },
      { f: '25-pipeline-tes-kompetensi.png', t: 'Tahap 4 — Tes Kompetensi (belum dikerjakan)', role: 'HR Admin', d: 'Panel hasil tes untuk ditinjau HR Admin. Pada kandidat ini tes belum dikerjakan, sehingga panel menampilkan status "belum menyelesaikan tes" beserta tautan tes.' },
      { f: '25b-tes-kompetensi-terisi.png', t: 'Tahap 4 — Tes Kompetensi (kandidat telah mengisi)', role: 'HR Admin', d: 'Setelah kandidat mengerjakan: panel menampilkan Total Skor, rincian jawaban (pilihan ganda ditandai benar/salah, esai dinilai manual), lalu form Keputusan Tes Kompetensi (Loloskan / Tangguhkan / Tolak) + catatan.' },
      { f: '26-pipeline-wawancara-user.png', t: 'Tahap 5 — Wawancara User (jadwal belum ditetapkan)', role: 'Kepala Unit', d: 'Form "Jadwalkan Wawancara" (tanggal & lokasi) beserta pemilihan pewawancara. Keputusan baru bisa diberikan setelah jadwal ditetapkan dan form penilaian diisi.' },
      { f: '26b-wawancara-user-penilaian.png', t: 'Tahap 5 — Wawancara User (jadwal & form penilaian)', role: 'Kepala Unit', d: 'Setelah jadwal & pewawancara ditetapkan, muncul "Penilaian Wawancara": skala 1–5 untuk tiap kriteria penilaian, pertanyaan kesiapan, lalu keputusan Lulus/Tunda/Gagal + catatan.' },
      { f: '27-pipeline-wawancara-manajer-hr.png', t: 'Tahap 6 — Wawancara Manajer HR (jadwal belum ditetapkan)', role: 'Manajer HR', d: 'Form penjadwalan wawancara oleh Manajer HR.' },
      { f: '27b-wawancara-manajer-hr-penilaian.png', t: 'Tahap 6 — Wawancara Manajer HR (form penilaian)', role: 'Manajer HR', d: 'Form Penilaian Wawancara (skala kriteria + kesiapan + keputusan) setelah jadwal ditetapkan, diakses sebagai Manajer HR.' },
      { f: '28-pipeline-wawancara-direktur.png', t: 'Tahap 7 — Wawancara Direktur (jadwal belum ditetapkan)', role: 'Direktur', d: 'Form penjadwalan wawancara final. Tangkapan diambil saat login sebagai Direktur — perhatikan badge peran dan menu samping yang menyusut.' },
      { f: '28b-wawancara-direktur-penilaian.png', t: 'Tahap 7 — Wawancara Direktur (form penilaian)', role: 'Direktur', d: 'Form Penilaian Wawancara final (kriteria + kesiapan + keputusan) setelah jadwal ditetapkan, diakses sebagai Direktur.' },
      { f: '29-pipeline-tes-disc.png', t: 'Tahap 8 — Tes DiSC (belum dikerjakan)', role: 'HR Admin (informasional)', d: 'Panel hasil DiSC. Pada kandidat ini tes belum dikerjakan → status "belum menyelesaikan tes".' },
      { f: '29b-tes-disc-selesai.png', t: 'Tahap 8 — Tes DiSC (kandidat selesai)', role: 'HR Admin (informasional)', d: 'Panel "Hasil Tes DiSC" menampilkan skor D/I/S/C beserta Tipe Primer & Sekunder (auto-skor, informasional). Disertai aksi Lanjutkan/Tolak.' },
      { f: '30-pipeline-tes-mbti.png', t: 'Tahap 9 — Tes MBTI (belum dikerjakan)', role: 'HR Admin (informasional)', d: 'Panel hasil MBTI. Pada kandidat ini masih kosong.' },
      { f: '30b-tes-mbti-selesai.png', t: 'Tahap 9 — Tes MBTI (kandidat selesai)', role: 'HR Admin (informasional)', d: 'Panel hasil MBTI menampilkan tipe kepribadian beserta kekuatan tiap dimensi (auto-skor, informasional).' },
      { f: '31-pipeline-surat-penawaran.png', t: 'Tahap 10 — Surat Penawaran', role: 'HR Admin', d: 'Form "Detail Penawaran" (jabatan ditawarkan, gaji, tanggal mulai kerja, catatan) dengan tombol "Kirim Surat Penawaran"; status tahap "Menunggu Pengiriman".' },
      { f: '32-pipeline-mcu.png', t: 'Tahap 11 — MCU (jadwal belum ditetapkan)', role: 'HR Admin', d: 'Form "Jadwalkan MCU" (tanggal & lokasi) dengan tombol "Simpan & Kirim Instruksi MCU".' },
      { f: '32b-mcu-input.png', t: 'Tahap 11 — MCU (input hasil oleh HR Admin)', role: 'HR Admin', d: 'Setelah jadwal ditetapkan, HR Admin mengunggah dokumen hasil MCU (PDF, maks. 5 MB) dan menetapkan keputusan (Lulus / Ditangguhkan / Tidak Lulus) + catatan.' },
      { f: '33-pipeline-selesai.png', t: 'Tahap 12 — Onboarding / Selesai', role: 'HR Admin', d: 'Kandidat yang lulus seluruh tahap; Onboarding sebagai tahap akhir pipeline.' },
      { f: '34-callback.png', t: 'Panggil Kembali Kandidat — daftar kosong', role: 'HR Admin', d: 'Tampilan callback ketika belum ada kandidat gagal dari periode sebelumnya pada template lowongan ini.' },
      { f: '34b-callback-terisi.png', t: 'Panggil Kembali Kandidat — daftar terisi', role: 'HR Admin', d: 'Kandidat gagal dari periode sebelumnya (template lowongan sama) muncul dengan badge status: A belum diundang (eligible), B "Sudah diundang", C "Sudah melamar". HR memilih kandidat lalu menekan "Kirim Undangan" — sistem mengirim email undangan melamar kembali.' },
      { f: '34c-callback-melamar-kembali.png', t: 'Panggil Kembali Kandidat — kandidat melamar kembali', role: 'HR Admin', d: 'Kandidat C yang telah diundang kemudian melamar ke periode/lowongan baru dan masuk kembali ke pipeline (tahap Lamaran) — menutup siklus callback dari gagal → diundang → melamar kembali.' },
    ],
  },
  {
    id: 'D',
    title: 'Konfigurasi Template',
    intro: 'Template menggerakkan alur, soal, kriteria wawancara, dan komunikasi email.',
    shots: [
      { f: '50-template-alur-list.png', t: 'Template Alur Kerja', role: 'HR Admin', d: 'Tiga template bawaan (Kepala Unit — 10 tahap, Koordinator — 12 tahap, Staf — 11 tahap) ditampilkan sebagai rangkaian chip tahap yang menunjukkan urutan. Tahap dapat diaktif/nonaktifkan dan diurut ulang (mesin alur semi-configurable).' },
      { f: '51-template-lowongan-list.png', t: 'Template Lowongan', role: 'HR Admin', d: 'Daftar template deskripsi & kualifikasi posisi yang dapat diterbitkan menjadi lowongan nyata.' },
      { f: '52-template-wawancara-list.png', t: 'Template Wawancara', role: 'HR Admin', d: 'Daftar kriteria penilaian wawancara terstruktur (default global, dapat dioverride per lowongan).' },
      { f: '53-template-bank-soal-list.png', t: 'Template Bank Soal', role: 'HR Admin', d: 'Koleksi soal tes kompetensi yang dapat dipakai di berbagai lowongan (per departemen). Pada data demo belum ada template.' },
      { f: '54-template-email-list.png', t: 'Template Email', role: 'HR Admin', d: 'Daftar template email otomatis (kunci, deskripsi, subjek) beserta placeholder yang tersedia ({nama_kandidat}, {link_tes}, {tanggal_interview}, dll). Email terkirim otomatis pada transisi tahap.' },
    ],
  },
  {
    id: 'E',
    title: 'Manajemen Karyawan & Akun',
    intro: 'Modul direktori karyawan, akun login, dan unit/departemen.',
    shots: [
      { f: '60-karyawan-list.png', t: 'Direktori Karyawan', role: 'HR Admin', d: 'Tabel data karyawan (NIP, Nama, Unit, Posisi, Jabatan) dengan tombol "Tambah Karyawan".' },
      { f: '61-akun-list.png', t: 'Akun Pengguna', role: 'HR Admin', d: 'Manajemen akun login (Karyawan, Username, Role, Status). Sebagian akun belum tertaut ke data karyawan. Tombol "Buat Akun" dan aksi edit/nonaktifkan per baris.' },
      { f: '62-unit-list.png', t: 'Data Unit', role: 'HR Admin', d: 'Daftar unit/departemen RS (Administrasi, Bedah, Farmasi, Gizi, ICU, IGD, dll — 18 unit), berpaginasi, dengan tombol "Tambah Unit".' },
    ],
  },
];

// A4 content box at 12mm margins. 297-24 = 273mm tall; use 271mm for slack.
const PAGE_H_MM = 271;

const css = `
  @page { size: A4; margin: 12mm; }
  * { box-sizing: border-box; }
  html, body { margin: 0; }
  body { font-family: -apple-system, "Segoe UI", Roboto, Arial, sans-serif; color: #1f2937; }
  .page { height: ${PAGE_H_MM}mm; overflow: hidden; break-after: page; break-inside: avoid; }
  .page:last-child { break-after: auto; }

  .cover { display: flex; flex-direction: column; justify-content: center; align-items: flex-start; }
  .cover .kicker { color: #0f766e; letter-spacing: .15em; font-size: 12px; text-transform: uppercase; }
  .cover h1 { font-size: 38px; line-height: 1.12; margin: 8px 0 6px; }
  .cover .sub { font-size: 15px; color: #4b5563; max-width: 150mm; }
  .cover .meta { margin-top: 22px; font-size: 12px; color: #6b7280; }
  .note { background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 8px; padding: 10px 14px; font-size: 12px; color: #115e59; margin-top: 16px; max-width: 155mm; }

  .sec-cover { display: flex; flex-direction: column; justify-content: center; }
  .sec-cover .tag { display: inline-block; background: #0f766e; color: #fff; border-radius: 6px; padding: 4px 10px; font-weight: 700; font-size: 14px; align-self: flex-start; }
  .sec-cover h2 { font-size: 28px; margin: 12px 0 8px; }
  .sec-cover p { color: #4b5563; font-size: 14px; max-width: 155mm; line-height: 1.5; }

  .shot { display: flex; flex-direction: column; }
  .shot .head { border-left: 4px solid #0f766e; padding-left: 10px; margin-bottom: 6px; flex: 0 0 auto; }
  .shot .num { color: #0f766e; font-weight: 700; font-size: 12px; }
  .shot h3 { margin: 2px 0; font-size: 17px; }
  .shot .role { display: inline-block; font-size: 11px; color: #0f766e; background: #ccfbf1; border-radius: 4px; padding: 1px 8px; }
  .shot .desc { font-size: 12px; color: #374151; margin: 6px 0 8px; line-height: 1.45; flex: 0 0 auto; }
  .shot .frame { flex: 1 1 auto; min-height: 0; border: 1px solid #e5e7eb; border-radius: 6px; background: #f9fafb; display: flex; align-items: center; justify-content: center; overflow: hidden; }
  .shot img { max-width: 100%; max-height: 100%; object-fit: contain; }
`;

const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const shotsUrl = (f) => pathToFileURL(resolve(docDir, 'screenshots', f)).href;

let n = 0;
let body = `
  <div class="page cover">
    <div class="kicker">Sistem Rekrutmen — RS Azra</div>
    <h1>Dokumentasi Alur Sistem<br/>Applicant Tracking System</h1>
    <div class="sub">Panduan visual alur kerja ATS Rumah Sakit Azra: dari pelamaran kandidat, seleksi multi-tahap, hingga onboarding — beserta deskripsi setiap layar. Edisi ini menambahkan varian "terisi" tiap tahap dan alur callback lengkap.</div>
    <div class="note">Catatan: seluruh tangkapan layar diambil dari basis data demo (seeder). Lowongan "Koordinator Medis (Demo)" memuat satu kandidat aktif di setiap tahap, plus kandidat kasus untuk varian terisi. Nama & data kandidat adalah data palsu.</div>
    <div class="meta">Dihasilkan otomatis dari scripts/build-pdf.mjs · ${new Date().toISOString().slice(0, 10)}</div>
  </div>
`;

for (const sec of sections) {
  body += `
    <div class="page sec-cover">
      <span class="tag">Bagian ${sec.id}</span>
      <h2>${esc(sec.title)}</h2>
      <p>${esc(sec.intro)}</p>
    </div>
  `;
  for (const s of sec.shots) {
    n += 1;
    body += `
      <div class="page shot">
        <div class="head">
          <div class="num">${sec.id} · Langkah ${n}</div>
          <h3>${esc(s.t)}</h3>
          <span class="role">${esc(s.role)}</span>
        </div>
        <div class="desc">${esc(s.d)}</div>
        <div class="frame"><img src="${shotsUrl(s.f)}" /></div>
      </div>
    `;
  }
}

const html = `<!doctype html><html lang="id"><head><meta charset="utf-8"><style>${css}</style></head><body>${body}</body></html>`;

const htmlPath = resolve(docDir, '_pdf.html');
writeFileSync(htmlPath, html, 'utf8');

const browser = await chromium.launch();
try {
  const page = await browser.newPage();
  await page.goto(pathToFileURL(htmlPath).href, { waitUntil: 'networkidle' });

  // Assert no page block overflows the A4 content height (would split a page).
  const limitPx = (PAGE_H_MM / 25.4) * 96;
  const heights = await page.$$eval('.page', (els) => els.map((e) => e.getBoundingClientRect().height));
  const maxH = Math.max(...heights);
  const overflowing = heights.filter((h) => h > limitPx + 1).length;
  console.log(`pages: ${heights.length}  maxBlockHeight: ${maxH.toFixed(0)}px  limit: ${limitPx.toFixed(0)}px  overflowing: ${overflowing}`);
  if (overflowing > 0) {
    console.log('WARNING: some page blocks exceed A4 content height and may split.');
  }

  const out = resolve(docDir, 'Dokumentasi-Alur-Sistem-ATS-v2.pdf');
  await page.pdf({ path: out, format: 'A4', printBackground: true });
  console.log(`PDF written: ${out}  (${n} screenshots)`);
} finally {
  await browser.close();
  rmSync(htmlPath, { force: true });
}
