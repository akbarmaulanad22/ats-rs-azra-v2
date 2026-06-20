<x-layouts.app title="Terbitkan Lowongan - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('template-lowongan.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Lowongan
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Terbitkan Lowongan</h1>
        <p class="text-xs text-gray-500 mt-0.5">{{ $templateLowongan->judul_posisi }} &mdash; {{ $templateLowongan->unit->nama }}</p>
    </div>

    @if ($hasTestStage && ! $templateLowongan->jobTemplateTest)
        <div class="mb-4 px-4 py-2.5 bg-amber-50 border border-amber-200 rounded text-xs text-amber-700">
            Workflow template ini memiliki tahap Tes Kompetensi tetapi tes belum dikonfigurasi.
            <a href="{{ route('template-lowongan.tes.show', $templateLowongan) }}" class="font-medium underline">Konfigurasi tes</a>
            terlebih dahulu untuk dapat menerbitkan sebagai Dipublikasikan.
        </div>
    @endif

    <div class="bg-white/80 border border-gray-200 rounded-md">
        <form method="POST" action="{{ route('template-lowongan.terbitkan', $templateLowongan) }}" enctype="multipart/form-data">
            @csrf
            @if (! empty($callbackReturn))
                <input type="hidden" name="callback" value="1">
            @endif

            <div class="px-4 pt-4 pb-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Periode Lowongan</p>
                <div class="space-y-3">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="jumlah_posisi" class="block text-xs font-medium text-gray-700 mb-1">Jumlah Posisi <span class="text-red-500">*</span></label>
                            <input
                                type="number"
                                id="jumlah_posisi"
                                name="jumlah_posisi"
                                value="{{ old('jumlah_posisi', 1) }}"
                                min="1"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('jumlah_posisi') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('jumlah_posisi')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="tenggat_lamaran" class="block text-xs font-medium text-gray-700 mb-1">Tenggat Lamaran <span class="text-red-500">*</span></label>
                            <input
                                type="date"
                                id="tenggat_lamaran"
                                name="tenggat_lamaran"
                                value="{{ old('tenggat_lamaran') }}"
                                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('tenggat_lamaran') border-red-400 @else border-gray-200 @enderror"
                            >
                            @error('tenggat_lamaran')
                                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($statuses as $s)
                                <label class="bg-white flex items-center gap-1.5 px-3 py-1.5 rounded border cursor-pointer text-xs font-medium transition-colors ease-out duration-150 has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 has-[:checked]:hover:bg-primary">
                                    <input
                                        type="radio"
                                        name="status"
                                        value="{{ $s->value }}"
                                        {{ old('status', \App\Enums\VacancyStatus::Draft->value) === $s->value ? 'checked' : '' }}
                                        class="sr-only"
                                    >
                                    {{ $s->label() }}
                                </label>
                            @endforeach
                        </div>
                        @error('status')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-data="{ preview: null }">
                        <label for="flyer" class="block text-xs font-medium text-gray-700 mb-1">Flyer Lowongan <span class="text-red-500">*</span></label>
                        <p class="text-[11px] text-gray-500 mb-2">Gambar poster yang ditampilkan di halaman karier. Format JPG, PNG, atau WEBP. Maksimal 4 MB.</p>
                        <div class="flex items-start gap-3">
                            <img
                                x-show="preview"
                                x-cloak
                                :src="preview"
                                alt="Pratinjau flyer"
                                class="w-28 aspect-[3/4] object-cover rounded border border-gray-200 bg-gray-50"
                            >
                            <div class="flex-1">
                                <input
                                    type="file"
                                    id="flyer"
                                    name="flyer"
                                    accept="image/jpeg,image/png,image/webp"
                                    x-on:change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                    class="block w-full text-xs text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-primary file:text-white hover:file:bg-primary-dark file:cursor-pointer cursor-pointer @error('flyer') text-red-600 @enderror"
                                >
                                @error('flyer')
                                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <hr class="border-t border-gray-300/80">

            <div class="px-4 py-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Kualifikasi (Opsional)</p>
                <p class="text-[11px] text-gray-500 mb-2">Biarkan kosong untuk memakai kualifikasi default template. Isi untuk menimpa khusus periode ini.</p>
                <textarea
                    id="kualifikasi"
                    name="kualifikasi"
                    rows="5"
                    placeholder="{{ \Illuminate\Support\Str::limit($templateLowongan->kualifikasi, 120) }}"
                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring resize-y @error('kualifikasi') border-red-400 @else border-gray-200 @enderror"
                >{{ old('kualifikasi') }}</textarea>
                @error('kualifikasi')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                <button
                    type="submit"
                    class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                >
                    Terbitkan Lowongan
                </button>
                <a href="{{ route('template-lowongan.index') }}" class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                    Batal
                </a>
            </div>
        </form>
    </div>

</x-layouts.app>
