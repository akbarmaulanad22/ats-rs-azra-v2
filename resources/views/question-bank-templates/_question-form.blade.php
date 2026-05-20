<div class="bg-white/80 border border-gray-200 rounded-md overflow-hidden mb-4">
    <div class="px-4 py-3 bg-gray-200/90 border-b border-gray-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Daftar Soal</p>
            <p class="text-[10px] text-gray-400 mt-0.5"><span x-text="questions.length"></span> soal &mdash; <span x-text="totalPoin"></span> poin total</p>
        </div>
        <button type="button" @click="addQuestion()"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-600 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Soal
        </button>
    </div>

    <div class="divide-y divide-gray-100 px-4">
        <template x-for="(question, qIndex) in questions" :key="qIndex">
            <div class="py-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary/10 text-primary text-[10px] font-bold shrink-0" x-text="qIndex + 1"></span>
                    <div class="flex items-center gap-1">
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
                            class="w-full text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">
                            <option value="mc">Pilihan Ganda</option>
                            <option value="essay">Esai</option>
                        </select>
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Poin</label>
                        <input type="number" x-model.number="question.nilai_poin" min="1" max="100"
                            class="w-full text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-[10px] font-medium text-gray-500 uppercase tracking-wide mb-1">Pertanyaan</label>
                    <textarea x-model="question.pertanyaan" rows="2"
                        class="w-full text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring"
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
                                    class="flex-1 text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">
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

<script>
    function templateForm(initialQuestions) {
        return {
            questions: initialQuestions && initialQuestions.length ? initialQuestions : [makeQuestion()],
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
                    if (q.id) {
                        addHidden(container, `questions[${qi}][id]`, q.id);
                    }
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
            id: null,
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
