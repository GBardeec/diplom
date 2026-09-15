<template>
    <div class="diagram-shell">
        <Transition name="hint">
            <aside v-if="isHintVisible" class="diagram-hint" aria-live="polite">
                <template v-if="activeTransitions.length">
                    <b>Переходы из роли «{{ activeNode?.title }}»</b>
                    <div class="transition-list">
                        <div v-for="transition in activeTransitions" :key="`${transition.from_category_id}-${transition.to_category_id}`" class="transition-item">
                            <span class="transition-target">{{ nodeById.get(transition.to_category_id)?.title }}</span>
                            <span v-if="transition.common_skills.length" class="transition-common">Уже общее: {{ transition.common_skills.map(skill => skill.title).join(', ') }}</span>
                            <span v-else class="transition-common">Общих навыков в вакансиях почти нет.</span>
                            <span v-if="transition.missing_skills.length" class="transition-skills">Стоит добавить: {{ transition.missing_skills.map(skill => `${skill.title} (${skill.percent}%)`).join(', ') }}</span>
                            <span v-else class="transition-skills">Явных недостающих навыков не найдено.</span>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <b>Как пользоваться схемой</b>
                    <p>Наведите курсор на роль, чтобы увидеть возможные переходы и навыки для них.</p>
                </template>
                <button type="button" class="diagram-hint-close" aria-label="Закрыть подсказку" @click="dismissHint">×</button>
            </aside>
        </Transition>
        <button type="button" class="diagram-hint-button" aria-label="Показать подсказку по схеме" @click="showHint">?</button>
        <div class="diagram-viewport">
            <div class="diagram-canvas" :style="canvasStyle">
            <svg class="diagram-lines" :viewBox="`0 0 ${layout.width} ${layout.height}`" aria-hidden="true">
                <defs>
                    <marker id="diagram-arrow" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto" markerUnits="strokeWidth"><path d="M0,0 L0,6 L9,3 z" fill="#008060" /></marker>
                </defs>
                <template v-if="!activeTransitions.length">
                    <path v-for="arrow in layout.levelArrows" :key="arrow.from" :d="arrow.path" class="diagram-line diagram-line-muted" marker-end="url(#diagram-arrow)" />
                </template>
                <path v-for="arrow in layout.transitionArrows" :key="`${arrow.from}-${arrow.to}`" :d="arrow.path" class="diagram-line diagram-transition-line" marker-end="url(#diagram-arrow)" />
            </svg>
            <div v-for="level in layout.levels" :key="`frame-${level.number}`" class="diagram-level-frame" :style="{ left: `${level.x}px`, top: `${level.frameY}px`, width: `${level.width}px`, height: `${level.height}px` }"></div>
            <div v-for="level in layout.levels" :key="level.number" class="diagram-level-label" :style="{ top: `${level.y + 8}px` }">Уровень {{ level.number }}</div>
            <button v-for="node in layout.nodes" :key="node.id" type="button" class="diagram-node" :class="{ 'diagram-node-selected': activeNodeId === node.id, 'diagram-node-target': transitionTargetIds.has(node.id) }" :style="{ left: `${node.x}px`, top: `${node.y}px` }" @mouseenter="showNodeTransitions(node.id)" @mouseleave="hideNodeTransitions" @click="selectNode(node)">
                <span class="diagram-node-title">{{ node.title }}</span>
                <span class="diagram-node-meta">Медиана {{ formatSalary(node.market_salary_median) }}</span>
                <span class="diagram-node-sample">{{ node.market_salary_sample_size }} вакансий с зарплатой</span>
            </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps({ nodes: { type: Array, required: true }, transitions: { type: Array, default: () => [] }, selectedId: { type: Number, default: null } });
const emit = defineEmits(['select', 'show-details']);
const hoveredId = ref(null);
const isHintVisible = ref(true);
let hintTimer = null;
const CARD_WIDTH = 166;
const CARD_HEIGHT = 98;
const NODE_GAP = 32;
const LEVEL_GAP = 72;
const LEFT_PADDING = 106;
const TOP_PADDING = 28;
const formatSalary = value => new Intl.NumberFormat('ru-RU').format(value) + ' ₽';
const nodeById = computed(() => new Map(props.nodes.map(node => [node.id, node])));
const activeNodeId = computed(() => hoveredId.value ?? props.selectedId);
const activeNode = computed(() => nodeById.value.get(activeNodeId.value) || null);
const activeTransitions = computed(() => props.transitions.filter(transition => Number(transition.from_category_id) === Number(activeNodeId.value)));
const transitionTargetIds = computed(() => new Set(activeTransitions.value.map(transition => transition.to_category_id)));

const layout = computed(() => {
    const byLevel = new Map();
    props.nodes.forEach(node => {
        const level = Number(node.market_level);
        if (!byLevel.has(level)) byLevel.set(level, []);
        byLevel.get(level).push(node);
    });
    const maxCount = Math.max(1, ...[...byLevel.values()].map(nodes => nodes.length));
    const maxLevel = Math.max(1, ...[...byLevel.keys()]);
    const levelNumbers = Array.from({ length: maxLevel }, (_, index) => index + 1);
    const width = Math.max(760, LEFT_PADDING * 2 + maxCount * CARD_WIDTH + Math.max(0, maxCount - 1) * NODE_GAP);
    const positioned = [];
    levelNumbers.forEach((level, index) => {
        const nodes = (byLevel.get(level) || []).sort((a, b) => Number(a.market_salary_median) - Number(b.market_salary_median));
        const rowWidth = nodes.length * CARD_WIDTH + Math.max(0, nodes.length - 1) * NODE_GAP;
        const firstX = (width - rowWidth) / 2 + CARD_WIDTH / 2;
        const y = TOP_PADDING + index * (CARD_HEIGHT + LEVEL_GAP);
        nodes.forEach((node, nodeIndex) => positioned.push({ ...node, x: firstX + nodeIndex * (CARD_WIDTH + NODE_GAP), y }));
    });
    const levels = levelNumbers.map((number, index) => {
        const y = TOP_PADDING + index * (CARD_HEIGHT + LEVEL_GAP);
        return { number, y, x: LEFT_PADDING - 10, width: width - LEFT_PADDING - 14, frameY: y - 10, height: CARD_HEIGHT + 20 };
    });
    const levelArrows = levels.slice(0, -1).map((level, index) => {
        const next = levels[index + 1];
        const x = width / 2;
        return { from: level.number, path: `M ${x} ${level.frameY + level.height} V ${next.frameY}` };
    });
    const positionedById = new Map(positioned.map(node => [node.id, node]));
    const transitionArrows = activeTransitions.value.map(transition => {
        const source = positionedById.get(transition.from_category_id);
        const target = positionedById.get(transition.to_category_id);
        if (!source || !target) return null;
        const sourceY = source.y + CARD_HEIGHT;
        const targetY = target.y;
        const levelDistance = Number(target.market_level) - Number(source.market_level);
        if (levelDistance === 1) {
            const middleY = sourceY + (targetY - sourceY) / 2;
            return { from: source.id, to: target.id, path: `M ${source.x} ${sourceY} V ${middleY} H ${target.x} V ${targetY}` };
        }

        const laneX = width - 26;
        const sourceExitY = sourceY + 16;
        const targetEntryY = targetY - 16;
        return { from: source.id, to: target.id, path: `M ${source.x} ${sourceY} V ${sourceExitY} H ${laneX} V ${targetEntryY} H ${target.x} V ${targetY}` };
    }).filter(Boolean);
    return { width, height: TOP_PADDING + maxLevel * CARD_HEIGHT + Math.max(0, maxLevel - 1) * LEVEL_GAP + 28, nodes: positioned, levelArrows, transitionArrows, levels };
});
const canvasStyle = computed(() => ({ width: `${layout.value.width}px`, height: `${layout.value.height}px` }));
const selectNode = node => { emit('select', node); emit('show-details', node); };
const clearHintTimer = () => {
    if (hintTimer) clearTimeout(hintTimer);
    hintTimer = null;
};
const dismissHint = () => {
    clearHintTimer();
    isHintVisible.value = false;
};
const scheduleHintDismiss = (delay = 5000) => {
    clearHintTimer();
    hintTimer = setTimeout(() => {
        isHintVisible.value = false;
        hintTimer = null;
    }, delay);
};
const showHint = () => {
    isHintVisible.value = true;
    scheduleHintDismiss(7000);
};
const showNodeTransitions = id => {
    hoveredId.value = id;
    isHintVisible.value = true;
    clearHintTimer();
};
const hideNodeTransitions = () => {
    hoveredId.value = null;
    scheduleHintDismiss(900);
};
onMounted(() => scheduleHintDismiss());
onBeforeUnmount(clearHintTimer);
</script>

<style scoped>
.diagram-shell { position: relative; }
.diagram-hint { position: absolute; z-index: 10; top: 14px; right: 14px; width: min(360px, calc(100% - 72px)); max-height: calc(100% - 28px); overflow: auto; border: 1px solid #b7d9ca; border-radius: 10px; background: rgba(255, 255, 255, .97); box-shadow: 0 8px 22px rgba(32, 34, 35, .14); padding: 12px 32px 12px 14px; color: #4a4f54; font-size: .82rem; line-height: 1.45; }
.diagram-hint b { color: #202223; font-size: .9rem; }
.diagram-hint p { margin-top: 3px; }
.diagram-hint-close { position: absolute; top: 6px; right: 8px; color: #6d7175; font-size: 1.25rem; line-height: 1; }
.diagram-hint-close:hover { color: #202223; }
.diagram-hint-button { position: absolute; z-index: 9; top: 14px; right: 14px; display: grid; width: 30px; height: 30px; place-items: center; border: 1px solid #b7d9ca; border-radius: 999px; background: #fff; color: #006e52; font-size: .9rem; font-weight: 700; box-shadow: 0 2px 5px rgba(32, 34, 35, .1); }
.diagram-hint-button:hover { border-color: #008060; background: #f1f8f5; }
.hint-enter-active, .hint-leave-active { transition: opacity .16s ease, transform .16s ease; }
.hint-enter-from, .hint-leave-to { opacity: 0; transform: translateY(-4px); }
.transition-list { display: grid; gap: 7px; margin-top: 10px; }
.transition-item { display: grid; gap: 2px; border-left: 3px solid #008060; padding-left: 9px; }
.transition-target { color: #202223; font-weight: 700; }
.transition-common { color: #4a4f54; }
.transition-skills { color: #006e52; }
.diagram-viewport { overflow: auto; padding: 4px 0 12px; border-radius: 12px; background: #f6f6f7; }
.diagram-canvas { position: relative; margin: 0 auto; }
.diagram-lines { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; overflow: visible; }
.diagram-line { fill: none; stroke: #008060; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; opacity: .82; }
.diagram-line-muted { opacity: .34; }
.diagram-transition-line { stroke-width: 3; opacity: 1; }
.diagram-level-frame { position: absolute; z-index: 1; border: 1px solid #dfe3e0; border-radius: 14px; background: #ffffff80; }
.diagram-level-label { position: absolute; left: 12px; z-index: 2; width: 78px; color: #6d7175; font-size: .72rem; font-weight: 700; line-height: 1.1; }
.diagram-node { position: absolute; z-index: 3; width: 166px; height: 98px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 10px; border: 1px solid #d2d5d8; border-radius: 10px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,.08); color: #202223; cursor: pointer; transform: translateX(-50%); transition: transform .2s, border-color .2s, box-shadow .2s; }
.diagram-node:hover { transform: translateX(-50%) translateY(-3px); border-color: #008060; box-shadow: 0 6px 14px rgba(0,128,96,.14); }
.diagram-node-selected { border-color: #008060; box-shadow: 0 0 0 3px rgba(0,128,96,.16), 0 6px 14px rgba(0,128,96,.14); }
.diagram-node-target { border-color: #5c9f83; background: #f1f8f5; }
.diagram-node-title { display: -webkit-box; overflow: hidden; text-align: center; font-size: .82rem; font-weight: 700; line-height: 1.15; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.diagram-node-meta { color: #006e52; font-size: .72rem; font-weight: 700; }
.diagram-node-sample { color: #6d7175; font-size: .67rem; }
</style>
