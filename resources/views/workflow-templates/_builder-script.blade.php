<script>
function stageBuilder(allStages, initialSelectedIds) {
    return {
        allStages: allStages,
        selectedStages: [],
        dragIndex: null,
        dragOverIndex: null,

        init() {
            if (initialSelectedIds.length > 0) {
                const stageMap = Object.fromEntries(allStages.map(s => [s.id, s]));
                this.selectedStages = initialSelectedIds
                    .map(id => stageMap[id])
                    .filter(Boolean);
            } else {
                const first = allStages.find(s => s.is_locked_first);
                const last = allStages.find(s => s.is_locked_last);
                this.selectedStages = [first, last].filter(Boolean);
            }
        },

        get availableStages() {
            const selectedIds = new Set(this.selectedStages.map(s => s.id));
            return this.allStages.filter(s => !selectedIds.has(s.id) && !s.is_locked_first && !s.is_locked_last);
        },

        add(stageId) {
            const stage = this.allStages.find(s => s.id === stageId);
            if (!stage) { return; }
            this.selectedStages.splice(this.selectedStages.length - 1, 0, stage);
        },

        remove(stageId) {
            this.selectedStages = this.selectedStages.filter(s => s.id !== stageId);
        },

        onDragStart(event, index) {
            const stage = this.selectedStages[index];
            if (stage.is_locked_first || stage.is_locked_last) {
                event.preventDefault();
                return;
            }
            this.dragIndex = index;
            event.dataTransfer.effectAllowed = 'move';
        },

        onDragOver(event, index) {
            if (this.dragIndex === null || this.dragIndex === index) { return; }
            const target = this.selectedStages[index];
            if (target.is_locked_first || target.is_locked_last) { return; }
            this.dragOverIndex = index;
        },

        onDrop(event, dropIndex) {
            if (this.dragIndex === null || this.dragIndex === dropIndex) { return; }
            const target = this.selectedStages[dropIndex];
            if (target.is_locked_first || target.is_locked_last) { return; }

            const stages = [...this.selectedStages];
            const [moved] = stages.splice(this.dragIndex, 1);
            stages.splice(dropIndex, 0, moved);
            this.selectedStages = stages;
            this.dragIndex = null;
            this.dragOverIndex = null;
        },

        prepareSubmit() {
            const container = document.getElementById('stage-inputs');
            container.innerHTML = '';
            this.selectedStages.forEach(stage => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'stages[]';
                input.value = stage.id;
                container.appendChild(input);
            });
        },
    };
}
</script>
