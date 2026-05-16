<x-layouts.app title="Kriteria Wawancara - {{ $lowongan->judul_posisi }} - ATS RS Azra">

    <div class="mb-5">
        <a href="{{ route('lowongan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Lowongan Kerja
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Kriteria Wawancara</h1>
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
        x-data="{
            stages: @js(
                collect($stageKeys)->mapWithKeys(fn ($label, $key) => [
                    $key => $criteria->get($key, collect())->map(fn ($c) => ['nama' => $c->nama])->values()->toArray()
                ])->toArray()
            )
        }"
    >
        @csrf

        <div class="space-y-6">
            @foreach ($stageKeys as $stageKey => $stageLabel)
                <div class="bg-white rounded-xl border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-800">{{ $stageLabel }}</h2>
                    </div>
                    <div class="p-5">
                        <div class="space-y-2 mb-3" x-data>
                            <template x-for="(item, index) in stages['{{ $stageKey }}']" :key="index">
                                <div class="flex items-center gap-2">
                                    <input
                                        type="text"
                                        :name="`criteria[{{ $stageKey }}][${index}][nama]`"
                                        x-model="item.nama"
                                        placeholder="Nama kriteria..."
                                        class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary/40 placeholder:text-gray-400"
                                        required
                                    >
                                    <button
                                        type="button"
                                        @click="stages['{{ $stageKey }}'].splice(index, 1)"
                                        class="text-red-400 hover:text-red-600 transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        <button
                            type="button"
                            @click="stages['{{ $stageKey }}'].push({ nama: '' })"
                            class="inline-flex items-center gap-1.5 text-xs text-primary hover:underline"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Tambah Kriteria
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <button
                type="submit"
                class="px-6 py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
            >
                Simpan Kriteria
            </button>
        </div>
    </form>

</x-layouts.app>
