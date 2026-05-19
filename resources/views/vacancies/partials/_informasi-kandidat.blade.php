{{-- Left column: all candidate data --}}
{{-- Variables: $application, $lowongan --}}

@php $candidate = $application->candidate; @endphp

{{-- Section 1: Semua Lamaran Kandidat --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Riwayat Lamaran Kandidat</h2>
    <div class="space-y-2">
        @foreach ($candidate->applications as $otherApp)
            @php
                $otherStage = $otherApp->currentStage();
            @endphp
            <div class="flex items-start gap-2 p-2 rounded-lg {{ $otherApp->id === $application->id ? 'bg-primary/5 border border-primary/20' : 'bg-gray-50' }}">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800 truncate">{{ $otherApp->vacancy->judul_posisi }}</p>
                    <p class="text-[11px] text-gray-500">{{ $otherApp->vacancy->unit->nama }}</p>
                    <p class="text-[11px] text-gray-500">{{ $otherStage?->nama ?? '—' }}</p>
                </div>
                <div class="flex-shrink-0">
                    @if ($otherStage?->status === \App\Enums\ApplicationStageStatus::Reserved)
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">Ditangguhkan</span>
                    @elseif ($otherStage?->status === \App\Enums\ApplicationStageStatus::Aktif)
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-700">Aktif</span>
                    @elseif ($otherStage?->status === \App\Enums\ApplicationStageStatus::Selesai)
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700">Selesai</span>
                    @elseif ($otherStage?->status === \App\Enums\ApplicationStageStatus::Gagal)
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-600">Gagal</span>
                    @else
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-500">Menunggu</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Section 2: Data Pribadi --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Data Pribadi</h2>
    <dl class="grid grid-cols-2 gap-x-4 gap-y-3">
        @foreach ([
            'Nama Lengkap' => $candidate->nama_lengkap,
            'Email' => $candidate->email,
            'No. Telepon' => $candidate->no_telepon,
            'Tempat Lahir' => $candidate->tempat_lahir,
            'Tanggal Lahir' => $candidate->tanggal_lahir?->format('d M Y'),
            'Jenis Kelamin' => $candidate->jenis_kelamin?->label(),
            'Agama' => $candidate->agama,
            'Status Perkawinan' => $candidate->status_perkawinan?->label(),
            'Golongan Darah' => $candidate->golongan_darah?->label(),
            'No. KTP' => $candidate->no_ktp,
            'NPWP' => $candidate->npwp,
            'Nama Ibu Kandung' => $candidate->nama_ibu_kandung,
        ] as $label => $value)
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">{{ $label }}</dt>
                <dd class="text-xs text-gray-800 mt-0.5">{{ $value ?: '—' }}</dd>
            </div>
        @endforeach
    </dl>
    @if ($candidate->alamat_ktp)
        <div class="mt-3">
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Alamat KTP</dt>
            <dd class="text-xs text-gray-800 mt-0.5">{{ $candidate->alamat_ktp }}</dd>
        </div>
    @endif
    @if ($candidate->alamat_domisili)
        <div class="mt-3">
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Alamat Domisili</dt>
            <dd class="text-xs text-gray-800 mt-0.5">{{ $candidate->alamat_domisili }}</dd>
        </div>
    @endif
</div>

{{-- Section 3: Kontak Darurat --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Kontak Darurat</h2>
    <dl class="space-y-2">
        @foreach ([
            'Nama' => $candidate->kontak_darurat_nama,
            'No. Telepon' => $candidate->kontak_darurat_no_telp,
            'Hubungan' => $candidate->kontak_darurat_hubungan,
        ] as $label => $value)
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">{{ $label }}</dt>
                <dd class="text-xs text-gray-800 mt-0.5">{{ $value ?: '—' }}</dd>
            </div>
        @endforeach
    </dl>
</div>

{{-- Section 4: Data Keluarga --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Data Keluarga</h2>

    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-2">Orang Tua</p>
    <div class="grid grid-cols-2 gap-x-4 gap-y-2 mb-4">
        @foreach ([
            'Nama Ayah' => $candidate->ayah_nama,
            'Usia Ayah' => $candidate->ayah_usia,
            'Pendidikan Ayah' => $candidate->ayah_pendidikan_terakhir?->label() ?? $candidate->ayah_pendidikan_terakhir,
            'Pekerjaan Ayah' => $candidate->ayah_pekerjaan,
            'Nama Ibu' => $candidate->ibu_nama,
            'Usia Ibu' => $candidate->ibu_usia,
            'Pendidikan Ibu' => $candidate->ibu_pendidikan_terakhir?->label() ?? $candidate->ibu_pendidikan_terakhir,
            'Pekerjaan Ibu' => $candidate->ibu_pekerjaan,
        ] as $label => $value)
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">{{ $label }}</dt>
                <dd class="text-xs text-gray-800 mt-0.5">{{ $value ?: '—' }}</dd>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-x-4 gap-y-2 mb-4">
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Anak Ke</dt>
            <dd class="text-xs text-gray-800 mt-0.5">{{ $candidate->saudara_anak_ke ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Dari Bersaudara</dt>
            <dd class="text-xs text-gray-800 mt-0.5">{{ $candidate->saudara_dari_bersaudara ?: '—' }}</dd>
        </div>
    </div>

    @if ($candidate->siblings->isNotEmpty())
        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Saudara</p>
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-xs">
                <thead><tr class="text-[10px] text-gray-400 border-b border-gray-100">
                    <th class="text-left py-1 pr-3">Nama</th>
                    <th class="text-left py-1 pr-3">Usia</th>
                    <th class="text-left py-1 pr-3">JK</th>
                    <th class="text-left py-1 pr-3">Pendidikan</th>
                    <th class="text-left py-1">Pekerjaan</th>
                </tr></thead>
                <tbody>
                    @foreach ($candidate->siblings as $sibling)
                        <tr class="border-b border-gray-50">
                            <td class="py-1 pr-3 text-gray-700">{{ $sibling->nama }}</td>
                            <td class="py-1 pr-3 text-gray-500">{{ $sibling->usia ?: '—' }}</td>
                            <td class="py-1 pr-3 text-gray-500">{{ $sibling->jenis_kelamin ?: '—' }}</td>
                            <td class="py-1 pr-3 text-gray-500">{{ $sibling->pendidikan_terakhir ?: '—' }}</td>
                            <td class="py-1 text-gray-500">{{ $sibling->pekerjaan_jabatan ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($candidate->spouses->isNotEmpty())
        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Pasangan</p>
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-xs">
                <thead><tr class="text-[10px] text-gray-400 border-b border-gray-100">
                    <th class="text-left py-1 pr-3">Nama</th>
                    <th class="text-left py-1 pr-3">Usia</th>
                    <th class="text-left py-1 pr-3">Pendidikan</th>
                    <th class="text-left py-1">Pekerjaan</th>
                </tr></thead>
                <tbody>
                    @foreach ($candidate->spouses as $spouse)
                        <tr class="border-b border-gray-50">
                            <td class="py-1 pr-3 text-gray-700">{{ $spouse->nama }}</td>
                            <td class="py-1 pr-3 text-gray-500">{{ $spouse->usia ?: '—' }}</td>
                            <td class="py-1 pr-3 text-gray-500">{{ $spouse->pendidikan_terakhir ?: '—' }}</td>
                            <td class="py-1 text-gray-500">{{ $spouse->pekerjaan_jabatan ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($candidate->children->isNotEmpty())
        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Anak</p>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead><tr class="text-[10px] text-gray-400 border-b border-gray-100">
                    <th class="text-left py-1 pr-3">Nama</th>
                    <th class="text-left py-1 pr-3">Usia</th>
                    <th class="text-left py-1">Pendidikan</th>
                </tr></thead>
                <tbody>
                    @foreach ($candidate->children as $child)
                        <tr class="border-b border-gray-50">
                            <td class="py-1 pr-3 text-gray-700">{{ $child->nama }}</td>
                            <td class="py-1 pr-3 text-gray-500">{{ $child->usia ?: '—' }}</td>
                            <td class="py-1 text-gray-500">{{ $child->pendidikan_terakhir ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- Section 5: Pendidikan Formal --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Pendidikan Formal</h2>
    @if ($candidate->formalEducations->isNotEmpty())
        <div class="space-y-3">
            @foreach ($candidate->formalEducations as $edu)
                <div class="border-l-2 border-primary/30 pl-3">
                    <p class="text-xs font-medium text-gray-800">{{ $edu->nama_sekolah }}</p>
                    <p class="text-[11px] text-gray-500">{{ $edu->jenis_pendidikan?->label() ?? $edu->jenis_pendidikan }} &mdash; {{ $edu->jurusan ?: '—' }}</p>
                    <p class="text-[11px] text-gray-400">{{ $edu->kota }}, Lulus {{ $edu->tahun_lulus }} &mdash; IPK/Nilai: {{ $edu->ip_nilai ?: '—' }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400">Belum ada data</p>
    @endif
</div>

{{-- Section 6: Pendidikan Informal --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Pendidikan Informal / Pelatihan</h2>
    @if ($candidate->informalEducations->isNotEmpty())
        <div class="space-y-2">
            @foreach ($candidate->informalEducations as $inf)
                <div class="border-l-2 border-primary/30 pl-3">
                    <p class="text-xs font-medium text-gray-800">{{ $inf->nama }}</p>
                    <p class="text-[11px] text-gray-500">{{ $inf->topik }} &mdash; {{ $inf->penyelenggara }}</p>
                    <p class="text-[11px] text-gray-400">
                        {{ $inf->periode_mulai?->format('M Y') }} &ndash; {{ $inf->periode_selesai?->format('M Y') ?? 'Sekarang' }}
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400">Belum ada data</p>
    @endif
</div>

{{-- Section 7: Kemampuan Bahasa --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Kemampuan Bahasa</h2>
    @if ($candidate->languageSkills->isNotEmpty())
        <div class="space-y-2">
            @foreach ($candidate->languageSkills as $lang)
                <div>
                    <p class="text-xs font-medium text-gray-800">{{ $lang->nama_bahasa }}</p>
                    <p class="text-[11px] text-gray-500">
                        Berbicara: {{ $lang->berbicara }} &mdash;
                        Menulis: {{ $lang->menulis }} &mdash;
                        Membaca: {{ $lang->membaca }}
                    </p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400">Belum ada data</p>
    @endif
</div>

{{-- Section 8: Pengalaman Kerja --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Pengalaman Kerja</h2>
    @if ($candidate->is_fresh_graduate)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
            <p class="text-xs font-medium text-amber-700">Fresh Graduate &mdash; belum memiliki pengalaman kerja.</p>
        </div>
    @elseif ($candidate->workExperiences->isNotEmpty())
        <div class="space-y-3">
            @foreach ($candidate->workExperiences as $work)
                <div class="border-l-2 border-primary/30 pl-3">
                    <p class="text-xs font-medium text-gray-800">{{ $work->jabatan }} &mdash; {{ $work->nama_perusahaan }}</p>
                    <p class="text-[11px] text-gray-400">{{ $work->periode_mulai->format('M Y') }} &ndash; {{ $work->periode_selesai ? $work->periode_selesai->format('M Y') : 'Sekarang' }}</p>
                    @if ($work->rincian_tugas)
                        <p class="text-[11px] text-gray-600 mt-0.5">{{ Str::limit($work->rincian_tugas, 200) }}</p>
                    @endif
                    <p class="text-[11px] text-gray-400">Gaji terakhir: {{ $work->gaji_terakhir ?: '—' }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400">Belum ada data</p>
    @endif
</div>

{{-- Section 9: Pengalaman Organisasi --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Pengalaman Organisasi</h2>
    @if ($candidate->organizationExperiences->isNotEmpty())
        <div class="space-y-2">
            @foreach ($candidate->organizationExperiences as $org)
                <div class="border-l-2 border-primary/30 pl-3">
                    <p class="text-xs font-medium text-gray-800">{{ $org->jabatan }} &mdash; {{ $org->nama_organisasi }}</p>
                    <p class="text-[11px] text-gray-400">{{ $org->periode_mulai->format('M Y') }} &ndash; {{ $org->periode_selesai ? $org->periode_selesai->format('M Y') : 'Sekarang' }}</p>
                    @if ($org->keterangan)
                        <p class="text-[11px] text-gray-500">{{ $org->keterangan }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400">Belum ada data</p>
    @endif
</div>

{{-- Section 10: Prestasi --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Prestasi</h2>
    @if ($candidate->achievements->isNotEmpty())
        <div class="space-y-1">
            @foreach ($candidate->achievements as $achievement)
                <div class="flex items-center justify-between">
                    <p class="text-xs text-gray-800">{{ $achievement->nama_prestasi }}</p>
                    <p class="text-[11px] text-gray-400">{{ $achievement->tahun }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-xs text-gray-400">Belum ada data</p>
    @endif
</div>

{{-- Section 11: Kesehatan --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Kesehatan</h2>
    <dl class="space-y-2">
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Pernah Sakit Serius</dt>
            <dd class="text-xs text-gray-800 mt-0.5">{{ $candidate->pernah_sakit_serius ? 'Ya' : 'Tidak' }}</dd>
        </div>
        @if ($candidate->pernah_sakit_serius && $candidate->diagnosis_sakit)
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Diagnosis</dt>
                <dd class="text-xs text-gray-800 mt-0.5">{{ $candidate->diagnosis_sakit }}</dd>
            </div>
        @endif
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Vaksinasi COVID</dt>
            <dd class="text-xs text-gray-800 mt-0.5">{{ $candidate->vaksinasi_covid ?: '—' }}</dd>
        </div>
    </dl>
</div>

{{-- Section 12: Data Lamaran --}}
<div class="bg-white rounded-xl border border-gray-100 p-5">
    <h2 class="text-sm font-semibold text-gray-800 mb-3">Data Lamaran</h2>
    <dl class="space-y-3">
        <div>
            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">CV</dt>
            <dd class="mt-0.5">
                @if ($application->cv_path)
                    <a
                        href="{{ Storage::temporaryUrl($application->cv_path, now()->addMinutes(5)) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1 text-xs text-primary hover:text-primary-dark transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Unduh CV
                    </a>
                @else
                    <span class="text-xs text-gray-400">—</span>
                @endif
            </dd>
        </div>
        @if ($application->str_sip_path)
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">STR/SIP</dt>
                <dd class="mt-0.5">
                    <a
                        href="{{ Storage::temporaryUrl($application->str_sip_path, now()->addMinutes(5)) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1 text-xs text-primary hover:text-primary-dark transition-colors"
                    >
                        Unduh STR/SIP
                    </a>
                </dd>
            </div>
        @endif
        @foreach ([
            'Alasan Melamar' => $application->alasan_melamar,
            'Gaji Diharapkan' => $application->gaji_diharapkan ? 'Rp ' . number_format($application->gaji_diharapkan, 0, ',', '.') : null,
            'Fasilitas Diharapkan' => $application->fasilitas_diharapkan,
            'Kesiapan Kerja' => $application->kesiapan_kerja,
            'Sumber Informasi' => $application->sumber_informasi,
        ] as $label => $value)
            <div>
                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">{{ $label }}</dt>
                <dd class="text-xs text-gray-800 mt-0.5">{{ $value ?: '—' }}</dd>
            </div>
        @endforeach
    </dl>
</div>

{{-- Section 13: Akun Media Sosial --}}
@if ($application->socialMediaAccounts->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Akun Media Sosial</h2>
        <div class="space-y-1">
            @foreach ($application->socialMediaAccounts as $sma)
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-wide w-20 flex-shrink-0">{{ $sma->platform }}</span>
                    <span class="text-xs text-gray-700">{{ $sma->link ?: '—' }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Section 14: Referensi --}}
@if ($application->references->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Referensi</h2>
        <div class="space-y-2">
            @foreach ($application->references as $ref)
                <div class="border-l-2 border-primary/30 pl-3">
                    <p class="text-xs font-medium text-gray-800">{{ $ref->nama_karyawan }}</p>
                    <p class="text-[11px] text-gray-500">{{ $ref->hubungan ?: '—' }}</p>
                    @if ($ref->keterangan)
                        <p class="text-[11px] text-gray-400">{{ $ref->keterangan }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Section 15: Hasil Tes DiSC (shown when completed, regardless of current stage) --}}
@if ($application->discSubmission?->submitted_at && $application->discSubmission->result)
    @php $discResult = $application->discSubmission->result; @endphp
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes DiSC</h2>
        <div class="flex items-center gap-3 mb-3">
            <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-bold rounded-full">
                {{ $discResult->tipe_primer->value }}
            </span>
            <div class="text-xs text-gray-700">
                <span class="font-medium">Tipe Primer:</span> {{ $discResult->tipe_primer->shortLabel() }}
            </div>
            <div class="text-xs text-gray-500">
                <span class="font-medium">Sekunder:</span> {{ $discResult->tipe_sekunder->shortLabel() }}
            </div>
        </div>
        <div class="grid grid-cols-4 gap-2">
            @foreach ([['D', $discResult->skor_d, 'bg-red-50 text-red-600'], ['I', $discResult->skor_i, 'bg-yellow-50 text-yellow-600'], ['S', $discResult->skor_s, 'bg-green-50 text-green-600'], ['C', $discResult->skor_c, 'bg-blue-50 text-blue-600']] as [$dim, $score, $color])
                <div class="text-center p-2 rounded-lg {{ $color }}">
                    <p class="text-lg font-bold">{{ $score }}</p>
                    <p class="text-[10px] font-semibold uppercase tracking-wide">{{ $dim }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- Section 16: Hasil Tes MBTI (shown when completed, regardless of current stage) --}}
@if ($application->mbtiSubmission?->submitted_at && $application->mbtiSubmission->result)
    @php $mbtiResult = $application->mbtiSubmission->result; @endphp
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes MBTI</h2>
        <div class="flex items-center gap-3 mb-3">
            <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-bold rounded-full">
                {{ $mbtiResult->tipe }}
            </span>
            <div class="text-xs text-gray-700">
                <span class="font-medium">Tipe Kepribadian:</span> {{ $mbtiResult->tipe }}
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
            @foreach ([
                ['EI', $mbtiResult->skor_e, $mbtiResult->skor_i, 'E', 'I', 'Ekstrovert', 'Introvert', $mbtiResult->kekuatan_ei],
                ['SN', $mbtiResult->skor_s, $mbtiResult->skor_n, 'S', 'N', 'Penginderaan', 'Intuisi', $mbtiResult->kekuatan_sn],
                ['TF', $mbtiResult->skor_t, $mbtiResult->skor_f, 'T', 'F', 'Pemikiran', 'Perasaan', $mbtiResult->kekuatan_tf],
                ['JP', $mbtiResult->skor_j, $mbtiResult->skor_p, 'J', 'P', 'Terstruktur', 'Fleksibel', $mbtiResult->kekuatan_jp],
            ] as [$dim, $scoreA, $scoreB, $poleA, $poleB, $labelA, $labelB, $strength])
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-xs font-semibold text-gray-600">{{ $dim }}</span>
                        <span class="text-xs text-gray-400">{{ $strength }}%</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-700">
                        <span class="font-semibold">{{ $poleA }}: {{ $scoreA }}</span>
                        <span>{{ $poleB }}: {{ $scoreB }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
