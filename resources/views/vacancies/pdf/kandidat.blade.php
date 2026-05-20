{{-- PDF view for candidate export — inline CSS only (no Tailwind, DomPDF-compatible) --}}
{{-- Variables: $application, $lowongan --}}
@php
    $candidate = $application->candidate;
    $val = fn ($v): string => ($v !== null && $v !== '') ? (string) $v : '—';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Data Kandidat - {{ $candidate->nama_lengkap }}</title>
<style>
    /* Brand palette (from app.css — CSS vars not supported in DomPDF, values inlined)
       --color-primary:      #007774
       --color-primary-dark: #005855
       --color-primary-50:   #e5f1f0
       --color-ink:          #0d1614
       --color-ink-2:        #2a3835
       --color-ink-3:        #5a6864
       --color-ink-4:        #8a948f
       --color-line:         #d9ddd9
       --color-line-2:       #ebeeea
       --color-paper:        #f7f6f1
    */

    @page {
        margin: 0;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9.5pt; color: #0d1614; line-height: 1.5; margin: 20mm 18mm 24mm 18mm; }

    /* ── Footer (fixed = repeats on every page) ── */
    .pdf-footer {
        position: fixed;
        bottom: 0;
        left: 18mm;
        right: 18mm;
        height: 16mm;
        border-top: 1px solid #d9ddd9;
        padding-top: 3mm;
        background-color: #ffffff;
    }
    .pdf-footer table { width: 100%; border-collapse: collapse; }
    .pdf-footer td { font-size: 7.5pt; color: #8a948f; vertical-align: middle; padding: 0; }
    .pdf-footer .footer-left { text-align: left; }
    .pdf-footer .footer-right { text-align: right; }

    /* ── Header ── */
    .header { border-bottom: 2.5px solid #007774; padding-bottom: 12px; margin-bottom: 20px; }
    .header h1 { font-size: 16pt; font-weight: bold; color: #005855; }
    .header .sub { font-size: 9.5pt; color: #2a3835; margin-top: 4px; }
    .header .meta { font-size: 8pt; color: #8a948f; margin-top: 3px; }

    /* ── Sections ── */
    .section { margin-bottom: 18px; }
    .section-title {
        font-size: 10pt; font-weight: bold; color: #007774;
        border-bottom: 1px solid #d9ddd9;
        padding: 0 0 5px 0; margin-bottom: 10px;
        letter-spacing: 0.02em;
    }

    /* ── Field labels & values ── */
    .label { font-size: 7.5pt; font-weight: bold; color: #5a6864; text-transform: uppercase; letter-spacing: 0.05em; }
    .value { font-size: 9.5pt; color: #0d1614; margin-top: 3px; margin-bottom: 8px; }

    /* ── Two-column grid (via table) ── */
    table.grid { width: 100%; border-collapse: collapse; }
    table.grid td { padding: 0 14px 2px 0; vertical-align: top; width: 50%; }
    table.grid td:last-child { padding-right: 0; }

    /* ── Data tables ── */
    table.data-table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-bottom: 8px; }
    table.data-table th {
        text-align: left; padding: 5px 8px;
        background-color: #e5f1f0;
        border-bottom: 1px solid #d9ddd9;
        font-weight: bold; color: #5a6864;
        font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.04em;
    }
    table.data-table td { padding: 5px 8px; border-bottom: 1px solid #ebeeea; color: #2a3835; vertical-align: top; }

    /* ── Subsections ── */
    .subsection-title { font-size: 8.5pt; font-weight: bold; color: #2a3835; margin: 10px 0 5px; text-transform: uppercase; letter-spacing: 0.04em; }

    /* ── Misc ── */
    .empty { color: #8a948f; font-style: italic; font-size: 8.5pt; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 4px; font-size: 9pt; font-weight: bold; background-color: #e5f1f0; color: #005855; }
    .note { font-size: 8pt; color: #5a6864; font-style: italic; margin-top: 3px; }
    .page-break { page-break-before: always; }
    .border-left { border-left: 3px solid #007774; padding-left: 10px; margin-bottom: 10px; }
    .fresh-grad-box { background-color: #f7f6f1; border: 1px solid #d9ddd9; padding: 8px 12px; border-radius: 4px; font-size: 8.5pt; color: #5a6864; }
</style>
</head>
<body>

{{-- ===== HEADER ===== --}}
<div class="header">
    <h1>{{ $candidate->nama_lengkap }}</h1>
    <div class="sub">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit?->nama }}</div>
    <div class="meta">Dicetak: {{ now()->format('d M Y') }}</div>
</div>

{{-- ===== SECTION 1: DATA PRIBADI ===== --}}
<div class="section">
    <div class="section-title">Data Pribadi</div>
    <table class="grid">
        <tr>
            <td>
                <div class="label">Nama Lengkap</div>
                <div class="value">{{ $val($candidate->nama_lengkap) }}</div>
            </td>
            <td>
                <div class="label">Email</div>
                <div class="value">{{ $val($candidate->email) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">No. Telepon</div>
                <div class="value">{{ $val($candidate->no_telepon) }}</div>
            </td>
            <td>
                <div class="label">Jenis Kelamin</div>
                <div class="value">{{ $val($candidate->jenis_kelamin?->label()) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Tempat Lahir</div>
                <div class="value">{{ $val($candidate->tempat_lahir) }}</div>
            </td>
            <td>
                <div class="label">Tanggal Lahir</div>
                <div class="value">{{ $val($candidate->tanggal_lahir?->format('d M Y')) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Agama</div>
                <div class="value">{{ $val($candidate->agama) }}</div>
            </td>
            <td>
                <div class="label">Status Perkawinan</div>
                <div class="value">{{ $val($candidate->status_perkawinan?->label()) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Golongan Darah</div>
                <div class="value">{{ $val($candidate->golongan_darah?->label()) }}</div>
            </td>
            <td>
                <div class="label">No. KTP</div>
                <div class="value">{{ $val($candidate->no_ktp) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">NPWP</div>
                <div class="value">{{ $val($candidate->npwp) }}</div>
            </td>
            <td>
                <div class="label">Nama Ibu Kandung</div>
                <div class="value">{{ $val($candidate->nama_ibu_kandung) }}</div>
            </td>
        </tr>
    </table>
    @if ($candidate->alamat_ktp)
        <div class="label">Alamat KTP</div>
        <div class="value">{{ $candidate->alamat_ktp }}</div>
    @else
        <div class="label">Alamat KTP</div>
        <div class="value">—</div>
    @endif
    @if ($candidate->alamat_domisili)
        <div class="label">Alamat Domisili</div>
        <div class="value">{{ $candidate->alamat_domisili }}</div>
    @else
        <div class="label">Alamat Domisili</div>
        <div class="value">—</div>
    @endif
</div>

{{-- ===== SECTION 2: KONTAK DARURAT ===== --}}
<div class="section">
    <div class="section-title">Kontak Darurat</div>
    <table class="grid">
        <tr>
            <td>
                <div class="label">Nama</div>
                <div class="value">{{ $val($candidate->kontak_darurat_nama) }}</div>
            </td>
            <td>
                <div class="label">No. Telepon</div>
                <div class="value">{{ $val($candidate->kontak_darurat_no_telp) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Hubungan</div>
                <div class="value">{{ $val($candidate->kontak_darurat_hubungan) }}</div>
            </td>
            <td></td>
        </tr>
    </table>
</div>

{{-- ===== SECTION 3: DATA KELUARGA ===== --}}
<div class="section">
    <div class="section-title">Data Keluarga</div>

    <div class="subsection-title">Orang Tua</div>
    <table class="grid">
        <tr>
            <td>
                <div class="label">Nama Ayah</div>
                <div class="value">{{ $val($candidate->ayah_nama) }}</div>
                <div class="label">Usia Ayah</div>
                <div class="value">{{ $val($candidate->ayah_usia) }}</div>
                <div class="label">Pendidikan Ayah</div>
                <div class="value">{{ $val($candidate->ayah_pendidikan_terakhir?->label()) }}</div>
                <div class="label">Pekerjaan Ayah</div>
                <div class="value">{{ $val($candidate->ayah_pekerjaan) }}</div>
            </td>
            <td>
                <div class="label">Nama Ibu</div>
                <div class="value">{{ $val($candidate->ibu_nama) }}</div>
                <div class="label">Usia Ibu</div>
                <div class="value">{{ $val($candidate->ibu_usia) }}</div>
                <div class="label">Pendidikan Ibu</div>
                <div class="value">{{ $val($candidate->ibu_pendidikan_terakhir?->label()) }}</div>
                <div class="label">Pekerjaan Ibu</div>
                <div class="value">{{ $val($candidate->ibu_pekerjaan) }}</div>
            </td>
        </tr>
    </table>

    <div class="label" style="margin-top:4px;">Anak Ke</div>
    <div class="value">{{ $val($candidate->saudara_anak_ke) }} dari {{ $val($candidate->saudara_dari_bersaudara) }} bersaudara</div>

    @if ($candidate->siblings->isNotEmpty())
        <div class="subsection-title">Saudara</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama</th><th>Usia</th><th>JK</th><th>Pendidikan</th><th>Pekerjaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($candidate->siblings as $sibling)
                    <tr>
                        <td>{{ $sibling->nama }}</td>
                        <td>{{ $val($sibling->usia) }}</td>
                        <td>{{ $val($sibling->jenis_kelamin) }}</td>
                        <td>{{ $val($sibling->pendidikan_terakhir) }}</td>
                        <td>{{ $val($sibling->pekerjaan_jabatan) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($candidate->spouses->isNotEmpty())
        <div class="subsection-title">Pasangan</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama</th><th>Usia</th><th>Pendidikan</th><th>Pekerjaan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($candidate->spouses as $spouse)
                    <tr>
                        <td>{{ $spouse->nama }}</td>
                        <td>{{ $val($spouse->usia) }}</td>
                        <td>{{ $val($spouse->pendidikan_terakhir) }}</td>
                        <td>{{ $val($spouse->pekerjaan_jabatan) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($candidate->children->isNotEmpty())
        <div class="subsection-title">Anak</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama</th><th>Usia</th><th>Pendidikan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($candidate->children as $child)
                    <tr>
                        <td>{{ $child->nama }}</td>
                        <td>{{ $val($child->usia) }}</td>
                        <td>{{ $val($child->pendidikan_terakhir) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ===== SECTION 4: PENDIDIKAN FORMAL ===== --}}
<div class="section">
    <div class="section-title">Pendidikan Formal</div>
    @if ($candidate->formalEducations->isNotEmpty())
        @foreach ($candidate->formalEducations as $edu)
            <div class="border-left">
                <div style="font-weight:bold; font-size:9.5pt; color:#0d1614;">{{ $edu->nama_sekolah }}</div>
                <div style="font-size:8.5pt; color:#5a6864;">{{ $edu->jenis_pendidikan?->label() ?? $edu->jenis_pendidikan }} &mdash; {{ $val($edu->jurusan) }}</div>
                <div style="font-size:8pt; color:#8a948f;">{{ $val($edu->kota) }}, Lulus {{ $edu->tahun_lulus }} &mdash; IPK/Nilai: {{ $val($edu->ip_nilai) }}</div>
            </div>
        @endforeach
    @else
        <div class="empty">Belum ada data</div>
    @endif
</div>

{{-- ===== SECTION 5: PENDIDIKAN INFORMAL ===== --}}
<div class="section">
    <div class="section-title">Pendidikan Informal / Pelatihan</div>
    @if ($candidate->informalEducations->isNotEmpty())
        @foreach ($candidate->informalEducations as $inf)
            <div class="border-left">
                <div style="font-weight:bold; font-size:9.5pt; color:#0d1614;">{{ $inf->nama }}</div>
                <div style="font-size:8.5pt; color:#5a6864;">{{ $val($inf->topik) }} &mdash; {{ $val($inf->penyelenggara) }}</div>
                <div style="font-size:8pt; color:#8a948f;">{{ $inf->periode_mulai?->format('M Y') }} &ndash; {{ $inf->periode_selesai?->format('M Y') ?? 'Sekarang' }}</div>
            </div>
        @endforeach
    @else
        <div class="empty">Belum ada data</div>
    @endif
</div>

{{-- ===== SECTION 6: KEMAMPUAN BAHASA ===== --}}
<div class="section">
    <div class="section-title">Kemampuan Bahasa</div>
    @if ($candidate->languageSkills->isNotEmpty())
        <table class="data-table">
            <thead>
                <tr><th>Bahasa</th><th>Berbicara</th><th>Menulis</th><th>Membaca</th></tr>
            </thead>
            <tbody>
                @foreach ($candidate->languageSkills as $lang)
                    <tr>
                        <td>{{ $lang->nama_bahasa }}</td>
                        <td>{{ $lang->berbicara }}</td>
                        <td>{{ $lang->menulis }}</td>
                        <td>{{ $lang->membaca }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">Belum ada data</div>
    @endif
</div>

{{-- ===== SECTION 7: PENGALAMAN KERJA ===== --}}
<div class="section">
    <div class="section-title">Pengalaman Kerja</div>
    @if ($candidate->is_fresh_graduate)
        <div class="fresh-grad-box">Fresh Graduate &mdash; belum memiliki pengalaman kerja.</div>
    @elseif ($candidate->workExperiences->isNotEmpty())
        @foreach ($candidate->workExperiences as $work)
            <div class="border-left">
                <div style="font-weight:bold; font-size:9.5pt; color:#0d1614;">{{ $work->jabatan }} &mdash; {{ $work->nama_perusahaan }}</div>
                <div style="font-size:8pt; color:#8a948f;">{{ $work->periode_mulai?->format('M Y') ?? '—' }} &ndash; {{ $work->periode_selesai?->format('M Y') ?? 'Sekarang' }}</div>
                @if ($work->rincian_tugas)
                    <div style="font-size:8.5pt; color:#2a3835; margin-top:3px;">{{ $work->rincian_tugas }}</div>
                @endif
                <div style="font-size:8pt; color:#8a948f;">Gaji terakhir: {{ $val($work->gaji_terakhir) }}</div>
            </div>
        @endforeach
    @else
        <div class="empty">Belum ada data</div>
    @endif
</div>

{{-- ===== SECTION 8: PENGALAMAN ORGANISASI ===== --}}
<div class="section">
    <div class="section-title">Pengalaman Organisasi</div>
    @if ($candidate->organizationExperiences->isNotEmpty())
        @foreach ($candidate->organizationExperiences as $org)
            <div class="border-left">
                <div style="font-weight:bold; font-size:9.5pt; color:#0d1614;">{{ $org->jabatan }} &mdash; {{ $org->nama_organisasi }}</div>
                <div style="font-size:8pt; color:#8a948f;">{{ $org->periode_mulai?->format('M Y') ?? '—' }} &ndash; {{ $org->periode_selesai?->format('M Y') ?? 'Sekarang' }}</div>
                @if ($org->keterangan)
                    <div style="font-size:8.5pt; color:#2a3835;">{{ $org->keterangan }}</div>
                @endif
            </div>
        @endforeach
    @else
        <div class="empty">Belum ada data</div>
    @endif
</div>

{{-- ===== SECTION 9: PRESTASI ===== --}}
<div class="section">
    <div class="section-title">Prestasi</div>
    @if ($candidate->achievements->isNotEmpty())
        <table class="data-table">
            <thead><tr><th>Prestasi</th><th style="width:60px;">Tahun</th></tr></thead>
            <tbody>
                @foreach ($candidate->achievements as $achievement)
                    <tr>
                        <td>{{ $achievement->nama_prestasi }}</td>
                        <td>{{ $achievement->tahun }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty">Belum ada data</div>
    @endif
</div>

{{-- ===== SECTION 10: KESEHATAN ===== --}}
<div class="section">
    <div class="section-title">Kesehatan</div>
    <div class="label">Pernah Sakit Serius</div>
    <div class="value">{{ $candidate->pernah_sakit_serius ? 'Ya' : 'Tidak' }}</div>
    @if ($candidate->pernah_sakit_serius && $candidate->diagnosis_sakit)
        <div class="label">Diagnosis</div>
        <div class="value">{{ $candidate->diagnosis_sakit }}</div>
    @endif
    <div class="label">Vaksinasi COVID</div>
    <div class="value">{{ $val($candidate->vaksinasi_covid) }}</div>
</div>

{{-- ===== SECTION 11: DATA LAMARAN ===== --}}
<div class="section">
    <div class="section-title">Data Lamaran</div>
    <div class="label">Alasan Melamar</div>
    <div class="value">{{ $val($application->alasan_melamar) }}</div>
    <div class="label">Gaji Diharapkan</div>
    <div class="value">{{ $application->gaji_diharapkan ? 'Rp ' . number_format($application->gaji_diharapkan, 0, ',', '.') : '—' }}</div>
    <div class="label">Fasilitas Diharapkan</div>
    <div class="value">{{ $val($application->fasilitas_diharapkan) }}</div>
    <div class="label">Kesiapan Kerja</div>
    <div class="value">{{ $val($application->kesiapan_kerja) }}</div>
    <div class="label">Sumber Informasi</div>
    <div class="value">{{ $val($application->sumber_informasi) }}</div>

    @php
        $cvExt = $application->cv_path ? strtolower(pathinfo($application->cv_path, PATHINFO_EXTENSION)) : null;
        $strExt = $application->str_sip_path ? strtolower(pathinfo($application->str_sip_path, PATHINFO_EXTENSION)) : null;
    @endphp

    @if ($cvExt && $cvExt !== 'pdf')
        <div class="label" style="margin-top:4px;">CV</div>
        <div class="note">File CV ({{ strtoupper($cvExt) }}) tersedia di sistem &mdash; tidak dapat disisipkan dalam PDF.</div>
    @endif
    @if ($strExt && $strExt !== 'pdf')
        <div class="label" style="margin-top:4px;">STR/SIP</div>
        <div class="note">File STR/SIP ({{ strtoupper($strExt) }}) tersedia di sistem &mdash; tidak dapat disisipkan dalam PDF.</div>
    @endif
</div>

{{-- ===== SECTION 12: AKUN MEDIA SOSIAL ===== --}}
@if ($application->socialMediaAccounts->isNotEmpty())
    <div class="section">
        <div class="section-title">Akun Media Sosial</div>
        <table class="data-table">
            <thead><tr><th>Platform</th><th>Link</th></tr></thead>
            <tbody>
                @foreach ($application->socialMediaAccounts as $sma)
                    <tr>
                        <td>{{ $sma->platform }}</td>
                        <td>{{ $val($sma->link) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

{{-- ===== SECTION 13: REFERENSI ===== --}}
@if ($application->references->isNotEmpty())
    <div class="section">
        <div class="section-title">Referensi</div>
        @foreach ($application->references as $ref)
            <div class="border-left">
                <div style="font-weight:bold; font-size:9.5pt; color:#0d1614;">{{ $ref->nama_karyawan }}</div>
                <div style="font-size:8.5pt; color:#5a6864;">{{ $val($ref->hubungan) }}</div>
                @if ($ref->keterangan)
                    <div style="font-size:8.5pt; color:#2a3835;">{{ $ref->keterangan }}</div>
                @endif
            </div>
        @endforeach
    </div>
@endif

{{-- ===== SECTION 14: HASIL TES DISC ===== --}}
@if ($application->discSubmission?->submitted_at && $application->discSubmission->result)
    @php $discResult = $application->discSubmission->result; @endphp
    <div class="section">
        <div class="section-title">Hasil Tes DiSC</div>
        <div style="margin-bottom:6px;">
            <span class="badge">{{ $discResult->tipe_primer->value }}</span>
            <span style="font-size:8.5pt; margin-left:8px;">Tipe Primer: {{ $discResult->tipe_primer->shortLabel() }} &mdash; Sekunder: {{ $discResult->tipe_sekunder->shortLabel() }}</span>
        </div>
        <table class="data-table" style="width:auto;">
            <thead>
                <tr><th>Dimensi</th><th>Skor</th></tr>
            </thead>
            <tbody>
                <tr><td>D (Dominance)</td><td>{{ $discResult->skor_d }}</td></tr>
                <tr><td>I (Influence)</td><td>{{ $discResult->skor_i }}</td></tr>
                <tr><td>S (Steadiness)</td><td>{{ $discResult->skor_s }}</td></tr>
                <tr><td>C (Conscientiousness)</td><td>{{ $discResult->skor_c }}</td></tr>
            </tbody>
        </table>
    </div>
@endif

{{-- ===== SECTION 15: HASIL TES MBTI ===== --}}
@if ($application->mbtiSubmission?->submitted_at && $application->mbtiSubmission->result)
    @php $mbtiResult = $application->mbtiSubmission->result; @endphp
    <div class="section">
        <div class="section-title">Hasil Tes MBTI</div>
        <div style="margin-bottom:6px;">
            <span class="badge">{{ $mbtiResult->tipe }}</span>
        </div>
        <table class="data-table" style="width:auto;">
            <thead>
                <tr><th>Dimensi</th><th>Skor A</th><th>Skor B</th><th>Kekuatan (%)</th></tr>
            </thead>
            <tbody>
                <tr><td>E / I (Ekstrovert / Introvert)</td><td>{{ $mbtiResult->skor_e }}</td><td>{{ $mbtiResult->skor_i }}</td><td>{{ $mbtiResult->kekuatan_ei }}</td></tr>
                <tr><td>S / N (Penginderaan / Intuisi)</td><td>{{ $mbtiResult->skor_s }}</td><td>{{ $mbtiResult->skor_n }}</td><td>{{ $mbtiResult->kekuatan_sn }}</td></tr>
                <tr><td>T / F (Pemikiran / Perasaan)</td><td>{{ $mbtiResult->skor_t }}</td><td>{{ $mbtiResult->skor_f }}</td><td>{{ $mbtiResult->kekuatan_tf }}</td></tr>
                <tr><td>J / P (Terstruktur / Fleksibel)</td><td>{{ $mbtiResult->skor_j }}</td><td>{{ $mbtiResult->skor_p }}</td><td>{{ $mbtiResult->kekuatan_jp }}</td></tr>
            </tbody>
        </table>
    </div>
@endif

{{-- ===== FOOTER (repeats every page via position:fixed) ===== --}}
<div class="pdf-footer">
    <table>
        <tr>
            <td class="footer-left">
                <strong style="color:#5a6864;">RS Azra</strong> &mdash; Sistem Rekrutmen (ATS)
            </td>
            <td class="footer-right">
                {{ $candidate->nama_lengkap }}
                &nbsp;&bull;&nbsp;
                {{ $lowongan->judul_posisi }}, {{ $lowongan->unit?->nama }}
                &nbsp;&bull;&nbsp;
                Dicetak: {{ now()->format('d M Y') }}
            </td>
        </tr>
    </table>
</div>

</body>
</html>
