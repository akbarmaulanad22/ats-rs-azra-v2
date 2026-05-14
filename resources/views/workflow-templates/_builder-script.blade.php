<script>
function stageBuilder(allStages, initialSelectedIds) {
    return {
        allStages: allStages,
        selectedStages: [],

        init() {
            // If editing, use the saved order; else default to Application + Onboarding only.
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
            // Insert before the last (Onboarding)
            this.selectedStages.splice(this.selectedStages.length - 1, 0, stage);
        },

        remove(stageId) {
            this.selectedStages = this.selectedStages.filter(s => s.id !== stageId);
        },

        moveUp(index) {
            if (index <= 1) { return; }
            [this.selectedStages[index - 1], this.selectedStages[index]] =
                [this.selectedStages[index], this.selectedStages[index - 1]];
            this.selectedStages = [...this.selectedStages];
        },

        moveDown(index) {
            if (index >= this.selectedStages.length - 2) { return; }
            [this.selectedStages[index], this.selectedStages[index + 1]] =
                [this.selectedStages[index + 1], this.selectedStages[index]];
            this.selectedStages = [...this.selectedStages];
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
