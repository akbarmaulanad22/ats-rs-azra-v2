<div class="px-4 py-5">
    <div class="flex items-center justify-between mb-3">
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

    <div class="space-y-1.5">
        <template x-for="(item, index) in items" :key="index">
            <div
                class="flex items-center gap-2 px-3 py-2 bg-primary/5 border border-primary/20 rounded-lg transition-colors cursor-grab active:cursor-grabbing"
                :class="dragOverIndex === index && dragIndex !== index ? 'border-primary border-dashed bg-primary/10' : ''"
                draggable="true"
                @dragstart="onDragStart($event, index)"
                @dragover.prevent="onDragOver($event, index)"
                @dragleave="dragOverIndex = null"
                @drop.prevent="onDrop($event, index)"
                @dragend="dragIndex = null; dragOverIndex = null"
            >
                <span class="text-gray-300 shrink-0" title="Seret untuk mengatur ulang">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8 6a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm8-16a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4zm0 8a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                </span>

                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary/10 text-primary text-[10px] font-bold shrink-0" x-text="index + 1"></span>

                <input type="text" x-model="item.teks" placeholder="Teks item..."
                    class="flex-1 text-xs border border-gray-200 rounded px-2.5 py-1.5 bg-white focus-ring">

                <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                    class="p-1 rounded text-red-400/60 hover:text-red-500 hover:bg-red-50 transition-colors cursor-pointer shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>

<div id="hidden-fields"></div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('itemForm', (initialItems) => ({
            items: initialItems && initialItems.length ? initialItems : [{ id: null, teks: '' }],
            dragIndex: null,
            dragOverIndex: null,

            addItem() {
                this.items.push({ id: null, teks: '' });
            },

            removeItem(index) {
                if (this.items.length <= 1) { return; }
                this.items.splice(index, 1);
            },

            onDragStart(event, index) {
                this.dragIndex = index;
                event.dataTransfer.effectAllowed = 'move';
            },

            onDragOver(event, index) {
                if (this.dragIndex === null || this.dragIndex === index) { return; }
                this.dragOverIndex = index;
            },

            onDrop(event, dropIndex) {
                if (this.dragIndex === null || this.dragIndex === dropIndex) { return; }
                const items = [...this.items];
                const [moved] = items.splice(this.dragIndex, 1);
                items.splice(dropIndex, 0, moved);
                this.items = items;
                this.dragIndex = null;
                this.dragOverIndex = null;
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
