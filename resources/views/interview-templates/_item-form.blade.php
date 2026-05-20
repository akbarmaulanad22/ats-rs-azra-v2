<div class="bg-white/80 border border-gray-200 rounded-md overflow-hidden mb-4">
    <div class="px-4 py-3 bg-gray-200/90 border-b border-gray-200 flex items-center justify-between">
        <div>
            <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Daftar Item</p>
            <p class="text-[10px] text-gray-400 mt-0.5"><span x-text="items.length"></span> item</p>
        </div>
        <button type="button" @click="addItem()"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium border border-gray-300 text-gray-600 rounded bg-white hover:bg-gray-50 transition-colors ease-out duration-150">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Item
        </button>
    </div>

    <div class="divide-y divide-gray-100 px-4">
        <template x-for="(item, index) in items" :key="index">
            <div class="flex items-center gap-3 py-2.5">
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary/10 text-primary text-[10px] font-bold shrink-0" x-text="index + 1"></span>
                <input type="text" x-model="item.teks" placeholder="Teks item..."
                    class="flex-1 text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">
                <div class="flex items-center gap-1">
                    <button type="button" @click="moveUp(index)" x-show="index > 0"
                        class="p-1 text-gray-400 hover:text-primary rounded transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <button type="button" @click="moveDown(index)" x-show="index < items.length - 1"
                        class="p-1 text-gray-400 hover:text-primary rounded transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                        class="p-1 text-red-400 hover:text-red-600 rounded transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
</div>

<div id="hidden-fields"></div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('itemForm', (initialItems) => ({
            items: initialItems && initialItems.length ? initialItems : [{ id: null, teks: '' }],
            addItem() {
                this.items.push({ id: null, teks: '' });
            },
            removeItem(index) {
                if (this.items.length <= 1) return;
                this.items.splice(index, 1);
            },
            moveUp(index) {
                if (index <= 0) return;
                [this.items[index - 1], this.items[index]] = [this.items[index], this.items[index - 1]];
            },
            moveDown(index) {
                if (index >= this.items.length - 1) return;
                [this.items[index], this.items[index + 1]] = [this.items[index + 1], this.items[index]];
            },
            prepareSubmit(event) {
                const container = document.getElementById('hidden-fields');
                container.innerHTML = '';
                this.items.forEach((item, i) => {
                    if (item.id) {
                        this.addHidden(container, `items[${i}][id]`, item.id);
                    }
                    this.addHidden(container, `items[${i}][teks]`, item.teks);
                });
            },
            addHidden(container, name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                container.appendChild(input);
            },
        }));
    });
</script>
