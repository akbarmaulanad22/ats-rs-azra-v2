<x-layouts.app title="Template Lowongan - ATS RS Azra">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Template Lowongan</h1>
            <p class="text-xs text-gray-500 mt-0.5">Definisi lowongan yang dapat diterbitkan berulang kali</p>
        </div>
        @can('create', App\Models\JobTemplate::class)
        <a
            href="{{ route('template-lowongan.create') }}"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Template
        </a>
        @endcan
    </div>

    @if (session('status'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded text-xs text-green-700">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded text-xs text-red-700">{{ session('error') }}</div>
    @endif

    @php
        $activeFilters = collect(['status', 'unit_id'])->filter(fn ($k) => request($k))->count();
    @endphp

    <div class="mb-3" x-data="{ open: {{ $activeFilters > 0 ? 'true' : 'false' }} }">
        <form method="GET" action="{{ route('template-lowongan.index') }}">
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
                    <a href="{{ route('template-lowongan.index') }}" class="py-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">Reset</a>
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
                    :options="collect(\App\Enums\JobTemplateStatus::cases())->map(fn ($s) => ['id' => $s->value, 'label' => $s->label()])"
                    :value="request('status')"
                    placeholder="Semua Status"
                    label-class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1"
                />

                <x-autocomplete-select
                    name="unit_id"
                    label="Unit"
                    :options="$units->map(fn ($u) => ['id' => $u->id, 'label' => $u->nama])"
                    :value="request('unit_id')"
                    placeholder="Semua Unit"
                    label-class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1"
                />
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
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-24">Lowongan</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-28">Status</th>
                        <th class="w-12 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jobTemplates as $jobTemplate)
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                            <td class="px-3 py-1.5 text-xs text-gray-400 tabular-nums">{{ $jobTemplates->firstItem() + $loop->index }}</td>
                            <td class="px-3 py-1.5">
                                <span class="text-xs font-semibold text-gray-900">{{ $jobTemplate->judul_posisi }}</span>
                            </td>
                            <td class="px-3 py-1.5 text-xs text-gray-600">{{ $jobTemplate->unit->nama }}</td>
                            <td class="px-3 py-1.5 text-xs text-gray-600">{{ $jobTemplate->jenis_pekerjaan->label() }}</td>
                            <td class="px-3 py-1.5 text-xs text-gray-600 tabular-nums">{{ $jobTemplate->vacancies_count }}</td>
                            <td class="px-3 py-1.5">
                                @if ($jobTemplate->status === \App\Enums\JobTemplateStatus::Active)
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-medium rounded-full bg-green-100 text-green-700">{{ $jobTemplate->status->label() }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-medium rounded-full bg-gray-100 text-gray-500">{{ $jobTemplate->status->label() }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5">
                                <div class="relative flex justify-end" x-data="{ open: false }" @click.outside="open = false">
                                    <button type="button" @click="open = !open" class="p-1 text-gray-400 hover:text-primary rounded transition-colors">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>
                                    <div x-show="open" x-transition x-cloak class="absolute right-0 top-full mt-1 z-10 w-44 bg-white border border-gray-200 rounded-md shadow-lg py-1 text-xs">
                                        @if ($jobTemplate->status === \App\Enums\JobTemplateStatus::Active)
                                            <a href="{{ route('template-lowongan.terbitkan.form', $jobTemplate) }}" class="block px-3 py-1.5 text-primary font-medium hover:bg-primary/5">Terbitkan Lowongan</a>
                                            <hr class="my-1 border-gray-100">
                                        @endif
                                        <a href="{{ route('template-lowongan.tes.show', $jobTemplate) }}" class="block px-3 py-1.5 text-gray-700 hover:bg-gray-50">Konfigurasi Tes</a>
                                        <a href="{{ route('template-lowongan.template-wawancara.show', $jobTemplate) }}" class="block px-3 py-1.5 text-gray-700 hover:bg-gray-50">Template Wawancara</a>
                                        <a href="{{ route('template-lowongan.edit', $jobTemplate) }}" class="block px-3 py-1.5 text-gray-700 hover:bg-gray-50">Edit</a>
                                        <hr class="my-1 border-gray-100">
                                        <form method="POST" action="{{ route('template-lowongan.destroy', $jobTemplate) }}" onsubmit="return confirm('Hapus template ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="block w-full text-left px-3 py-1.5 text-red-600 hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-10 text-center text-xs text-gray-400">Belum ada template lowongan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $jobTemplates->links() }}
    </div>

</x-layouts.app>
