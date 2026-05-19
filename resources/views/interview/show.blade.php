<x-layouts.app title="Wawancara - {{ $application->candidate->nama_lengkap }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.wawancara.index', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Wawancara
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Wawancara Kandidat</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Left: Decision panel --}}
        <div class="lg:col-span-1 space-y-4">

            {{-- Candidate summary --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Identitas Kandidat</h2>
                <dl class="space-y-2">
                    <div>
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Nama Lengkap</dt>
                        <dd class="text-sm font-semibold text-gray-900 mt-0.5">{{ $application->candidate->nama_lengkap }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Email</dt>
                        <dd class="text-sm text-gray-700 mt-0.5">{{ $application->candidate->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">No. Telepon</dt>
                        <dd class="text-sm text-gray-700 mt-0.5">{{ $application->candidate->no_telepon }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Tanggal Melamar</dt>
                        <dd class="text-sm text-gray-700 mt-0.5">{{ $application->created_at->format('d M Y') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- CV download --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Dokumen</h2>
                @if ($application->cv_path)
                    <a
                        href="{{ Storage::temporaryUrl($application->cv_path, now()->addMinutes(5)) }}"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150 w-full justify-center"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Unduh CV
                    </a>
                @else
                    <p class="text-xs text-gray-400 text-center py-2">CV belum diunggah.</p>
                @endif
            </div>

            {{-- Interview decision form --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Penilaian Wawancara</h2>

                @if ($existingResult)
                    @php
                        $statusBadge = match ($existingResult->keputusan) {
                            'lulus' => ['bg-green-100 text-green-700', 'Diloloskan'],
                            'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                            'reserved' => ['bg-amber-100 text-amber-700', 'Ditangguhkan'],
                            default => ['bg-gray-100 text-gray-500', $existingResult->keputusan],
                        };
                    @endphp
                    <div class="text-center py-2 mb-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
                            {{ $statusBadge[1] }}
                        </span>
                    </div>

                    @if ($existingResult->ratings->isNotEmpty())
                        <div class="space-y-2 mb-3">
                            @foreach ($existingResult->ratings as $rating)
                                <div class="flex items-center justify-between">
                                    <span class="text-xs text-gray-600">{{ $rating->nama_kriteria }}</span>
                                    <span class="text-xs font-semibold text-gray-900">{{ $rating->nilai }}/5</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($existingResult->catatan)
                        <p class="text-xs text-gray-600 bg-gray-50 rounded-lg px-3 py-2">
                            {{ $existingResult->catatan }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400 text-center">Tidak ada catatan.</p>
                    @endif
                @elseif ($interviewStage->status->isAdvanceable())
                    @if ($assignedTemplates->isEmpty())
                        <div class="text-center py-4">
                            <p class="text-sm text-gray-500">Belum ada kriteria, hubungi HR Admin.</p>
                        </div>
                    @else
                    <form
                        method="POST"
                        action="{{ route('lowongan.wawancara.keputusan', [$lowongan, $application]) }}"
                        x-data="{ keputusan: '{{ old('keputusan') }}' }"
                    >
                        @csrf

                        @php $ratingIndex = 0; @endphp
                        @foreach ($assignedTemplates as $template)
                            <div class="mb-4 space-y-3">
                                <p class="text-[10px] font-medium text-gray-700 uppercase tracking-wide">{{ $template->nama }}</p>
                                @foreach ($template->items as $item)
                                    <div>
                                        <label class="block text-xs text-gray-700 mb-1">
                                            {{ $item->teks }}
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <input type="hidden" name="ratings[{{ $ratingIndex }}][interview_template_id]" value="{{ $template->id }}">
                                        <input type="hidden" name="ratings[{{ $ratingIndex }}][nama_kriteria]" value="{{ $item->teks }}">
                                        <div class="flex items-center gap-2">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <label class="flex flex-col items-center cursor-pointer">
                                                    <input
                                                        type="radio"
                                                        name="ratings[{{ $ratingIndex }}][nilai]"
                                                        value="{{ $i }}"
                                                        class="sr-only peer"
                                                        @if (old("ratings.{$ratingIndex}.nilai") == $i) checked @endif
                                                        required
                                                    >
                                                    <span class="w-8 h-8 flex items-center justify-center text-sm font-semibold rounded-full border border-gray-200 peer-checked:bg-primary peer-checked:text-white peer-checked:border-primary hover:border-primary/40 transition-colors cursor-pointer">
                                                        {{ $i }}
                                                    </span>
                                                </label>
                                            @endfor
                                        </div>
                                        @error("ratings.{$ratingIndex}.nilai")
                                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    @php $ratingIndex++ @endphp
                                @endforeach
                            </div>
                        @endforeach

                        <div class="space-y-2 mb-4">
                            @foreach (['lulus' => ['Lulus', 'bg-green-50 border-green-300 text-green-700'], 'reserved' => ['Tunda', 'bg-amber-50 border-amber-300 text-amber-700'], 'gagal' => ['Gagal', 'bg-red-50 border-red-300 text-red-700']] as $value => $config)
                                <label
                                    class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors ease-out duration-150"
                                    :class="keputusan === '{{ $value }}' ? '{{ $config[1] }}' : 'border-gray-200 hover:border-gray-300'"
                                >
                                    <input
                                        type="radio"
                                        name="keputusan"
                                        value="{{ $value }}"
                                        x-model="keputusan"
                                        class="w-4 h-4 accent-current"
                                        @if (old('keputusan') === $value) checked @endif
                                    >
                                    <span class="text-sm font-medium" :class="keputusan === '{{ $value }}' ? '' : 'text-gray-700'">{{ $config[0] }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error('keputusan')
                            <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
                        @enderror

                        <div class="mb-4">
                            <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                                Catatan <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                            </label>
                            <textarea
                                name="catatan"
                                rows="4"
                                placeholder="Catatan penilaian wawancara..."
                                class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                            >{{ old('catatan') }}</textarea>
                            @error('catatan')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 disabled:opacity-50 cursor-pointer"
                            x-bind:disabled="!keputusan"
                        >
                            Simpan Keputusan
                        </button>
                    </form>
                    @endif
                @else
                    @php
                        $statusBadge = match ($interviewStage->status->value) {
                            'selesai' => ['bg-green-100 text-green-700', 'Diloloskan'],
                            'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                            default => ['bg-gray-100 text-gray-500', $interviewStage->status->label()],
                        };
                    @endphp
                    <div class="text-center py-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
                            {{ $statusBadge[1] }}
                        </span>
                        <p class="mt-2 text-xs text-gray-400">Belum ada catatan.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Candidate profile --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Screening notes --}}
            @if ($screeningStages->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Catatan Skrining CV</h2>
                    <div class="space-y-3">
                        @foreach ($screeningStages as $screeningStage)
                            @if ($screeningStage->catatan)
                                <div class="border-l-2 border-primary/30 pl-3">
                                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">
                                        {{ $screeningStage->key === 'skrining_cv_hr' ? 'Skrining CV HR' : 'Skrining CV Kepala Unit' }}
                                    </p>
                                    <p class="text-xs text-gray-700">{{ $screeningStage->catatan }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Test result --}}
            @if ($application->testSubmission && $application->testSubmission->submitted_at)
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes Kompetensi</h2>
                    <div class="flex items-center gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold text-primary">{{ $application->testSubmission->total_skor ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wide mt-0.5">Total Skor</p>
                        </div>
                        <div class="text-xs text-gray-500">
                            <p>Diselesaikan: {{ $application->testSubmission->submitted_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- MBTI result --}}
            @if ($application->mbtiSubmission && $application->mbtiSubmission->result)
                @php $mbtiResult = $application->mbtiSubmission->result; @endphp
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes MBTI</h2>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-bold rounded-full">
                            {{ $mbtiResult->tipe }}
                        </span>
                        <div class="text-sm text-gray-700">
                            <span class="font-medium">Tipe Kepribadian:</span> {{ $mbtiResult->tipe }}
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        @foreach ([
                            ['EI', $mbtiResult->skor_e, $mbtiResult->skor_i, 'E', 'I', 'Ekstrovert', 'Introvert', $mbtiResult->kekuatan_ei],
                            ['SN', $mbtiResult->skor_s, $mbtiResult->skor_n, 'S', 'N', 'Penginderaan', 'Intuisi', $mbtiResult->kekuatan_sn],
                            ['TF', $mbtiResult->skor_t, $mbtiResult->skor_f, 'T', 'F', 'Pemikiran', 'Perasaan', $mbtiResult->kekuatan_tf],
                            ['JP', $mbtiResult->skor_j, $mbtiResult->skor_p, 'J', 'P', 'Terstruktur', 'Fleksibel', $mbtiResult->kekuatan_jp],
                        ] as [$dim, $scoreA, $scoreB, $poleA, $poleB, $labelA, $labelB, $strength])
                            <div class="bg-gray-50 rounded-lg p-3">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-semibold text-gray-600">{{ $dim }}</span>
                                    <span class="text-xs text-gray-400">Kekuatan: {{ $strength }}%</span>
                                </div>
                                <div class="flex justify-between text-xs text-gray-700">
                                    <span :class="'font-semibold'">{{ $poleA }}: {{ $scoreA }}</span>
                                    <span>{{ $poleB }}: {{ $scoreB }}</span>
                                </div>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $scoreA >= $scoreB ? $labelA : $labelB }}</div>
                            </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-400">Diselesaikan: {{ $application->mbtiSubmission->submitted_at->format('d M Y, H:i') }}</p>
                </div>
            @endif

            {{-- DiSC result --}}
            @if ($application->discSubmission && $application->discSubmission->result)
                @php $discResult = $application->discSubmission->result; @endphp
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes DiSC</h2>
                    <div class="flex items-center gap-3 mb-4">
                        <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-bold rounded-full">
                            {{ $discResult->tipe_primer->value }}
                        </span>
                        <div class="text-sm text-gray-700">
                            <span class="font-medium">Tipe Primer:</span> {{ $discResult->tipe_primer->shortLabel() }}
                        </div>
                        <div class="text-sm text-gray-500">
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
                    <p class="text-xs text-gray-400 mt-3">Diselesaikan: {{ $application->discSubmission->submitted_at->format('d M Y, H:i') }}</p>
                </div>
            @endif

            {{-- Prior interviews --}}
            @if ($priorInterviews->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Wawancara Sebelumnya</h2>
                    <div class="space-y-4">
                        @foreach ($priorInterviews as $priorStage)
                            @php
                                $priorResult = $priorStage->interviewResult;
                                $stageLabels = [
                                    'wawancara_kepala_unit' => 'Wawancara Kepala Unit',
                                    'wawancara_manajer_hr' => 'Wawancara Manajer HR',
                                ];
                                $priorBadge = match ($priorResult->keputusan) {
                                    'lulus' => 'bg-green-100 text-green-700',
                                    'gagal' => 'bg-red-100 text-red-600',
                                    default => 'bg-amber-100 text-amber-700',
                                };
                                $priorLabel = match ($priorResult->keputusan) {
                                    'lulus' => 'Lulus', 'gagal' => 'Gagal', default => 'Ditangguhkan',
                                };
                            @endphp
                            <div class="border-l-2 border-primary/30 pl-3">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-gray-800">{{ $stageLabels[$priorStage->key] ?? $priorStage->key }}</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $priorBadge }}">
                                        {{ $priorLabel }}
                                    </span>
                                </div>
                                @if ($priorResult->ratings->isNotEmpty())
                                    <div class="space-y-2 mb-2">
                                        @foreach ($priorResult->ratings->groupBy('interview_template_id') as $templateId => $groupRatings)
                                            @php $templateName = $groupRatings->first()->interviewTemplate?->nama ?? 'Template Dihapus'; @endphp
                                            <div>
                                                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">{{ $templateName }}</p>
                                                @foreach ($groupRatings as $rating)
                                                    <div class="flex items-center justify-between text-xs">
                                                        <span class="text-gray-600">{{ $rating->nama_kriteria }}</span>
                                                        <span class="font-medium text-gray-800">{{ $rating->nilai }}/5</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($priorResult->catatan)
                                    <p class="text-xs text-gray-600 bg-gray-50 rounded px-2 py-1.5">{{ $priorResult->catatan }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Identitas Diri --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Identitas Diri</h2>
                <dl class="grid grid-cols-2 gap-x-6 gap-y-3">
                    @foreach ([
                        'Tempat Lahir' => $application->candidate->tempat_lahir,
                        'Tanggal Lahir' => $application->candidate->tanggal_lahir?->format('d M Y'),
                        'Jenis Kelamin' => $application->candidate->jenis_kelamin?->label(),
                        'Agama' => $application->candidate->agama,
                        'Status Perkawinan' => $application->candidate->status_perkawinan?->label(),
                        'Golongan Darah' => $application->candidate->golongan_darah?->label(),
                        'No. KTP' => $application->candidate->no_ktp,
                        'NPWP' => $application->candidate->npwp,
                    ] as $label => $value)
                        @if ($value)
                            <div>
                                <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">{{ $label }}</dt>
                                <dd class="text-sm text-gray-800 mt-0.5">{{ $value }}</dd>
                            </div>
                        @endif
                    @endforeach
                </dl>
                @if ($application->candidate->alamat_domisili)
                    <div class="mt-3">
                        <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide">Alamat Domisili</dt>
                        <dd class="text-sm text-gray-800 mt-0.5">{{ $application->candidate->alamat_domisili }}</dd>
                    </div>
                @endif
            </div>

            {{-- Pendidikan Formal --}}
            @if ($application->candidate->formalEducations->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Pendidikan Formal</h2>
                    <div class="space-y-3">
                        @foreach ($application->candidate->formalEducations as $edu)
                            <div class="border-l-2 border-primary/30 pl-3">
                                <p class="text-sm font-medium text-gray-800">{{ $edu->nama_sekolah }}</p>
                                <p class="text-xs text-gray-500">{{ $edu->jenis_pendidikan?->label() ?? $edu->jenis_pendidikan }} &mdash; {{ $edu->jurusan }}</p>
                                <p class="text-xs text-gray-400">{{ $edu->kota }}, Lulus {{ $edu->tahun_lulus }} &mdash; IPK/Nilai: {{ $edu->ip_nilai }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pengalaman Kerja --}}
            @if ($application->candidate->is_fresh_graduate)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <p class="text-xs font-medium text-amber-700">Fresh Graduate &mdash; belum memiliki pengalaman kerja.</p>
                </div>
            @elseif ($application->candidate->workExperiences->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Pengalaman Kerja</h2>
                    <div class="space-y-3">
                        @foreach ($application->candidate->workExperiences as $work)
                            <div class="border-l-2 border-primary/30 pl-3">
                                <p class="text-sm font-medium text-gray-800">{{ $work->jabatan }} &mdash; {{ $work->nama_perusahaan }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $work->periode_mulai->format('M Y') }} &ndash; {{ $work->periode_selesai ? $work->periode_selesai->format('M Y') : 'Sekarang' }}
                                </p>
                                @if ($work->rincian_tugas)
                                    <p class="text-xs text-gray-600 mt-1">{{ Str::limit($work->rincian_tugas, 150) }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Motivasi & Ekspektasi --}}
            @if ($application->alasan_melamar || $application->gaji_diharapkan)
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Motivasi & Ekspektasi</h2>
                    @if ($application->alasan_melamar)
                        <div class="mb-3">
                            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Alasan Melamar</dt>
                            <dd class="text-sm text-gray-700">{{ $application->alasan_melamar }}</dd>
                        </div>
                    @endif
                    @if ($application->gaji_diharapkan)
                        <div>
                            <dt class="text-[10px] font-medium text-gray-400 uppercase tracking-wide mb-1">Gaji Diharapkan</dt>
                            <dd class="text-sm text-gray-700">Rp {{ number_format($application->gaji_diharapkan, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

</x-layouts.app>
