<x-layouts.app title="Edit Soal - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('bank-soal.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Bank Soal
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Soal</h1>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 p-6 max-w-2xl"
         x-data="questionForm()">

        <form method="POST" action="{{ route('bank-soal.update', $question) }}">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                    <select name="unit_id" required
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40 @error('unit_id') border-red-400 @enderror">
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" {{ (old('unit_id', $question->unit_id) == $unit->id) ? 'selected' : '' }}>{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Tipe Soal <span class="text-red-500">*</span></label>
                    <select name="tipe" x-model="tipe" required
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40">
                        <option value="mc" {{ old('tipe', $question->tipe->value) === 'mc' ? 'selected' : '' }}>Pilihan Ganda</option>
                        <option value="essay" {{ old('tipe', $question->tipe->value) === 'essay' ? 'selected' : '' }}>Esai</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Pertanyaan <span class="text-red-500">*</span></label>
                    <textarea name="pertanyaan" rows="3" required
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40">{{ old('pertanyaan', $question->pertanyaan) }}</textarea>
                    @error('pertanyaan') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nilai Poin <span class="text-red-500">*</span></label>
                    <input type="number" name="nilai_poin" min="1" max="100"
                        value="{{ old('nilai_poin', $question->nilai_poin) }}" required
                        class="w-32 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-primary/40">
                    @error('nilai_poin') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- MC Options --}}
                <div x-show="tipe === 'mc'" x-cloak>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-700">Opsi Jawaban <span class="text-red-500">*</span></label>
                        <button type="button" @click="addOption()"
                            class="text-xs text-primary hover:underline">+ Tambah Opsi</button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(option, index) in options" :key="index">
                            <div class="flex items-center gap-2">
                                <input type="radio" name="correct_option" :value="index"
                                    x-model="correctIndex"
                                    class="w-4 h-4 text-primary focus:ring-primary/40">
                                <input type="text" :name="`options[${index}][teks_opsi]`"
                                    x-model="option.teks_opsi" placeholder="Teks opsi..."
                                    class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary/40">
                                <button type="button" @click="removeOption(index)"
                                    x-show="options.length > 2"
                                    class="text-xs text-red-400 hover:text-red-600">Hapus</button>
                            </div>
                        </template>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Pilih radio button di kiri untuk menandai jawaban benar.</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                    Perbarui Soal
                </button>
                <a href="{{ route('bank-soal.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>

    @php
        $existingOptions = $question->options->map(fn($o) => ['teks_opsi' => $o->teks_opsi, 'is_correct' => $o->is_correct])->toArray();
        $correctIdx = $question->options->search(fn($o) => $o->is_correct);
    @endphp

    <script>
        function questionForm() {
            return {
                tipe: '{{ old('tipe', $question->tipe->value) }}',
                correctIndex: '{{ $correctIdx !== false ? $correctIdx : 0 }}',
                options: @json(count($existingOptions) ? $existingOptions : [['teks_opsi'=>''],['teks_opsi'=>''],['teks_opsi'=>''],['teks_opsi'=>'']]),
                addOption() {
                    this.options.push({ teks_opsi: '' });
                },
                removeOption(index) {
                    if (this.options.length <= 2) return;
                    this.options.splice(index, 1);
                    if (parseInt(this.correctIndex) >= this.options.length) {
                        this.correctIndex = '0';
                    }
                },
            };
        }
    </script>

</x-layouts.app>
