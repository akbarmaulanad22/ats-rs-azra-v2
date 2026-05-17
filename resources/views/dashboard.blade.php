<x-layouts.app title="Dashboard - ATS RS Azra">

@if($isHrAdmin ?? false)

    {{-- Page header --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 leading-tight">Dashboard Rekrutmen</h1>
            <p class="text-xs text-gray-400 mt-1">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-primary/10 text-primary font-medium whitespace-nowrap mt-1">
            {{ auth()->user()->role->label() }}
        </span>
    </div>

    {{-- Filter bar --}}
    <div class="bg-white rounded-xl px-5 py-4 mb-5">
        <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Dari Tanggal</label>
                <input
                    type="date"
                    name="date_from"
                    value="{{ $filters['date_from'] ?? '' }}"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Sampai Tanggal</label>
                <input
                    type="date"
                    name="date_to"
                    value="{{ $filters['date_to'] ?? '' }}"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30"
                >
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Unit / Departemen</label>
                <select
                    name="unit_id"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30 bg-white"
                >
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected(($filters['unit_id'] ?? null) == $unit->id)>
                            {{ $unit->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500">Lowongan</label>
                <select
                    name="vacancy_id"
                    class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30 bg-white"
                >
                    <option value="">Semua Lowongan</option>
                    @foreach($vacancies as $vacancy)
                        <option value="{{ $vacancy->id }}" @selected(($filters['vacancy_id'] ?? null) == $vacancy->id)>
                            {{ $vacancy->judul_posisi }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button
                type="submit"
                class="px-4 py-2 rounded-lg bg-primary text-white text-sm font-medium hover:bg-primary/90 transition-colors"
            >
                Terapkan
            </button>
            @if(array_filter($filters, fn ($v) => $v !== null && $v !== ''))
            <a
                href="{{ route('dashboard') }}"
                class="px-4 py-2 rounded-lg border border-gray-200 text-gray-600 text-sm font-medium hover:bg-gray-50 transition-colors"
            >
                Reset
            </a>
            @endif
        </form>
    </div>

    {{-- Stats band --}}
    <div class="bg-white rounded-xl px-6 py-5 mb-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-5 lg:gap-y-0 lg:divide-x lg:divide-gray-100">
            <div class="lg:pr-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Total Lamaran</p>
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ number_format($totalApplications) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $filters['date_from'] ?? 'semua waktu' }}</p>
            </div>
            <div class="lg:px-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Dalam Proses</p>
                <p class="text-3xl font-bold text-primary leading-none">{{ number_format($inProcess) }}</p>
                <p class="text-xs text-gray-400 mt-1">sedang berjalan</p>
            </div>
            <div class="lg:px-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Diterima</p>
                <p class="text-3xl font-bold text-secondary-dark leading-none">{{ number_format($accepted) }}</p>
                <p class="text-xs text-gray-400 mt-1">onboarding selesai</p>
            </div>
            <div class="lg:pl-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Lowongan Aktif</p>
                <p class="text-3xl font-bold text-gray-900 leading-none">{{ number_format($openVacancies) }}</p>
                <p class="text-xs text-gray-400 mt-1">dipublikasikan</p>
            </div>
        </div>
    </div>

    {{-- Pipeline funnel --}}
    <div class="bg-white rounded-xl p-6 mb-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-1">Corong Pipeline</h2>
        <p class="text-xs text-gray-400 mb-5">Jumlah kandidat yang mencapai setiap tahap (kumulatif)</p>

        @if($funnel->isEmpty())
            <p class="text-sm text-gray-400 text-center py-6">Belum ada data lamaran.</p>
        @else
        <div class="space-y-3">
            @foreach($funnel as $stage)
            @php $pct = $funnelMax > 0 ? round(($stage->total / $funnelMax) * 100) : 0; @endphp
            <div class="flex items-center gap-3">
                <div class="w-44 shrink-0 text-xs text-gray-600 truncate font-medium" title="{{ $stage->nama }}">
                    {{ $stage->nama }}
                </div>
                <div class="flex-1 bg-gray-100 rounded-full h-5 relative overflow-hidden">
                    <div
                        class="h-full bg-primary/80 rounded-full transition-all duration-500"
                        style="width: {{ $pct }}%"
                    ></div>
                </div>
                <div class="w-14 text-right text-sm font-bold text-gray-900">{{ number_format($stage->total) }}</div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Stage rates and bottlenecks --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">

        {{-- Pass / Fail / Reserved rates --}}
        <div class="bg-white rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Tingkat Lulus / Gagal per Tahap</h2>
            <p class="text-xs text-gray-400 mb-4">Dari kandidat yang mencapai tahap tersebut</p>

            @if($stageRates->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Belum ada data.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left font-semibold text-gray-500 pb-2 pr-3">Tahap</th>
                            <th class="text-right font-semibold text-secondary-dark pb-2 px-2">Lulus</th>
                            <th class="text-right font-semibold text-red-500 pb-2 px-2">Gagal</th>
                            <th class="text-right font-semibold text-amber-500 pb-2 px-2">Reserved</th>
                            <th class="text-right font-semibold text-gray-400 pb-2 pl-2">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($stageRates as $row)
                        @php
                            $passRate = $row->total > 0 ? round(($row->passed / $row->total) * 100) : 0;
                            $failRate = $row->total > 0 ? round(($row->failed / $row->total) * 100) : 0;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 pr-3 font-medium text-gray-700 max-w-[120px] truncate" title="{{ $row->nama }}">
                                {{ $row->nama }}
                            </td>
                            <td class="py-2.5 px-2 text-right">
                                <span class="text-secondary-dark font-semibold">{{ $row->passed }}</span>
                                <span class="text-gray-400 ml-0.5">({{ $passRate }}%)</span>
                            </td>
                            <td class="py-2.5 px-2 text-right">
                                <span class="text-red-500 font-semibold">{{ $row->failed }}</span>
                                <span class="text-gray-400 ml-0.5">({{ $failRate }}%)</span>
                            </td>
                            <td class="py-2.5 px-2 text-right text-amber-600 font-semibold">
                                {{ $row->reserved_count }}
                            </td>
                            <td class="py-2.5 pl-2 text-right text-gray-500 font-semibold">
                                {{ $row->total }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Stage bottlenecks --}}
        <div class="bg-white rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Hambatan Tahap</h2>
            <p class="text-xs text-gray-400 mb-5">Rata-rata hari yang dihabiskan di setiap tahap</p>

            @if($bottlenecks->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Belum ada data.</p>
            @else
            <div class="space-y-3">
                @foreach($bottlenecks as $row)
                @php $pct = $bottleneckMax > 0 ? round((floatval($row->avg_days) / $bottleneckMax) * 100) : 0; @endphp
                <div class="flex items-center gap-3">
                    <div class="w-36 shrink-0 text-xs text-gray-600 truncate font-medium" title="{{ $row->nama }}">
                        {{ $row->nama }}
                    </div>
                    <div class="flex-1 bg-gray-100 rounded-full h-4 relative overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-500 {{ floatval($row->avg_days) > ($bottleneckMax * 0.6) ? 'bg-red-400' : (floatval($row->avg_days) > ($bottleneckMax * 0.3) ? 'bg-amber-400' : 'bg-secondary/70') }}"
                            style="width: {{ $pct }}%"
                        ></div>
                    </div>
                    <div class="w-16 text-right text-xs font-bold text-gray-700">
                        {{ number_format(floatval($row->avg_days), 1) }} hr
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

    {{-- Time-to-hire + Vacancy summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Time-to-hire --}}
        <div class="bg-white rounded-xl p-6 flex flex-col">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Waktu Rekrutmen</h2>
            <p class="text-xs text-gray-400 mb-6">Rata-rata hari dari lamaran hingga onboarding selesai</p>

            <div class="flex-1 flex flex-col items-center justify-center">
                @if($timeToHire === null)
                    <p class="text-sm text-gray-400">Belum ada kandidat yang menyelesaikan onboarding.</p>
                @else
                    <p class="text-6xl font-bold text-primary leading-none">
                        {{ number_format(floatval($timeToHire), 1) }}
                    </p>
                    <p class="text-sm text-gray-500 mt-3">hari rata-rata</p>
                @endif
            </div>
        </div>

        {{-- Vacancy summary --}}
        <div class="lg:col-span-2 bg-white rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Ringkasan Lowongan</h2>

            @if($vacancySummary->isEmpty())
                <p class="text-sm text-gray-400 text-center py-6">Belum ada lowongan.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left font-semibold text-gray-500 pb-2 pr-3">Posisi</th>
                            <th class="text-left font-semibold text-gray-500 pb-2 pr-3">Unit</th>
                            <th class="text-right font-semibold text-gray-500 pb-2 px-2">Pelamar</th>
                            <th class="text-right font-semibold text-gray-500 pb-2 px-2">Terisi / Tersedia</th>
                            <th class="text-left font-semibold text-gray-500 pb-2 px-2">Status</th>
                            <th class="text-left font-semibold text-gray-500 pb-2 pl-2">Tenggat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($vacancySummary as $vacancy)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-2.5 pr-3 font-medium text-gray-800 max-w-[140px] truncate" title="{{ $vacancy->judul_posisi }}">
                                {{ $vacancy->judul_posisi }}
                            </td>
                            <td class="py-2.5 pr-3 text-gray-500 max-w-[100px] truncate">
                                {{ $vacancy->unit?->nama ?? '—' }}
                            </td>
                            <td class="py-2.5 px-2 text-right font-semibold text-gray-700">
                                {{ $vacancy->total_pelamar }}
                            </td>
                            <td class="py-2.5 px-2 text-right">
                                <span class="font-semibold {{ $vacancy->posisi_terisi >= $vacancy->jumlah_posisi ? 'text-secondary-dark' : 'text-gray-700' }}">
                                    {{ $vacancy->posisi_terisi }}
                                </span>
                                <span class="text-gray-400">/ {{ $vacancy->jumlah_posisi }}</span>
                            </td>
                            <td class="py-2.5 px-2">
                                @php
                                    $badge = match($vacancy->status) {
                                        \App\Enums\VacancyStatus::Published => 'bg-green-50 text-green-700',
                                        \App\Enums\VacancyStatus::Closed => 'bg-red-50 text-red-600',
                                        default => 'bg-gray-100 text-gray-500',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full {{ $badge }} font-medium">
                                    {{ $vacancy->status->label() }}
                                </span>
                            </td>
                            <td class="py-2.5 pl-2 text-gray-500 whitespace-nowrap">
                                {{ $vacancy->tenggat_lamaran->locale('id')->isoFormat('D MMM YYYY') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>

@else

    {{-- Non-HR-Admin landing --}}
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                Selamat datang, {{ auth()->user()->name }}
            </h1>
            <p class="text-xs text-gray-400 mt-1">
                {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
            </p>
        </div>
        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs bg-primary/10 text-primary font-medium whitespace-nowrap mt-1">
            {{ auth()->user()->role->label() }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="bg-white rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="space-y-1">
                @can('viewAny', App\Models\Vacancy::class)
                <a href="{{ route('lowongan.index') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Lowongan Kerja</span>
                </a>
                @endcan

                @can('view', auth()->user()->employee ?? new App\Models\Employee)
                <a href="{{ route('karyawan.show', auth()->user()->employee) }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Profil Saya</span>
                </a>
                @endcan

                <a href="{{ route('password.change') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Ubah Kata Sandi</span>
                </a>
            </div>

            <div class="border-t border-gray-100 my-4"></div>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Versi Sistem</span>
                    <span class="text-xs font-medium text-gray-600">v2.0.0</span>
                </div>
            </div>
        </div>
    </div>

@endif

</x-layouts.app>
