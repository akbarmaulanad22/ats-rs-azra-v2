<x-layouts.app title="{{ $candidate->nama_lengkap }} - Kandidat Potensial - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('kandidat-potensial.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Kandidat Potensial
        </a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold text-gray-900">{{ $candidate->nama_lengkap }}</h1>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Kandidat Potensial
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">{{ $candidate->email }} &mdash; {{ $candidate->no_telepon }}</p>
            </div>
            @can('unflagTalentPool', $candidate)
                <form method="POST" action="{{ route('kandidat-potensial.destroy', $candidate) }}"
                      onsubmit="return confirm('Hapus kandidat ini dari Kandidat Potensial?')" class="flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors ease-out duration-150 cursor-pointer">
                        Hapus dari Kandidat Potensial
                    </button>
                </form>
            @endcan
        </div>
    </div>

    {{-- Talent pool meta --}}
    <div class="mb-4 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
        <p class="text-[10px] font-medium text-emerald-700 uppercase tracking-wide mb-1">Alasan Ditandai</p>
        <p class="text-sm text-gray-800">{{ $candidate->talent_pool_reason }}</p>
        <p class="text-[11px] text-emerald-600 mt-2">
            Ditandai oleh {{ $candidate->talentPoolFlaggedBy?->name ?? '—' }}
            pada {{ $candidate->talent_pool_flagged_at?->translatedFormat('d M Y H:i') }}
        </p>
    </div>

    <div class="space-y-4">
        @include('vacancies.partials._informasi-kandidat', ['application' => $application, 'lowongan' => $application->vacancy])
    </div>

</x-layouts.app>
