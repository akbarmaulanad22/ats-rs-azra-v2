<x-layouts.app title="{{ $application->candidate->nama_lengkap }} - Pipeline - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Pipeline
        </a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $application->candidate->nama_lengkap }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
            </div>
            @if ($currentStage)
                @php
                    $stageBadgeClass = match ($currentStage->status->value) {
                        'aktif' => 'bg-blue-100 text-blue-700',
                        'reserved' => 'bg-amber-100 text-amber-700',
                        'selesai' => 'bg-green-100 text-green-700',
                        'gagal' => 'bg-red-100 text-red-600',
                        default => 'bg-gray-100 text-gray-500',
                    };
                    $stageStatusLabel = match ($currentStage->status->value) {
                        'aktif' => 'Aktif',
                        'reserved' => 'Ditangguhkan',
                        'selesai' => 'Selesai',
                        'gagal' => 'Ditolak',
                        default => ucfirst($currentStage->status->value),
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $stageBadgeClass }}">
                    {{ $stageStatusLabel }}
                </span>
            @endif
        </div>
    </div>

    {{-- Stage progress strip --}}
    @if ($snapshotStages->isNotEmpty())
        <div class="mb-5 overflow-x-auto">
            <div class="flex items-center gap-0 min-w-max">
                @foreach ($snapshotStages as $index => $stage)
                    @php
                        $appStage = $application->stages->firstWhere('key', $stage->key);
                        $isCurrent = $currentStage && $currentStage->key === $stage->key;
                        $stageStatus = $appStage?->status?->value ?? 'pending';

                        $dotClass = match (true) {
                            $isCurrent && $stageStatus === 'aktif' => 'bg-blue-500 ring-2 ring-blue-200',
                            $isCurrent && $stageStatus === 'reserved' => 'bg-amber-500 ring-2 ring-amber-200',
                            $isCurrent && $stageStatus === 'gagal' => 'bg-red-500 ring-2 ring-red-200',
                            $stageStatus === 'selesai' => 'bg-green-500',
                            $stageStatus === 'gagal' => 'bg-red-400',
                            $stageStatus === 'reserved' => 'bg-amber-400',
                            $stageStatus === 'aktif' => 'bg-blue-400',
                            default => 'bg-gray-200',
                        };

                        $labelClass = $isCurrent ? 'font-semibold text-gray-900' : 'text-gray-400';
                        $lineClass = $stageStatus === 'selesai' ? 'bg-green-300' : 'bg-gray-200';
                    @endphp

                    <div class="flex flex-col items-center">
                        <div class="flex items-center">
                            @if ($index > 0)
                                <div class="w-8 h-0.5 {{ $lineClass }}"></div>
                            @endif
                            <div class="w-3 h-3 rounded-full flex-shrink-0 {{ $dotClass }}"></div>
                        </div>
                        <span class="text-[10px] mt-1 px-1 {{ $labelClass }} whitespace-nowrap">{{ $stage->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Two-column layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 items-start">

        {{-- Left: Candidate info (2/5 width) --}}
        <div class="lg:col-span-2 space-y-4">
            @include('vacancies.partials._informasi-kandidat', ['application' => $application, 'lowongan' => $lowongan])
        </div>

        {{-- Right: Stage action panel (3/5 width) --}}
        <div class="lg:col-span-3 space-y-4">

            @if (!$currentStage)
                <div class="bg-white rounded-xl border border-gray-100 p-5 text-center text-sm text-gray-400">
                    Tidak ada tahap aktif untuk kandidat ini.
                </div>
            @else
                @php $stageKey = $currentStage->key; @endphp

                @if ($stageKey === 'lamaran')
                    @include('vacancies.partials._aksi-lamaran', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'skrining_cv')
                    @include('vacancies.partials._aksi-skrining', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'tes_kompetensi')
                    @include('vacancies.partials._aksi-tes-kompetensi', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                        'testAllReviewed' => $testAllReviewed,
                    ])
                @elseif ($stageKey === 'tes_disc')
                    @include('vacancies.partials._aksi-tes-disc', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'tes_mbti')
                    @include('vacancies.partials._aksi-tes-mbti', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif (in_array($stageKey, ['wawancara_kepala_unit', 'wawancara_manajer_hr', 'wawancara_direktur']))
                    @include('vacancies.partials._aksi-wawancara', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                        'assignedTemplates' => $assignedTemplates,
                        'assignedReadinessTemplates' => $assignedReadinessTemplates,
                        'priorInterviews' => $priorInterviews,
                    ])
                @elseif ($stageKey === 'surat_penawaran')
                    @include('vacancies.partials._aksi-surat-penawaran', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'mcu')
                    @include('vacancies.partials._aksi-mcu', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @elseif ($stageKey === 'onboarding')
                    @include('vacancies.partials._aksi-onboarding', [
                        'application' => $application,
                        'lowongan' => $lowongan,
                        'currentStage' => $currentStage,
                    ])
                @else
                    <div class="bg-white rounded-xl border border-gray-100 p-5 text-center text-sm text-gray-400">
                        Tidak ada panel aksi untuk tahap ini.
                    </div>
                @endif
            @endif

        </div>
    </div>

</x-layouts.app>
