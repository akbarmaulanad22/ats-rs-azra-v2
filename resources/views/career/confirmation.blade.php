<x-layouts.public title="Lamaran Terkirim - RS Azra">

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-primary px-6 py-6">
            <h1 class="text-xl font-bold text-white mb-1">Lamaran Berhasil Dikirim</h1>
            <p class="text-white/70 text-sm">{{ $application->vacancy->judul_posisi }} &mdash; {{ $application->vacancy->unit->nama }}</p>
        </div>

        <div class="px-6 py-6 space-y-5">
            {{-- Success message --}}
            <div class="flex items-start gap-3 p-4 rounded-lg bg-green-50 border border-green-200">
                <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-green-800">Lamaran Anda telah kami terima.</p>
                    <p class="text-xs text-green-600 mt-0.5">
                        Simpan halaman ini atau catat kode lamaran Anda untuk memantau status.
                    </p>
                </div>
            </div>

            {{-- Application summary --}}
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Ringkasan Lamaran</h2>
                <dl class="space-y-2">
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Nama</dt>
                        <dd class="text-sm text-gray-800 font-medium">{{ $application->candidate->nama_lengkap }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Email</dt>
                        <dd class="text-sm text-gray-800">{{ $application->candidate->email }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Posisi</dt>
                        <dd class="text-sm text-gray-800">{{ $application->vacancy->judul_posisi }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="text-xs text-gray-400 w-28 shrink-0">Kode Lamaran</dt>
                        <dd class="text-sm text-gray-800 font-mono break-all">{{ $application->token }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Pipeline stages --}}
            <div>
                <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Tahapan Seleksi</h2>
                <ol class="space-y-2">
                    @foreach ($application->stages as $stage)
                        <li class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0
                                @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai) bg-green-500
                                @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif) bg-primary
                                @else bg-gray-200
                                @endif">
                                @if ($stage->status === \App\Enums\ApplicationStageStatus::Selesai)
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                @elseif ($stage->status === \App\Enums\ApplicationStageStatus::Aktif)
                                    <div class="w-2 h-2 rounded-full bg-white"></div>
                                @else
                                    <span class="text-[9px] text-gray-400 font-medium">{{ $loop->iteration }}</span>
                                @endif
                            </div>
                            <span class="text-sm @if ($stage->status === \App\Enums\ApplicationStageStatus::Aktif) font-medium text-gray-900 @else text-gray-500 @endif">
                                {{ $stage->nama }}
                            </span>
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
