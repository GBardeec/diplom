<template>
  <p v-if="error" class="rounded border border-red-200 bg-red-50 p-4 text-red-800">{{ error }}</p>
  <p v-else-if="!result" class="rounded-xl border border-dashed border-[#c9cccf] bg-white p-10 text-center text-[#6d7175]">Загружаем сохранённый отчёт…</p>
  <template v-else>
    <div class="notice"><p>После закрытия вкладки введённые данные не сохраняются в браузере. Сохраните ссылку на готовый отчёт, если захотите вернуться к нему позже.</p><div class="mt-3 flex gap-2"><input readonly :value="result.report_url" class="field min-w-0 flex-1"/><button type="button" class="copy" @click="$emit('copy')">{{ copied ? 'Скопировано' : 'Копировать ссылку' }}</button></div></div>
    <div class="mt-5 grid gap-4 sm:grid-cols-3"><article class="card"><p>Текущий уровень</p><b>{{ result.profile.current_level }}</b><small>{{ result.profile.basis }}</small></article><article class="card"><p>Навыков в профиле</p><b>{{ result.profile.skills_count }}</b><small>Чем точнее набор, тем точнее маршрут.</small></article><article class="card"><p>Следующий уровень</p><b>{{ result.growth.next_level || 'Верхний уровень' }}</b><small>Ориентир для развития.</small></article></div>
    <article v-if="careerPath.length" class="panel"><h2>Ваш возможный путь</h2><CareerPath :nodes="careerPath" /></article>
    <article class="panel"><h2>Что уже доступно</h2><div class="mt-4 grid gap-3 sm:grid-cols-3"><div v-for="item in result.opportunities" :key="item.title" class="inner"><b>{{ item.title }}</b><p>{{ item.group }}</p><strong>{{ item.fit }}% соответствие профилю</strong></div></div></article>
    <article class="panel"><h2>Что подтянуть</h2><div class="mt-3 flex flex-wrap gap-2"><span v-for="item in result.growth.skills_to_build" :key="item" class="tag">{{ item }}</span></div></article>
    <article class="panel"><h2>План следующего шага</h2><ol class="mt-4 space-y-3"><li v-for="(item, index) in result.roadmap" :key="item.title"><b>{{ index + 1 }}. {{ item.title }}</b><p>{{ item.text }}</p></li></ol></article>
  </template>
</template>

<script setup>
import CareerPath from '@/Components/CareerPath.vue';

defineProps({ result: Object, error: String, careerPath: { type: Array, default: () => [] }, copied: Boolean });
defineEmits(['copy']);
</script>

<style scoped>
.field{width:100%;border:1px solid #8c9196;border-radius:.375rem;background:#fff;padding:.65rem .75rem;font-size:.875rem}.notice{border:1px solid #f1d59a;border-radius:.5rem;background:#fff8db;padding:1rem;font-size:.875rem;color:#6d5600}.copy{border:1px solid #b18a24;border-radius:.375rem;background:#fff;padding:.4rem .7rem;font-size:.75rem;font-weight:600;white-space:nowrap}.card,.panel{border:1px solid #e1e3e5;border-radius:.75rem;background:#fff;padding:1.25rem;box-shadow:0 1px 2px #0000000d}.card p,.card small,.inner p,.panel li p{color:#6d7175;font-size:.8rem}.card b{display:block;margin:.4rem 0;font-size:1.35rem}.panel{margin-top:1.25rem}.panel h2{font-size:1.1rem;font-weight:600}.inner{border:1px solid #e1e3e5;border-radius:.5rem;padding:1rem}.inner strong{display:block;margin-top:.75rem;color:#008060;font-size:.8rem}.tag{border-radius:999px;background:#e3f1df;padding:.35rem .65rem;color:#006e52;font-size:.8rem}
</style>
