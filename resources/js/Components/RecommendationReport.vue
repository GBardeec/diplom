<template>
  <p v-if="error" class="rounded border border-red-200 bg-red-50 p-4 text-red-800">{{ error }}</p>
  <p v-else-if="!result" class="rounded-xl border border-dashed border-[#c9cccf] bg-white p-10 text-center text-[#6d7175]">Загружаем сохранённый отчёт…</p>
  <template v-else>
    <div class="notice"><p>После закрытия вкладки введённые данные не сохраняются в браузере. Сохраните ссылку на готовый отчёт, если захотите вернуться к нему позже.</p><div class="mt-3 flex gap-2"><input readonly :value="result.report_url" class="field min-w-0 flex-1"/><button type="button" class="copy" @click="$emit('copy')">{{ copied ? 'Скопировано' : 'Копировать ссылку' }}</button></div></div>
    <div class="mt-5 grid gap-4 sm:grid-cols-3"><article class="card"><p>Текущий уровень</p><b>{{ result.profile.current_level }}</b><small>{{ result.profile.basis }}</small></article><article class="card"><p>Навыков в профиле</p><b>{{ result.profile.skills_count }}</b><small>Чем точнее набор, тем точнее маршрут.</small></article><article class="card"><p>Следующий уровень</p><b>{{ result.growth.next_level || 'Верхний уровень' }}</b><small>Ориентир для развития.</small></article></div>

    <article v-if="result.assessment" class="panel"><h2>Почему этот уровень</h2><p class="summary-text">{{ result.assessment.summary }}</p><div class="mt-4 grid gap-3 sm:grid-cols-2"><div class="insight insight-good"><b>Уже получается</b><ul><li v-for="item in result.assessment.strengths" :key="item">{{ item }}</li></ul></div><div class="insight"><b>Что усилить</b><ul><li v-for="item in result.assessment.focus" :key="item">{{ item }}</li></ul></div></div></article>

    <article v-if="directions.length" class="panel">
      <h2>Подходящие направления</h2>
      <p class="summary-text">Основной вариант выбран по совпадению навыков и достаточному числу вакансий. Грейд пользователя и зарплатный уровень профессии считаются отдельно.</p>
      <div class="mt-4 grid gap-3 sm:grid-cols-3">
        <button v-for="(direction, index) in directions" :key="direction.group_id" type="button" class="direction-card" :class="{ active: activeDirectionIndex === index }" @click="activeDirectionIndex = index">
          <span class="direction-name">{{ index === 0 ? 'Основной вариант' : 'Альтернативный вариант' }}</span>
          <b>{{ direction.group }}</b>
          <small>В анализе {{ direction.sample_size }} вакансий</small>
          <small>{{ direction.matched_skills_count }} из {{ result.profile.skills_count }} навыков встретились в направлении</small>
        </button>
      </div>

      <section v-if="activeDirection" class="direction mt-4">
        <h3>Подробнее: {{ activeDirection.group }}</h3>
        <p class="mt-1 text-sm text-[#616161]">Профессии ниже подходят по выбранным навыкам. Зарплатный уровень показывает положение профессии по зарплатам внутри направления, а не ваш личный грейд.</p>
        <div class="mt-4"><p class="subheading">Подходящие профессии</p><div class="mt-2 grid gap-3 sm:grid-cols-3"><button v-for="role in activeDirection.roles" :key="role.category_id" type="button" class="inner role-card" :class="{ active: activeRole?.category_id === role.category_id }" @click="activeRoleId = role.category_id"><b>{{ role.title }}</b><p>{{ vacancyLabel(role.vacancies_count) }} в выборке</p><p v-if="role.market_level" class="mt-1 text-xs text-[#6d7175]">Зарплатный уровень {{ role.market_level }}<span v-if="role.market_salary_median"> - медиана {{ formatSalary(role.market_salary_median) }}</span></p><strong>{{ role.matched_skills_count }} из {{ role.selected_skills_count }} выбранных навыков встречаются в профессии</strong></button></div></div>
        <div v-if="activeRole" class="mt-4"><p class="subheading">Навыки профессии: {{ activeRole.title }}</p><p class="mt-1 text-sm text-[#616161]">Проценты показывают, в какой доле вакансий этой профессии встречается навык.</p><div v-if="activeRole.skills?.length" class="mt-2 grid gap-2 sm:grid-cols-2"><div v-for="skill in activeRole.skills" :key="skill.id" class="skill-gap"><b>{{ skill.title }}</b><span>{{ skill.percent }}% вакансий</span></div></div><p v-else class="mt-2 text-sm text-[#616161]">По этой профессии пока нет данных о навыках.</p><div class="mt-4 grid gap-4 lg:grid-cols-2"><div class="inner"><p class="subheading">Приоритет навыков</p><p class="mt-2 text-sm text-[#616161]"><b>Основной дефицит:</b> {{ primaryGaps.join(', ') || 'не выявлен' }}.</p><p class="mt-2 text-sm text-[#616161]"><b>Полезно усилить:</b> {{ strengthsToBuild.join(', ') || 'нет частых выбранных навыков' }}.</p><p class="mt-2 text-sm text-[#616161]"><b>Дополнительный стек:</b> {{ optionalSkills.join(', ') || 'не выявлен' }}.</p></div><div class="inner"><p class="subheading">Данные рынка профессии</p><p class="mt-2 text-sm text-[#616161]">{{ vacancyLabel(activeRole.vacancies_count) }} в анализе. Медиана зарплаты: {{ activeRole.market_salary_median ? formatSalary(activeRole.market_salary_median) : 'нет данных' }}.</p><p class="mt-2 text-sm text-[#616161]">Города: {{ activeRole.locations?.map(location => `${location.title} (${location.percent}%)`).join(', ') || 'нет данных' }}.</p><a :href="`/hierarchy-structure?group=${activeDirection.group_id}&role=${activeRole.category_id}`" class="market-link">Посмотреть профессию на карте уровней</a></div></div><div v-if="comparisonOptions.length" class="mt-4"><label class="subheading" for="role-comparison">Сравнить с другой профессией</label><select id="role-comparison" v-model="comparisonRoleId" class="comparison-select"><option :value="null">Не сравнивать</option><option v-for="role in comparisonOptions" :key="role.category_id" :value="role.category_id">{{ role.title }}</option></select><div v-if="comparisonRole" class="mt-3 grid gap-3 sm:grid-cols-2"><div class="inner"><p class="subheading">Общие навыки</p><p class="mt-2 text-sm text-[#616161]">{{ commonSkills.join(', ') || 'нет в верхних навыках выборки' }}.</p></div><div class="inner"><p class="subheading">Отличаются</p><p class="mt-2 text-sm text-[#616161]">Только у {{ activeRole.title }}: {{ onlyActiveSkills.join(', ') || 'нет' }}.</p><p class="mt-2 text-sm text-[#616161]">Только у {{ comparisonRole.title }}: {{ onlyComparisonSkills.join(', ') || 'нет' }}.</p></div></div></div></div>
        <div class="mt-4 grid gap-4 lg:grid-cols-2"><div><p class="subheading">Ваши навыки в вакансиях направления</p><div v-if="activeDirection.matched_skills.length" class="mt-2 grid gap-2"><div v-for="skill in activeDirection.matched_skills" :key="skill.id" class="skill-gap"><b>{{ skill.title }}</b><span>{{ skill.percent }}% вакансий</span></div></div><p v-if="activeDirection.missing_skills.length" class="mt-2 text-sm text-[#616161]">Не встретились в этой выборке: {{ activeDirection.missing_skills.join(', ') }}.</p></div><div><p class="subheading">Что подтянуть для {{ result.growth.next_level || 'следующего уровня' }}</p><p class="mt-1 text-sm text-[#616161]">Это часто встречающиеся навыки вакансий следующего грейда в данном направлении.</p><div v-if="activeDirection.skills_to_build.length" class="mt-2 grid gap-2"><div v-for="skill in activeDirection.skills_to_build" :key="skill.title" class="skill-gap"><b>{{ skill.title }}</b><span>{{ skill.percent }}% вакансий</span></div></div><p v-else class="mt-2 text-sm text-[#616161]">Для этого направления пока недостаточно вакансий следующего грейда, чтобы выделить надёжные дефициты.</p></div></div>
      </section>
    </article>

    <article v-if="result.portfolio_evidence" class="panel"><h2>Что подтвердит ваш уровень</h2><p class="summary-text">Это примеры, которые стоит показать работодателю или обсудить на собеседовании.</p><div class="mt-4 grid gap-3 sm:grid-cols-3"><div v-for="(item, index) in result.portfolio_evidence" :key="item.title" class="inner"><div class="evidence-title"><span class="number">{{ index + 1 }}</span><b>{{ item.title }}</b></div><p>{{ item.text }}</p></div></div></article>
    <article class="panel"><h2>{{ result.roadmap_direction ? `План следующего шага: ${result.roadmap_direction}` : 'План следующего шага' }}</h2><p v-if="result.roadmap_direction" class="summary-text">План построен для основного направления. Альтернативы можно сравнить выше.</p><ol class="mt-4 space-y-3"><li v-for="(item, index) in result.roadmap" :key="item.title"><b>{{ index + 1 }}. {{ item.title }}</b><p>{{ item.text }}</p></li></ol></article>
  </template>
</template>

<script setup>
import { computed, nextTick, ref, watch } from 'vue';

const props = defineProps({ result: Object, error: String, copied: Boolean });
defineEmits(['copy']);
const activeDirectionIndex = ref(0);
const activeRoleId = ref(null);
const comparisonRoleId = ref(null);
const directions = computed(() => props.result?.directions || []);
const activeDirection = computed(() => directions.value[activeDirectionIndex.value] || directions.value[0] || null);
const activeRole = computed(() => activeDirection.value?.roles?.find(role => role.category_id === activeRoleId.value) || activeDirection.value?.roles?.[0] || null);
const comparisonOptions = computed(() => []);
const comparisonRole = computed(() => comparisonOptions.value.find(role => role.category_id === comparisonRoleId.value) || null);
const commonSkills = computed(() => activeRole.value?.skills?.filter(skill => comparisonRole.value?.skills?.some(other => other.id === skill.id)).map(skill => skill.title) || []);
const onlyActiveSkills = computed(() => activeRole.value?.skills?.filter(skill => !comparisonRole.value?.skills?.some(other => other.id === skill.id)).map(skill => skill.title) || []);
const onlyComparisonSkills = computed(() => comparisonRole.value?.skills?.filter(skill => !activeRole.value?.skills?.some(other => other.id === skill.id)).map(skill => skill.title) || []);
const primaryGaps = computed(() => activeRole.value?.skills?.filter(skill => !skill.selected && skill.percent >= 20).map(skill => skill.title) || []);
const strengthsToBuild = computed(() => activeRole.value?.skills?.filter(skill => skill.selected && skill.percent >= 20).map(skill => skill.title) || []);
const optionalSkills = computed(() => activeRole.value?.skills?.filter(skill => !skill.selected && skill.percent < 20).map(skill => skill.title) || []);
watch(() => props.result?.report_uuid, () => { activeDirectionIndex.value = 0; activeRoleId.value = null; comparisonRoleId.value = null; });
watch(() => activeDirection.value?.group_id, () => { activeRoleId.value = null; comparisonRoleId.value = null; });
watch(() => activeRole.value?.category_id, () => { comparisonRoleId.value = null; });
watch(activeRole, () => nextTick(() => {
  const link = document.querySelector('.market-link');
  if (link) {
    link.target = '_blank';
    link.rel = 'noopener noreferrer';
  }
}), { immediate: true });
const formatSalary = value => new Intl.NumberFormat('ru-RU').format(value) + ' ₽';
const vacancyLabel = value => {
  const count = Number(value) || 0;
  const mod10 = count % 10, mod100 = count % 100;
  const word = mod10 === 1 && mod100 !== 11 ? 'вакансия' : (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14) ? 'вакансии' : 'вакансий');
  return `${count} ${word}`;
};
</script>

<style scoped>
.field{width:100%;border:1px solid #8c9196;border-radius:.375rem;background:#fff;padding:.65rem .75rem;font-size:.875rem}.notice{border:1px solid #f1d59a;border-radius:.5rem;background:#fff8db;padding:1rem;font-size:.875rem;color:#6d5600}.copy{border:1px solid #b18a24;border-radius:.375rem;background:#fff;padding:.4rem .7rem;font-size:.75rem;font-weight:600;white-space:nowrap}.card,.panel{border:1px solid #e1e3e5;border-radius:.75rem;background:#fff;padding:1.25rem;box-shadow:0 1px 2px #0000000d}.card p,.card small,.inner p,.panel li p{color:#6d7175;font-size:.8rem}.card b{display:block;margin:.4rem 0;font-size:1.35rem}.panel{margin-top:1.25rem}.panel h2{font-size:1.1rem;font-weight:600}.panel h3{font-size:1rem;font-weight:600}.inner{border:1px solid #e1e3e5;border-radius:.5rem;background:#fff;padding:1rem}.inner strong{display:block;margin-top:.75rem;color:#008060;font-size:.8rem}.role-card{text-align:left;transition:.2s}.role-card:hover,.role-card.active{border-color:#008060;background:#f1f8f5;box-shadow:0 0 0 1px #008060}.direction{border:1px solid #dfe3e0;border-radius:.65rem;background:#f7f8f8;padding:1rem}.direction-card{display:flex;min-height:8.5rem;flex-direction:column;align-items:flex-start;border:1px solid #e1e3e5;border-radius:.65rem;background:#fff;padding:1rem;text-align:left;transition:.2s}.direction-card:hover,.direction-card.active{border-color:#008060;background:#f1f8f5;box-shadow:0 0 0 1px #008060}.direction-card b{margin:.35rem 0;color:#202223}.direction-card small{color:#6d7175;font-size:.75rem;line-height:1.35}.direction-name{color:#008060;font-size:.75rem;font-weight:700}.subheading{font-size:.8rem;font-weight:600;color:#4a4f54}.summary-text{margin-top:.4rem;color:#616161;font-size:.875rem}.market-link{display:inline-block;margin-top:.8rem;color:#008060;font-size:.8rem;font-weight:700}.comparison-select{display:block;width:100%;margin-top:.5rem;border:1px solid #8c9196;border-radius:.375rem;background:#fff;padding:.55rem .7rem;font-size:.875rem}.insight{border:1px solid #e1e3e5;border-radius:.5rem;background:#f7f8f8;padding:1rem}.insight-good{border-color:#b7d9c8;background:#f1f8f5}.insight b{font-size:.875rem}.insight ul{margin-top:.6rem;display:grid;gap:.4rem;color:#4a4f54;font-size:.8rem}.insight li{padding-left:1rem;position:relative}.insight li:before{content:'•';left:0;position:absolute;color:#008060}.evidence-title{display:flex;align-items:center;gap:.45rem;margin-bottom:.55rem}.evidence-title b{margin:0}.number{display:inline-flex;height:1.5rem;width:1.5rem;flex:none;align-items:center;justify-content:center;border-radius:999px;background:#e3f1df;color:#006e52;font-size:.75rem;font-weight:700}.skill-gap{display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid #e1e3e5;border-radius:.5rem;background:#fff;padding:.8rem}.skill-gap b{font-size:.9rem}.skill-gap span{color:#008060;font-size:.8rem;font-weight:600;white-space:nowrap}
</style>
