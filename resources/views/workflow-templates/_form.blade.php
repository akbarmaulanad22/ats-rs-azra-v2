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
    <p class="text-xs text-gray-400 mb-4">Aktifkan tahap yang diperlukan dan atur urutannya dengan drag. Tahap <strong>Lamaran</strong> dan <strong>Onboarding</strong> selalu ada dan tidak dapat dipindahkan.</p>

    @error('stages')
        <p class="mb-3 text-xs text-red-500">{{ $message }}</p>
    @enderror

    {{-- Selected (ordered) stages --}}
    <div class="space-y-1.5 mb-4">
        <template x-for="(stage, index) in selectedStages" :key="stage.id">
            <div
                class="flex items-center gap-2 px-3 py-2 bg-primary/5 border border-primary/20 rounded-lg transition-opacity"
                :class="(dragOverIndex === index && dragIndex !== index ? 'border-primary border-dashed bg-primary/10' : '') + (!stage.is_locked_first && !stage.is_locked_last ? ' cursor-pointer' : '')"
                :draggable="!stage.is_locked_first && !stage.is_locked_last"
                @dragstart="onDragStart($event, index)"
                @dragover.prevent="onDragOver($event, index)"
                @dragleave="dragOverIndex = null"
                @drop.prevent="onDrop($event, index)"
                @dragend="dragIndex = null; dragOverIndex = null"
            >
                <span
                    x-show="!stage.is_locked_first && !stage.is_locked_last"
                    class="text-gray-300 cursor-grab active:cursor-grabbing"
                    title="Seret untuk mengatur ulang"
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm8-16a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </span>
                <span
                    x-show="stage.is_locked_first || stage.is_locked_last"
                    class="text-gray-200"
                >
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm8-16a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </span>

                <span class="text-[10px] font-mono text-gray-400 w-5 text-center" x-text="index + 1"></span>

                <span class="flex-1 text-sm font-medium text-gray-800" x-text="stage.nama"></span>

                <span
                    x-show="stage.is_locked_first || stage.is_locked_last"
                    class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium bg-primary/10 text-primary rounded"
                >Wajib</span>

                <button
                    x-show="!stage.is_locked_first && !stage.is_locked_last"
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
