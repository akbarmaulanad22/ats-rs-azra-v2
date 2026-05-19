<x-layouts.app title="Template Wawancara - {{ $lowongan->judul_posisi }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.pipeline', $lowongan) }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Pipeline
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Template Wawancara</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $lowongan->judul_posisi }} &mdash; {{ $lowongan->unit->nama }}</p>
    </div>

    @if (session('success'))
        <div class="mb-4 px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('lowongan.kriteria-wawancara.save', $lowongan) }}"
        x-data="templateAssignment()"
    >
        @csrf

        <div class="space-y-6">
            @foreach ($wawancaraStages as $stage)
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-800">{{ $stage->nama }}</h2>
                    </div>
                    <div class="p-5" x-data="stageDropdown('{{ $stage->key }}')" @click.outside="open = false">
                        {{-- Selected chips --}}
                        <div class="flex flex-wrap gap-2 mb-3" x-show="stages['{{ $stage->key }}'].length > 0">
                            <template x-for="(tpl, index) in stages['{{ $stage->key }}']" :key="tpl.id + '-{{ $stage->key }}'">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-primary/10 text-primary">
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

                        {{-- Searchable dropdown --}}
                        <div class="relative">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="text"
                                    x-model="search"
                                    @focus="open = true"
                                    @input="open = true"
                                    placeholder="Cari template..."
                                    class="w-full pl-8 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 placeholder:text-gray-400"
                                >
                            </div>
                            <div x-show="open && filtered().length > 0" x-transition
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                <template x-for="tpl in filtered()" :key="tpl.id">
                                    <button type="button"
                                        @click="addTemplate('{{ $stage->key }}', tpl); search = ''; open = false"
                                        class="w-full px-3 py-2 text-left text-sm hover:bg-primary/5 flex items-center justify-between gap-2 transition-colors">
                                        <span x-text="tpl.nama" class="text-gray-800"></span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-medium shrink-0"
                                            :class="tpl.tipe === 'kriteria_penilaian' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600'"
                                            x-text="tpl.tipe === 'kriteria_penilaian' ? 'Kriteria' : 'Kesiapan'"></span>
                                    </button>
                                </template>
                            </div>
                            <div x-show="open && search.length > 0 && filtered().length === 0"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg">
                                <p class="px-3 py-2 text-sm text-gray-400">Tidak ada template ditemukan</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($wawancaraStages->isEmpty())
            <div class="bg-white rounded-xl border border-gray-100 p-10 text-center">
                <p class="text-sm text-gray-500">Tidak ada tahap wawancara pada workflow lowongan ini.</p>
            </div>
        @else
            <div class="mt-6">
                <button
                    type="submit"
                    class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                >
                    Simpan Template
                </button>
            </div>
        @endif
    </form>

    <script>
        function templateAssignment() {
            const allTemplates = @js($templates->map(fn ($t) => ['id' => $t->id, 'nama' => $t->nama, 'tipe' => $t->tipe->value]));
            const initialAssigned = @js(
                $assigned->map(fn ($items) => $items->map(fn ($vit) => [
                    'id' => $vit->interview_template_id,
                    'nama' => $vit->interviewTemplate->nama,
                    'tipe' => $vit->interviewTemplate->tipe->value,
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
