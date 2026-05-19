<x-layouts.app title="Pipeline - {{ $lowongan->judul_posisi }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Lowongan Kerja
        </a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Pipeline Kandidat</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
            </div>
            <div class="flex items-center gap-2">
                @can('create', \App\Models\VacancyTest::class)
                    <a
                        href="{{ route('lowongan.tes.show', $lowongan) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-primary/30 text-primary rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                    >
                        Tes Kompetensi
                    </a>
                @endcan
                @can('manageInterviewTemplates', $lowongan)
                    <a
                        href="{{ route('lowongan.template-wawancara.show', $lowongan) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-primary/30 text-primary rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                    >
                        Template Wawancara
                    </a>
                @endcan
                @can('export', $lowongan)
                    <div x-data="{ open: false }" class="relative">
                        <button
                            @click="open = !open"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition-colors ease-out duration-150"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Ekspor
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute right-0 mt-1 w-44 bg-white border border-gray-100 rounded-lg shadow-lg z-10"
                        >
                            <a
                                href="{{ route('lowongan.export.list', array_merge(request()->only(['stage', 'status', 'search']), ['lowongan' => $lowongan, 'format' => 'xlsx'])) }}"
                                class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-t-lg"
                            >
                                Ekspor Excel (.xlsx)
                            </a>
                            <a
                                href="{{ route('lowongan.export.list', array_merge(request()->only(['stage', 'status', 'search']), ['lowongan' => $lowongan, 'format' => 'csv'])) }}"
                                class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-b-lg"
                            >
                                Ekspor CSV (.csv)
                            </a>
                        </div>
                    </div>
                @endcan
                <span class="text-xs font-medium px-2.5 py-1 rounded-full
                    {{ $lowongan->status->value === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    {{ $lowongan->status->label() }}
                </span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('pipeline'))
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first('pipeline') }}
        </div>
    @endif

    {{-- Search & Filter --}}
    @php
        $activeFilters = collect(['stage', 'status'])->filter(fn ($k) => request($k))->count();
    @endphp

    <div class="mb-3" x-data="{ open: {{ $activeFilters > 0 ? 'true' : 'false' }} }">
        <form method="GET" action="{{ route('lowongan.pipeline', $lowongan) }}">

            <div class="flex flex-wrap items-center gap-2 mb-2">
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

                @if (request()->hasAny(['search', 'stage', 'status']))
                    <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="py-1.5 text-xs text-gray-400 hover:text-gray-600 transition-colors ease-out duration-150">
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
                class="grid grid-cols-2 gap-2.5"
            >
                <div>
                    <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Tahap</label>
                    <select name="stage" class="w-full px-2.5 py-1.5 text-sm border border-gray-200 rounded-md bg-white focus:outline-none focus:ring-1 focus:ring-primary/30">
                        <option value="">Semua Tahap</option>
                        @foreach ($snapshotStages as $stage)
                            <option value="{{ $stage->key }}" @selected(request('stage') === $stage->key)>{{ $stage->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">Status</label>
                    <select name="status" class="w-full px-2.5 py-1.5 text-sm border border-gray-200 rounded-md bg-white focus:outline-none focus:ring-1 focus:ring-primary/30">
                        <option value="">Semua</option>
                        <option value="menunggu" @selected(request('status') === 'menunggu')>Menunggu</option>
                        <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                        <option value="ditangguhkan" @selected(request('status') === 'ditangguhkan')>Ditangguhkan</option>
                        <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
                        <option value="gagal" @selected(request('status') === 'gagal')>Gagal</option>
                    </select>
                </div>
            </div>

        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-primary border-b border-primary/10 text-white">
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-10">No</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Nama Kandidat</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider min-w-96">Tahap</th>
                        <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-28">Status</th>
                        <th class="w-20 px-3 py-2.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($applications as $application)
                        @php
                            $currentStage = $application->currentStage();
                            $currentKey = $currentStage?->key;
                            $stagesArr = $snapshotStages;
                            $currentIdx = $stagesArr->search(fn ($s) => $s->key === $currentKey);
                            if ($currentIdx === false) { $currentIdx = 0; }
                            $total = $stagesArr->count();
                            $windowSize = 7;
                            $windowStart = max(0, $currentIdx - 3);
                            $windowEnd = min($total - 1, $currentIdx + 3);
                            if ($windowEnd - $windowStart + 1 < min($windowSize, $total)) {
                                if ($windowStart === 0) {
                                    $windowEnd = min($total - 1, $windowSize - 1);
                                } else {
                                    $windowStart = max(0, $total - $windowSize);
                                }
                            }
                            $showLeadingEllipsis = $windowStart > 0;
                            $showTrailingEllipsis = $windowEnd < $total - 1;
                            $windowStages = $stagesArr->slice($windowStart, $windowEnd - $windowStart + 1)->values();
                        @endphp
                        <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                            <td class="px-3 py-2 text-xs text-gray-400 tabular-nums">
                                {{ $applications->firstItem() + $loop->index }}
                            </td>
                            <td class="px-3 py-2">
                                <span class="font-medium text-gray-800 text-xs leading-tight block">
                                    {{ $application->candidate->nama_lengkap }}
                                </span>
                                <span class="text-[11px] text-gray-400">{{ $application->candidate->email }}</span>
                            </td>
                            <td class="px-3 py-2">
                                <div class="flex items-center gap-1 text-xs text-gray-500 flex-wrap">
                                    @if ($showLeadingEllipsis)
                                        <span class="text-gray-400">...</span>
                                        <span class="text-gray-300">→</span>
                                    @endif
                                    @foreach ($windowStages as $wStage)
                                        @if (!$loop->first)
                                            <span class="text-gray-300">→</span>
                                        @endif
                                        @if ($wStage->key === $currentKey)
                                            <span class="font-semibold text-primary">[{{ $wStage->nama }}]</span>
                                        @else
                                            <span class="text-gray-500">{{ $wStage->nama }}</span>
                                        @endif
                                    @endforeach
                                    @if ($showTrailingEllipsis)
                                        <span class="text-gray-300">→</span>
                                        <span class="text-gray-400">...</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2">
                                @php
                                    $statusValue = $currentStage?->status;
                                @endphp
                                @if ($statusValue === \App\Enums\ApplicationStageStatus::Reserved)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-100 text-amber-700">Ditangguhkan</span>
                                @elseif ($statusValue === \App\Enums\ApplicationStageStatus::Aktif)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-100 text-blue-700">Aktif</span>
                                @elseif ($statusValue === \App\Enums\ApplicationStageStatus::Selesai)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-green-100 text-green-700">Selesai</span>
                                @elseif ($statusValue === \App\Enums\ApplicationStageStatus::Gagal)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-100 text-red-600">Gagal</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-500">Menunggu</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <a
                                    href="{{ route('lowongan.pipeline.show', [$lowongan, $application]) }}"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                                >
                                    Tinjau
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center">
                                <div class="flex flex-col items-center gap-2.5 max-w-xs mx-auto">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                    </div>
                                    @if (request()->hasAny(['search', 'stage', 'status']))
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Tidak ada hasil</p>
                                            <p class="text-xs text-gray-400 mt-0.5">Coba ubah filter atau kata kunci pencarian</p>
                                        </div>
                                        <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="text-xs text-primary hover:text-primary-dark transition-colors ease-out duration-150">
                                            Reset filter
                                        </a>
                                    @else
                                        <div>
                                            <p class="text-sm font-medium text-gray-700">Belum ada kandidat yang melamar</p>
                                        </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($applications->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $applications->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
