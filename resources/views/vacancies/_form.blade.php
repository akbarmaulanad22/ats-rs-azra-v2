{{-- judul_posisi --}}
<div>
    <label class="block text-xs font-medium text-gray-700 mb-1">Judul Posisi <span class="text-red-500">*</span></label>
    <input
        type="text"
        name="judul_posisi"
        value="{{ old('judul_posisi', $lowongan->judul_posisi ?? '') }}"
        class="w-full px-3 py-1.5 text-sm border @error('judul_posisi') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white"
        placeholder="Contoh: Perawat IGD"
    >
    @error('judul_posisi')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    {{-- unit_id --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
        <select
            name="unit_id"
            class="w-full px-3 py-1.5 text-sm border @error('unit_id') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white"
        >
            <option value="">-- Pilih Unit --</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $lowongan->unit_id ?? '') == $unit->id)>{{ $unit->nama }}</option>
            @endforeach
        </select>
        @error('unit_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- workflow_template_id --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Template Alur Kerja <span class="text-red-500">*</span></label>
        <select
            name="workflow_template_id"
            class="w-full px-3 py-1.5 text-sm border @error('workflow_template_id') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white"
        >
            <option value="">-- Pilih Template --</option>
            @foreach ($templates as $template)
                <option value="{{ $template->id }}" @selected(old('workflow_template_id', $lowongan->workflow_template_id ?? '') == $template->id)>{{ $template->nama }}</option>
            @endforeach
        </select>
        @error('workflow_template_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    {{-- jenis_pekerjaan --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Jenis Pekerjaan <span class="text-red-500">*</span></label>
        <select
            name="jenis_pekerjaan"
            class="w-full px-3 py-1.5 text-sm border @error('jenis_pekerjaan') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white"
        >
            <option value="">-- Pilih Jenis --</option>
            @foreach ($employmentTypes as $type)
                <option value="{{ $type->value }}" @selected(old('jenis_pekerjaan', ($lowongan->jenis_pekerjaan ?? null)?->value) === $type->value)>{{ $type->label() }}</option>
            @endforeach
        </select>
        @error('jenis_pekerjaan')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- jumlah_posisi --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Jumlah Posisi <span class="text-red-500">*</span></label>
        <input
            type="number"
            name="jumlah_posisi"
            value="{{ old('jumlah_posisi', $lowongan->jumlah_posisi ?? 1) }}"
            min="1"
            class="w-full px-3 py-1.5 text-sm border @error('jumlah_posisi') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white"
        >
        @error('jumlah_posisi')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- tenggat_lamaran --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">Tenggat Lamaran <span class="text-red-500">*</span></label>
        <input
            type="date"
            name="tenggat_lamaran"
            value="{{ old('tenggat_lamaran', isset($lowongan) ? $lowongan->tenggat_lamaran->format('Y-m-d') : '') }}"
            class="w-full px-3 py-1.5 text-sm border @error('tenggat_lamaran') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white"
        >
        @error('tenggat_lamaran')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- status --}}
<div>
    <label class="block text-xs font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
    <select
        name="status"
        class="w-full px-3 py-1.5 text-sm border @error('status') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white"
    >
        @foreach ($statuses as $s)
            <option value="{{ $s->value }}" @selected(old('status', ($lowongan->status ?? null)?->value) === $s->value)>{{ $s->label() }}</option>
        @endforeach
    </select>
    @error('status')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- deskripsi_pekerjaan --}}
<div>
    <label class="block text-xs font-medium text-gray-700 mb-1">Deskripsi Pekerjaan <span class="text-red-500">*</span></label>
    <textarea
        name="deskripsi_pekerjaan"
        rows="6"
        class="w-full px-3 py-1.5 text-sm border @error('deskripsi_pekerjaan') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white resize-y"
        placeholder="Uraikan tanggung jawab dan deskripsi pekerjaan..."
    >{{ old('deskripsi_pekerjaan', $lowongan->deskripsi_pekerjaan ?? '') }}</textarea>
    @error('deskripsi_pekerjaan')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- kualifikasi --}}
<div>
    <label class="block text-xs font-medium text-gray-700 mb-1">Kualifikasi <span class="text-red-500">*</span></label>
    <textarea
        name="kualifikasi"
        rows="6"
        class="w-full px-3 py-1.5 text-sm border @error('kualifikasi') border-red-400 @else border-gray-200 @enderror rounded-md focus-ring bg-white resize-y"
        placeholder="Tuliskan persyaratan dan kualifikasi yang dibutuhkan..."
    >{{ old('kualifikasi', $lowongan->kualifikasi ?? '') }}</textarea>
    @error('kualifikasi')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>
