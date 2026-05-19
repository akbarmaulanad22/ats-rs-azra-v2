<x-layouts.app title="Konfigurasi Tes - {{ $lowongan->judul_posisi }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Pipeline
        </a>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Konfigurasi Tes Kompetensi</h1>
                <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
            </div>
            @if ($vacancyTest)
                <a
                    href="{{ route('lowongan.pipeline', $lowongan) }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-primary/30 text-primary rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150"
                >
                    Ulasan Esai
                </a>
            @endif
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4"
         x-data="testConfig(
            {{ $vacancyTest ? $vacancyTest->questions->pluck('id')->toJson() : '[]' }},
            {{ $templateQuestions->toJson() }}
         )">

        {{-- Left: Template Selection + Questions --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Template Selector --}}
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Pilih Template Bank Soal</h2>
                <select x-model="selectedTemplate" @change="applyTemplate()"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40 bg-white">
                    <option value="">-- Pilih template --</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->nama }} ({{ $template->questions_count }} soal)</option>
                    @endforeach
                </select>
                <p class="mt-1.5 text-[10px] text-gray-400">Memilih template akan mengisi daftar soal. Anda masih bisa menambah/menghapus soal setelah memilih.</p>
            </div>

            {{-- Questions Preview --}}
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-800">Soal Terpilih</h2>
                </div>

                <div x-show="selectedIds.length === 0" class="px-5 py-10 text-center">
                    <p class="text-sm text-gray-400">Belum ada soal dipilih. Pilih template di atas.</p>
                </div>

                <div class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto" x-show="selectedIds.length > 0">
                    <template x-for="(qId, index) in selectedIds" :key="qId">
                        <div class="px-5 py-3 flex items-start gap-3 hover:bg-gray-50/50 transition-colors">
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary/10 text-primary text-[10px] font-bold shrink-0 mt-0.5" x-text="index + 1"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-800" x-text="getQuestion(qId)?.pertanyaan || 'Soal #' + qId"></p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full"
                                        :class="getQuestion(qId)?.tipe === 'mc' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600'"
                                        x-text="getQuestion(qId)?.tipe_label || ''"></span>
                                    <span class="text-xs text-gray-400" x-text="(getQuestion(qId)?.nilai_poin || 0) + ' poin'"></span>
                                </div>
                            </div>
                            <button type="button" @click="removeQuestion(index)"
                                class="p-1 text-red-400 hover:text-red-600 rounded transition-colors shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Right: Config Form --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-4">Pengaturan Tes</h2>

                <form method="POST" action="{{ route('lowongan.tes.save', $lowongan) }}" @submit="prepareSubmit()">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Batas Waktu (menit) <span class="text-red-500">*</span></label>
                        <input type="number" name="batas_waktu_menit" min="5" max="480"
                            value="{{ old('batas_waktu_menit', $vacancyTest?->batas_waktu_menit ?? 60) }}" required
                            class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40">
                        @error('batas_waktu_menit') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    <div id="selected-ids-container"></div>
                    @error('question_ids') <p class="mb-2 text-xs text-red-500">{{ $message }}</p> @enderror

                    <div class="mb-4 text-xs text-gray-500">
                        <span x-text="selectedIds.length"></span> soal dipilih
                        (<span x-text="totalPoin"></span> poin total)
                    </div>

                    <button type="submit"
                        class="w-full px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                        Simpan Konfigurasi
                    </button>
                </form>
            </div>

            @if ($vacancyTest)
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-4 text-xs text-gray-500">
                    <p class="font-medium text-gray-700 mb-1">Konfigurasi Saat Ini</p>
                    <p>{{ $vacancyTest->questions->count() }} soal &mdash; {{ $vacancyTest->batas_waktu_menit }} menit</p>
                    <p>Total: {{ $vacancyTest->totalNilaiMaksimal() }} poin</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function testConfig(initialSelected, templateQuestions) {
            const allQuestionsMap = {};
            Object.values(templateQuestions).forEach(questions => {
                questions.forEach(q => { allQuestionsMap[q.id] = q; });
            });

            return {
                selectedIds: initialSelected.map(Number),
                selectedTemplate: '',
                templateQuestions: templateQuestions,
                get totalPoin() {
                    return this.selectedIds.reduce((sum, id) => {
                        const q = allQuestionsMap[id];
                        return sum + (q ? q.nilai_poin : 0);
                    }, 0);
                },
                getQuestion(id) {
                    return allQuestionsMap[id] || null;
                },
                applyTemplate() {
                    if (!this.selectedTemplate) return;
                    const questions = this.templateQuestions[this.selectedTemplate] || [];
                    this.selectedIds = questions.map(q => q.id);
                },
                removeQuestion(index) {
                    this.selectedIds.splice(index, 1);
                },
                prepareSubmit() {
                    const container = document.getElementById('selected-ids-container');
                    container.innerHTML = '';
                    this.selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'question_ids[]';
                        input.value = id;
                        container.appendChild(input);
                    });
                },
            };
        }
    </script>

</x-layouts.app>
