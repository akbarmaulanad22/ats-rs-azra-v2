<x-layouts.app title="Wawancara - {{ $lowongan->judul_posisi }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Lowongan Kerja
        </a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Wawancara</h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}
                    &mdash;
                    <span class="font-medium">
                        @php
                            $stageLabels = [
                                'wawancara_kepala_unit' => 'Wawancara Kepala Unit',
                                'wawancara_manajer_hr' => 'Wawancara Manajer HR',
                                'wawancara_direktur' => 'Wawancara Direktur',
                            ];
                        @endphp
                        {{ $stageLabels[$stageKey] ?? $stageKey }}
                    </span>
                </p>
            </div>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full
                {{ $lowongan->status->value === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $lowongan->status->label() }}
            </span>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        @php
            $filterOptions = [
                '' => 'Semua',
                'aktif' => 'Menunggu',
                'reserved' => 'Ditangguhkan',
                'selesai' => 'Diloloskan',
                'gagal' => 'Ditolak',
            ];
        @endphp
        @foreach ($filterOptions as $value => $label)
            <a
                href="{{ route('lowongan.wawancara.index', array_merge(['lowongan' => $lowongan->id], $value !== '' ? ['status' => $value] : [])) }}"
                class="px-3 py-1 text-xs font-medium rounded-full transition-colors ease-out duration-150 border
                    {{ $statusFilter === ($value ?: null) || ($value === '' && ! $statusFilter)
                        ? 'bg-primary text-white border-primary'
                        : 'bg-white text-gray-500 border-gray-200 hover:border-primary/40 hover:text-primary' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($applications->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 px-6 py-12 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm text-gray-400">Tidak ada kandidat di tahap ini.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-primary border-b border-primary/10 text-white">
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-8">No.</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Nama Kandidat</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider">Email</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-32">Tanggal Melamar</th>
                            <th class="text-left px-3 py-2.5 text-[10px] font-semibold uppercase tracking-wider w-28">Status</th>
                            <th class="w-20 px-3 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($applications as $index => $application)
                            @php
                                $stage = $application->interview_stage;
                                $statusBadge = match ($stage?->status->value ?? '') {
                                    'aktif' => ['bg-blue-100 text-blue-700', 'Menunggu'],
                                    'reserved' => ['bg-amber-100 text-amber-700', 'Ditangguhkan'],
                                    'selesai' => ['bg-green-100 text-green-700', 'Diloloskan'],
                                    'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                                    default => ['bg-gray-100 text-gray-500', '-'],
                                };
                            @endphp
                            <tr class="odd:bg-white even:bg-primary/5 hover:bg-primary/10 transition-colors ease-out duration-100">
                                <td class="px-3 py-2 text-xs text-gray-400 tabular-nums">{{ $index + 1 }}</td>
                                <td class="px-3 py-2">
                                    <span class="text-xs font-semibold text-gray-900">{{ $application->candidate->nama_lengkap }}</span>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-600">{{ $application->candidate->email }}</td>
                                <td class="px-3 py-2 text-xs text-gray-400 tabular-nums">{{ $application->created_at->format('d M Y') }}</td>
                                <td class="px-3 py-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $statusBadge[0] }}">
                                        {{ $statusBadge[1] }}
                                    </span>
                                </td>
                                <td class="px-3 py-2">
                                    <a
                                        href="{{ route('lowongan.wawancara.show', [$lowongan, $application]) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-medium rounded text-primary border border-primary/30 hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                                    >
                                        Tinjau
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</x-layouts.app>
