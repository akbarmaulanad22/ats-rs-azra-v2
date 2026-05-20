<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Profil Kandidat - {{ $application->candidate->nama_lengkap }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; color: #1a1a1a; line-height: 1.4; }
        .page { padding: 20mm 15mm; }

        /* Header */
        .header { border-bottom: 2px solid #1a56db; padding-bottom: 8px; margin-bottom: 16px; }
        .header-title { font-size: 14pt; font-weight: bold; color: #1a56db; }
        .header-subtitle { font-size: 8pt; color: #555; margin-top: 2px; }
        .header-meta { font-size: 8pt; color: #888; margin-top: 4px; }

        /* Sections */
        .section { margin-bottom: 14px; }
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1a56db;
            background-color: #eff6ff;
            padding: 4px 8px;
            margin-bottom: 6px;
            border-left: 3px solid #1a56db;
        }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        .data-table td { padding: 3px 6px; vertical-align: top; }
        .data-table td.label { font-weight: bold; color: #444; width: 35%; }
        .data-table td.value { color: #1a1a1a; }

        .items-table { border: 1px solid #ddd; }
        .items-table th { background-color: #f3f4f6; padding: 4px 6px; text-align: left; font-size: 8pt; font-weight: bold; border-bottom: 1px solid #ddd; }
        .items-table td { padding: 3px 6px; border-bottom: 1px solid #eee; font-size: 8pt; vertical-align: top; }

        /* Status badges */
        .badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 7.5pt; font-weight: bold; }
        .badge-pass { background-color: #d1fae5; color: #065f46; }
        .badge-fail { background-color: #fee2e2; color: #991b1b; }
        .badge-reserved { background-color: #fef3c7; color: #92400e; }
        .badge-done { background-color: #dbeafe; color: #1e40af; }

        .empty-note { color: #9ca3af; font-style: italic; font-size: 8pt; padding: 4px 6px; }
        .divider { border-top: 1px solid #e5e7eb; margin: 8px 0; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="header-title">RS Azra — Profil Kandidat</div>
        <div class="header-subtitle">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</div>
        <div class="header-meta">Dicetak: {{ now()->format('d M Y, H:i') }}</div>
    </div>

    {{-- 1. Identitas Diri --}}
    <div class="section">
        <div class="section-title">1. Identitas Diri</div>
        @php $c = $application->candidate; @endphp
        <table class="data-table">
            <tr><td class="label">Nama Lengkap</td><td class="value">{{ $c->nama_lengkap }}</td></tr>
            <tr><td class="label">Email</td><td class="value">{{ $c->email }}</td></tr>
            <tr><td class="label">No. Telepon</td><td class="value">{{ $c->no_telepon }}</td></tr>
            <tr><td class="label">Tempat, Tanggal Lahir</td><td class="value">{{ $c->tempat_lahir }}, {{ $c->tanggal_lahir?->format('d M Y') ?? '-' }}</td></tr>
            <tr><td class="label">Jenis Kelamin</td><td class="value">{{ $c->jenis_kelamin?->label() ?? '-' }}</td></tr>
            <tr><td class="label">Status Perkawinan</td><td class="value">{{ $c->status_perkawinan?->label() ?? '-' }}</td></tr>
            <tr><td class="label">Agama</td><td class="value">{{ $c->agama ?? '-' }}</td></tr>
            <tr><td class="label">Golongan Darah</td><td class="value">{{ $c->golongan_darah?->value ?? '-' }}</td></tr>
            <tr><td class="label">No. KTP</td><td class="value">{{ $c->no_ktp ?? '-' }}</td></tr>
            <tr><td class="label">NPWP</td><td class="value">{{ $c->npwp ?? '-' }}</td></tr>
            <tr><td class="label">Alamat KTP</td><td class="value">{{ $c->alamat_ktp ?? '-' }}</td></tr>
            <tr><td class="label">Alamat Domisili</td><td class="value">{{ $c->alamat_domisili ?? '-' }}</td></tr>
            <tr><td class="label">Nama Ibu Kandung</td><td class="value">{{ $c->nama_ibu_kandung ?? '-' }}</td></tr>
            <tr>
                <td class="label">Kontak Darurat</td>
                <td class="value">
                    @if($c->kontak_darurat_nama)
                        {{ $c->kontak_darurat_nama }} ({{ $c->kontak_darurat_hubungan ?? '-' }}) &mdash; {{ $c->kontak_darurat_no_telp ?? '-' }}
                    @else
                        -
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- 2. Latar Belakang Keluarga --}}
    <div class="section">
        <div class="section-title">2. Latar Belakang Keluarga</div>
        <table class="data-table">
            <tr><td class="label">Anak ke</td><td class="value">{{ $c->saudara_anak_ke ?? '-' }} dari {{ $c->saudara_dari_bersaudara ?? '-' }} bersaudara</td></tr>
            <tr><td class="label">Ayah</td><td class="value">{{ $c->ayah_nama ?? '-' }}, {{ $c->ayah_usia ?? '-' }} thn, Pend. {{ $c->ayah_pendidikan_terakhir?->label() ?? '-' }}, {{ $c->ayah_pekerjaan ?? '-' }}</td></tr>
            <tr><td class="label">Ibu</td><td class="value">{{ $c->ibu_nama ?? '-' }}, {{ $c->ibu_usia ?? '-' }} thn, Pend. {{ $c->ibu_pendidikan_terakhir?->label() ?? '-' }}, {{ $c->ibu_pekerjaan ?? '-' }}</td></tr>
        </table>

        @if($c->siblings->isNotEmpty())
            <div style="margin-top:4px; font-size:8pt; font-weight:bold; color:#444;">Saudara Kandung:</div>
            <table class="items-table" style="margin-top:3px;">
                <tr><th>Nama</th><th>Usia</th><th>Pendidikan</th><th>Pekerjaan</th></tr>
                @foreach($c->siblings as $s)
                    <tr>
                        <td>{{ $s->nama }}</td>
                        <td>{{ $s->usia ?? '-' }} thn</td>
                        <td>{{ $s->pendidikan_terakhir ?? '-' }}</td>
                        <td>{{ $s->pekerjaan ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if($c->spouses->isNotEmpty())
            <div style="margin-top:4px; font-size:8pt; font-weight:bold; color:#444;">Pasangan:</div>
            <table class="items-table" style="margin-top:3px;">
                <tr><th>Nama</th><th>Usia</th><th>Pendidikan</th><th>Pekerjaan</th></tr>
                @foreach($c->spouses as $s)
                    <tr>
                        <td>{{ $s->nama }}</td>
                        <td>{{ $s->usia ?? '-' }} thn</td>
                        <td>{{ $s->pendidikan_terakhir ?? '-' }}</td>
                        <td>{{ $s->pekerjaan ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if($c->children->isNotEmpty())
            <div style="margin-top:4px; font-size:8pt; font-weight:bold; color:#444;">Anak:</div>
            <table class="items-table" style="margin-top:3px;">
                <tr><th>Nama</th><th>Usia</th><th>Pendidikan</th></tr>
                @foreach($c->children as $child)
                    <tr>
                        <td>{{ $child->nama }}</td>
                        <td>{{ $child->usia ?? '-' }} thn</td>
                        <td>{{ $child->pendidikan ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    {{-- 3. Pendidikan --}}
    <div class="section">
        <div class="section-title">3. Pendidikan</div>
        @if($c->formalEducations->isNotEmpty())
            <div style="font-size:8pt; font-weight:bold; color:#444; margin-bottom:3px;">Pendidikan Formal:</div>
            <table class="items-table">
                <tr><th>Jenjang</th><th>Institusi</th><th>Jurusan</th><th>Tahun Lulus</th><th>IPK/Nilai</th></tr>
                @foreach($c->formalEducations as $edu)
                    <tr>
                        <td>{{ $edu->jenjang ?? '-' }}</td>
                        <td>{{ $edu->nama_institusi ?? '-' }}</td>
                        <td>{{ $edu->jurusan ?? '-' }}</td>
                        <td>{{ $edu->tahun_lulus ?? '-' }}</td>
                        <td>{{ $edu->ipk ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-note">Tidak ada data pendidikan formal.</div>
        @endif

        @if($c->achievements->isNotEmpty())
            <div style="margin-top:4px; font-size:8pt; font-weight:bold; color:#444;">Prestasi:</div>
            <table class="items-table" style="margin-top:3px;">
                <tr><th>Nama Prestasi</th><th>Tahun</th><th>Penyelenggara</th></tr>
                @foreach($c->achievements as $ach)
                    <tr>
                        <td>{{ $ach->nama }}</td>
                        <td>{{ $ach->tahun ?? '-' }}</td>
                        <td>{{ $ach->penyelenggara ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if($c->informalEducations->isNotEmpty())
            <div style="margin-top:4px; font-size:8pt; font-weight:bold; color:#444;">Pendidikan Non-Formal:</div>
            <table class="items-table" style="margin-top:3px;">
                <tr><th>Nama Kursus/Pelatihan</th><th>Institusi</th><th>Tahun</th><th>Durasi</th></tr>
                @foreach($c->informalEducations as $inf)
                    <tr>
                        <td>{{ $inf->nama }}</td>
                        <td>{{ $inf->institusi ?? '-' }}</td>
                        <td>{{ $inf->tahun ?? '-' }}</td>
                        <td>{{ $inf->durasi ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        @if($c->languageSkills->isNotEmpty())
            <div style="margin-top:4px; font-size:8pt; font-weight:bold; color:#444;">Kemampuan Bahasa:</div>
            <table class="items-table" style="margin-top:3px;">
                <tr><th>Bahasa</th><th>Lisan</th><th>Tulisan</th></tr>
                @foreach($c->languageSkills as $lang)
                    <tr>
                        <td>{{ $lang->bahasa }}</td>
                        <td>{{ $lang->kemampuan_lisan?->label() ?? '-' }}</td>
                        <td>{{ $lang->kemampuan_tulisan?->label() ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    </div>

    {{-- 4. Pengalaman Organisasi --}}
    <div class="section">
        <div class="section-title">4. Pengalaman Organisasi</div>
        @if($c->organizationExperiences->isNotEmpty())
            <table class="items-table">
                <tr><th>Organisasi</th><th>Jabatan</th><th>Tahun</th></tr>
                @foreach($c->organizationExperiences as $org)
                    <tr>
                        <td>{{ $org->nama_organisasi }}</td>
                        <td>{{ $org->jabatan ?? '-' }}</td>
                        <td>{{ $org->tahun ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-note">Tidak ada pengalaman organisasi.</div>
        @endif
    </div>

    {{-- 5. Pengalaman Kerja --}}
    <div class="section">
        <div class="section-title">5. Pengalaman Kerja</div>
        @if($c->is_fresh_graduate)
            <div class="empty-note">Fresh graduate.</div>
        @elseif($c->workExperiences->isNotEmpty())
            <table class="items-table">
                <tr><th>Perusahaan</th><th>Posisi</th><th>Mulai</th><th>Selesai</th><th>Alasan Keluar</th></tr>
                @foreach($c->workExperiences as $work)
                    <tr>
                        <td>{{ $work->nama_perusahaan }}</td>
                        <td>{{ $work->posisi ?? '-' }}</td>
                        <td>{{ $work->tanggal_mulai?->format('M Y') ?? '-' }}</td>
                        <td>{{ $work->tanggal_selesai?->format('M Y') ?? 'Sekarang' }}</td>
                        <td>{{ $work->alasan_keluar ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-note">Tidak ada pengalaman kerja.</div>
        @endif
    </div>

    {{-- 6. Minat & Motivasi --}}
    <div class="section">
        <div class="section-title">6. Minat &amp; Motivasi</div>
        <table class="data-table">
            <tr><td class="label">Alasan Melamar</td><td class="value">{{ $application->alasan_melamar ?? '-' }}</td></tr>
            <tr><td class="label">Gaji Diharapkan</td><td class="value">{{ $application->gaji_diharapkan ? 'Rp '.number_format($application->gaji_diharapkan, 0, ',', '.') : '-' }}</td></tr>
            <tr><td class="label">Fasilitas Diharapkan</td><td class="value">{{ $application->fasilitas_diharapkan ?? '-' }}</td></tr>
            <tr><td class="label">Kesiapan Kerja</td><td class="value">{{ $application->kesiapan_kerja ?? '-' }}</td></tr>
            <tr><td class="label">Sumber Informasi</td><td class="value">{{ $application->sumber_informasi ?? '-' }}</td></tr>
        </table>
    </div>

    {{-- 7. Referensi --}}
    <div class="section">
        <div class="section-title">7. Referensi / Rekomendasi</div>
        @if($application->references->isNotEmpty())
            <table class="items-table">
                <tr><th>Nama</th><th>Jabatan</th><th>No. Telepon</th><th>Hubungan</th></tr>
                @foreach($application->references as $ref)
                    <tr>
                        <td>{{ $ref->nama }}</td>
                        <td>{{ $ref->jabatan ?? '-' }}</td>
                        <td>{{ $ref->no_telepon ?? '-' }}</td>
                        <td>{{ $ref->hubungan ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-note">Tidak ada referensi.</div>
        @endif
    </div>

    {{-- 8. Hasil Skrining CV --}}
    <div class="section">
        <div class="section-title">8. Hasil Skrining CV</div>
        @php
            $screeningStages = $application->stages->filter(fn($s) => str_starts_with($s->key, 'skrining_cv'));
        @endphp
        @if($screeningStages->isNotEmpty())
            <table class="items-table">
                <tr><th>Tahap</th><th>Status</th><th>Catatan</th></tr>
                @foreach($screeningStages as $stage)
                    <tr>
                        <td>{{ $stage->nama }}</td>
                        <td>{{ $stage->status->label() }}</td>
                        <td>{{ $stage->catatan ?? '-' }}</td>
                    </tr>
                @endforeach
            </table>
        @else
            <div class="empty-note">Belum ada hasil skrining.</div>
        @endif
    </div>

    {{-- 9. Hasil Tes Kompetensi --}}
    <div class="section">
        <div class="section-title">9. Hasil Tes Kompetensi</div>
        @php $test = $application->testSubmission; @endphp
        @if($test && $test->submitted_at)
            <table class="data-table">
                <tr><td class="label">Skor Total</td><td class="value">{{ $test->total_skor ?? '-' }}</td></tr>
                <tr><td class="label">Waktu Submit</td><td class="value">{{ $test->submitted_at->format('d M Y, H:i') }}</td></tr>
            </table>
        @else
            <div class="empty-note">Tes belum diselesaikan.</div>
        @endif
    </div>

    {{-- 10. Hasil Wawancara --}}
    <div class="section">
        <div class="section-title">10. Hasil Wawancara</div>
        @if($application->interviewResults->isNotEmpty())
            @foreach($application->interviewResults as $result)
                <div style="margin-bottom:8px;">
                    <div style="font-size:8pt; font-weight:bold; color:#374151; margin-bottom:3px;">
                        {{ $result->applicationStage?->nama ?? 'Wawancara' }}
                        @if($result->interviewer)
                            &mdash; {{ $result->interviewer->name }}
                        @endif
                    </div>
                    <table class="data-table">
                        <tr><td class="label">Keputusan</td><td class="value">{{ $result->keputusan ?? '-' }}</td></tr>
                        <tr><td class="label">Catatan</td><td class="value">{{ $result->catatan ?? '-' }}</td></tr>
                    </table>
                    @if($result->ratings->isNotEmpty())
                        <table class="items-table" style="margin-top:3px;">
                            <tr><th>Kriteria</th><th>Nilai</th></tr>
                            @foreach($result->ratings as $rating)
                                <tr>
                                    <td>{{ $rating->nama_kriteria }}</td>
                                    <td>{{ $rating->nilai }}</td>
                                </tr>
                            @endforeach
                        </table>
                    @endif
                </div>
            @endforeach
        @else
            <div class="empty-note">Belum ada hasil wawancara.</div>
        @endif
    </div>

    {{-- 11. Hasil DiSC --}}
    <div class="section">
        <div class="section-title">11. Hasil Tes DiSC</div>
        @php $disc = $application->discSubmission?->result; @endphp
        @if($disc)
            <table class="data-table">
                <tr><td class="label">Tipe Primer</td><td class="value">{{ $disc->tipe_primer?->value ?? '-' }}</td></tr>
                <tr><td class="label">Tipe Sekunder</td><td class="value">{{ $disc->tipe_sekunder?->value ?? '-' }}</td></tr>
                <tr><td class="label">Skor D / I / S / C</td><td class="value">{{ $disc->skor_d }} / {{ $disc->skor_i }} / {{ $disc->skor_s }} / {{ $disc->skor_c }}</td></tr>
            </table>
        @else
            <div class="empty-note">Tes DiSC belum diselesaikan.</div>
        @endif
    </div>

    {{-- 12. Hasil MBTI --}}
    <div class="section">
        <div class="section-title">12. Hasil Tes MBTI</div>
        @php $mbti = $application->mbtiSubmission?->result; @endphp
        @if($mbti)
            <table class="data-table">
                <tr><td class="label">Tipe</td><td class="value">{{ $mbti->tipe ?? '-' }}</td></tr>
                <tr><td class="label">E/I</td><td class="value">E={{ $mbti->skor_e }} / I={{ $mbti->skor_i }} (kekuatan: {{ $mbti->kekuatan_ei ?? '-' }})</td></tr>
                <tr><td class="label">S/N</td><td class="value">S={{ $mbti->skor_s }} / N={{ $mbti->skor_n }} (kekuatan: {{ $mbti->kekuatan_sn ?? '-' }})</td></tr>
                <tr><td class="label">T/F</td><td class="value">T={{ $mbti->skor_t }} / F={{ $mbti->skor_f }} (kekuatan: {{ $mbti->kekuatan_tf ?? '-' }})</td></tr>
                <tr><td class="label">J/P</td><td class="value">J={{ $mbti->skor_j }} / P={{ $mbti->skor_p }} (kekuatan: {{ $mbti->kekuatan_jp ?? '-' }})</td></tr>
            </table>
        @else
            <div class="empty-note">Tes MBTI belum diselesaikan.</div>
        @endif
    </div>

    {{-- 13. Status MCU --}}
    <div class="section">
        <div class="section-title">13. Status MCU (Medical Check-Up)</div>
        @php $mcu = $application->mcuResult; @endphp
        @if($mcu)
            <table class="data-table">
                <tr><td class="label">Keputusan</td><td class="value">{{ $mcu->keputusan?->label() ?? '-' }}</td></tr>
                <tr><td class="label">Catatan</td><td class="value">{{ $mcu->catatan ?? '-' }}</td></tr>
                <tr><td class="label">Dokumen</td><td class="value">{{ $mcu->dokumen_path ? 'Tersedia' : 'Belum diunggah' }}</td></tr>
            </table>
        @else
            <div class="empty-note">MCU belum dilakukan.</div>
        @endif
    </div>

    {{-- 14. Timeline --}}
    <div class="section">
        <div class="section-title">14. Timeline Lamaran</div>
        <table class="items-table">
            <tr><th>Tahap</th><th>Status</th><th>Tanggal Update</th></tr>
            <tr>
                <td>Pendaftaran</td>
                <td>Selesai</td>
                <td>{{ $application->created_at->format('d M Y, H:i') }}</td>
            </tr>
            @foreach($application->stages->sortBy('position') as $stage)
                @if($stage->status->value !== 'pending')
                    <tr>
                        <td>{{ $stage->nama }}</td>
                        <td>{{ $stage->status->label() }}</td>
                        <td>{{ $stage->updated_at->format('d M Y, H:i') }}</td>
                    </tr>
                @endif
            @endforeach
        </table>
    </div>

</div>
</body>
</html>
