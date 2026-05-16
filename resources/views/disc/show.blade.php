<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tes DiSC - {{ $submission->application->vacancy->judul_posisi }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen">

    @if ($submission->isSubmitted())
        {{-- Completed state --}}
        <div class="min-h-screen flex items-center justify-center" x-data="{ showToast: true }">
            <div
                x-show="showToast"
                x-init="setTimeout(() => showToast = false, 4000)"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-2"
                class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-green-600 text-white px-5 py-3 rounded-lg shadow-lg flex items-center gap-2"
            >
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="text-sm font-medium">Tes DiSC berhasil dikirim!</span>
            </div>

            <div class="max-w-md w-full mx-4">
                <div class="bg-white rounded-2xl border border-gray-100 p-8 text-center shadow-sm">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>

                    <h1 class="text-xl font-semibold text-gray-900 mb-2">Tes DiSC Berhasil Dikirim</h1>
                    <p class="text-sm text-gray-500 mb-6">
                        Jawaban Anda telah diterima. Tim HR akan meninjau hasil asesmen dan menghubungi Anda melalui email.
                    </p>

                    <div class="bg-gray-50 rounded-xl p-4 text-left space-y-2 mb-6">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Posisi</span>
                            <span class="font-medium text-gray-800">{{ $submission->application->vacancy->judul_posisi }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Waktu Pengiriman</span>
                            <span class="font-medium text-gray-800">{{ $submission->submitted_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400">Halaman ini dapat ditutup.</p>
                </div>
            </div>
        </div>
    @else
        {{-- Active test --}}
        <div x-data="discEngine()" x-init="init()">
            {{-- Header bar --}}
            <div class="fixed top-0 inset-x-0 z-50 bg-white border-b border-gray-200 shadow-sm">
                <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">Tes DiSC</p>
                        <p class="text-xs text-gray-500">{{ $submission->application->vacancy->judul_posisi }}</p>
                    </div>
                    <div class="text-xs text-gray-500">
                        <span x-text="answered"></span> / {{ $questions->count() }} dijawab
                    </div>
                </div>
            </div>

            <div class="pt-16 pb-16 max-w-3xl mx-auto px-4 py-8">
                <div class="mt-4 mb-6">
                    <p class="text-sm font-medium text-gray-700 mb-1">Petunjuk</p>
                    <p class="text-xs text-gray-500">
                        Untuk setiap kelompok kata di bawah ini, pilih kata yang <strong>paling mencerminkan diri Anda</strong> (Paling Mirip)
                        dan kata yang <strong>paling tidak mencerminkan diri Anda</strong> (Paling Tidak Mirip).
                        Setiap kata hanya boleh dipilih untuk satu kolom per soal.
                    </p>
                </div>

                <form id="disc-form" method="POST" action="{{ route('tes-disc.submit', $submission->token) }}">
                    @csrf

                    <div class="space-y-6">
                        @foreach ($questions as $index => $question)
                            <div class="bg-white rounded-xl border border-gray-100 p-5"
                                 x-data="discQuestion({{ $question->id }})"
                                 @answer-change="updateAnswered()">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="flex-shrink-0 w-6 h-6 bg-primary/10 text-primary text-xs font-semibold rounded-full flex items-center justify-center">
                                        {{ $index + 1 }}
                                    </span>
                                    <p class="text-xs text-gray-500">Pilih satu <span class="font-semibold text-green-600">Paling Mirip</span> dan satu <span class="font-semibold text-red-500">Paling Tidak Mirip</span></p>
                                </div>

                                <div class="grid grid-cols-1 gap-2">
                                    @foreach ($question->words as $word)
                                        <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50"
                                             :class="{
                                                'border-green-300 bg-green-50': most === {{ $word->id }},
                                                'border-red-300 bg-red-50': least === {{ $word->id }}
                                             }">
                                            <span class="flex-1 text-sm text-gray-800">{{ $word->teks }}</span>
                                            <div class="flex items-center gap-2">
                                                <label class="flex items-center gap-1 cursor-pointer">
                                                    <input type="radio"
                                                        name="most[{{ $question->id }}]"
                                                        value="{{ $word->id }}"
                                                        x-model.number="most"
                                                        @change="onMostChange({{ $word->id }})"
                                                        :disabled="least === {{ $word->id }}"
                                                        class="w-4 h-4 text-green-600 focus:ring-green-500/40">
                                                    <span class="text-xs text-green-600 font-medium">Paling Mirip</span>
                                                </label>
                                                <label class="flex items-center gap-1 cursor-pointer">
                                                    <input type="radio"
                                                        name="least[{{ $question->id }}]"
                                                        value="{{ $word->id }}"
                                                        x-model.number="least"
                                                        @change="onLeastChange({{ $word->id }})"
                                                        :disabled="most === {{ $word->id }}"
                                                        class="w-4 h-4 text-red-500 focus:ring-red-400/40">
                                                    <span class="text-xs text-red-500 font-medium">Paling Tidak Mirip</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                            @click="confirmSubmit($event)"
                            :disabled="answered < {{ $questions->count() }}"
                            :class="answered < {{ $questions->count() }}
                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-primary text-white hover:bg-primary/90'"
                            class="px-6 py-2.5 text-sm font-medium rounded-lg transition-colors">
                            Kirim Jawaban
                            <span x-show="answered < {{ $questions->count() }}" class="text-xs">
                                (<span x-text="{{ $questions->count() }} - answered"></span> soal belum dijawab)
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function discQuestion(questionId) {
                return {
                    questionId,
                    most: null,
                    least: null,

                    onMostChange(wordId) {
                        if (this.most === wordId && this.least === wordId) {
                            this.least = null;
                        }
                        this.$dispatch('answer-change');
                    },

                    onLeastChange(wordId) {
                        if (this.least === wordId && this.most === wordId) {
                            this.most = null;
                        }
                        this.$dispatch('answer-change');
                    },
                };
            }

            function discEngine() {
                return {
                    answered: 0,
                    submitted: false,

                    init() {
                        this.updateAnswered();
                    },

                    updateAnswered() {
                        // Count questions where both most and least are set
                        let count = 0;
                        document.querySelectorAll('[x-data*="discQuestion"]').forEach(el => {
                            const mostSelected = el.querySelector('input[name^="most"]:checked');
                            const leastSelected = el.querySelector('input[name^="least"]:checked');
                            if (mostSelected && leastSelected) {
                                count++;
                            }
                        });
                        this.answered = count;
                    },

                    confirmSubmit(event) {
                        if (this.submitted) {
                            event.preventDefault();
                            return;
                        }
                        if (this.answered < {{ $questions->count() }}) {
                            event.preventDefault();
                            return;
                        }
                        if (!confirm('Anda yakin ingin mengirim jawaban sekarang? Tes tidak dapat diulang.')) {
                            event.preventDefault();
                            return;
                        }
                        this.submitted = true;
                    },
                };
            }
        </script>
    @endif

</body>
</html>
