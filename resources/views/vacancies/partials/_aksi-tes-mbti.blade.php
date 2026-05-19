{{-- Aksi Tahap: Tes MBTI --}}
{{-- Variables: $application, $lowongan, $currentStage --}}

@php $mbtiSubmission = $application->mbtiSubmission; @endphp

@if (!$mbtiSubmission || !$mbtiSubmission->submitted_at)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-2">Tes MBTI</h2>
        <p class="text-xs text-gray-500 mb-3">Kandidat belum menyelesaikan tes MBTI.</p>
        @if ($mbtiSubmission)
            <p class="text-[10px] text-gray-400 mb-1">Link tes untuk kandidat:</p>
            <input
                type="text"
                readonly
                value="{{ route('tes-mbti.show', $mbtiSubmission->token) }}"
                class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50 text-gray-600"
            >
        @endif

        @if ($currentStage?->status->isAdvanceable())
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-amber-600">Kandidat harus menyelesaikan tes sebelum melanjutkan.</p>
            </div>
        @endif
    </div>
@else
    @php $mbtiResult = $mbtiSubmission->result; @endphp

    @if ($mbtiResult)
        <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes MBTI</h2>
            <div class="flex items-center gap-3 mb-4">
                <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-bold rounded-full">
                    {{ $mbtiResult->tipe }}
                </span>
                <div class="text-xs text-gray-700">
                    <span class="font-medium">Tipe Kepribadian:</span> {{ $mbtiResult->tipe }}
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-3">
                @foreach ([
                    ['EI', $mbtiResult->skor_e, $mbtiResult->skor_i, 'E', 'I', 'Ekstrovert', 'Introvert', $mbtiResult->kekuatan_ei],
                    ['SN', $mbtiResult->skor_s, $mbtiResult->skor_n, 'S', 'N', 'Penginderaan', 'Intuisi', $mbtiResult->kekuatan_sn],
                    ['TF', $mbtiResult->skor_t, $mbtiResult->skor_f, 'T', 'F', 'Pemikiran', 'Perasaan', $mbtiResult->kekuatan_tf],
                    ['JP', $mbtiResult->skor_j, $mbtiResult->skor_p, 'J', 'P', 'Terstruktur', 'Fleksibel', $mbtiResult->kekuatan_jp],
                ] as [$dim, $scoreA, $scoreB, $poleA, $poleB, $labelA, $labelB, $strength])
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-semibold text-gray-600">{{ $dim }}</span>
                            <span class="text-xs text-gray-400">Kekuatan: {{ $strength }}%</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-700">
                            <span class="font-semibold">{{ $poleA }}: {{ $scoreA }}</span>
                            <span>{{ $poleB }}: {{ $scoreB }}</span>
                        </div>
                        <div class="text-[10px] text-gray-400 mt-0.5">{{ $scoreA >= $scoreB ? $labelA : $labelB }}</div>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400">Diselesaikan: {{ $mbtiSubmission->submitted_at->translatedFormat('d M Y, H:i') }}</p>
        </div>
    @endif

    @if ($currentStage?->status->isAdvanceable())
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Keputusan</h3>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('lowongan.lamaran.lanjut', [$lowongan, $application]) }}"
                      onsubmit="return confirm('Lanjutkan kandidat ke tahap berikutnya?')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors cursor-pointer">
                        Lanjutkan
                    </button>
                </form>
                <form method="POST" action="{{ route('lowongan.lamaran.gagal', [$lowongan, $application]) }}"
                      onsubmit="return confirm('Tolak kandidat ini? Tindakan ini tidak dapat dibatalkan.')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors cursor-pointer">
                        Tolak
                    </button>
                </form>
            </div>
        </div>
    @elseif ($currentStage?->status === \App\Enums\ApplicationStageStatus::Selesai)
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-700">Diloloskan</span>
        </div>
    @elseif ($currentStage?->status === \App\Enums\ApplicationStageStatus::Gagal)
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-600">Ditolak</span>
        </div>
    @endif
@endif
