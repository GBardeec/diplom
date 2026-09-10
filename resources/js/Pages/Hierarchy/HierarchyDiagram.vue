<template>
    <div class="diagram-viewport">
        <div class="diagram-canvas" :style="canvasStyle">
            <svg class="diagram-lines" :viewBox="`0 0 ${layout.width} ${layout.height}`" aria-hidden="true">
                <defs>
                    <marker id="diagram-arrow" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto" markerUnits="strokeWidth"><path d="M0,0 L0,6 L9,3 z" fill="#008060" /></marker>
                </defs>
                <path v-for="arrow in layout.levelArrows" :key="arrow.from" :d="arrow.path" class="diagram-line" marker-end="url(#diagram-arrow)" />
            </svg>
            <div v-for="level in layout.levels" :key="`frame-${level.number}`" class="diagram-level-frame" :style="{ left: `${level.x}px`, top: `${level.frameY}px`, width: `${level.width}px`, height: `${level.height}px` }"></div>
            <div v-for="level in layout.levels" :key="level.number" class="diagram-level-label" :style="{ top: `${level.y + 8}px` }">Уровень {{ level.number }}</div>
            <button v-for="node in layout.nodes" :key="node.id" type="button" class="diagram-node" :class="{ 'diagram-node-selected': selectedId === node.id }" :style="{ left: `${node.x}px`, top: `${node.y}px` }" @click="selectNode(node)">
                <span class="diagram-node-title">{{ node.title }}</span>
                <span class="diagram-node-meta">Медиана {{ formatSalary(node.market_salary_median) }}</span>
                <span class="diagram-node-sample">{{ node.market_salary_sample_size }} вакансий с зарплатой</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({ nodes: { type: Array, required: true }, selectedId: { type: Number, default: null } });
const emit = defineEmits(['select', 'show-details']);
const CARD_WIDTH = 166;
const CARD_HEIGHT = 98;
const NODE_GAP = 32;
const LEVEL_GAP = 72;
const LEFT_PADDING = 106;
const TOP_PADDING = 28;
const formatSalary = value => new Intl.NumberFormat('ru-RU').format(value) + ' ₽';

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
    return { width, height: TOP_PADDING + maxLevel * CARD_HEIGHT + Math.max(0, maxLevel - 1) * LEVEL_GAP + 28, nodes: positioned, levelArrows, levels };
});
const canvasStyle = computed(() => ({ width: `${layout.value.width}px`, height: `${layout.value.height}px` }));
const selectNode = node => { emit('select', node); emit('show-details', node); };
</script>

<style scoped>
.diagram-viewport { overflow: auto; padding: 4px 0 12px; border-radius: 12px; background: #f6f6f7; }
.diagram-canvas { position: relative; margin: 0 auto; }
.diagram-lines { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; overflow: visible; }
.diagram-line { fill: none; stroke: #008060; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; opacity: .82; }
.diagram-level-frame { position: absolute; z-index: 1; border: 1px solid #dfe3e0; border-radius: 14px; background: #ffffff80; }
.diagram-level-label { position: absolute; left: 12px; z-index: 2; width: 78px; color: #6d7175; font-size: .72rem; font-weight: 700; line-height: 1.1; }
.diagram-node { position: absolute; z-index: 3; width: 166px; height: 98px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; padding: 10px; border: 1px solid #d2d5d8; border-radius: 10px; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,.08); color: #202223; cursor: pointer; transform: translateX(-50%); transition: transform .2s, border-color .2s, box-shadow .2s; }
.diagram-node:hover { transform: translateX(-50%) translateY(-3px); border-color: #008060; box-shadow: 0 6px 14px rgba(0,128,96,.14); }
.diagram-node-selected { border-color: #008060; box-shadow: 0 0 0 3px rgba(0,128,96,.16), 0 6px 14px rgba(0,128,96,.14); }
.diagram-node-title { display: -webkit-box; overflow: hidden; text-align: center; font-size: .82rem; font-weight: 700; line-height: 1.15; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.diagram-node-meta { color: #006e52; font-size: .72rem; font-weight: 700; }
.diagram-node-sample { color: #6d7175; font-size: .67rem; }
</style>
