<x-layouts.public title="Status Lamaran - RS Azra">

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-primary px-6 py-6">
            <h1 class="text-xl font-bold text-white mb-1">Status Lamaran</h1>
            <p class="text-white/70 text-sm">{{ $application->vacancy->judul_posisi }} &mdash; {{ $application->vacancy->unit->nama }}</p>
        </div>

        <div class="px-6 py-6 space-y-6">
            {{-- Candidate info --}}
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Informasi Pelamar</h2>
                <dl class="space-y-2">
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Nama</dt>
                        <dd class="text-sm text-gray-800 font-medium">{{ $application->candidate->nama_lengkap }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Posisi</dt>
                        <dd class="text-sm text-gray-800">{{ $application->vacancy->judul_posisi }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Unit</dt>
                        <dd class="text-sm text-gray-800">{{ $application->vacancy->unit->nama }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Progress tracker --}}
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Tahapan Seleksi</h2>
                @php
                    $gagalStage = $application->stages->firstWhere('status', \App\Enums\ApplicationStageStatus::Gagal);
                    $gagalPosition = $gagalStage?->position;
                @endphp
                <ol class="space-y-0">
                    @foreach ($application->stages as $stage)
                        @php
                            $isAfterGagal = $gagalPosition !== null && $stage->position > $gagalPosition;
                            $isLast = $loop->last;
                        @endphp
                        <li class="flex gap-4 {{ $isLast ? '' : 'pb-5' }}">
                            {{-- Timeline icon + line --}}
                            <div class="flex flex-col items-center shrink-0">
                                <div class="flex items-center justify-center w-7 h-7 rounded-full
                                    @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai) bg-green-500
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif) bg-primary
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved) bg-amber-400
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Gagal) bg-red-500
                                    @else bg-gray-200
                                    @endif">
                                    @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                                        <div class="w-2.5 h-2.5 rounded-full bg-white"></div>
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved)
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Gagal)
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @else
                                        <span class="text-[9px] text-gray-400 font-medium">{{ $loop->iteration }}</span>
                                    @endif
                                </div>
                                @unless ($isLast)
                                    <div class="w-px flex-1 mt-1 {{ $stage->status === \App\Enums\ApplicationStageStatus::Selesai ? 'bg-green-200' : 'bg-gray-100' }}"></div>
                                @endunless
                            </div>

                            {{-- Stage info --}}
                            <div class="pt-0.5 pb-1 {{ $isAfterGagal ? 'opacity-40' : '' }}">
                                <p class="text-sm font-medium leading-tight
                                    @if ($stage->status === \App\Enums\ApplicationStageStatus::Gagal) text-red-700
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif) text-gray-900
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved) text-amber-700
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Selesai) text-gray-700
                                    @else text-gray-400
                                    @endif">
                                    {{ $stage->nama }}
                                </p>
                                <p class="text-xs mt-0.5
                                    @if ($stage->status === \App\Enums\ApplicationStageStatus::Gagal) text-red-500
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Selesai) text-green-600
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif) text-primary/70
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved) text-amber-600
                                    @else text-gray-300
                                    @endif">
                                    @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                                        Selesai &middot; {{ $stage->updated_at->format('d/m/Y') }}
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                                        Sedang berlangsung
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Reserved)
                                        Ditangguhkan
                                    @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Gagal)
                                        Tidak Lolos
                                    @else
                                        Menunggu
                                    @endif
                                </p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            <a href="{{ route('karier.index') }}" class="text-sm text-primary hover:underline">
                &larr; Lihat lowongan lainnya
            </a>
        </div>
    </div>

</x-layouts.public>
