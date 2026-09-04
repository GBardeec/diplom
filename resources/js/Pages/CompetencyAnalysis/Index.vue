<template>
    <AppLayout>
        <div class="flex-1 px-4 py-10 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-3xl">
                <div class="mb-8"><p class="text-sm font-semibold text-[#008060]">Оценка компетенций</p><h1 class="mt-2 text-3xl font-semibold tracking-tight">Расскажите о своём опыте</h1><p class="mt-2 text-[#616161]">Это займёт пару минут и поможет зафиксировать текущий уровень.</p></div>
                <div class="rounded-xl border border-[#e1e3e5] bg-white shadow-sm">
                    <form @submit.prevent="submitForm" class="space-y-7 p-6 sm:p-8">
                        <div><label class="mb-2 block text-sm font-medium">Направление</label><input v-model="form.subject" required type="text" class="w-full rounded-md border border-[#8c9196] px-3 py-2 text-sm outline-none focus:border-[#008060] focus:ring-1 focus:ring-[#008060]" placeholder="Например, backend-разработка" /></div>
                        <div><div class="mb-3 flex items-center justify-between"><label class="text-sm font-medium">Уровень владения</label><span class="rounded bg-[#e3f1df] px-2 py-1 text-sm font-semibold text-[#006e52]">{{ form.rating }} / 10</span></div><input v-model.number="form.rating" class="range-input w-full" type="range" min="1" max="10" /><div class="mt-2 flex justify-between text-xs text-[#6d7175]"><span>Начинающий</span><span>Эксперт</span></div></div>
                        <div><label class="mb-2 block text-sm font-medium">Ключевые навыки</label><textarea v-model="form.skills" required rows="4" class="w-full resize-none rounded-md border border-[#8c9196] px-3 py-2 text-sm outline-none focus:border-[#008060] focus:ring-1 focus:ring-[#008060]" placeholder="PHP, Laravel, Vue.js, Docker"></textarea><p class="mt-2 text-xs text-[#6d7175]">Перечислите навыки через запятую.</p></div>
                        <button type="submit" :disabled="processing" class="rounded-md bg-[#008060] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#006e52] disabled:opacity-60">{{ processing ? 'Готовим результат…' : 'Сохранить оценку' }}</button>
                    </form>
                </div>
                <div v-if="submittedData" class="mt-6 rounded-xl border border-[#b7d9c8] bg-[#f1f8f5] p-6"><p class="text-sm font-semibold text-[#006e52]">Оценка сохранена</p><h2 class="mt-2 text-xl font-semibold">{{ submittedData.subject }} - {{ submittedData.rating }}/10</h2><p class="mt-2 text-sm text-[#4a4f54]">{{ getRecommendations(submittedData.rating) }}</p><div class="mt-4 flex flex-wrap gap-2"><span v-for="skill in submittedData.skills.split(',')" :key="skill" class="rounded-full bg-white px-3 py-1 text-sm text-[#4a4f54] shadow-sm">{{ skill.trim() }}</span></div></div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = ref({ subject: '', rating: 5, skills: '' });
const processing = ref(false);
const submittedData = ref(null);
const submitForm = () => { processing.value = true; setTimeout(() => { submittedData.value = { ...form.value }; processing.value = false; }, 500); };
const getRecommendations = (rating) => rating <= 3 ? 'Начните с базы и небольшой практики на реальных задачах.' : rating <= 6 ? 'Хорошая основа: углубляйтесь в архитектуру и системный подход.' : rating <= 8 ? 'Сильный уровень: берите больше ответственности и делитесь опытом.' : 'Экспертный уровень: развивайте техническое лидерство и стратегическое мышление.';
</script>

<style scoped>
.range-input { accent-color: #008060; }
</style>
