<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tes Kompetensi - {{ $submission->application->vacancy->judul_posisi }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen" x-data="testEngine({{ $submission->remainingSeconds() }})">

    {{-- Timer bar --}}
    <div class="fixed top-0 inset-x-0 z-50 bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-gray-800">Tes Kompetensi</p>
                <p class="text-xs text-gray-500">{{ $submission->application->vacancy->judul_posisi }}</p>
            </div>
            <div class="flex items-center gap-2"
                 :class="timeLeft <= 60 ? 'text-red-600' : (timeLeft <= 300 ? 'text-amber-600' : 'text-gray-700')">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm font-mono font-semibold" x-text="formatTime(timeLeft)"></span>
            </div>
        </div>
    </div>

    <div class="pt-16 pb-16 max-w-3xl mx-auto px-4 py-8">
        <div class="mt-4 mb-6">
            <p class="text-xs text-gray-500">Jawab semua pertanyaan di bawah ini. Tes akan otomatis terkirim saat waktu habis.</p>
        </div>

        <form id="test-form" method="POST" action="{{ route('tes.submit', $submission->token) }}">
            @csrf

            <div class="space-y-6">
                @foreach ($questions as $index => $question)
                    <div class="bg-white rounded-xl border border-gray-100 p-5">
                        <div class="flex items-start gap-3 mb-4">
                            <span class="flex-shrink-0 w-6 h-6 bg-primary/10 text-primary text-xs font-semibold rounded-full flex items-center justify-center">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1">
                                <p class="text-sm text-gray-800">{{ $question->pertanyaan }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full
                                        {{ $question->tipe->value === 'mc' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                        {{ $question->tipe->label() }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $question->nilai_poin }} poin</span>
                                </div>
                            </div>
                        </div>

                        @if ($question->tipe->value === 'mc')
                            <div class="space-y-2 pl-9">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-2.5 cursor-pointer group">
                                        <input type="radio"
                                            name="answers[{{ $question->id }}]"
                                            value="{{ $option->id }}"
                                            class="w-4 h-4 text-primary focus:ring-primary/40">
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $option->teks_opsi }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <div class="pl-9">
                                <textarea
                                    name="answers[{{ $question->id }}]"
                                    rows="4"
                                    placeholder="Tulis jawaban Anda di sini..."
                                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40 resize-y"
                                ></textarea>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    @click="confirmSubmit($event)"
                    class="px-6 py-2.5 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Kirim Jawaban
                </button>
            </div>
        </form>
    </div>

    <script>
        function testEngine(initialSeconds) {
            return {
                timeLeft: initialSeconds,
                timer: null,
                submitted: false,

                init() {
                    this.timer = setInterval(() => {
                        if (this.timeLeft <= 0) {
                            this.autoSubmit();
                            return;
                        }
                        this.timeLeft--;
                    }, 1000);
                },

                formatTime(seconds) {
                    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
                    const s = (seconds % 60).toString().padStart(2, '0');
                    return `${m}:${s}`;
                },

                autoSubmit() {
                    if (this.submitted) return;
                    this.submitted = true;
                    clearInterval(this.timer);
                    document.getElementById('test-form').submit();
                },

                confirmSubmit(event) {
                    if (this.submitted) {
                        event.preventDefault();
                        return;
                    }
                    if (!confirm('Anda yakin ingin mengirim jawaban sekarang?')) {
                        event.preventDefault();
                        return;
                    }
                    this.submitted = true;
                    clearInterval(this.timer);
                },
            };
        }
    </script>

</body>
</html>
