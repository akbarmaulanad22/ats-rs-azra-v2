<div class="bg-white rounded-xl border border-gray-100 p-6 mb-4">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">Daftar Item</h2>
            <p class="text-xs text-gray-400 mt-0.5"><span x-text="items.length"></span> item</p>
        </div>
        <button type="button" @click="addItem()"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary border border-primary/30 rounded-lg hover:bg-primary hover:text-white transition-colors ease-out duration-150">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Item
        </button>
    </div>

    <div class="space-y-3">
        <template x-for="(item, index) in items" :key="index">
            <div class="flex items-center gap-3 border border-gray-100 rounded-lg p-3 bg-gray-50/50">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold shrink-0" x-text="index + 1"></span>

                <input type="text" x-model="item.teks" placeholder="Teks item..."
                    class="flex-1 text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary/40">

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
    function itemForm(initialItems) {
        return {
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
                        addHidden(container, `items[${i}][id]`, item.id);
                    }
                    addHidden(container, `items[${i}][teks]`, item.teks);
                });
            },
        };
    }

    function addHidden(container, name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        container.appendChild(input);
    }
</script>
