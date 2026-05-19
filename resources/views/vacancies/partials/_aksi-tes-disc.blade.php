{{-- Aksi Tahap: Tes DISC --}}
{{-- Variables: $application, $lowongan, $currentStage --}}

@php $discSubmission = $application->discSubmission; @endphp

@if (!$discSubmission || !$discSubmission->submitted_at)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-2">Tes DISC</h2>
        <p class="text-xs text-gray-500 mb-3">Kandidat belum menyelesaikan tes DISC.</p>
        @if ($discSubmission)
            <p class="text-[10px] text-gray-400 mb-1">Link tes untuk kandidat:</p>
            <input
                type="text"
                readonly
                value="{{ route('tes-disc.show', $discSubmission->token) }}"
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
    @php $discResult = $discSubmission->result; @endphp

    @if ($discResult)
        <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes DiSC</h2>
            <div class="flex items-center gap-3 mb-4">
                <span class="px-3 py-1 bg-primary/10 text-primary text-sm font-bold rounded-full">
                    {{ $discResult->tipe_primer->value }}
                </span>
                <div class="text-xs text-gray-700">
                    <span class="font-medium">Tipe Primer:</span> {{ $discResult->tipe_primer->shortLabel() }}
                </div>
                <div class="text-xs text-gray-500">
                    <span class="font-medium">Sekunder:</span> {{ $discResult->tipe_sekunder->shortLabel() }}
                </div>
            </div>
            <div class="grid grid-cols-4 gap-2 mb-3">
                @foreach ([['D', $discResult->skor_d, 'bg-red-50 text-red-600'], ['I', $discResult->skor_i, 'bg-yellow-50 text-yellow-600'], ['S', $discResult->skor_s, 'bg-green-50 text-green-600'], ['C', $discResult->skor_c, 'bg-blue-50 text-blue-600']] as [$dim, $score, $color])
                    <div class="text-center p-2 rounded-lg {{ $color }}">
                        <p class="text-lg font-bold">{{ $score }}</p>
                        <p class="text-[10px] font-semibold uppercase tracking-wide">{{ $dim }}</p>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-400">Diselesaikan: {{ $discSubmission->submitted_at->translatedFormat('d M Y, H:i') }}</p>
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
