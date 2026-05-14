<x-layouts.app title="Template Alur Kerja - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Template Alur Kerja</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola template tahapan rekrutmen</p>
        </div>
        <a
            href="{{ route('template-alur.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Template
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    {{-- Search --}}
    <div class="mb-3">
        <form method="GET" action="{{ route('template-alur.index') }}">
            <div class="flex items-center gap-2">
                <div class="relative flex-1 min-w-52">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari nama template..."
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400"
                    >
                </div>
                <button type="submit" class="px-3.5 py-1.5 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Cari
                </button>
                @if (request('q'))
                    <a href="{{ route('template-alur.index') }}" class="py-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary border-b border-primary/10 text-white">
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-8">No.</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-40">Nama Template</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Tahapan</th>
                        <th class="w-20 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($templates as $template)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                            <td class="px-3 py-2 text-xs text-gray-400 tabular-nums">{{ $templates->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-2">
                                <span class="text-xs font-semibold text-gray-900">{{ $template->nama }}</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">{{ $template->stages->count() }} tahap</span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex flex-wrap items-center gap-y-1">
                                    @foreach ($template->stages as $stage)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium
                                            @if ($stage->is_locked_first || $stage->is_locked_last)
                                                bg-primary/15 text-primary
                                            @else
                                                bg-gray-100 text-gray-600
                                            @endif
                                        ">{{ $stage->nama }}</span>
                                        @if (!$loop->last)
                                            <svg class="w-3 h-3 text-gray-300 mx-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center justify-end gap-0.5">
                                    <a
                                        href="{{ route('template-alur.edit', $template) }}"
                                        class="p-1.5 rounded text-amber-400/60 hover:text-amber-500 hover:bg-amber-50 transition-colors ease-out duration-150"
                                        title="Edit template"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('template-alur.destroy', $template) }}" onsubmit="return confirm('Hapus template ' + {{ Js::from($template->nama) }} + '?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors ease-out duration-150 cursor-pointer"
                                            title="Hapus template"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                        </svg>
                                    </div>
                                    @if (request('q'))
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Tidak ada hasil</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Coba ubah kata kunci pencarian</p>
                                        </div>
                                        <a href="{{ route('template-alur.index') }}" class="text-xs text-primary hover:text-primary-dark transition-colors ease-out duration-150">
                                            Reset pencarian
                                        </a>
                                    @else
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Belum ada template</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Buat template alur kerja rekrutmen pertama Anda</p>
                                        </div>
                                        <a
                                            href="{{ route('template-alur.create') }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Buat Template
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($templates->hasPages())
        <div class="mt-4">
            {{ $templates->links() }}
        </div>
    @endif

</x-layouts.app>
