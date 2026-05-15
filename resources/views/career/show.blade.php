<x-layouts.public title="{{ $vacancy->judul_posisi }} - RS Azra">

    <div class="mb-4">
        <a href="{{ route('karier.index') }}" class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Lowongan
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        {{-- Header --}}
        <div class="bg-primary px-6 py-6">
            <h1 class="text-xl font-bold text-white mb-1">{{ $vacancy->judul_posisi }}</h1>
            <p class="text-white/70 text-sm">{{ $vacancy->unit->nama }}</p>
        </div>

        {{-- Meta info --}}
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap gap-4">
            <div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Jenis Pekerjaan</span>
                <span class="text-sm text-gray-700 font-medium">{{ $vacancy->jenis_pekerjaan->label() }}</span>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Jumlah Posisi</span>
                <span class="text-sm text-gray-700 font-medium">{{ $vacancy->jumlah_posisi }}</span>
            </div>
            <div>
                <span class="text-[10px] text-gray-400 uppercase tracking-wider font-medium block">Tenggat Lamaran</span>
                <span class="text-sm text-gray-700 font-medium">{{ $vacancy->tenggat_lamaran->format('d M Y') }}</span>
            </div>
        </div>

        {{-- Description & qualifications --}}
        <div class="px-6 py-5 space-y-6">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Deskripsi Pekerjaan</h2>
                <div class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $vacancy->deskripsi_pekerjaan }}</div>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900 mb-2">Kualifikasi</h2>
                <div class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $vacancy->kualifikasi }}</div>
            </div>
        </div>

        {{-- Apply CTA --}}
        <div class="px-6 py-5 border-t border-gray-100 bg-gray-50/50">
            <p class="text-xs text-gray-500 mb-3">Tertarik melamar posisi ini? Klik tombol di bawah untuk memulai proses lamaran.</p>
            <a
                href="{{ route('karier.lamar', $vacancy) }}"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lamar Sekarang
            </a>
        </div>
    </div>

</x-layouts.public>
