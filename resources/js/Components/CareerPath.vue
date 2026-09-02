<template>
    <div class="career-path">
        <svg class="career-lines" viewBox="0 0 1000 32" preserveAspectRatio="none" aria-hidden="true">
            <defs><marker id="career-arrow" markerWidth="10" markerHeight="10" refX="8" refY="3" orient="auto"><path d="M0,0 L0,6 L9,3 z" fill="#008060" /></marker></defs>
            <line v-for="index in nodes.length - 1" :key="index" :x1="point(index - 1)" y1="7" :x2="point(index) - 10" y2="7" stroke="#008060" stroke-width="2" marker-end="url(#career-arrow)" />
        </svg>
        <div class="career-steps" :style="{ gridTemplateColumns: `repeat(${nodes.length}, minmax(0, 1fr))` }">
            <div v-for="node in [...nodes].reverse()" :key="node.id" class="career-step"><span class="career-dot"></span><span class="career-title">{{ node.title }}</span></div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({ nodes: { type: Array, default: () => [] } });
const point = (index) => ((index + 0.5) / props.nodes.length) * 1000;
</script>

<style scoped>
.career-path { position:relative; overflow-x:auto; padding:1rem .5rem .25rem; }.career-lines { position:absolute; top:1rem; left:.5rem; width:calc(100% - 1rem); height:32px; }.career-steps { position:relative; display:grid; min-width:420px; }.career-step { display:flex; flex-direction:column; align-items:center; gap:.65rem; text-align:center; color:#4a4f54; font-size:.85rem; }.career-dot { z-index:1; width:.9rem; height:.9rem; border:3px solid #fff; border-radius:50%; background:#008060; box-shadow:0 0 0 1px #008060; }.career-title { max-width:10rem; line-height:1.25; }
</style>
