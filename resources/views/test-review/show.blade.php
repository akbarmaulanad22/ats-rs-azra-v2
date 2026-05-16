<x-layouts.app title="Detail Ulasan - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.tes.ulasan.index', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Ulasan Esai
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Detail Jawaban Tes</h1>
        <p class="text-xs text-gray-500 mt-0.5">
            {{ $submission->application->candidate->nama_lengkap }}
            &mdash; {{ $lowongan->judul_posisi }}
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @foreach ($submission->answers as $index => $answer)
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div class="flex items-start gap-3 flex-1">
                        <span class="flex-shrink-0 w-6 h-6 bg-primary/10 text-primary text-xs font-semibold rounded-full flex items-center justify-center">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="text-sm text-gray-800">{{ $answer->question->pertanyaan }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs font-medium px-1.5 py-0.5 rounded-full
                                    {{ $answer->question->tipe->value === 'mc' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $answer->question->tipe->label() }}
                                </span>
                                <span class="text-xs text-gray-400">Maks. {{ $answer->question->nilai_poin }} poin</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right flex-shrink-0">
                        @if ($answer->skor !== null)
                            <span class="text-sm font-semibold text-gray-800">{{ $answer->skor }} / {{ $answer->question->nilai_poin }}</span>
                        @else
                            <span class="text-xs text-gray-400">Belum dinilai</span>
                        @endif
                    </div>
                </div>

                @if ($answer->question->tipe->value === 'mc')
                    {{-- MC: show selected option --}}
                    <div class="pl-9 space-y-1">
                        @foreach ($answer->question->options as $option)
                            <div class="flex items-center gap-2 text-sm
                                {{ $option->id === $answer->question_option_id ? 'font-medium' : 'text-gray-500' }}">
                                <span class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0
                                    {{ $option->id === $answer->question_option_id ? 'border-primary' : 'border-gray-200' }}">
                                    @if ($option->id === $answer->question_option_id)
                                        <span class="w-2 h-2 rounded-full bg-primary"></span>
                                    @endif
                                </span>
                                <span class="{{ $option->is_correct ? 'text-green-700 font-medium' : '' }}">
                                    {{ $option->teks_opsi }}
                                    @if ($option->is_correct) <span class="text-xs">(jawaban benar)</span> @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Essay: show text answer + scoring form --}}
                    <div class="pl-9">
                        <div class="bg-gray-50 rounded-lg p-3 text-sm text-gray-700 mb-3 whitespace-pre-wrap">
                            {{ $answer->jawaban_teks ?: '(tidak ada jawaban)' }}
                        </div>

                        @if (!$answer->is_reviewed)
                            <form method="POST" action="{{ route('lowongan.tes.ulasan.skor', [$lowongan, $answer]) }}"
                                  class="flex items-center gap-2">
                                @csrf
                                <label class="text-xs text-gray-600">Nilai (0–{{ $answer->question->nilai_poin }}):</label>
                                <input type="number" name="skor" min="0" max="{{ $answer->question->nilai_poin }}"
                                    class="w-20 text-sm border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary/40"
                                    required>
                                <button type="submit"
                                    class="px-3 py-1 text-xs font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                    Simpan Nilai
                                </button>
                            </form>
                        @else
                            <p class="text-xs text-green-600 font-medium">Sudah dinilai: {{ $answer->skor }} poin</p>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-4 bg-white rounded-xl border border-gray-100 p-4 flex items-center justify-between">
        <span class="text-sm text-gray-600">Total Skor</span>
        <span class="text-lg font-semibold text-gray-900">
            {{ $submission->total_skor ?? '-' }} / {{ $vacancyTest->totalNilaiMaksimal() }}
        </span>
    </div>

</x-layouts.app>
