<x-layouts.app title="Edit Template - ATS RS Azra">

    <div class="mb-4">
        <a href="{{ route('alur-rekrutmen.index') }}" class="inline-flex items-center gap-1 text-xs text-gray-500 hover:text-primary transition-colors ease-out duration-150 mb-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Template Alur Rekrutmen
        </a>
        <h1 class="text-xl font-semibold text-gray-900">Edit Template: {{ $template->name }}</h1>
    </div>

    @php
        $currentStageIds = old('stage_ids', $template->stages->pluck('id')->map(fn($id) => (string)$id)->all());
    @endphp

    <div
        class="bg-white/80 border border-gray-200 rounded-md"
        x-data="workflowBuilder(
            {{ $stages->toJson() }},
            {{ json_encode($currentStageIds) }}
        )"
    >
        <form method="POST" action="{{ route('alur-rekrutmen.update', $template) }}" @submit="prepareSubmit">
            @csrf
            @method('PUT')

            {{-- Informasi Template --}}
            <div class="px-4 pt-4 pb-5">
                <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-3">Informasi Template</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                            Nama Template <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name', $template->name) }}"
                            placeholder="cth. Koordinator, Staf Medis..."
                            class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring @error('name') border-red-400 @else border-gray-200 @enderror"
                        >
                        @error('name')
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">Deskripsi</label>
                        <input
                            type="text"
                            id="description"
                            name="description"
                            value="{{ old('description', $template->description) }}"
                            placeholder="Opsional — keterangan singkat template ini"
                            class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring border-gray-200"
                        >
                    </div>
                </div>
            </div>

            <hr class="border-t border-gray-300/80">

            {{-- Stage Builder --}}
            <div class="px-4 py-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Tahapan Rekrutmen</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Aktifkan tahap yang diinginkan, lalu seret untuk mengatur urutan</p>
                    </div>
                    <span class="text-[11px] text-gray-400" x-text="enabledCount + ' tahap dipilih'"></span>
                </div>

                @error('stage_ids')
                    <p class="mb-3 text-[11px] text-red-600 bg-red-50 border border-red-200 rounded px-3 py-2">{{ $message }}</p>
                @enderror

                <template x-for="stageId in orderedEnabledIds" :key="stageId">
                    <input type="hidden" name="stage_ids[]" :value="stageId">
                </template>

                <div class="space-y-1.5" x-ref="stageList">
                    <template x-for="stage in orderedStages" :key="stage.id">
                        <div
                            class="flex items-center gap-2.5 px-3 py-2 rounded-lg border transition-all ease-out duration-150 select-none"
                            :class="{
                                'bg-primary/5 border-primary/20': stage.enabled,
                                'bg-gray-50 border-gray-100 opacity-50': !stage.enabled,
                                'cursor-grab active:cursor-grabbing': stage.enabled && !stage.is_locked_first && !stage.is_locked_last,
                            }"
                            :draggable="stage.enabled && !stage.is_locked_first && !stage.is_locked_last"
                            @dragstart="onDragStart($event, stage)"
                            @dragover.prevent="onDragOver($event, stage)"
                            @dragleave="onDragLeave($event)"
                            @drop.prevent="onDrop($event, stage)"
                            @dragend="onDragEnd"
                        >
                            <div class="flex-shrink-0 w-4">
                                <template x-if="stage.enabled && !stage.is_locked_first && !stage.is_locked_last">
                                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M8 6a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm8-16a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4z"/>
                                    </svg>
                                </template>
                                <template x-if="stage.is_locked_first || stage.is_locked_last">
                                    <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </template>
                            </div>

                            <span
                                class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold"
                                :class="stage.enabled ? 'bg-primary text-white' : 'bg-gray-200 text-gray-400'"
                                x-text="stage.enabled ? orderedEnabledIds.indexOf(stage.id) + 1 : ''"
                            ></span>

                            <span class="flex-1 text-xs" :class="stage.enabled ? 'font-medium text-gray-800' : 'text-gray-400'" x-text="stage.label"></span>

                            <template x-if="stage.is_locked_first">
                                <span class="text-[10px] text-primary/60 font-medium">Pertama</span>
                            </template>
                            <template x-if="stage.is_locked_last">
                                <span class="text-[10px] text-primary/60 font-medium">Terakhir</span>
                            </template>

                            <button
                                type="button"
                                :disabled="stage.is_locked_first || stage.is_locked_last"
                                @click="toggleStage(stage)"
                                class="flex-shrink-0 w-8 h-4.5 rounded-full transition-colors ease-out duration-200 relative focus:outline-none cursor-pointer"
                                :class="{
                                    'bg-primary': stage.enabled,
                                    'bg-gray-200': !stage.enabled,
                                    'opacity-50 cursor-not-allowed': stage.is_locked_first || stage.is_locked_last,
                                }"
                                :title="stage.is_locked_first || stage.is_locked_last ? 'Tahap ini wajib dan tidak dapat dinonaktifkan' : (stage.enabled ? 'Nonaktifkan tahap' : 'Aktifkan tahap')"
                            >
                                <span
                                    class="absolute top-0.5 w-3.5 h-3.5 rounded-full bg-white shadow transition-transform ease-out duration-200"
                                    :class="stage.enabled ? 'translate-x-[18px]' : 'translate-x-0.5'"
                                ></span>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-2 px-4 py-3 border-t border-gray-200 bg-gray-200/90 rounded-b-md">
                <button
                    type="submit"
                    class="px-4 py-1.5 bg-primary text-white text-xs font-medium rounded hover:bg-primary-dark transition-colors ease-out duration-150 cursor-pointer"
                >
                    Simpan Perubahan
                </button>
                <a href="{{ route('alur-rekrutmen.index') }}" class="px-4 py-1.5 text-xs text-gray-500 border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
                    Batal
                </a>
            </div>
        </form>
    </div>

    @include('alur-rekrutmen._builder_script')

</x-layouts.app>
