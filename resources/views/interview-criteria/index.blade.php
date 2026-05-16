<x-layouts.app title="Kriteria Wawancara - ATS RS Azra">

    <div class="mb-5 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Kriteria Wawancara</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola kriteria penilaian default per tahap wawancara.</p>
        </div>
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

    <div class="space-y-6">
        @foreach ($stageKeys as $stageKey => $stageLabel)
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-800">{{ $stageLabel }}</h2>
                </div>

                <div class="p-5">
                    @php $stageCriteria = $criteria->get($stageKey, collect()); @endphp

                    @if ($stageCriteria->isEmpty())
                        <p class="text-xs text-gray-400 py-2">Belum ada kriteria untuk tahap ini.</p>
                    @else
                        <div class="space-y-2 mb-4">
                            @foreach ($stageCriteria as $criterion)
                                <div class="flex items-center justify-between gap-3 px-3 py-2 bg-gray-50 rounded-lg">
                                    <form
                                        method="POST"
                                        action="{{ route('kriteria-wawancara.update', $criterion) }}"
                                        class="flex-1 flex items-center gap-2"
                                        x-data="{ editing: false, nama: '{{ $criterion->nama }}' }"
                                    >
                                        @csrf
                                        @method('PUT')
                                        <span x-show="!editing" class="text-sm text-gray-700 flex-1">{{ $criterion->nama }}</span>
                                        <input
                                            x-show="editing"
                                            x-cloak
                                            type="text"
                                            name="nama"
                                            x-model="nama"
                                            class="flex-1 px-2 py-1 text-sm border border-gray-200 rounded focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40"
                                        >
                                        <button
                                            type="button"
                                            x-show="!editing"
                                            @click="editing = true"
                                            class="text-xs text-primary hover:underline"
                                        >Edit</button>
                                        <button
                                            type="submit"
                                            x-show="editing"
                                            x-cloak
                                            class="text-xs text-green-600 hover:underline"
                                        >Simpan</button>
                                        <button
                                            type="button"
                                            x-show="editing"
                                            x-cloak
                                            @click="editing = false; nama = '{{ $criterion->nama }}'"
                                            class="text-xs text-gray-400 hover:underline"
                                        >Batal</button>
                                    </form>

                                    <form method="POST" action="{{ route('kriteria-wawancara.destroy', $criterion) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            onclick="return confirm('Hapus kriteria ini?')"
                                            class="text-xs text-red-500 hover:text-red-700 transition-colors"
                                        >Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Add criteria form --}}
                    <form
                        method="POST"
                        action="{{ route('kriteria-wawancara.store') }}"
                        class="flex items-center gap-2 mt-2"
                        x-data="{ nama: '' }"
                    >
                        @csrf
                        <input type="hidden" name="stage_key" value="{{ $stageKey }}">
                        <input
                            type="text"
                            name="nama"
                            x-model="nama"
                            placeholder="Nama kriteria baru..."
                            class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 placeholder:text-gray-400"
                        >
                        <button
                            type="submit"
                            class="px-3 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 disabled:opacity-50 cursor-pointer"
                            :disabled="!nama.trim()"
                        >Tambah</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

</x-layouts.app>
