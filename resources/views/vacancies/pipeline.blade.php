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
            <span class="text-xs font-medium px-2.5 py-1 rounded-full
                {{ $lowongan->status->value === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                {{ $lowongan->status->label() }}
            </span>
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
                                    <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Tanggal Melamar</th>
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
                                        <td class="px-5 py-3 text-gray-400 text-xs">
                                            {{ $application->created_at->format('d M Y') }}
                                        </td>
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
