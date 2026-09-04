<template>
    <div class="diagram-viewport">
        <div class="diagram-canvas" :style="canvasStyle">
            <svg class="diagram-lines" :viewBox="`0 0 ${layout.width} ${layout.height}`" aria-hidden="true">
                <defs>
                    <marker id="diagram-arrow" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto" markerUnits="strokeWidth">
                        <path d="M0,0 L0,6 L9,3 z" fill="#008060" />
                    </marker>
                </defs>

                <g v-for="branch in layout.branches" :key="branch.parentId">
                    <path :d="branch.trunk" class="diagram-line diagram-trunk" />
                    <path v-if="branch.bus" :d="branch.bus" class="diagram-line diagram-bus" />
                    <path
                        v-for="child in branch.children"
                        :key="child.id"
                        :d="child.path"
                        class="diagram-line"
                        marker-end="url(#diagram-arrow)"
                    />
                </g>
            </svg>

            <div
                v-for="level in layout.levels"
                :key="level.index"
                class="diagram-level-label"
                :style="{ top: `${level.y + 28}px` }"
            >
                Уровень {{ level.index + 1 }}
            </div>

            <button
                v-for="node in layout.nodes"
                :key="node.id"
                type="button"
                class="diagram-node"
                :class="{ 'diagram-node-selected': selectedId === node.id }"
                :style="{ left: `${node.x}px`, top: `${node.y}px` }"
                @click="selectNode(node)"
            >
                <span class="diagram-node-port diagram-node-port-top"></span>
                <svg class="diagram-node-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V7.5A1.5 1.5 0 0 1 5.5 6h13A1.5 1.5 0 0 1 20 7.5V20M2 20h20M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01" /></svg>
                <span class="diagram-node-title">{{ node.title }}</span>
                <span class="diagram-node-meta">{{ vacancyLabel(node.vacancies_count) }} найдено</span>
                <span v-if="node.children.length" class="diagram-node-port diagram-node-port-bottom"></span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    nodes: { type: Array, required: true },
    selectedId: { type: Number, default: null },
});

const emit = defineEmits(['select', 'show-details']);
const vacancyLabel = (value) => {
    const count = Number(value) || 0;
    const mod10 = count % 10;
    const mod100 = count % 100;
    const word = mod10 === 1 && mod100 !== 11 ? 'вакансия' : (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14) ? 'вакансии' : 'вакансий');
    return `${count} ${word}`;
};
const CARD_WIDTH = 142;
const CARD_HEIGHT = 90;
const SIBLING_GAP = 28;
const LEVEL_GAP = 92;
const LEFT_PADDING = 100;
const TOP_PADDING = 28;

const layout = computed(() => {
    const source = new Map(props.nodes.map(node => [node.id, { ...node, children: [] }]));
    const roots = [];

    source.forEach(node => {
        const parent = source.get(node.parent_id);
        if (parent) parent.children.push(node);
        else roots.push(node);
    });
    source.forEach(node => node.children.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0)));

    const minLevel = Math.min(...props.nodes.map(node => Number(node.level) || 0), 0);
    const cursor = { value: 0 };
    const positioned = [];
    const place = (node) => {
        const y = TOP_PADDING + ((Number(node.level) || 0) - minLevel) * (CARD_HEIGHT + LEVEL_GAP);
        let x;
        if (node.children.length) {
            const childXs = node.children.map(place);
            x = (childXs[0] + childXs[childXs.length - 1]) / 2;
        } else {
            x = LEFT_PADDING + cursor.value * (CARD_WIDTH + SIBLING_GAP) + CARD_WIDTH / 2;
            cursor.value += 1;
        }
        const positionedNode = { ...node, x, y };
        positioned.push(positionedNode);
        return x;
    };
    roots.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0)).forEach(place);

    const byId = new Map(positioned.map(node => [node.id, node]));
    const branches = positioned
        .filter(node => node.children.length)
        .map(parent => {
            const children = parent.children.map(child => byId.get(child.id));
            const startY = parent.y + CARD_HEIGHT + 6;
            const childTop = Math.min(...children.map(child => child.y));
            const busY = childTop - 28;
            const minX = Math.min(...children.map(child => child.x));
            const maxX = Math.max(...children.map(child => child.x));

            return {
                parentId: parent.id,
                trunk: `M ${parent.x} ${startY} V ${busY}`,
                bus: children.length > 1 ? `M ${minX} ${busY} H ${maxX}` : null,
                children: children.map(child => ({
                    id: child.id,
                    path: `M ${child.x} ${busY} V ${child.y - 9}`,
                })),
            };
        });

    const distinctLevels = [...new Set(positioned.map(node => Number(node.level) || 0))].sort((a, b) => a - b);
    const width = Math.max(760, LEFT_PADDING * 2 + Math.max(cursor.value, 1) * (CARD_WIDTH + SIBLING_GAP));
    const height = Math.max(250, TOP_PADDING + distinctLevels.length * (CARD_HEIGHT + LEVEL_GAP) - LEVEL_GAP + 36);

    return {
        width,
        height,
        nodes: positioned,
        branches,
        levels: distinctLevels.map((level, index) => ({
            index,
            y: TOP_PADDING + (level - minLevel) * (CARD_HEIGHT + LEVEL_GAP),
        })),
    };
});

const canvasStyle = computed(() => ({ width: `${layout.value.width}px`, height: `${layout.value.height}px` }));

const selectNode = (node) => {
    emit('select', node);
    emit('show-details', node);
};

</script>

<style scoped>
.diagram-viewport { overflow: auto; padding: 4px 0 12px; border-radius: 12px; background: #f6f6f7; }
.diagram-canvas { position: relative; margin: 0 auto; }
.diagram-lines { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; overflow: visible; }
.diagram-line { fill: none; stroke: #008060; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; }
.diagram-trunk { stroke: #006e52; stroke-width: 3; }
.diagram-bus { stroke: #008060; stroke-width: 3; }
.diagram-level-label { position: absolute; left: 12px; z-index: 2; width: 72px; color: #6d7175; font-size: .72rem; font-weight: 700; line-height: 1.1; }
.diagram-node { position: absolute; z-index: 3; width: 142px; height: 90px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; padding: 10px; border: 1px solid #d2d5d8; border-radius: 10px; background: #ffffff; box-shadow: 0 2px 5px rgba(0,0,0,.08); color: #202223; cursor: pointer; transform: translateX(-50%); transition: transform .2s, border-color .2s, box-shadow .2s; }
.diagram-node:hover { transform: translateX(-50%) translateY(-3px); border-color: #008060; box-shadow: 0 6px 14px rgba(0,128,96,.14); }
.diagram-node-selected { border-color: #008060; box-shadow: 0 0 0 3px rgba(0,128,96,.16), 0 6px 14px rgba(0,128,96,.14); }
.diagram-node-icon { width: 18px; height: 18px; fill: none; stroke: #008060; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
.diagram-node-title { display: -webkit-box; overflow: hidden; text-align: center; font-size: .8rem; font-weight: 700; line-height: 1.15; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.diagram-node-meta { color: #6d7175; font-size: .72rem; }
.diagram-node-port { position: absolute; left: 50%; width: 10px; height: 10px; border: 2px solid #ffffff; border-radius: 50%; background: #008060; transform: translateX(-50%); box-shadow: 0 0 0 2px #008060; }
.diagram-node-port-top { top: -6px; }
.diagram-node-port-bottom { bottom: -6px; }
</style>
