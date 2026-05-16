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
                    href="{{ route('lowongan.tes.ulasan.index', $lowongan) }}"
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
         x-data="testConfig({{ $vacancyTest ? $vacancyTest->questions->pluck('id')->toJson() : '[]' }})">

        {{-- Left: Question Bank --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-100">
                <h2 class="text-sm font-semibold text-gray-800">Bank Soal Tersedia</h2>
            </div>

            {{-- Unit filter --}}
            <div class="px-5 py-3 border-b border-gray-100">
                <select x-model="unitFilter"
                    class="text-xs border border-gray-200 rounded-lg px-3 py-1.5 bg-white text-gray-700 focus:outline-none focus:ring-1 focus:ring-primary/40">
                    <option value="">Semua Unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="divide-y divide-gray-50 max-h-[500px] overflow-y-auto">
                @foreach ($allQuestions as $question)
                    <div class="px-5 py-3 flex items-start gap-3 hover:bg-gray-50/50 transition-colors"
                         x-show="!unitFilter || unitFilter == '{{ $question->unit_id }}'">
                        <input type="checkbox"
                            :value="{{ $question->id }}"
                            x-model="selectedIds"
                            class="mt-0.5 w-4 h-4 text-primary rounded focus:ring-primary/40">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800">{{ $question->pertanyaan }}</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-gray-400">{{ $question->unit->nama }}</span>
                                <span class="text-xs font-medium px-1.5 py-0.5 rounded-full
                                    {{ $question->tipe->value === 'mc' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $question->tipe->label() }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $question->nilai_poin }} poin</span>
                            </div>
                        </div>
                    </div>
                @endforeach
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
        function testConfig(initialSelected) {
            const allQuestions = @json($allQuestions->map(fn($q) => ['id' => $q->id, 'nilai_poin' => $q->nilai_poin]));

            return {
                selectedIds: initialSelected.map(String),
                unitFilter: '',
                get totalPoin() {
                    return allQuestions
                        .filter(q => this.selectedIds.includes(String(q.id)))
                        .reduce((sum, q) => sum + q.nilai_poin, 0);
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
