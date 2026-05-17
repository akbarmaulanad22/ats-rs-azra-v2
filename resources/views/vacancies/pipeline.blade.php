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
            @can('viewScreening', $lowongan)
                <a
                    href="{{ route('lowongan.skrining.index', $lowongan) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-primary/30 text-primary rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Skrining CV
                </a>
            @endcan
            @can('create', \App\Models\VacancyTest::class)
                <a
                    href="{{ route('lowongan.tes.show', $lowongan) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-primary/30 text-primary rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                >
                    Tes Kompetensi
                </a>
            @endcan
            @can('manageInterviewCriteria', $lowongan)
                <a
                    href="{{ route('lowongan.kriteria-wawancara.show', $lowongan) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-primary/30 text-primary rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Kriteria Wawancara
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
                            href="{{ route('lowongan.export.list', ['lowongan' => $lowongan, 'format' => 'xlsx']) }}"
                            class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-t-lg"
                        >
                            <svg class="w-3.5 h-3.5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Ekspor Excel (.xlsx)
                        </a>
                        <a
                            href="{{ route('lowongan.export.list', ['lowongan' => $lowongan, 'format' => 'csv']) }}"
                            class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 rounded-b-lg"
                        >
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
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

    @if ($lowongan->applications->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 px-6 py-12 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            <p class="text-sm text-gray-400">Belum ada kandidat yang melamar.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($applicationsByStage as $stageKey => $data)
                @if ($data['applications']->isNotEmpty())
                    <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full
                                    @if ($data['stage']->is_locked_first) bg-green-500
                                    @elseif ($data['stage']->is_locked_last) bg-primary
                                    @else bg-amber-400
                                    @endif">
                                </span>
                                <h2 class="text-sm font-semibold text-gray-800">{{ $data['stage']->nama }}</h2>
                            </div>
                            <span class="text-xs font-medium text-gray-500 bg-white border border-gray-200 px-2 py-0.5 rounded-full">
                                {{ $data['applications']->count() }} kandidat
                            </span>
                        </div>

                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Nama</th>
                                    <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Email</th>
                                    <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">No. Telepon</th>
                                    <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Skor Tes</th>
                                    <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Tanggal Melamar</th>
                                    @can('export', $lowongan)
                                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5"></th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($data['applications'] as $application)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-5 py-3 font-medium text-gray-800">
                                            {{ $application->candidate->nama_lengkap }}
                                        </td>
                                        <td class="px-5 py-3 text-gray-600">{{ $application->candidate->email }}</td>
                                        <td class="px-5 py-3 text-gray-600">{{ $application->candidate->no_telepon }}</td>
                                        <td class="px-5 py-3 text-gray-600 text-xs">
                                            @php
                                                $testSubmission = $application->testSubmission;
                                            @endphp
                                            @if ($testSubmission?->submitted_at)
                                                <span class="font-medium">{{ $testSubmission->total_skor ?? '-' }}</span>
                                                @if ($lowongan->vacancyTest)
                                                    <span class="text-gray-400">/ {{ $lowongan->vacancyTest->totalNilaiMaksimal() }}</span>
                                                @endif
                                            @else
                                                <span class="text-gray-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-gray-400 text-xs">
                                            {{ $application->created_at->format('d M Y') }}
                                        </td>
                                        @can('export', $lowongan)
                                            <td class="px-5 py-3">
                                                <a
                                                    href="{{ route('lowongan.kandidat.pdf', ['lowongan' => $lowongan, 'application' => $application]) }}"
                                                    class="inline-flex items-center gap-1 text-xs text-red-500 hover:text-red-700 transition-colors"
                                                    title="Unduh PDF Profil"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                    </svg>
                                                    PDF
                                                </a>
                                            </td>
                                        @endcan
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

</x-layouts.app>
