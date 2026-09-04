<template>
    <AppLayout>
    <div class="shopify-page flex-1 bg-[#f6f6f7] text-[#202223]">
        <!-- Основной контент -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="mb-8 max-w-xl">
                <p class="mb-2 text-sm font-semibold text-[#008060]">Карта карьерных ролей</p>
                <h1 class="text-3xl font-semibold tracking-tight text-[#202223]">
                    Иерархическая структура должностей
                </h1>
                <p class="mt-3 text-[#616161]">
                    Посмотрите, как связаны роли и уровни: от стартовых позиций до архитектурных и управленческих направлений.
                </p>
            </div>

            <!-- Управление представлением -->
            <div class="mb-8 rounded-xl border border-[#e1e3e5] bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-[#e1e3e5] pb-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[#202223]">Выберите направление</p>
                        <p class="mt-1 text-xs text-[#6d7175]">Выбранное направление определяет роли, уровни и карьерные связи ниже.</p>
                    </div>
                    <div class="flex w-full rounded-md border border-[#c9cccf] bg-[#f6f6f7] p-1 sm:w-auto" aria-label="Вид структуры">
                        <button @click="viewMode = 'table'" :class="['flex-1 rounded px-4 py-2 text-sm font-semibold transition sm:flex-none', viewMode === 'table' ? 'bg-white text-[#006e52] shadow-sm' : 'text-[#616161] hover:text-[#202223]']">Таблица</button>
                        <button @click="viewMode = 'tree'" :class="['flex-1 rounded px-4 py-2 text-sm font-semibold transition sm:flex-none', viewMode === 'tree' ? 'bg-white text-[#006e52] shadow-sm' : 'text-[#616161] hover:text-[#202223]']">Схема</button>
                    </div>
                </div>
                <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <button v-for="group in groups" :key="group.id" type="button" @click="selectedGroupId = group.id" :class="['rounded-md border px-3 py-2.5 text-left text-sm font-medium transition', selectedGroupId === group.id ? 'border-[#008060] bg-[#e3f1df] text-[#006e52] shadow-sm' : 'border-[#e1e3e5] bg-white text-[#4a4f54] hover:border-[#8c9196] hover:bg-[#f6f6f7]']">
                        {{ group.title }}
                    </button>
                </div>
            </div>

            <!-- Табличное представление (карточный вид по уровням) -->
            <div v-if="viewMode === 'table'" class="space-y-8">
                <div
                    v-for="level in maxLevel + 1"
                    :key="level"
                    class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10"
                >
                    <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
                        <span class="shopify-level-badge w-8 h-8 rounded-full flex items-center justify-center text-sm">{{ level }}</span>
                        Уровень {{ level }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="category in getFilteredCategoriesByLevel(level - 1)"
                            :key="category.id"
                            class="bg-white/10 rounded-xl p-4 border border-white/10 hover:border-purple-500 transition-all group cursor-pointer"
                            @click="showCategoryDetails(category)"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-2xl">{{ getIconForCategory(category.title) }}</span>
                            </div>
                            <h3 class="font-semibold text-white group-hover:text-purple-300 transition">
                                {{ category.title }}
                            </h3>
                            <p class="text-sm text-white/60 mt-2 line-clamp-2">{{ category.description }}</p>
                            <div class="mt-3 flex items-center gap-2 text-xs text-white/40">
                                <span>{{ vacancyFoundLabel(category.vacancies_count) }}</span>
                                <span v-if="category.parent_id">↑ {{ getParentTitle(category.parent_id) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Древовидное представление -->
            <div v-else class="bg-white/5 backdrop-blur-md rounded-2xl p-6 border border-white/10">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-white">Иерархическая схема</h2>
                    <p class="text-sm text-white/60">Нажмите на должность, чтобы открыть подробности</p>
                </div>

                <div v-if="rootNodes.length" class="mb-5 rounded-xl border border-white/10 bg-black/10 p-4">
                    <label for="hierarchy-root" class="mb-2 block text-sm font-medium text-white/80">
                        Ветка карьерной иерархии
                    </label>
                    <select
                        id="hierarchy-root"
                        v-model="selectedRootId"
                        class="w-full rounded-lg border border-[#8c9196] bg-white px-3 py-2 text-[#202223] outline-none transition focus:border-[#008060] focus:ring-1 focus:ring-[#008060] sm:max-w-md"
                    >
                        <option v-for="root in rootNodes" :key="root.id" :value="root.id">
                            {{ root.title }}
                        </option>
                    </select>
                    <p class="mt-2 text-xs text-white/50">
                        Схема показывает одну ветку за раз - так связи остаются читаемыми даже для большой базы должностей.
                    </p>
                </div>

                <HierarchyDiagram
                    v-if="diagramNodes.length"
                    :nodes="diagramNodes"
                    :selected-id="selectedTreeNode?.id"
                    @select="handleTreeSelect"
                    @show-details="handleTreeShowDetails"
                />

            </div>

            <!-- Модальное окно со статистикой -->
            <Modal :show="isCategoryModalOpen" @close="closeModal" max-width="2xl">
                <div class="rounded-lg bg-white text-[#202223]">
                    <!-- Заголовок -->
                    <div class="p-6 pb-4 border-b border-white/10">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h2 class="text-2xl font-bold text-white">{{ selectedCategory?.title }}</h2>
                                    <p class="text-purple-300 text-sm">{{ getGroupTitle(selectedCategory?.group_id) }}</p>
                                </div>
                            </div>
                            <button @click="closeModal" class="text-[#6d7175] hover:text-[#202223] text-2xl transition" aria-label="Закрыть">×</button>
                        </div>
                    </div>

                    <!-- Контент с прокруткой -->
                    <div class="overflow-y-auto p-6 space-y-6" style="max-height: calc(90vh - 120px);">
                        <!-- Описание -->
                        <div class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4">
                            <p class="text-white/80">{{ selectedCategory?.description || 'Нет описания' }}</p>
                        </div>

                        <!-- Общая статистика -->
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4 text-center">
                                <div class="text-xs mb-2 uppercase tracking-wide text-[#6d7175]">Данные</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ selectedCategory?.vacancies_count || 0 }}
                                </div>
                                <div class="text-sm text-white">Всего вакансий</div>
                            </div>
                            <div class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4 text-center">
                                <div class="text-xs mb-2 uppercase tracking-wide text-[#6d7175]">Охват</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ selectedCategory?.locations_count || 0 }}
                                </div>
                                <div class="text-sm text-white">Городов</div>
                            </div>
                            <div class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4 text-center">
                                <div class="text-xs mb-2 uppercase tracking-wide text-[#6d7175]">Уровни</div>
                                <div class="text-2xl font-bold text-white">
                                    {{ selectedCategory?.grades_count || 0 }}
                                </div>
                                <div class="text-sm text-white">Грейдов</div>
                            </div>
                        </div>

                        <!-- Динамика публикаций -->
                        <div
                            v-if="publicationTimeline.length"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4"
                        >
                            <div class="flex items-baseline justify-between gap-3">
                                <h3 class="text-lg font-semibold text-[#202223]">Динамика публикаций</h3>
                                <span class="text-xs text-[#6d7175]">Последние 14 дней</span>
                            </div>
                            <p class="mt-1 text-sm text-[#616161]">Количество вакансий по дате публикации.</p>
                            <div class="mt-5 grid h-40 [grid-template-columns:repeat(14,minmax(0,1fr))] items-end gap-1" aria-label="График публикаций вакансий">
                                <div v-for="point in publicationTimeline" :key="point.date" class="flex h-full min-w-0 flex-col justify-end">
                                    <span v-if="point.count" class="mb-1 text-center text-[10px] font-semibold text-[#4a4f54]">{{ point.count }}</span>
                                    <div class="rounded-t bg-[#008060] transition-all" :class="point.count ? 'min-h-1.5' : 'h-1 bg-[#dfe3e0]'" :style="point.count ? { height: `${Math.max(8, Math.round(point.count / maxPublicationCount * 100))}%` } : undefined" :title="`${formatPublicationDate(point.date)}: ${vacancyLabel(point.count)}`"></div>
                                </div>
                            </div>
                            <div class="mt-2 grid [grid-template-columns:repeat(14,minmax(0,1fr))] gap-1 text-center text-[9px] text-[#6d7175]">
                                <span v-for="point in publicationTimeline" :key="`${point.date}-label`">{{ formatPublicationDate(point.date) }}</span>
                            </div>
                        </div>

                        <!-- Зарплатная статистика -->
                        <div
                            v-if="selectedCategory?.salary_stats && selectedCategory.salary_stats.avg_salary > 0"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4"
                        >
                            <h3 class="mb-4 text-lg font-semibold text-[#202223]">
                                Зарплатная вилка
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="rounded-lg border border-[#e1e3e5] bg-white p-4 text-center">
                                    <div class="mb-2 text-sm text-[#616161]">Средняя зарплата</div>
                                    <div class="text-2xl font-bold text-[#202223]">
                                        {{ formatSalaryValue(selectedCategory.salary_stats.avg_salary) }}
                                    </div>
                                </div>
                                <div class="rounded-lg border border-[#e1e3e5] bg-white p-4 text-center">
                                    <div class="mb-2 text-sm text-[#616161]">Диапазон</div>
                                    <div class="text-lg font-semibold text-[#202223]">
                                        {{ formatSalaryValue(selectedCategory.salary_stats.min_salary) }}
                                        -
                                        {{ formatSalaryValue(selectedCategory.salary_stats.max_salary) }}
                                    </div>
                                </div>
                            </div>
                            <div v-if="Object.keys(selectedCategory.salary_stats.by_grade || {}).length" class="pt-4 border-t border-[#e1e3e5]">
                                <div class="mb-3 text-sm font-semibold text-[#4a4f54]">Средняя зарплата по грейдам</div>
                                <div class="space-y-3">
                                    <div v-for="(salary, grade) in selectedCategory.salary_stats.by_grade" :key="grade">
                                        <div class="mb-1 flex items-center justify-between gap-3 text-sm"><span class="font-medium text-[#202223]">{{ grade }}</span><span class="text-[#4a4f54]">{{ formatSalaryValue(salary.avg) }}</span></div>
                                        <div class="h-2 overflow-hidden rounded-full bg-[#e1e3e5]"><div class="h-2 rounded-full bg-[#008060]" :style="{ width: `${Math.max(6, Math.round(salary.avg / maxGradeSalary * 100))}%` }"></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Топ навыки -->
                        <div
                            v-if="selectedCategory?.top_skills && selectedCategory.top_skills.length"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4"
                        >
                            <h3 class="mb-1 text-lg font-semibold text-[#202223]">
                                Ключевые навыки
                            </h3>
                            <p class="text-sm text-[#616161]">Навыки по доле вакансий выбранной роли.</p>
                            <div class="mt-4 space-y-3">
                                <div v-for="skill in visibleTopSkills" :key="skill.skill_id">
                                    <div class="mb-1 flex items-center justify-between gap-3 text-sm"><span class="font-medium text-[#202223]">{{ skill.title }}</span><span class="text-[#4a4f54]">{{ skill.percentage }}% - {{ vacancyLabel(skill.count) }}</span></div>
                                    <div class="h-2 overflow-hidden rounded-full bg-[#e1e3e5]"><div class="h-2 rounded-full bg-[#008060]" :style="{ width: `${skill.percentage}%` }"></div></div>
                                </div>
                            </div>
                            <button v-if="allTopSkills.length > 8" type="button" class="mt-4 text-sm font-semibold text-[#008060] hover:text-[#006e52]" @click="isSkillsExpanded = !isSkillsExpanded">{{ isSkillsExpanded ? 'Свернуть список' : `Показать все навыки (${allTopSkills.length})` }}</button>
                        </div>

                        <!-- Локации -->
                        <div
                            v-if="selectedCategory?.top_locations && selectedCategory.top_locations.length"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4"
                        >
                            <h3 class="mb-1 text-lg font-semibold text-[#202223]">
                                География вакансий
                            </h3>
                            <p class="text-sm text-[#616161]">Распределение вакансий по городам.</p>
                            <div class="space-y-3">
                                <div
                                    v-for="location in visibleTopLocations"
                                    :key="location.location_id"
                                    class="rounded-lg border border-[#e1e3e5] bg-white p-3"
                                >
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-[#202223]">{{ location.title }}</span>
                                        <span class="text-sm text-[#4a4f54]">{{ vacancyLabel(location.count) }} ({{ location.percentage }}%)</span>
                                    </div>
                                    <div class="w-full bg-[#e1e3e5] rounded-full h-2 overflow-hidden">
                                        <div class="bg-[#008060] h-2 rounded-full transition-all duration-300" :style="{ width: location.percentage + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                            <button v-if="allTopLocations.length > 8" type="button" class="mt-4 text-sm font-semibold text-[#008060] hover:text-[#006e52]" @click="isLocationsExpanded = !isLocationsExpanded">{{ isLocationsExpanded ? 'Свернуть список' : `Показать все города (${allTopLocations.length})` }}</button>
                        </div>

                        <!-- Грейды -->
                        <div
                            v-if="selectedCategory?.grades_distribution && selectedCategory.grades_distribution.length"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4"
                        >
                            <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                                Распределение по грейдам
                            </h3>
                            <div class="space-y-3">
                                <div
                                    v-for="grade in selectedCategory.grades_distribution"
                                    :key="grade.grade_id"
                                    class="bg-white/5 rounded-lg p-3 border border-white/10"
                                >
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-white">{{ grade.title }}</span>
                                        <span class="text-sm text-white">{{ vacancyLabel(grade.count) }} ({{ grade.percentage }}%)</span>
                                    </div>
                                    <div class="w-full bg-[#e1e3e5] rounded-full h-2 overflow-hidden">
                                        <div class="bg-[#008060] h-2 rounded-full transition-all duration-300" :style="{ width: grade.percentage + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Форматы работы -->
                        <div
                            v-if="selectedCategory?.employment_stats && Object.keys(selectedCategory.employment_stats).length"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4"
                        >
                            <h3 class="mb-1 text-lg font-semibold text-[#202223]">
                                Формат работы
                            </h3>
                            <p class="text-sm text-[#616161]">Как распределяются форматы работы в вакансиях.</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div
                                    v-for="(count, type) in selectedCategory.employment_stats"
                                    :key="type"
                                    class="rounded-lg border border-[#e1e3e5] bg-white p-3 text-center transition hover:border-[#8c9196]"
                                >
                                    <div class="font-semibold text-[#202223] text-sm">{{ getEmploymentType(type) }}</div>
                                    <div class="mt-1 text-lg font-bold text-[#202223]">{{ count }}</div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-[#e1e3e5]"><div class="h-1.5 rounded-full bg-[#008060]" :style="{ width: `${employmentPercent(count)}%` }"></div></div>
                                    <div class="mt-1 text-xs text-[#6d7175]">{{ employmentPercent(count) }}%</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопка закрытия -->
                    <div class="p-4 border-t border-white/20 rounded-b-lg">
                        <button @click="closeModal" class="w-full py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl text-white font-semibold hover:shadow-lg transition hover:scale-[1.02]">
                            Закрыть
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import HierarchyDiagram from './HierarchyDiagram.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    categories: { type: Array, required: true },
    groups: { type: Array, required: true }
});

const selectedCategory = ref(null);
const isCategoryModalOpen = ref(false);
const isSkillsExpanded = ref(false);
const isLocationsExpanded = ref(false);
const selectedGroupId = ref(null);
const viewMode = ref('table');
const selectedTreeNode = ref(null);
const selectedRootId = ref(null);
let modalCloseTimer = null;

const publicationTimeline = computed(() => selectedCategory.value?.publication_timeline || []);
const maxPublicationCount = computed(() => Math.max(1, ...publicationTimeline.value.map(point => point.count)));
const allTopSkills = computed(() => selectedCategory.value?.top_skills || []);
const visibleTopSkills = computed(() => isSkillsExpanded.value ? allTopSkills.value : allTopSkills.value.slice(0, 8));
const allTopLocations = computed(() => selectedCategory.value?.top_locations || []);
const visibleTopLocations = computed(() => isLocationsExpanded.value ? allTopLocations.value : allTopLocations.value.slice(0, 8));
const gradeSalaries = computed(() => Object.values(selectedCategory.value?.salary_stats?.by_grade || {}));
const maxGradeSalary = computed(() => Math.max(1, ...gradeSalaries.value.map(item => item.avg || 0)));

// Фильтрация категорий - исключаем "Другое" (sort_order === 99)
const mainCategories = computed(() => {
    return props.categories.filter(c => (c.sort_order || 0) !== 99);
});

// Категории "Другое" для отдельного отображения
const otherCategories = computed(() => {
    return props.categories.filter(c => (c.sort_order || 0) === 99);
});

watch(() => props.groups, (groups) => {
    if (!groups.some(group => group.id === selectedGroupId.value)) {
        selectedGroupId.value = groups[0]?.id ?? null;
    }
}, { immediate: true });

// Фильтрация по группе (только основные категории)
const filteredCategories = computed(() => {
    if (!selectedGroupId.value) return mainCategories.value;
    return mainCategories.value.filter(c => c.group_id === selectedGroupId.value);
});

// Фильтрованные "Другое" категории
const filteredOtherCategories = computed(() => {
    if (!selectedGroupId.value) return otherCategories.value;
    return otherCategories.value.filter(c => c.group_id === selectedGroupId.value);
});

const rootNodes = computed(() => {
    const ids = new Set(filteredCategories.value.map(category => category.id));
    return filteredCategories.value
        .filter(category => !category.parent_id || !ids.has(category.parent_id))
        .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
});

const diagramNodes = computed(() => {
    const rootId = Number(selectedRootId.value);
    if (!rootId) return [];

    const children = new Map();
    filteredCategories.value.forEach(category => {
        const parentId = category.parent_id;
        if (!children.has(parentId)) children.set(parentId, []);
        children.get(parentId).push(category);
    });

    const result = [];
    const visit = (id) => {
        const node = filteredCategories.value.find(category => category.id === id);
        if (!node) return;
        result.push(node);
        (children.get(node.id) || [])
            .sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0))
            .forEach(child => visit(child.id));
    };
    visit(rootId);
    return result;
});

watch(rootNodes, (roots) => {
    if (!roots.some(root => root.id === selectedRootId.value)) {
        selectedRootId.value = roots[0]?.id ?? null;
    }
}, { immediate: true });

const maxLevel = computed(() => {
    const cats = filteredCategories.value;
    if (!cats.length) return 0;
    return Math.max(...cats.map(c => c.level));
});

const groupMap = computed(() => new Map(props.groups.map(g => [g.id, g.title])));
const parentMap = computed(() => new Map(props.categories.map(c => [c.id, c.title])));


const getGroupTitle = (groupId) => groupMap.value.get(groupId) || 'Неизвестно';
const getParentTitle = (parentId) => parentMap.value.get(parentId) || null;
const vacancyLabel = (value) => {
    const count = Number(value) || 0;
    const mod10 = count % 10;
    const mod100 = count % 100;
    const word = mod10 === 1 && mod100 !== 11 ? 'вакансия' : (mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14) ? 'вакансии' : 'вакансий');
    return `${count} ${word}`;
};
const vacancyFoundLabel = (value) => {
    const count = Number(value) || 0;
    const mod10 = count % 10;
    const mod100 = count % 100;
    return `${vacancyLabel(count)} ${mod10 === 1 && mod100 !== 11 ? 'найдена' : 'найдено'}`;
};
const getFilteredCategoriesByLevel = (level) => filteredCategories.value.filter(c => c.level === level).sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));

const getLevelLabel = (level) => {
    const labels = ['Высшее руководство', 'Директора / Архитекторы', 'Руководители отделов', 'Ведущие специалисты', 'Специалисты', 'Младшие специалисты'];
    return `Уровень ${level}`;
};

const getIconForCategory = () => '';

const formatSalaryValue = (value) => {
    if (!value || value === 0) return 'Не указано';
    return new Intl.NumberFormat('ru-RU').format(value) + ' ₽';
};

const formatPublicationDate = (value) => {
    const date = new Date(`${value}T00:00:00`);
    return new Intl.DateTimeFormat('ru-RU', { day: '2-digit', month: '2-digit' }).format(date);
};

const getEmploymentType = (type) => {
    const types = {
        'full_time': 'Полный день',
        'part_time': 'Частичная занятость',
        'remote': 'Удаленно',
        'hybrid': 'Гибрид'
    };
    return types[type] || type;
};

const getEmploymentIcon = () => '';

const employmentPercent = (count) => {
    const total = Number(selectedCategory.value?.vacancies_count) || 0;
    return total ? Math.round(Number(count) / total * 100) : 0;
};

const showCategoryDetails = (category) => {
    if (modalCloseTimer) {
        clearTimeout(modalCloseTimer);
        modalCloseTimer = null;
    }
    isSkillsExpanded.value = false;
    isLocationsExpanded.value = false;
    selectedCategory.value = category;
    isCategoryModalOpen.value = true;
};

const closeModal = () => {
    isCategoryModalOpen.value = false;
    selectedTreeNode.value = null;

    // Компонент Modal завершает анимацию за 200 мс. Держим данные до её
    // окончания, чтобы при закрытии не возникала пустая белая карточка.
    modalCloseTimer = setTimeout(() => {
        if (!isCategoryModalOpen.value) {
            selectedCategory.value = null;
        }
        modalCloseTimer = null;
    }, 200);
};

const handleTreeSelect = (node) => {
    selectedTreeNode.value = node;
};

const handleTreeShowDetails = (node) => {
    showCategoryDetails(node);
};

</script>

<style scoped>
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

</style>
