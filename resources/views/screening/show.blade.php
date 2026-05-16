<x-layouts.app title="Tinjau Kandidat - {{ $application->candidate->nama_lengkap }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.skrining.index', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Skrining
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Tinjau Kandidat</h1>
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

            {{-- Candidate summary card --}}
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
                        href="{{ Storage::url($application->cv_path) }}"
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

                @if ($application->str_sip_path)
                    <a
                        href="{{ Storage::url($application->str_sip_path) }}"
                        target="_blank"
                        class="mt-2 inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors ease-out duration-150 w-full justify-center"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Unduh STR/SIP
                    </a>
                @endif
            </div>

            {{-- Decision form --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Keputusan Skrining</h2>

                @if ($screeningStage->status->isAdvanceable())
                    <form
                        method="POST"
                        action="{{ route('lowongan.skrining.keputusan', [$lowongan, $application]) }}"
                        x-data="{ keputusan: '{{ old('keputusan') }}' }"
                    >
                        @csrf

                        <div class="space-y-2 mb-4">
                            @foreach (['lulus' => ['Lulus', 'bg-green-50 border-green-300 text-green-700', 'checked:bg-green-600'], 'reserved' => ['Tunda', 'bg-amber-50 border-amber-300 text-amber-700', 'checked:bg-amber-500'], 'gagal' => ['Gagal', 'bg-red-50 border-red-300 text-red-700', 'checked:bg-red-600']] as $value => $config)
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
                                placeholder="Alasan keputusan, kekuatan atau kelemahan kandidat..."
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
                @else
                    @php
                        $statusBadge = match ($screeningStage->status->value) {
                            'selesai' => ['bg-green-100 text-green-700', 'Diloloskan'],
                            'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                            default => ['bg-gray-100 text-gray-500', $screeningStage->status->label()],
                        };
                    @endphp
                    <div class="text-center py-3">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
                            {{ $statusBadge[1] }}
                        </span>
                        @if ($screeningStage->catatan)
                            <p class="mt-3 text-xs text-gray-600 text-left bg-gray-50 rounded-lg px-3 py-2">
                                {{ $screeningStage->catatan }}
                            </p>
                        @else
                            <p class="mt-2 text-xs text-gray-400">Tidak ada catatan.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Personal data summary --}}
        <div class="lg:col-span-2 space-y-4">

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

            {{-- Pengalaman Organisasi --}}
            @if ($application->candidate->organizationExperiences->isNotEmpty())
                <div class="bg-white rounded-xl border border-gray-100 p-5">
                    <h2 class="text-sm font-semibold text-gray-800 mb-3">Pengalaman Organisasi</h2>
                    <div class="space-y-3">
                        @foreach ($application->candidate->organizationExperiences as $org)
                            <div class="border-l-2 border-primary/30 pl-3">
                                <p class="text-sm font-medium text-gray-800">{{ $org->jabatan }} &mdash; {{ $org->nama_organisasi }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $org->periode_mulai->format('M Y') }} &ndash; {{ $org->periode_selesai ? $org->periode_selesai->format('M Y') : 'Sekarang' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Pendidikan Informal + Bahasa --}}
            @if ($application->candidate->informalEducations->isNotEmpty() || $application->candidate->languageSkills->isNotEmpty())
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if ($application->candidate->informalEducations->isNotEmpty())
                        <div class="bg-white rounded-xl border border-gray-100 p-5">
                            <h2 class="text-sm font-semibold text-gray-800 mb-3">Pendidikan Non-Formal</h2>
                            <div class="space-y-2">
                                @foreach ($application->candidate->informalEducations as $inf)
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $inf->nama }}</p>
                                        <p class="text-xs text-gray-500">{{ $inf->topik }} &mdash; {{ $inf->penyelenggara }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($application->candidate->languageSkills->isNotEmpty())
                        <div class="bg-white rounded-xl border border-gray-100 p-5">
                            <h2 class="text-sm font-semibold text-gray-800 mb-3">Kemampuan Bahasa</h2>
                            <div class="space-y-2">
                                @foreach ($application->candidate->languageSkills as $lang)
                                    <div>
                                        <p class="text-sm font-medium text-gray-800">{{ $lang->nama_bahasa }}</p>
                                        <p class="text-xs text-gray-500">
                                            Berbicara: {{ $lang->berbicara }} &mdash;
                                            Menulis: {{ $lang->menulis }} &mdash;
                                            Membaca: {{ $lang->membaca }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
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
                            <dd class="text-sm text-gray-700">{{ $application->gaji_diharapkan }}</dd>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>

</x-layouts.app>
