<x-layouts.app title="Kandidat Potensial - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Kandidat Potensial</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kandidat yang disimpan untuk dipertimbangkan pada lowongan mendatang</p>
        </div>
    </div>

    <div class="mb-3">
        <form method="GET" action="{{ route('kandidat-potensial.index') }}">
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative flex-1 min-w-52">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama kandidat..."
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400"
                    >
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Cari
                </button>
                @if (request('search'))
                    <a href="{{ route('kandidat-potensial.index') }}" class="py-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary border-b border-primary/10 text-white">
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Nama Kandidat</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Alasan</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Lamaran</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-44">Ditandai</th>
                        <th class="w-12 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($candidates as $candidate)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100 align-top">
                            <td class="px-3 py-2 text-xs">
                                <a href="{{ route('kandidat-potensial.show', $candidate) }}" class="font-medium text-gray-800 hover:text-primary transition-colors ease-out duration-150 leading-tight block">{{ $candidate->nama_lengkap }}</a>
                                <span class="text-gray-400">{{ $candidate->email }}</span>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-600 max-w-xs">{{ $candidate->talent_pool_reason }}</td>
                            <td class="px-3 py-2 text-xs">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($candidate->applications as $app)
                                        <a href="{{ route('lowongan.pipeline.show', [$app->vacancy, $app]) }}"
                                           class="inline-flex items-center px-2 py-0.5 rounded bg-primary/10 text-primary font-medium hover:bg-primary/20 transition-colors ease-out duration-150">
                                            {{ $app->vacancy->judul_posisi }}
                                        </a>
                                    @empty
                                        <span class="text-gray-300">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-3 py-2 text-xs text-gray-500">
                                <span class="block">{{ $candidate->talent_pool_flagged_at?->translatedFormat('d M Y') }}</span>
                                <span class="text-gray-400">oleh {{ $candidate->talentPoolFlaggedBy?->name ?? '—' }}</span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center justify-end gap-0.5">
                                    <a href="{{ route('kandidat-potensial.show', $candidate) }}"
                                       class="p-1.5 rounded text-primary/40 hover:text-primary hover:bg-primary/10 transition-colors ease-out duration-150"
                                       title="Lihat detail"
                                       aria-label="Lihat detail {{ $candidate->nama_lengkap }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @can('unflagTalentPool', $candidate)
                                        <form method="POST" action="{{ route('kandidat-potensial.destroy', $candidate) }}"
                                              onsubmit="return confirm('Hapus ' + @js($candidate->nama_lengkap) + ' dari Kandidat Potensial?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors ease-out duration-150 cursor-pointer"
                                                    title="Hapus dari Kandidat Potensial"
                                                    aria-label="Hapus {{ $candidate->nama_lengkap }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.562.562 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/>
                                        </svg>
                                    </div>
                                    @if (request('search'))
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Tidak ada hasil</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Coba ubah kata kunci pencarian</p>
                                        </div>
                                        <a href="{{ route('kandidat-potensial.index') }}" class="text-xs text-primary hover:text-primary-dark transition-colors ease-out duration-150">
                                            Reset pencarian
                                        </a>
                                    @else
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Belum ada Kandidat Potensial</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Tandai kandidat yang ditangguhkan dari halaman pipeline</p>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($candidates->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
