{{-- Aksi Tahap: Tes Kompetensi --}}
{{-- Variables: $application, $lowongan, $currentStage, $testAllReviewed --}}

@php $submission = $application->testSubmission; @endphp

@if ($errors->any())
    <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
        {{ $errors->first() }}
    </div>
@endif

@if (!$submission || !$submission->submitted_at)
    <div class="bg-white rounded-xl border border-gray-100 p-5">
        <h2 class="text-sm font-semibold text-gray-800 mb-2">Tes Kompetensi</h2>
        <p class="text-xs text-gray-500">Kandidat belum menyelesaikan tes kompetensi.</p>
        @if ($submission)
            <div class="mt-3">
                <p class="text-[10px] text-gray-400 mb-1">Link tes untuk kandidat:</p>
                <input
                    type="text"
                    readonly
                    value="{{ route('tes.show', $submission->token) }}"
                    class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded-lg bg-gray-50 text-gray-600"
                >
            </div>
        @endif
    </div>
@else
    {{-- Score summary --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 mb-4">
        <h2 class="text-sm font-semibold text-gray-800 mb-3">Hasil Tes Kompetensi</h2>
        <div class="flex items-center gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-primary">{{ $submission->total_skor ?? '-' }}</p>
                <p class="text-[10px] text-gray-400 uppercase tracking-wide mt-0.5">Total Skor</p>
            </div>
            @if ($submission->snapshot)
                <div class="text-xs text-gray-500">
                    <p>Maksimal: {{ $submission->snapshot->totalNilaiMaksimal() }}</p>
                    <p>Diselesaikan: {{ $submission->submitted_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Answers --}}
    <div class="space-y-3 mb-4">
        @foreach ($submission->answers as $index => $answer)
            <div class="bg-white rounded-xl border border-gray-100 p-4">
                <div class="flex items-start justify-between gap-4 mb-2">
                    <div class="flex items-start gap-2 flex-1">
                        <span class="flex-shrink-0 w-5 h-5 bg-primary/10 text-primary text-xs font-semibold rounded-full flex items-center justify-center">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="text-xs text-gray-800">{{ $answer->question->pertanyaan }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full
                                    {{ $answer->question->tipe->value === 'mc' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $answer->question->tipe->label() }}
                                </span>
                                <span class="text-[10px] text-gray-400">Maks. {{ $answer->question->nilai_poin }} poin</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        @if ($answer->skor !== null)
                            <span class="text-xs font-semibold text-gray-800">{{ $answer->skor }} / {{ $answer->question->nilai_poin }}</span>
                        @else
                            <span class="text-xs text-gray-400">Belum dinilai</span>
                        @endif
                    </div>
                </div>

                @if ($answer->question->tipe->value === 'mc')
                    <div class="pl-7 space-y-1">
                        @foreach ($answer->question->options as $option)
                            <div class="flex items-center gap-2 text-xs
                                {{ $option->id === $answer->vacancy_test_snapshot_option_id ? 'font-medium' : 'text-gray-500' }}">
                                <span class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                    {{ $option->id === $answer->vacancy_test_snapshot_option_id ? 'border-primary' : 'border-gray-200' }}">
                                    @if ($option->id === $answer->vacancy_test_snapshot_option_id)
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                                    @endif
                                </span>
                                <span class="{{ $option->is_correct ? 'text-green-700 font-medium' : '' }}">
                                    {{ $option->teks_opsi }}
                                    @if ($option->is_correct) <span class="text-[10px]">(benar)</span> @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="pl-7">
                        <div class="bg-gray-50 rounded-lg p-2.5 text-xs text-gray-700 mb-2 whitespace-pre-wrap">
                            {{ $answer->jawaban_teks ?: '(tidak ada jawaban)' }}
                        </div>

                        @if ($currentStage?->status === \App\Enums\ApplicationStageStatus::Aktif && !$answer->is_reviewed)
                            <form method="POST" action="{{ route('lowongan.tes.ulasan.skor', [$lowongan, $answer]) }}"
                                  class="flex items-center gap-2">
                                @csrf
                                <label class="text-xs text-gray-600">Nilai (0&ndash;{{ $answer->question->nilai_poin }}):</label>
                                <input type="number" name="skor" min="0" max="{{ $answer->question->nilai_poin }}"
                                    class="w-20 text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary/40"
                                    required>
                                <button type="submit"
                                    class="px-2.5 py-1 text-xs font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    Simpan Nilai
                                </button>
                            </form>
                        @elseif ($answer->is_reviewed)
                            <p class="text-xs text-green-600 font-medium">Sudah dinilai: {{ $answer->skor }} poin</p>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Decision --}}
    @if ($currentStage?->status->isAdvanceable())
        <div class="bg-white rounded-xl border border-gray-100 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Keputusan Tes Kompetensi</h3>

            @if (!$testAllReviewed)
                <p class="text-xs text-amber-600">Semua jawaban harus dinilai sebelum mengambil keputusan.</p>
            @else
                <form
                    method="POST"
                    action="{{ route('lowongan.tes.ulasan.keputusan', [$lowongan, $submission]) }}"
                    x-data="{ keputusan: '{{ old('keputusan') }}' }"
                >
                    @csrf

                    <div class="space-y-2 mb-4">
                        @foreach (['lulus' => ['Loloskan', 'bg-green-50 border-green-300 text-green-700'], 'reserved' => ['Tangguhkan', 'bg-amber-50 border-amber-300 text-amber-700'], 'gagal' => ['Tolak', 'bg-red-50 border-red-300 text-red-700']] as $value => $config)
                            <label
                                class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer transition-colors ease-out duration-150"
                                :class="keputusan === '{{ $value }}' ? '{{ $config[1] }}' : 'border-gray-200 hover:border-gray-300'"
                            >
                                <input
                                    type="radio"
                                    name="keputusan"
                                    value="{{ $value }}"
                                    x-model="keputusan"
                                    class="w-4 h-4 accent-current"
                                    @if (old('keputusan') === $value) checked @endif
                                >
                                <span class="text-sm font-medium" :class="keputusan === '{{ $value }}' ? '' : 'text-gray-700'">{{ $config[0] }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error('keputusan')
                        <p class="text-xs text-red-600 mb-3">{{ $message }}</p>
                    @enderror

                    <div class="mb-4">
                        <label class="block text-[10px] font-medium text-gray-700 uppercase tracking-wide mb-1">
                            Catatan <span class="text-gray-400 normal-case font-normal">(opsional)</span>
                        </label>
                        <textarea
                            name="catatan"
                            rows="4"
                            placeholder="Alasan keputusan, kekuatan atau kelemahan kandidat..."
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 resize-none placeholder:text-gray-400"
                        >{{ old('catatan') }}</textarea>
                        @error('catatan')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 disabled:opacity-50 cursor-pointer"
                        x-bind:disabled="!keputusan"
                    >
                        Simpan Keputusan
                    </button>
                </form>
            @endif
        </div>
    @else
        @php
            $statusBadge = match ($currentStage?->status?->value) {
                'selesai' => ['bg-green-100 text-green-700', 'Diloloskan'],
                'gagal' => ['bg-red-100 text-red-600', 'Ditolak'],
                'reserved' => ['bg-amber-100 text-amber-700', 'Ditangguhkan'],
                default => ['bg-gray-100 text-gray-500', $currentStage?->status?->label() ?? '—'],
            };
        @endphp
        <div class="bg-white rounded-xl border border-gray-100 p-5 text-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusBadge[0] }}">
                {{ $statusBadge[1] }}
            </span>
            @if ($currentStage?->catatan)
                <p class="mt-3 text-xs text-gray-600 text-left bg-gray-50 rounded-lg px-3 py-2">
                    {{ $currentStage->catatan }}
                </p>
            @endif
        </div>
    @endif
@endif
