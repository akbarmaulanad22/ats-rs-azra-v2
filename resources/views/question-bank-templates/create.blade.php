<x-layouts.app title="Buat Template Bank Soal - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('template-bank-soal.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Bank Soal
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Buat Template Bank Soal</h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            <p class="font-medium mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="templateForm()" class="space-y-4 max-w-4xl">
        <form method="POST" action="{{ route('template-bank-soal.store') }}" @submit="prepareSubmit($event)">
            @csrf

            <div class="bg-white rounded-xl border border-gray-100 p-6 mb-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Template <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" required
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40"
                        placeholder="Contoh: Tes Kompetensi Perawat">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 p-6 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-800">Daftar Soal</h2>
                        <p class="text-xs text-gray-400 mt-0.5"><span x-text="questions.length"></span> soal &mdash; <span x-text="totalPoin"></span> poin total</p>
                    </div>
                    <button type="button" @click="addQuestion()"
                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Soal
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(question, qIndex) in questions" :key="qIndex">
                        <div class="border border-gray-100 rounded-lg p-4 bg-gray-50/50">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold shrink-0" x-text="qIndex + 1"></span>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="moveUp(qIndex)" x-show="qIndex > 0"
                                        class="p-1 text-gray-400 hover:text-primary rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" @click="moveDown(qIndex)" x-show="qIndex < questions.length - 1"
                                        class="p-1 text-gray-400 hover:text-primary rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <button type="button" @click="removeQuestion(qIndex)" x-show="questions.length > 1"
                                        class="p-1 text-red-400 hover:text-red-600 rounded transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Tipe</label>
                                    <select x-model="question.tipe"
                                        class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary/40 bg-white">
                                        <option value="mc">Pilihan Ganda</option>
                                        <option value="essay">Esai</option>
                                    </select>
                                </div>
                                <div class="md:col-span-1">
                                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Poin</label>
                                    <input type="number" x-model.number="question.nilai_poin" min="1" max="100"
                                        class="w-full text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary/40">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Pertanyaan</label>
                                <textarea x-model="question.pertanyaan" rows="2"
                                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40"
                                    placeholder="Tulis pertanyaan..."></textarea>
                            </div>

                            <div x-show="question.tipe === 'mc'" x-cloak>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-[10px] font-medium text-gray-500 uppercase tracking-wide">Opsi Jawaban</label>
                                    <button type="button" @click="addOption(qIndex)"
                                        class="text-[10px] text-primary hover:underline">+ Tambah Opsi</button>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="(option, oIndex) in question.options" :key="oIndex">
                                        <div class="flex items-center gap-2">
                                            <input type="radio" :name="'q_correct_' + qIndex" :value="oIndex"
                                                x-model.number="question.correct_option"
                                                class="w-3.5 h-3.5 text-primary focus:ring-primary/40">
                                            <input type="text" x-model="option.teks_opsi" placeholder="Teks opsi..."
                                                class="flex-1 text-xs border border-gray-200 rounded-lg px-2.5 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary/40">
                                            <button type="button" @click="removeOption(qIndex, oIndex)"
                                                x-show="question.options.length > 2"
                                                class="text-[10px] text-red-400 hover:text-red-600">Hapus</button>
                                        </div>
                                    </template>
                                </div>
                                <p class="mt-1 text-[10px] text-gray-400">Pilih radio untuk menandai jawaban benar.</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div id="hidden-fields"></div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Simpan Template
                </button>
                <a href="{{ route('template-bank-soal.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        function templateForm() {
            return {
                questions: [makeQuestion()],
                get totalPoin() {
                    return this.questions.reduce((sum, q) => sum + (parseInt(q.nilai_poin) || 0), 0);
                },
                addQuestion() {
                    this.questions.push(makeQuestion());
                },
                removeQuestion(index) {
                    if (this.questions.length <= 1) return;
                    this.questions.splice(index, 1);
                },
                moveUp(index) {
                    if (index <= 0) return;
                    [this.questions[index - 1], this.questions[index]] = [this.questions[index], this.questions[index - 1]];
                },
                moveDown(index) {
                    if (index >= this.questions.length - 1) return;
                    [this.questions[index], this.questions[index + 1]] = [this.questions[index + 1], this.questions[index]];
                },
                addOption(qIndex) {
                    this.questions[qIndex].options.push({ teks_opsi: '' });
                },
                removeOption(qIndex, oIndex) {
                    if (this.questions[qIndex].options.length <= 2) return;
                    this.questions[qIndex].options.splice(oIndex, 1);
                    if (this.questions[qIndex].correct_option >= this.questions[qIndex].options.length) {
                        this.questions[qIndex].correct_option = 0;
                    }
                },
                prepareSubmit(event) {
                    const container = document.getElementById('hidden-fields');
                    container.innerHTML = '';
                    this.questions.forEach((q, qi) => {
                        addHidden(container, `questions[${qi}][tipe]`, q.tipe);
                        addHidden(container, `questions[${qi}][pertanyaan]`, q.pertanyaan);
                        addHidden(container, `questions[${qi}][nilai_poin]`, q.nilai_poin);
                        if (q.tipe === 'mc') {
                            addHidden(container, `questions[${qi}][correct_option]`, q.correct_option);
                            q.options.forEach((o, oi) => {
                                addHidden(container, `questions[${qi}][options][${oi}][teks_opsi]`, o.teks_opsi);
                            });
                        }
                    });
                },
            };
        }

        function makeQuestion() {
            return {
                tipe: 'mc',
                pertanyaan: '',
                nilai_poin: 1,
                correct_option: 0,
                options: [{ teks_opsi: '' }, { teks_opsi: '' }, { teks_opsi: '' }, { teks_opsi: '' }],
            };
        }

        function addHidden(container, name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            container.appendChild(input);
        }
    </script>

</x-layouts.app>
