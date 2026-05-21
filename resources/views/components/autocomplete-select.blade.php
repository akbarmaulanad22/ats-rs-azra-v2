@props([
    'name',
    'label',
    'options' => [],      // Client-side mode: collection or array of objects with ->id and ->label (or 'id'/'label' keys)
    'searchUrl' => null,  // AJAX mode: endpoint returning { results: [{id, label, ...}], has_more: bool }
    'value' => null,      // Selected ID (e.g. old('name'))
    'selectedLabel' => null,  // AJAX mode: initial display label for the selected value
    'required' => false,
    'createUrl' => null,
    'refreshUrl' => null,
    'createLabel' => 'Buat Baru',
    'placeholder' => 'Ketik untuk mencari...',
    'emptyMessage' => 'Tidak ada data yang cocok.',
    'labelClass' => 'block text-xs font-medium text-gray-700 mb-1',
])

@php
    $selectedId = old($name, $value);
    if ($searchUrl) {
        $optionsList = [];
        $displayLabel = $selectedLabel ?? '';
    } else {
        $optionsList = collect($options)->map(fn ($o) => is_array($o)
            ? $o
            : ['id' => $o->id, 'label' => $o->label]
        )->values()->all();
        $displayLabel = collect($optionsList)->firstWhere('id', $selectedId)['label'] ?? '';
    }
@endphp

<div
    x-data="autocompleteSelect({
        options: @js($optionsList),
        selectedId: @js((string) $selectedId),
        selectedLabel: @js($displayLabel),
        fieldName: @js($name),
        createUrl: @js($createUrl),
        refreshUrl: @js($refreshUrl),
        searchUrl: @js($searchUrl),
    })"
    @click.outside="close()"
    class="relative"
>
    {{-- Hidden real input for form submission --}}
    <input type="hidden" name="{{ $name }}" x-model="selectedId">

    {{-- Label --}}
    <label class="{{ $labelClass }}">
        {{ $label }}
        @if($required) <span class="text-red-500">*</span> @endif
    </label>

    {{-- Trigger input --}}
    <div class="relative">
        <input
            type="text"
            x-ref="searchInput"
            x-model="query"
            @focus="onFocus()"
            @input="onInput()"
            @keydown.escape="close()"
            @keydown.enter.prevent="selectHighlighted()"
            @keydown.arrow-down.prevent="moveDown()"
            @keydown.arrow-up.prevent="moveUp()"
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
        {{-- Loading indicator --}}
        <div x-show="loading" class="px-3 py-3 text-xs text-gray-500">Mencari...</div>

        {{-- Results list --}}
        <template x-if="!loading && filtered.length > 0">
            <div>
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
                <template x-if="hasMore">
                    <p class="px-3 py-2 text-[10px] text-gray-400 border-t border-gray-100 italic">
                        Menampilkan 10 hasil. Ketik lebih spesifik untuk mempersempit.
                    </p>
                </template>
            </div>
        </template>

        {{-- Empty state --}}
        <template x-if="!loading && filtered.length === 0">
            <div class="px-3 py-3 text-xs text-gray-500 flex items-center justify-between gap-2">
                <span x-text="refreshing ? 'Memperbarui data...' : '{{ $emptyMessage }}'"></span>
                @if($createUrl)
                    <button
                        type="button"
                        @click="openCreateTab()"
                        :disabled="refreshing"
                        class="inline-flex items-center gap-1 text-xs text-primary hover:text-primary-dark font-medium whitespace-nowrap transition-colors ease-out duration-150 disabled:opacity-50"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        {{ $createLabel }}
                    </button>
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
    window.autocompleteSelect = window.autocompleteSelect || function ({ options, selectedId, selectedLabel, fieldName, createUrl, refreshUrl, searchUrl }) {
        return {
            options,
            selectedId,
            fieldName,
            createUrl,
            refreshUrl,
            searchUrl,
            query: selectedLabel,
            open: false,
            highlighted: 0,
            refreshing: false,
            loading: false,
            hasMore: false,
            _debounceTimer: null,
            _abortController: null,

            get filtered() {
                if (this.searchUrl) {
                    return this.options;
                }
                if (!this.query) {
                    return this.options;
                }
                const q = this.query.toLowerCase();
                return this.options.filter(o => o.label.toLowerCase().includes(q));
            },

            async fetchOptions() {
                if (!this.searchUrl) return;
                if (this._abortController) {
                    this._abortController.abort();
                }
                this._abortController = new AbortController();
                this.loading = true;
                try {
                    const url = new URL(this.searchUrl, window.location.href);
                    if (this.query) url.searchParams.set('q', this.query);
                    const res = await fetch(url.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        signal: this._abortController.signal,
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.options = data.results;
                        this.hasMore = data.has_more;
                        this.highlighted = 0;
                    }
                } catch (e) {
                    if (e.name !== 'AbortError') { /* silently fail */ }
                } finally {
                    this.loading = false;
                }
            },

            debouncedFetch() {
                clearTimeout(this._debounceTimer);
                this._debounceTimer = setTimeout(() => this.fetchOptions(), 250);
            },

            onFocus() {
                this.open = true;
                if (this.searchUrl && this.options.length === 0) {
                    this.fetchOptions();
                }
            },

            onInput() {
                this.open = true;
                this.selectedId = '';
                this.highlighted = 0;
                if (this.searchUrl) {
                    this.debouncedFetch();
                }
            },

            select(opt) {
                this.selectedId = String(opt.id);
                this.query = opt.label;
                this.open = false;
                this.$dispatch('autocomplete-selected', { name: this.fieldName, option: opt });
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
                if (this.searchUrl) {
                    this.options = [];
                    this.hasMore = false;
                }
                this.$refs.searchInput.focus();
            },

            close() {
                this.open = false;
                if (!this.selectedId) {
                    this.query = '';
                }
            },

            openCreateTab() {
                if (!this.createUrl) return;
                const url = new URL(this.createUrl, window.location.href);
                url.searchParams.set('popup', '1');
                const popup = window.open(url.toString(), '_blank');
                if (!popup) return;
                const poll = setInterval(() => {
                    if (popup.closed) {
                        clearInterval(poll);
                        if (this.searchUrl) {
                            this.fetchOptions();
                        } else if (this.refreshUrl) {
                            this.refreshOptions();
                        }
                    }
                }, 500);
            },

            async refreshOptions() {
                this.refreshing = true;
                this.open = true;
                try {
                    const res = await fetch(this.refreshUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (res.ok) {
                        this.options = await res.json();
                        this.$refs.searchInput.focus();
                    }
                } catch (_) {
                    // silently fail
                } finally {
                    this.refreshing = false;
                }
            },
        };
    };
</script>
