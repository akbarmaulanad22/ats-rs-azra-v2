<x-layouts.app title="Lowongan Kerja - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Lowongan Kerja</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola lowongan kerja RS Azra</p>
        </div>
        @can('create', App\Models\Vacancy::class)
        <a
            href="{{ route('lowongan.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Lowongan
        </a>
        @endcan
    </div>

    @php
        $activeFilters = collect(['status', 'unit_id'])
            ->filter(fn ($k) => request($k))->count();
    @endphp

    <div class="mb-3" x-data="{ open: {{ $activeFilters > 0 ? 'true' : 'false' }} }">
        <form method="GET" action="{{ route('lowongan.index') }}">

            <div class="flex flex-wrap items-center gap-2 mb-2">
                <div class="relative flex-1 min-w-52">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Cari judul posisi..."
                        class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400"
                    >
                </div>

                <button
                    type="button"
                    @click="open = !open"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm border rounded-md transition-colors ease-out duration-150 cursor-pointer bg-white"
                    :class="open ? 'border-primary/40 text-primary bg-primary/5' : 'border-gray-200 text-gray-500 hover:border-primary/40 hover:text-primary'"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filter
                    @if ($activeFilters > 0)
                        <span class="inline-flex items-center justify-center w-3.5 h-3.5 text-[9px] font-bold bg-primary text-white rounded-full">{{ $activeFilters }}</span>
                    @endif
                    <svg class="w-3 h-3 transition-transform ease-out duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <button type="submit" class="px-3.5 py-1.5 bg-primary text-white text-sm font-medium rounded-md hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                    Cari
                </button>

                @if (request()->anyFilled(['q', 'status', 'unit_id']))
                    <a href="{{ route('lowongan.index') }}" class="py-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
                        Reset
                    </a>
                @endif
            </div>

            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="grid grid-cols-2 md:grid-cols-4 gap-2.5"
            >
                <x-autocomplete-select
                    name="status"
                    label="Status"
                    :options="collect(\App\Enums\VacancyStatus::cases())->map(fn ($s) => ['id' => $s->value, 'label' => $s->label()])"
                    :value="request('status')"
                    placeholder="Semua Status"
                    label-class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1"
                />

                @unless(auth()->user()->hasRole(\App\Enums\Role::UnitHead))
                <x-autocomplete-select
                    name="unit_id"
                    label="Unit"
                    :options="$units->map(fn ($u) => ['id' => $u->id, 'label' => $u->nama])"
                    :value="request('unit_id')"
                    placeholder="Semua Unit"
                    label-class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1"
                />
                @endunless
            </div>

        </form>
    </div>

    {{-- Table card --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary border-b border-primary/10 text-white">
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-8">No.</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Posisi</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-32">Unit</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-28">Jenis</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-28">Tenggat</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-28">Status</th>
                        <th class="w-20 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($vacancies as $vacancy)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                            <td class="px-3 py-1.5 text-xs text-gray-400 tabular-nums">{{ $vacancies->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-gray-900">{{ $vacancy->judul_posisi }}</span>
                                <span class="block text-[10px] text-gray-400 mt-0.5">{{ $vacancy->jumlah_posisi }} posisi</span>
                            </td>
                            <td class="px-3 py-1.5 text-xs text-gray-600">{{ $vacancy->unit->nama }}</td>
                            <td class="px-3 py-1.5 text-xs text-gray-600">{{ $vacancy->jenis_pekerjaan->label() }}</td>
                            <td class="px-3 py-1.5 text-xs text-gray-600 tabular-nums">
                                {{ $vacancy->tenggat_lamaran->format('d M Y') }}
                                @if ($vacancy->tenggat_lamaran->isPast())
                                    <span class="block text-[10px] text-red-500">Kedaluwarsa</span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5">
                                @php
                                    $badgeClass = match ($vacancy->status) {
                                        \App\Enums\VacancyStatus::Draft => 'bg-gray-100 text-gray-600',
                                        \App\Enums\VacancyStatus::Published => 'bg-green-100 text-green-700',
                                        \App\Enums\VacancyStatus::Closed => 'bg-red-100 text-red-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $badgeClass }}">
                                    {{ $vacancy->status->label() }}
                                </span>
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="flex items-center justify-end gap-0.5">
                                    <a
                                        href="{{ route('lowongan.pipeline', $vacancy) }}"
                                        class="p-1.5 rounded text-blue-400/60 hover:text-blue-500 hover:bg-blue-50 transition-colors ease-out duration-150"
                                        title="Lihat pipeline kandidat"
                                        aria-label="Pipeline {{ $vacancy->judul_posisi }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    </a>
                                    @can('update', $vacancy)
                                    <a
                                        href="{{ route('lowongan.edit', $vacancy) }}"
                                        class="p-1.5 rounded text-amber-400/60 hover:text-amber-500 hover:bg-amber-50 transition-colors ease-out duration-150"
                                        title="Edit lowongan"
                                        aria-label="Edit {{ $vacancy->judul_posisi }}"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('delete', $vacancy)
                                    <form method="POST" action="{{ route('lowongan.destroy', $vacancy) }}" onsubmit="return confirm('Hapus lowongan ' + @js($vacancy->judul_posisi) + '?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-1.5 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors ease-out duration-150 cursor-pointer"
                                            title="Hapus lowongan"
                                            aria-label="Hapus {{ $vacancy->judul_posisi }}"
                                        >
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
                            <td colspan="7" class="px-4 py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/>
                                        </svg>
                                    </div>
                                    @if (request()->anyFilled(['q', 'status', 'unit_id']))
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Tidak ada hasil</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Coba ubah filter atau kata kunci pencarian</p>
                                        </div>
                                        <a href="{{ route('lowongan.index') }}" class="text-xs text-primary hover:text-primary-dark transition-colors ease-out duration-150">
                                            Reset filter
                                        </a>
                                    @else
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Belum ada lowongan</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Buat lowongan kerja pertama RS Azra</p>
                                        </div>
                                        @can('create', App\Models\Vacancy::class)
                                        <a
                                            href="{{ route('lowongan.create') }}"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            Buat Lowongan
                                        </a>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vacancies->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $vacancies->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
