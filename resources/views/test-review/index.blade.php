<x-layouts.app title="Ulasan Esai - {{ $lowongan->judul_posisi }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.tes.show', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Konfigurasi Tes
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Ulasan Jawaban Esai</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($submissions->isEmpty())
        <div class="bg-white rounded-xl border border-gray-100 px-6 py-12 text-center">
            <p class="text-sm text-gray-400">Belum ada kandidat yang menyelesaikan tes.</p>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Kandidat</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Waktu Submit</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Skor MC</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Status Esai</th>
                        <th class="text-left text-xs font-medium text-gray-400 px-5 py-2.5">Total Skor</th>
                        <th class="text-right text-xs font-medium text-gray-400 px-5 py-2.5">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($submissions as $submission)
                        @php
                            $essayAnswers = $submission->answers->filter(fn($a) => $a->question?->tipe?->value === 'essay');
                            $essayReviewed = $essayAnswers->every(fn($a) => $a->is_reviewed);
                            $mcSkor = $submission->answers->where('is_reviewed', true)->where('skor', '!=', null)->sum('skor');
                            $mcSkor -= $essayAnswers->where('is_reviewed', true)->sum('skor');
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-3 font-medium text-gray-800">
                                {{ $submission->application->candidate->nama_lengkap }}
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">
                                {{ $submission->submitted_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-5 py-3 text-gray-600">
                                {{ $submission->answers->where('is_reviewed', true)->whereNull('question_id', false)->sum('skor') }}
                            </td>
                            <td class="px-5 py-3">
                                @if ($essayAnswers->isEmpty())
                                    <span class="text-xs text-gray-400">Tidak ada esai</span>
                                @elseif ($essayReviewed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">Sudah diulas</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700">Perlu diulas</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 font-medium text-gray-800">
                                {{ $submission->total_skor ?? '-' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('lowongan.tes.ulasan.show', [$lowongan, $submission]) }}"
                                    class="text-xs text-primary hover:underline">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</x-layouts.app>
