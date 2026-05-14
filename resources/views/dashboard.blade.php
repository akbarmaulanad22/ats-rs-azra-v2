<x-layouts.app title="Dashboard - ATS RS Azra">

    {{-- Welcome header --}}
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

    {{-- Stats band: single card, 4 sections with dividers — avoids isolated metric-card cliché --}}
    <div class="bg-white rounded-xl px-6 py-5 mb-5">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-y-5 lg:gap-y-0 lg:divide-x lg:divide-gray-100">
            <div class="lg:pr-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Total Lamaran</p>
                <p class="text-3xl font-bold text-gray-900 leading-none">124</p>
                <p class="text-xs text-gray-400 mt-1">+12 bulan ini</p>
            </div>
            <div class="lg:px-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Dalam Proses</p>
                <p class="text-3xl font-bold text-primary leading-none">38</p>
                <p class="text-xs text-gray-400 mt-1">perlu tindakan</p>
            </div>
            <div class="lg:px-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Diterima</p>
                <p class="text-3xl font-bold text-secondary-dark leading-none">56</p>
                <p class="text-xs text-gray-400 mt-1">tahun ini</p>
            </div>
            <div class="lg:pl-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1.5">Karyawan Aktif</p>
                <p class="text-3xl font-bold text-gray-900 leading-none">214</p>
                <p class="text-xs text-gray-400 mt-1">terdaftar</p>
            </div>
        </div>
    </div>

    {{-- Main grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Quick actions — 1/3 width, LEFT --}}
        <div class="bg-white rounded-xl p-6">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Aksi Cepat</h2>

            <div class="space-y-1">
                <a href="#" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Tambah Lamaran</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Data Karyawan</span>
                </a>

                <a href="#" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Laporan</span>
                </a>

                <a href="{{ route('password.change') }}" class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 group-hover:text-gray-900 transition-colors">Ubah Kata Sandi</span>
                </a>
            </div>

            {{-- Separator --}}
            <div class="border-t border-gray-100 my-4"></div>

            {{-- System info --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Versi Sistem</span>
                    <span class="text-xs font-medium text-gray-600">v2.0.0</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Terakhir Masuk</span>
                    <span class="text-xs font-medium text-gray-600">Baru saja</span>
                </div>
            </div>
        </div>

        {{-- Recent applications — 2/3 width, RIGHT --}}
        <div class="lg:col-span-2 bg-white rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-gray-800">Lamaran Terbaru</h2>
                <span class="text-xs text-gray-400">5 terbaru</span>
            </div>

            <div class="space-y-1">
                @foreach ([
                    ['name' => 'Budi Santoso',   'pos' => 'Perawat IGD',         'status' => 'Baru',      'badge' => 'bg-gray-100 text-gray-500'],
                    ['name' => 'Sari Dewi',      'pos' => 'Staf Administrasi',   'status' => 'Direview',  'badge' => 'bg-amber-50 text-amber-600'],
                    ['name' => 'Ahmad Fauzi',    'pos' => 'Dokter Umum',         'status' => 'Wawancara', 'badge' => 'bg-blue-50 text-blue-600'],
                    ['name' => 'Rina Susanti',   'pos' => 'Ahli Gizi',           'status' => 'Diterima',  'badge' => 'bg-secondary/15 text-secondary-dark'],
                    ['name' => 'Doni Prasetyo',  'pos' => 'Teknisi Laboratorium','status' => 'Ditolak',   'badge' => 'bg-red-50 text-red-500'],
                ] as $item)
                <div class="flex items-center justify-between px-3 py-3 rounded-lg hover:bg-gray-50 transition-colors group">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-semibold text-gray-500 shrink-0">
                            {{ mb_substr($item['name'], 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item['name'] }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $item['pos'] }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full shrink-0 ml-3 {{ $item['badge'] }}">
                        {{ $item['status'] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</x-layouts.app>
