<x-layouts.app title="Template Wawancara - {{ $templateLowongan->judul_posisi }} - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('template-lowongan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Lowongan
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Template Wawancara</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $templateLowongan->judul_posisi }} &mdash; {{ $templateLowongan->unit->nama }}</p>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded text-xs text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded text-xs text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('template-lowongan.template-wawancara.save', $templateLowongan) }}"
        x-data="templateAssignment()"
    >
        @csrf

        @if ($wawancaraStages->isEmpty())
            <div class="bg-white/80 border border-gray-200 rounded-md px-4 py-10 text-center">
                <p class="text-xs text-gray-500">Tidak ada tahap wawancara pada workflow template ini.</p>
            </div>
        @else
            <div class="bg-white/80 border border-gray-200 rounded-md">
                @foreach ($wawancaraStages as $stage)
                    <div class="px-4 py-4" x-data="stageDropdown('{{ $stage->key }}')" @click.outside="open = false">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ $stage->nama }}</p>

                        <div class="flex flex-wrap gap-2 mb-3" x-show="stages['{{ $stage->key }}'].length > 0">
                            <template x-for="(tpl, index) in stages['{{ $stage->key }}']" :key="tpl.id + '-{{ $stage->key }}'">
                                <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded text-[10px] font-medium bg-primary/10 text-primary">
                                    <span x-text="tpl.nama"></span>
                                    <span class="text-[9px] px-1 py-0.5 rounded"
                                        :class="tpl.tipe === 'kriteria_penilaian' ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600'"
                                        x-text="tpl.tipe === 'kriteria_penilaian' ? 'Kriteria' : 'Kesiapan'"></span>
                                    <button type="button" @click="removeTemplate('{{ $stage->key }}', index)"
                                        class="ml-0.5 text-primary/60 hover:text-red-500 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                    <input type="hidden" :name="'assignments[{{ $stage->key }}][]'" :value="tpl.id">
                                </span>
                            </template>
                        </div>

                        <div class="relative">
                            <div class="relative">
                                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    placeholder="Cari template..."
                                    class="w-full pl-7 pr-3 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring placeholder:text-gray-400"
                                >
                            </div>
                            <div x-show="open && filtered().length > 0" x-transition
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="tpl in filtered()" :key="tpl.id">
                                    <button type="button"
                                        @click="addTemplate('{{ $stage->key }}', tpl); search = ''; open = false"
                                        class="w-full px-3 py-2 text-left text-xs hover:bg-primary/5 flex items-center justify-between gap-2 transition-colors">
                                        <span x-text="tpl.nama" class="text-gray-800"></span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-medium shrink-0"
                                            :class="tpl.tipe === 'kriteria_penilaian' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600'"
                                            x-text="tpl.tipe === 'kriteria_penilaian' ? 'Kriteria' : 'Kesiapan'"></span>
                                    </button>
                                </template>
                            </div>
                            <div x-show="open && search.length > 0 && filtered().length === 0"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg">
                                <p class="px-3 py-2 text-xs text-gray-400">Tidak ada template ditemukan</p>
                            </div>
                        </div>
                    </div>
                    @if (!$loop->last)
                        <hr class="border-t border-gray-300/80">
                    @endif
                @endforeach

                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                    <button
                        type="submit"
                        class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                    >
                        Simpan Template
                    </button>
                </div>
            </div>
        @endif
    </form>

    <script>
        function templateAssignment() {
            const allTemplates = @js($templates->map(fn ($t) => ['id' => $t->id, 'nama' => $t->nama, 'tipe' => $t->tipe->value]));
            const initialAssigned = @js(
                $assigned->map(fn ($items) => $items->map(fn ($jit) => [
                    'id' => $jit->interview_template_id,
                    'nama' => $jit->interviewTemplate->nama,
                    'tipe' => $jit->interviewTemplate->tipe->value,
                ])->values())->toArray()
            );

            return {
                allTemplates,
                stages: {
                    @foreach ($wawancaraStages as $stage)
                        '{{ $stage->key }}': initialAssigned['{{ $stage->key }}'] || [],
                    @endforeach
                },
                addTemplate(stageKey, tpl) {
                    if (this.stages[stageKey].some(t => t.id === tpl.id)) return;
                    this.stages[stageKey].push({ ...tpl });
                },
                removeTemplate(stageKey, index) {
                    this.stages[stageKey].splice(index, 1);
                },
            };
        }

        function stageDropdown(stageKey) {
            return {
                search: '',
                open: false,
                filtered() {
                    const assigned = (this.stages[stageKey] || []).map(t => t.id);
                    return this.allTemplates.filter(t => {
                        if (assigned.includes(t.id)) return false;
                        if (!this.search) return true;
                        return t.nama.toLowerCase().includes(this.search.toLowerCase());
                    });
                },
            };
        }
    </script>

</x-layouts.app>
