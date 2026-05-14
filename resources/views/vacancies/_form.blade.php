{{-- Informasi Lowongan --}}
<div class="px-4 pt-4 pb-5">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Lowongan</p>
    <div class="space-y-3">

        <div>
            <label for="judul_posisi" class="block text-xs font-medium text-gray-700 mb-1">Judul Posisi <span class="text-red-500">*</span></label>
            <input
                type="text"
                id="judul_posisi"
                name="judul_posisi"
                value="{{ old('judul_posisi', $lowongan->judul_posisi ?? '') }}"
                placeholder="Contoh: Perawat IGD"
                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('judul_posisi') border-red-400 @else border-gray-200 @enderror"
            >
            @error('judul_posisi')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="unit_id" class="block text-xs font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                <select
                    id="unit_id"
                    name="unit_id"
                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('unit_id') border-red-400 @else border-gray-200 @enderror"
                >
                    <option value="">-- Pilih Unit --</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(old('unit_id', $lowongan->unit_id ?? '') == $unit->id)>{{ $unit->nama }}</option>
                    @endforeach
                </select>
                @error('unit_id')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="workflow_template_id" class="block text-xs font-medium text-gray-700 mb-1">Template Alur Kerja <span class="text-red-500">*</span></label>
                <select
                    id="workflow_template_id"
                    name="workflow_template_id"
                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('workflow_template_id') border-red-400 @else border-gray-200 @enderror"
                >
                    <option value="">-- Pilih Template --</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected(old('workflow_template_id', $lowongan->workflow_template_id ?? '') == $template->id)>{{ $template->nama }}</option>
                    @endforeach
                </select>
                @error('workflow_template_id')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
                <label for="jenis_pekerjaan" class="block text-xs font-medium text-gray-700 mb-1">Jenis Pekerjaan <span class="text-red-500">*</span></label>
                <select
                    id="jenis_pekerjaan"
                    name="jenis_pekerjaan"
                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('jenis_pekerjaan') border-red-400 @else border-gray-200 @enderror"
                >
                    <option value="">-- Pilih Jenis --</option>
                    @foreach ($employmentTypes as $type)
                        <option value="{{ $type->value }}" @selected(old('jenis_pekerjaan', ($lowongan->jenis_pekerjaan ?? null)?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                @error('jenis_pekerjaan')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="jumlah_posisi" class="block text-xs font-medium text-gray-700 mb-1">Jumlah Posisi <span class="text-red-500">*</span></label>
                <input
                    type="number"
                    id="jumlah_posisi"
                    name="jumlah_posisi"
                    value="{{ old('jumlah_posisi', $lowongan->jumlah_posisi ?? 1) }}"
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
                    value="{{ old('tenggat_lamaran', isset($lowongan) ? $lowongan->tenggat_lamaran->format('Y-m-d') : '') }}"
                    class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('tenggat_lamaran') border-red-400 @else border-gray-200 @enderror"
                >
                @error('tenggat_lamaran')
                    <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select
                id="status"
                name="status"
                class="w-full sm:w-48 px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('status') border-red-400 @else border-gray-200 @enderror"
            >
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected(old('status', ($lowongan->status ?? null)?->value) === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
            @error('status')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

<hr class="border-t border-gray-300/80">

{{-- Deskripsi & Kualifikasi --}}
<div class="px-4 py-5">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Konten Lowongan</p>
    <div class="space-y-3">

        <div>
            <label for="deskripsi_pekerjaan" class="block text-xs font-medium text-gray-700 mb-1">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
            <textarea
                id="deskripsi_pekerjaan"
                name="deskripsi_pekerjaan"
                rows="6"
                placeholder="Uraikan tanggung jawab dan deskripsi pekerjaan..."
                class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring resize-y @error('deskripsi_pekerjaan') border-red-400 @else border-gray-200 @enderror"
            >{{ old('deskripsi_pekerjaan', $lowongan->deskripsi_pekerjaan ?? '') }}</textarea>
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
            >{{ old('kualifikasi', $lowongan->kualifikasi ?? '') }}</textarea>
            @error('kualifikasi')
                <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>
