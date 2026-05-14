<script>
function workflowBuilder(allStages, initialEnabledIds) {
    const initialIds = initialEnabledIds.map(id => parseInt(id));

    // Build ordered list: enabled stages in their saved order, then disabled stages in default order
    const enabledMap = new Map(initialIds.map((id, idx) => [id, idx]));
    const stagesWithState = allStages.map(stage => ({
        ...stage,
        id: parseInt(stage.id),
        enabled: enabledMap.has(parseInt(stage.id)),
    }));

    // Sort: enabled first (by saved position), then disabled (by default_order)
    stagesWithState.sort((a, b) => {
        if (a.enabled && b.enabled) {
            return enabledMap.get(a.id) - enabledMap.get(b.id);
        }
        if (a.enabled) return -1;
        if (b.enabled) return 1;
        return a.default_order - b.default_order;
    });

    return {
        orderedStages: stagesWithState,
        dragStage: null,

        get enabledCount() {
            return this.orderedStages.filter(s => s.enabled).length;
        },

        get orderedEnabledIds() {
            return this.orderedStages.filter(s => s.enabled).map(s => s.id);
        },

        toggleStage(stage) {
            if (stage.is_locked_first || stage.is_locked_last) return;

            stage.enabled = !stage.enabled;

            if (!stage.enabled) {
                // Move disabled stage to end of list
                const idx = this.orderedStages.indexOf(stage);
                this.orderedStages.splice(idx, 1);
                this.orderedStages.push(stage);
            } else {
                // Move newly enabled stage before disabled stages (but before locked-last)
                const idx = this.orderedStages.indexOf(stage);
                this.orderedStages.splice(idx, 1);

                const lockedLastIdx = this.orderedStages.findIndex(s => s.is_locked_last);
                const insertBefore = lockedLastIdx !== -1 ? lockedLastIdx : this.orderedStages.findIndex(s => !s.enabled);
                const insertAt = insertBefore === -1 ? this.orderedStages.length : insertBefore;
                this.orderedStages.splice(insertAt, 0, stage);
            }
        },

        onDragStart(event, stage) {
            this.dragStage = stage;
            event.dataTransfer.effectAllowed = 'move';
        },

        onDragOver(event, targetStage) {
            if (!this.dragStage || this.dragStage.id === targetStage.id) return;
            if (!targetStage.enabled || targetStage.is_locked_first || targetStage.is_locked_last) return;

            event.currentTarget.classList.add('ring-2', 'ring-primary/40');
        },

        onDragLeave(event) {
            event.currentTarget.classList.remove('ring-2', 'ring-primary/40');
        },

        onDrop(event, targetStage) {
            event.currentTarget.classList.remove('ring-2', 'ring-primary/40');

            if (!this.dragStage || this.dragStage.id === targetStage.id) return;
            if (!targetStage.enabled || targetStage.is_locked_first || targetStage.is_locked_last) return;
            if (this.dragStage.is_locked_first || this.dragStage.is_locked_last) return;

            const fromIdx = this.orderedStages.findIndex(s => s.id === this.dragStage.id);
            const toIdx = this.orderedStages.findIndex(s => s.id === targetStage.id);

            if (fromIdx === -1 || toIdx === -1) return;

            this.orderedStages.splice(fromIdx, 1);
            this.orderedStages.splice(toIdx, 0, this.dragStage);
        },

        onDragEnd() {
            this.dragStage = null;
            document.querySelectorAll('.ring-2').forEach(el => el.classList.remove('ring-2', 'ring-primary/40'));
        },

        prepareSubmit() {
            // Hidden inputs already bound via x-for — nothing extra needed
        },
    };
}
</script>
