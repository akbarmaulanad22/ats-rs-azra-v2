<x-layouts.public title="Lowongan Kerja - RS Azra">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Lowongan Kerja</h1>
        <p class="text-sm text-gray-500 mt-1">Bergabunglah bersama tim Rumah Sakit Azra</p>
    </div>

    @if ($vacancies->isEmpty())
        <div class="flex flex-col items-center py-20 text-center">
            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-700">Belum ada lowongan tersedia</p>
            <p class="text-xs text-gray-400 mt-1">Silakan kunjungi kembali nanti</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($vacancies as $vacancy)
                <a
                    href="{{ route('karier.show', $vacancy) }}"
                    class="bg-white rounded-xl border border-gray-100 p-5 hover:border-primary/30 hover:shadow-md transition-all ease-out duration-150 block"
                >
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <h2 class="text-sm font-semibold text-gray-900 leading-snug">{{ $vacancy->judul_posisi }}</h2>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-primary/10 text-primary shrink-0">
                            {{ $vacancy->jenis_pekerjaan->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">{{ $vacancy->unit->nama }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span>{{ $vacancy->jumlah_posisi }} posisi</span>
                        <span>Tenggat: {{ $vacancy->tenggat_lamaran->format('d M Y') }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        @if ($vacancies->hasPages())
            <div class="mt-6">
                {{ $vacancies->links() }}
            </div>
        @endif
    @endif

</x-layouts.public>
