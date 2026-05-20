<x-layouts.app title="Edit Template Wawancara - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('template-wawancara.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Wawancara
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Template Wawancara</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $template->nama }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2.5 bg-red-50 border border-red-200 rounded text-xs text-red-700">
            <p class="font-medium mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div x-data="itemForm(@js($template->items->map(fn ($item) => [
        'id' => $item->id,
        'teks' => $item->teks,
    ])->values()->all()))" class="max-w-4xl">
        <form method="POST" action="{{ route('template-wawancara.update', $template) }}" @submit="prepareSubmit($event)">
            @csrf
            @method('PUT')

            <div class="bg-white/80 border border-gray-200 rounded-md">
                <div class="px-4 pt-4 pb-5">
                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Template</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Nama Template <span class="text-red-500">*</span></label>
                            <input type="text" name="nama" value="{{ old('nama', $template->nama) }}" required
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-white focus-ring"
                                placeholder="Contoh: Kriteria Umum - Kepala Unit">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Tipe</label>
                            <input type="text" value="{{ $template->tipe->label() }}" disabled
                                class="w-full px-2.5 py-1.5 text-xs border border-gray-200 rounded bg-gray-50 text-gray-500 cursor-not-allowed">
                        </div>
                    </div>
                </div>

                <hr class="border-t border-gray-300/80">

                @include('interview-templates._item-form')

                <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                    <button type="submit"
                        class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer">
                        Perbarui Template
                    </button>
                    <a href="{{ route('template-wawancara.index') }}"
                        class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>

</x-layouts.app>
