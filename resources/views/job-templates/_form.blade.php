{{-- Informasi Template --}}
<div class="px-4 pt-4 pb-5">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Template</p>
    <div class="space-y-3">

        <div>
            <label for="judul_posisi" class="block text-xs font-medium text-gray-700 mb-1">Judul Posisi <span class="text-red-500">*</span></label>
            <input
                type="text"
                id="judul_posisi"
                name="judul_posisi"
                value="{{ old('judul_posisi', $templateLowongan->judul_posisi ?? '') }}"
                placeholder="Contoh: Perawat IGD"
                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('judul_posisi') border-red-400 @else border-gray-200 @enderror"
            >
            @error('judul_posisi')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <x-autocomplete-select
                name="unit_id"
                label="Unit"
                search-url="{{ route('unit.cari') }}"
                create-url="{{ route('unit.create') }}"
                :value="old('unit_id', $templateLowongan->unit_id ?? null)"
                :selected-label="($templateLowongan ?? null)?->unit?->nama ?? ''"
                :required="true"
                placeholder="Cari unit..."
                label-class="block text-xs font-medium text-gray-700 mb-1"
            />

            <x-autocomplete-select
                name="workflow_template_id"
                label="Template Alur Kerja"
                search-url="{{ route('template-alur.cari') }}"
                create-url="{{ route('template-alur.create') }}"
                :value="old('workflow_template_id', $templateLowongan->workflow_template_id ?? null)"
                :selected-label="($templateLowongan ?? null)?->workflowTemplate?->nama ?? ''"
                :required="true"
                placeholder="Cari template..."
                label-class="block text-xs font-medium text-gray-700 mb-1"
            />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <x-autocomplete-select
                name="jenis_pekerjaan"
                label="Jenis Pekerjaan"
                :options="collect(\App\Enums\EmploymentType::cases())->map(fn ($t) => ['id' => $t->value, 'label' => $t->label()])"
                :value="old('jenis_pekerjaan', ($templateLowongan->jenis_pekerjaan ?? null)?->value)"
                :required="true"
                placeholder="Pilih jenis pekerjaan..."
                label-class="block text-xs font-medium text-gray-700 mb-1"
            />

            @isset($statuses)
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach($statuses as $s)
                        <label class="bg-white flex items-center gap-1.5 px-3 py-1.5 rounded border cursor-pointer text-xs font-medium transition-colors ease-out duration-150 has-[:checked]:bg-primary has-[:checked]:text-white has-[:checked]:border-primary border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 has-[:checked]:hover:bg-primary">
                            <input
                                type="radio"
                                name="status"
                                value="{{ $s->value }}"
                                {{ old('status', ($templateLowongan->status ?? null)?->value) === $s->value ? 'checked' : '' }}
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
            @endisset
        </div>

    </div>
</div>

<hr class="border-t border-gray-300/80">

{{-- Konten --}}
<div class="px-4 py-5">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Konten Default</p>
    <p class="text-[11px] text-gray-500 mb-3">Konten ini disalin ke setiap lowongan yang diterbitkan dari template. Kualifikasi masih dapat ditimpa per periode saat menerbitkan.</p>
    <div class="space-y-3">

        <div>
            <label for="deskripsi_pekerjaan" class="block text-xs font-medium text-gray-700 mb-1">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
            <textarea
                id="deskripsi_pekerjaan"
                name="deskripsi_pekerjaan"
                rows="6"
                placeholder="Uraikan tanggung jawab dan deskripsi pekerjaan..."
                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring resize-y @error('deskripsi_pekerjaan') border-red-400 @else border-gray-200 @enderror"
            >{{ old('deskripsi_pekerjaan', $templateLowongan->deskripsi_pekerjaan ?? '') }}</textarea>
            @error('deskripsi_pekerjaan')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="kualifikasi" class="block text-xs font-medium text-gray-700 mb-1">Kualifikasi <span class="text-red-500">*</span></label>
            <textarea
                id="kualifikasi"
                name="kualifikasi"
                rows="6"
                placeholder="Tuliskan persyaratan dan kualifikasi yang dibutuhkan..."
                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring resize-y @error('kualifikasi') border-red-400 @else border-gray-200 @enderror"
            >{{ old('kualifikasi', $templateLowongan->kualifikasi ?? '') }}</textarea>
            @error('kualifikasi')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>
