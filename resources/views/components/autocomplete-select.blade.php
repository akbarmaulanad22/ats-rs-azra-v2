@props([
    'name',
    'label',
    'options',        // Collection or array of objects with ->id and ->label (or 'id'/'label' keys)
    'value' => null,  // Selected ID (e.g. old('name'))
    'required' => false,
    'createUrl' => null,    // URL to redirect when creating a new item
    'createLabel' => 'Buat Baru',
    'placeholder' => 'Ketik untuk mencari...',
    'emptyMessage' => 'Tidak ada data yang cocok.',
])

@php
    $selectedId = old($name, $value);
    $optionsList = collect($options)->map(fn ($o) => [
        'id'    => is_array($o) ? $o['id']    : $o->id,
        'label' => is_array($o) ? $o['label'] : $o->label,
    ])->values()->all();
    $selectedLabel = collect($optionsList)->firstWhere('id', $selectedId)['label'] ?? '';
@endphp

<div
    x-data="autocompleteSelect({
        options: @js($optionsList),
        selectedId: @js((string) $selectedId),
        selectedLabel: @js($selectedLabel),
    })"
    class="relative"
>
    {{-- Hidden real input for form submission --}}
    <input type="hidden" name="{{ $name }}" x-model="selectedId">

    {{-- Label --}}
    <label class="block text-xs font-medium text-gray-700 mb-1">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    {{-- Trigger input --}}
    <div class="relative">
        <input
            type="text"
            x-ref="searchInput"
            x-model="query"
            @focus="open = true"
            @input="open = true; selectedId = ''"
            @keydown.escape="close()"
            @keydown.enter.prevent="selectHighlighted()"
            @keydown.arrow-down.prevent="moveDown()"
            @keydown.arrow-up.prevent="moveUp()"
            @click.outside="close()"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="w-full px-2.5 py-1.5 text-xs border rounded bg-white focus-ring pr-7
                @error($name) border-red-400 @else border-gray-200 @enderror"
        >
        {{-- Clear button --}}
        <button
            type="button"
            x-show="query || selectedId"
            @click="clear()"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            tabindex="-1"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded shadow-lg max-h-52 overflow-y-auto"
        style="display: none;"
    >
        <template x-if="filtered.length > 0">
            <ul>
                <template x-for="(opt, i) in filtered" :key="opt.id">
                    <li
                        @click="select(opt)"
                        @mouseenter="highlighted = i"
                        :class="highlighted === i ? 'bg-primary/10 text-primary' : 'text-gray-700'"
                        class="px-3 py-2 text-xs cursor-pointer select-none"
                        x-text="opt.label"
                    ></li>
                </template>
            </ul>
        </template>
        <template x-if="filtered.length === 0">
            <div class="px-3 py-3 text-xs text-gray-500 flex items-center justify-between gap-2">
                <span>{{ $emptyMessage }}</span>
                @if($createUrl)
                    <a
                        href="{{ $createUrl }}?redirect={{ urlencode(url()->current()) }}"
                        class="inline-flex items-center gap-1 text-xs text-primary hover:text-primary-dark font-medium whitespace-nowrap transition-colors ease-out duration-150"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ $createLabel }}
                    </a>
                @endif
            </div>
        </template>
    </div>

    {{-- Validation error --}}
    @error($name)
        <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
    @enderror
</div>

<script>
    function autocompleteSelect({ options, selectedId, selectedLabel }) {
        return {
            options,
            selectedId,
            query: selectedLabel,
            open: false,
            highlighted: 0,

            get filtered() {
                if (!this.query) {
                    return this.options;
                }
                const q = this.query.toLowerCase();
                return this.options.filter(o => o.label.toLowerCase().includes(q));
            },

            select(opt) {
                this.selectedId = String(opt.id);
                this.query = opt.label;
                this.open = false;
            },

            selectHighlighted() {
                if (this.filtered[this.highlighted]) {
                    this.select(this.filtered[this.highlighted]);
                }
            },

            moveDown() {
                this.highlighted = Math.min(this.highlighted + 1, this.filtered.length - 1);
            },

            moveUp() {
                this.highlighted = Math.max(this.highlighted - 1, 0);
            },

            clear() {
                this.selectedId = '';
                this.query = '';
                this.$refs.searchInput.focus();
            },

            close() {
                this.open = false;
                if (!this.selectedId) {
                    this.query = '';
                }
            },
        };
    }
</script>
