{{-- Nama Template --}}
<div class="px-4 pt-4 pb-5">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Template</p>
    <div>
        <label for="nama" class="block text-xs font-medium text-gray-700 mb-1">Nama Template <span class="text-red-500">*</span></label>
        <input
            type="text"
            id="nama"
            name="nama"
            value="{{ old('nama', $templateAlur->nama ?? '') }}"
            placeholder="mis. Koordinator, Staf, Kepala Unit"
            required
            class="w-full px-3 py-1.5 text-sm border border-gray-200 rounded-md focus-ring bg-white placeholder:text-gray-400 @error('nama') border-red-400 @enderror"
        >
        @error('nama')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

<hr class="border-t border-gray-300/80">

{{-- Stage Builder --}}
<div class="px-4 py-5">
    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tahapan Rekrutmen</p>
    <p class="text-xs text-gray-400 mb-4">Aktifkan tahap yang diperlukan dan atur urutannya. Tahap <strong>Aplikasi</strong> dan <strong>Onboarding</strong> selalu ada dan tidak dapat dipindahkan.</p>

    @error('stages')
        <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
    @enderror

    {{-- Selected (ordered) stages --}}
    <div class="space-y-1.5 mb-4">
        <template x-for="(stage, index) in selectedStages" :key="stage.id">
            <div class="flex items-center gap-2 px-3 py-2 bg-primary/5 border border-primary/20 rounded-lg">
                <span class="text-[10px] font-mono text-gray-400 w-5 text-center" x-text="index + 1"></span>

                <span class="flex-1 text-sm font-medium text-gray-800" x-text="stage.nama"></span>

                <span
                    x-show="stage.is_locked_first || stage.is_locked_last"
                    class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium bg-primary/10 text-primary rounded"
                >Wajib</span>

                <div class="flex items-center gap-0.5" x-show="!stage.is_locked_first && !stage.is_locked_last">
                    <button
                        type="button"
                        @click="moveUp(index)"
                        :disabled="index === 1"
                        class="p-1 rounded text-gray-400 hover:text-gray-700 hover:bg-white transition-colors disabled:opacity-25 disabled:cursor-not-allowed cursor-pointer"
                        title="Pindah ke atas"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        @click="moveDown(index)"
                        :disabled="index === selectedStages.length - 2"
                        class="p-1 rounded text-gray-400 hover:text-gray-700 hover:bg-white transition-colors disabled:opacity-25 disabled:cursor-not-allowed cursor-pointer"
                        title="Pindah ke bawah"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <button
                        type="button"
                        @click="remove(stage.id)"
                        class="p-1 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors cursor-pointer"
                        title="Hapus dari template"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    {{-- Available (unselected) stages --}}
    <div x-show="availableStages.length > 0">
        <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Tahap Tersedia</p>
        <div class="space-y-1">
            <template x-for="stage in availableStages" :key="stage.id">
                <div class="flex items-center gap-2 px-3 py-2 border border-dashed border-gray-200 rounded-lg bg-white hover:border-primary/40 transition-colors">
                    <span class="flex-1 text-sm text-gray-500" x-text="stage.nama"></span>
                    <button
                        type="button"
                        @click="add(stage.id)"
                        class="inline-flex items-center gap-1 px-2 py-0.5 text-xs text-primary border border-primary/30 rounded hover:bg-primary/5 transition-colors cursor-pointer"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Hidden inputs submitted on form submit --}}
    <div id="stage-inputs"></div>
</div>
