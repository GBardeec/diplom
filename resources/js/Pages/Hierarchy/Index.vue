<template>
    <AppLayout>
    <div class="shopify-page flex-1 bg-[#f6f6f7] text-[#202223]">
        <!-- Основной контент -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="mb-8 max-w-xl">
                <p class="mb-2 text-sm font-semibold text-[#008060]">Карта зарплатных уровней</p>
                <h1 class="text-3xl font-semibold tracking-tight text-[#202223]">
                    Зарплатные уровни профессий
                </h1>
                <p class="mt-3 text-[#616161]">
                    Зарплатные уровни рассчитываются по медианной зарплате вакансий внутри направления. На схеме можно посмотреть переходы между профессиями с похожими навыками.
                </p>
            </div>

            <!-- Управление представлением -->
            <div class="mb-8 rounded-xl border border-[#e1e3e5] bg-white p-4 shadow-sm">
                <div class="grid gap-4 sm:grid-cols-[minmax(440px,1fr)_auto_auto] sm:items-end">
                    <div class="sm:order-3 sm:w-auto">
                        <span class="mb-1.5 block text-sm font-semibold text-[#202223]">Способ просмотра</span>
                        <div class="flex h-[42px] w-full rounded-md border border-[#c9cccf] bg-[#f6f6f7] p-1 sm:w-auto" aria-label="Способ просмотра карты">
                            <button @click="mapScope = 'direction'" :class="['flex-1 whitespace-nowrap rounded px-3 text-sm font-semibold transition', mapScope === 'direction' ? 'bg-white text-[#006e52] shadow-sm' : 'bg-transparent text-[#616161]']">По направлениям</button>
                            <button @click="mapScope = 'specialization'" :class="['flex-1 whitespace-nowrap rounded px-3 text-sm font-semibold transition', mapScope === 'specialization' ? 'bg-white text-[#006e52] shadow-sm' : 'bg-transparent text-[#616161]']">По профессии</button>
                        </div>
                    </div>
                    <div v-if="mapScope === 'direction'" class="relative block min-w-0 sm:order-1 sm:min-w-[440px]">
                        <span class="mb-1.5 block text-sm font-semibold text-[#202223]">Направление</span>
                        <div class="relative"><input id="career-direction" v-model="groupQuery" type="search" autocomplete="off" class="block h-[42px] w-full rounded-md border border-[#c9cccf] bg-white px-3 py-2 pr-10 text-sm font-medium text-[#202223] outline-none transition focus:border-[#008060] focus:ring-2 focus:ring-[#008060]/20" placeholder="Начните вводить направление" @focus="isGroupMenuOpen = true" @input="isGroupMenuOpen = true" @keydown.esc="isGroupMenuOpen = false"/><button type="button" class="absolute inset-y-0 right-0 grid w-10 place-items-center text-[#6d7175]" aria-label="Открыть список направлений" @click="isGroupMenuOpen = !isGroupMenuOpen"><svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 7 5 5 5-5"/></svg></button></div>
                        <div v-if="isGroupMenuOpen" class="hierarchy-select-menu"><button v-for="group in filteredGroups" :key="group.id" type="button" class="hierarchy-select-option" @mousedown.prevent="chooseGroup(group)">{{ group.title }}</button><p v-if="!filteredGroups.length" class="hierarchy-select-empty">Ничего не найдено.</p></div>
                    </div>
                    <div v-else class="relative block min-w-0 sm:order-1 sm:min-w-[440px]"><span class="mb-1.5 block text-sm font-semibold text-[#202223]">Исходная профессия</span><div class="relative"><input id="career-specialization" v-model="professionQuery" type="search" autocomplete="off" class="block h-[42px] w-full rounded-md border border-[#c9cccf] bg-white px-3 py-2 pr-10 text-sm font-medium text-[#202223] outline-none transition focus:border-[#008060] focus:ring-2 focus:ring-[#008060]/20" placeholder="Начните вводить профессию" @focus="isProfessionMenuOpen = true" @input="isProfessionMenuOpen = true" @keydown.esc="isProfessionMenuOpen = false"/><button type="button" class="absolute inset-y-0 right-0 grid w-10 place-items-center text-[#6d7175]" aria-label="Открыть список профессий" @click="isProfessionMenuOpen = !isProfessionMenuOpen"><svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 7 5 5 5-5"/></svg></button></div><div v-if="isProfessionMenuOpen" class="hierarchy-select-menu"><button v-for="category in filteredProfessionOptions" :key="category.id" type="button" class="hierarchy-select-option" @mousedown.prevent="chooseProfession(category)"><span>{{ category.title }}</span><small>{{ category.group_title || getGroupTitle(category.group_id) }}</small></button><p v-if="!filteredProfessionOptions.length" class="hierarchy-select-empty">Ничего не найдено.</p></div></div>
                    <div class="sm:order-2 sm:w-auto">
                        <span class="mb-1.5 block text-sm font-semibold text-[#202223]">Вид отображения</span>
                        <div class="flex h-[42px] w-full rounded-md border border-[#c9cccf] bg-[#f6f6f7] p-1 sm:w-auto" aria-label="Вид структуры">
                            <button @click="viewMode = 'tree'" :class="['flex-1 rounded px-4 text-sm font-semibold transition sm:flex-none', viewMode === 'tree' ? 'bg-white text-[#006e52] shadow-sm' : 'text-[#616161] hover:text-[#202223]']">Схема</button>
                            <button @click="viewMode = 'table'" :class="['flex-1 rounded px-4 text-sm font-semibold transition sm:flex-none', viewMode === 'table' ? 'bg-white text-[#006e52] shadow-sm' : 'text-[#616161] hover:text-[#202223]']">Таблица</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Табличное представление (карточный вид по уровням) -->
            <div v-if="viewMode === 'table'" class="space-y-8">
                <div v-if="!marketCategories.length" class="rounded-xl border border-[#e1e3e5] bg-white p-6 text-[#616161]">
                    {{ mapScope === 'specialization' ? 'По этой специализации пока нет профессий с достаточным числом вакансий и указанной зарплатой.' : 'Для этого направления пока недостаточно вакансий с указанной зарплатой. Зарплатный уровень появится, когда для профессии будет не менее трёх таких вакансий.' }}
                </div>
                <template v-for="level in maxMarketLevel" :key="level">
                <div v-if="getCategoriesByMarketLevel(level).length" class="rounded-xl border border-[#e1e3e5] bg-white p-6 shadow-sm">
                    <h2 class="mb-2 flex items-center gap-2 text-2xl font-semibold text-[#202223]">
                        <span class="shopify-level-badge w-8 h-8 rounded-full flex items-center justify-center text-sm">{{ level }}</span>
                        Зарплатный уровень {{ level }} из {{ maxMarketLevel }}
                    </h2>
                    <p class="mb-4 text-sm text-[#616161]">Профессии сгруппированы по медианной зарплате вакансий в выбранном направлении.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <div
                            v-for="category in getCategoriesByMarketLevel(level)"
                            :key="category.id"
                            class="group cursor-pointer rounded-xl border border-[#e1e3e5] bg-[#f7f8f8] p-4 transition hover:border-[#008060]"
                            @click="showCategoryDetails(category)"
                        >
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-2xl">{{ getIconForCategory(category.title) }}</span>
                            </div>
                            <h3 class="font-semibold text-[#202223] transition group-hover:text-[#006e52]">
                                {{ category.title }}
                            </h3>
                            <p class="mt-2 line-clamp-2 text-sm text-[#616161]">{{ category.description }}</p>
                            <p class="mt-3 text-sm font-semibold text-[#006e52]">Медиана: {{ formatSalaryValue(category.market_salary_median) }}</p>
                            <div class="mt-2 flex items-center gap-2 text-xs text-[#6d7175]">
                                <span>{{ vacancyFoundLabel(category.vacancies_count) }}</span>
                                <span>В выборке с зарплатой: {{ vacancyLabel(category.market_salary_sample_size) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                </template>

            </div>

            <!-- Древовидное представление -->
            <div v-else class="rounded-xl border border-[#e1e3e5] bg-white p-6 shadow-sm">
                <div v-if="!marketCategories.length" class="text-[#616161]">
                    {{ mapScope === 'specialization' ? 'По этой специализации пока нет профессий с достаточным числом вакансий и указанной зарплатой.' : 'Для этого направления пока недостаточно вакансий с указанной зарплатой. Зарплатный уровень появится, когда для профессии будет не менее трёх таких вакансий.' }}
                </div>
                <template v-else>
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-2xl font-semibold text-[#202223]">Схема переходов между профессиями</h2>
                        <p class="text-sm text-[#616161]">Выберите профессию, чтобы посмотреть связи</p>
                    </div>

                    <p class="mb-3 text-sm text-[#616161]">Показаны все подтверждённые переходы. Нажмите на стрелку, чтобы увидеть навыки конкретного перехода.</p>
                    <div class="mb-4 flex flex-wrap gap-x-4 gap-y-2 text-xs font-semibold text-[#4a4f54]" aria-label="Обозначения связей">
                        <span class="inline-flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-[#008060]"></i> Можно перейти из выбранной профессии</span>
                        <span class="inline-flex items-center gap-2"><i class="h-2.5 w-2.5 rounded-full bg-[#2c6ecb]"></i> Можно прийти в выбранную профессию</span>
                    </div>

                    <HierarchyDiagram
                        :nodes="diagramNodes"
                        :transitions="diagramTransitions"
                        :selected-id="selectedTreeNode?.id"
                        :use-global-salary-levels="mapScope === 'specialization'"
                        connection-mode="all"
                        @select="handleTreeSelect"
                        @show-details="handleTreeShowDetails"
                    />
                </template>

            </div>

            <!-- Модальное окно со статистикой -->
            <Modal :show="isCategoryModalOpen" @close="closeModal" max-width="2xl">
                <div class="rounded-lg bg-white text-[#202223]">
                    <!-- Заголовок -->
                    <div class="border-b border-[#e1e3e5] p-4">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h2 class="text-xl font-semibold text-[#202223]">{{ selectedCategory?.title }}</h2>
                                    <p class="text-sm text-[#616161]">{{ getGroupTitle(selectedCategory?.group_id) }}</p>
                                    <p class="mt-1 max-w-xl text-sm text-[#616161]">{{ selectedCategory?.description || 'Нет описания' }}</p>
                                </div>
                            </div>
                            <button @click="closeModal" class="text-[#6d7175] hover:text-[#202223] text-2xl transition" aria-label="Закрыть">×</button>
                        </div>
                    </div>

                    <!-- Контент с прокруткой -->
                    <div class="space-y-4 overflow-y-auto p-4" style="max-height: calc(82vh - 120px);">
                        <!-- Общая статистика -->
                        <div class="grid grid-cols-3 gap-2">
                            <div class="rounded-lg border border-[#e1e3e5] bg-[#f6f6f7] px-3 py-2 text-center">
                                <div class="text-lg font-semibold text-[#202223]">{{ selectedCategory?.vacancies_count || 0 }}</div>
                                <div class="text-xs text-[#6d7175]">вакансий</div>
                            </div>
                            <div class="rounded-lg border border-[#e1e3e5] bg-[#f6f6f7] px-3 py-2 text-center">
                                <div class="text-lg font-semibold text-[#202223]">{{ selectedCategory?.locations_count || 0 }}</div>
                                <div class="text-xs text-[#6d7175]">городов</div>
                            </div>
                            <div class="rounded-lg border border-[#e1e3e5] bg-[#f6f6f7] px-3 py-2 text-center">
                                <div class="text-lg font-semibold text-[#202223]">{{ selectedCategory?.grades_count || 0 }}</div>
                                <div class="text-xs text-[#6d7175]">грейдов</div>
                            </div>
                        </div>

                        <!-- Динамика публикаций -->
                        <div
                            v-if="publicationTimeline.length"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4 pb-6"
                        >
                            <div class="flex items-baseline justify-between gap-3">
                                <h3 class="text-lg font-semibold text-[#202223]">Динамика публикаций</h3>
                                <span class="text-xs text-[#6d7175]">Последние 6 месяцев</span>
                            </div>
                            <p class="mt-1 text-sm text-[#616161]">Количество вакансий по месяцам публикации.</p>
                            <div class="mt-5 grid h-40 grid-cols-6 items-end gap-3" aria-label="График публикаций вакансий">
                                <div v-for="point in publicationTimeline" :key="point.date" class="flex h-full min-w-0 flex-col justify-end">
                                    <span v-if="point.count" class="mb-1 text-center text-[10px] font-semibold text-[#4a4f54]">{{ point.count }}</span>
                                    <div class="rounded-t bg-[#008060] transition-all" :class="point.count ? 'min-h-1.5' : 'h-1 bg-[#dfe3e0]'" :style="point.count ? { height: `${Math.max(8, Math.round(point.count / maxPublicationCount * 100))}%` } : undefined" :title="`${formatPublicationMonth(point.date)}: ${vacancyLabel(point.count)}`"></div>
                                </div>
                            </div>
                            <div class="mt-2 grid grid-cols-6 gap-3 text-center text-[10px] text-[#6d7175]">
                                <span v-for="point in publicationTimeline" :key="`${point.date}-label`">{{ formatPublicationMonth(point.date) }}</span>
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
                            <div v-if="salaryTimeline.some(point => point.avg_salary)" class="mb-4 border-t border-[#e1e3e5] pt-4">
                                <div class="mb-1 flex items-baseline justify-between gap-3">
                                    <div class="text-sm font-semibold text-[#4a4f54]">Средняя зарплата по месяцам</div>
                                    <span class="text-xs text-[#6d7175]">По вакансиям с указанной оплатой</span>
                                </div>
                                <div v-if="salaryGradeOptions.length" class="mt-3 flex flex-wrap gap-2" aria-label="Фильтр графика зарплаты по грейду">
                                    <button type="button" :class="skillGradeButtonClass(null, selectedSalaryGradeId)" @click="selectedSalaryGradeId = null">Все вакансии</button>
                                    <button v-for="grade in salaryGradeOptions" :key="grade.grade_id" type="button" :class="skillGradeButtonClass(grade.grade_id, selectedSalaryGradeId)" @click="selectedSalaryGradeId = grade.grade_id">{{ grade.title }}</button>
                                </div>
                                <div class="mt-4 grid h-28 grid-cols-6 items-end gap-3" aria-label="График средней зарплаты по месяцам">
                                    <div v-for="point in salaryTimeline" :key="`salary-${point.date}`" class="flex h-full min-w-0 flex-col justify-end">
                                        <span v-if="point.avg_salary" class="mb-1 text-center text-[10px] font-semibold text-[#4a4f54]">{{ formatSalaryShort(point.avg_salary) }}</span>
                                        <div class="rounded-t bg-[#008060] transition-all" :class="point.avg_salary ? 'min-h-1.5' : 'h-1 bg-[#dfe3e0]'" :style="point.avg_salary ? { height: `${Math.max(8, Math.round(point.avg_salary / maxSalaryTimelineValue * 100))}%` } : undefined" :title="point.avg_salary ? `${formatPublicationMonth(point.date)}: ${formatSalaryValue(point.avg_salary)} (${vacancyLabel(point.count)})` : `${formatPublicationMonth(point.date)}: нет вакансий с зарплатой`"></div>
                                    </div>
                                </div>
                                <div class="mt-2 grid grid-cols-6 gap-3 text-center text-[10px] text-[#6d7175]">
                                    <span v-for="point in salaryTimeline" :key="`salary-label-${point.date}`">{{ formatPublicationMonth(point.date) }}</span>
                                </div>
                            </div>
                            <p v-else class="mb-4 border-t border-[#e1e3e5] pt-4 text-sm text-[#616161]">В выбранной выборке нет вакансий с указанной зарплатой.</p>
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
                            <p class="text-sm text-[#616161]">Навыки по доле вакансий выбранной профессии.</p>
                            <div v-if="monthOptions.length" class="mt-3 flex flex-nowrap gap-2 overflow-x-auto pb-1"><button type="button" :class="skillGradeButtonClass(null, selectedSkillsMonth)" @click="selectedSkillsMonth = null">Все месяцы</button><button v-for="month in monthOptions" :key="`skills-${month.value}`" type="button" :class="skillGradeButtonClass(month.value, selectedSkillsMonth)" @click="selectedSkillsMonth = month.value">{{ month.title }}</button></div>
                            <div v-if="skillGradeOptions.length" class="mt-3 flex flex-wrap gap-2" aria-label="Фильтр навыков по грейду">
                                <button type="button" :class="skillGradeButtonClass(null)" @click="selectedSkillsGradeId = null">Все вакансии</button>
                                <button v-for="grade in skillGradeOptions" :key="grade.grade_id" type="button" :class="skillGradeButtonClass(grade.grade_id)" @click="selectedSkillsGradeId = grade.grade_id">{{ grade.title }}</button>
                            </div>
                            <div class="mt-4 space-y-3">
                                <div v-for="skill in visibleTopSkills" :key="skill.skill_id">
                                    <div class="mb-1 flex items-center justify-between gap-3 text-sm"><span class="font-medium text-[#202223]">{{ skill.title }}</span><span class="text-[#4a4f54]">{{ skill.percentage }}% - {{ vacancyLabel(skill.count) }}</span></div>
                                    <div class="h-2 overflow-hidden rounded-full bg-[#e1e3e5]"><div class="h-2 rounded-full bg-[#008060]" :style="{ width: `${skill.percentage}%` }"></div></div>
                                </div>
                            </div>
                            <p v-if="!visibleTopSkills.length" class="mt-4 text-sm text-[#616161]">В выбранном месяце вакансий с указанными навыками не найдено.</p>
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
                            <p class="text-sm text-[#616161]">Распределение вакансий по городам и средняя зарплата по вакансиям с указанной оплатой.</p>
                            <div v-if="monthOptions.length" class="mt-3 flex flex-nowrap gap-2 overflow-x-auto pb-1"><button type="button" :class="skillGradeButtonClass(null, selectedLocationsMonth)" @click="selectedLocationsMonth = null">Все месяцы</button><button v-for="month in monthOptions" :key="`locations-${month.value}`" type="button" :class="skillGradeButtonClass(month.value, selectedLocationsMonth)" @click="selectedLocationsMonth = month.value">{{ month.title }}</button></div>
                            <div v-if="locationGradeOptions.length" class="mt-3 mb-4 flex flex-wrap gap-2" aria-label="Фильтр городов по грейду">
                                <button type="button" :class="skillGradeButtonClass(null, selectedLocationsGradeId)" @click="selectedLocationsGradeId = null">Все вакансии</button>
                                <button v-for="grade in locationGradeOptions" :key="grade.grade_id" type="button" :class="skillGradeButtonClass(grade.grade_id, selectedLocationsGradeId)" @click="selectedLocationsGradeId = grade.grade_id">{{ grade.title }}</button>
                            </div>
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
                                    <p class="mb-2 text-sm text-[#006e52]">Средняя зарплата: {{ formatSalaryValue(location.salary_avg) }}</p>
                                    <div class="w-full bg-[#e1e3e5] rounded-full h-2 overflow-hidden">
                                        <div class="bg-[#008060] h-2 rounded-full transition-all duration-300" :style="{ width: location.percentage + '%' }"></div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="!gradesStats.grades_distribution?.length" class="text-sm text-[#616161]">В выбранном месяце вакансий с указанным грейдом не найдено.</p>
                            <p v-if="!visibleTopLocations.length" class="mt-4 text-sm text-[#616161]">В выбранном месяце вакансий с указанной географией не найдено.</p>
                            <button v-if="allTopLocations.length > 8" type="button" class="mt-4 text-sm font-semibold text-[#008060] hover:text-[#006e52]" @click="isLocationsExpanded = !isLocationsExpanded">{{ isLocationsExpanded ? 'Свернуть список' : `Показать все города (${allTopLocations.length})` }}</button>
                        </div>

                        <!-- Грейды -->
                        <div
                            v-if="selectedCategory?.grades_distribution && selectedCategory.grades_distribution.length"
                            class="rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-4"
                        >
                            <h3 class="mb-4 text-lg font-semibold text-[#202223]">
                                Распределение по грейдам
                            </h3>
                            <div v-if="monthOptions.length" class="mb-4 flex flex-nowrap gap-2 overflow-x-auto pb-1"><button type="button" :class="skillGradeButtonClass(null, selectedGradesMonth)" @click="selectedGradesMonth = null">Все месяцы</button><button v-for="month in monthOptions" :key="`grades-${month.value}`" type="button" :class="skillGradeButtonClass(month.value, selectedGradesMonth)" @click="selectedGradesMonth = month.value">{{ month.title }}</button></div>
                            <div class="space-y-3">
                                <div
                                    v-for="grade in gradesStats.grades_distribution"
                                    :key="grade.grade_id"
                                    class="rounded-lg border border-[#e1e3e5] bg-white p-3"
                                >
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-semibold text-[#202223]">{{ grade.title }}</span>
                                        <span class="text-sm text-[#4a4f54]">{{ vacancyLabel(grade.count) }} ({{ grade.percentage }}%)</span>
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
                            <div v-if="monthOptions.length" class="mt-3 flex flex-nowrap gap-2 overflow-x-auto pb-1"><button type="button" :class="skillGradeButtonClass(null, selectedEmploymentMonth)" @click="selectedEmploymentMonth = null">Все месяцы</button><button v-for="month in monthOptions" :key="`employment-${month.value}`" type="button" :class="skillGradeButtonClass(month.value, selectedEmploymentMonth)" @click="selectedEmploymentMonth = month.value">{{ month.title }}</button></div>
                            <div v-if="selectedCategory.grades_distribution?.length" class="mt-3 mb-4 flex flex-nowrap gap-2 overflow-x-auto pb-1"><button type="button" :class="skillGradeButtonClass(null, selectedEmploymentGradeId)" @click="selectedEmploymentGradeId = null">Все грейды</button><button v-for="grade in selectedCategory.grades_distribution" :key="`employment-grade-${grade.grade_id}`" type="button" :class="skillGradeButtonClass(grade.grade_id, selectedEmploymentGradeId)" @click="selectedEmploymentGradeId = grade.grade_id">{{ grade.title }}</button></div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                <div
                                    v-for="(count, type) in employmentStatsSource.employment_stats"
                                    :key="type"
                                    class="rounded-lg border border-[#e1e3e5] bg-white p-3 text-center transition hover:border-[#8c9196]"
                                >
                                    <div class="font-semibold text-[#202223] text-sm">{{ getEmploymentType(type) }}</div>
                                    <div class="mt-1 text-lg font-bold text-[#202223]">{{ count }}</div>
                                    <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-[#e1e3e5]"><div class="h-1.5 rounded-full bg-[#008060]" :style="{ width: `${employmentPercent(count)}%` }"></div></div>
                                    <div class="mt-1 text-xs text-[#6d7175]">{{ employmentPercent(count) }}%</div>
                                </div>
                            </div>
                            <p v-if="!Object.keys(employmentStatsSource.employment_stats || {}).length" class="text-sm text-[#616161]">В выбранной выборке нет вакансий с указанным форматом работы.</p>
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
    groups: { type: Array, required: true },
    transitions: { type: Array, default: () => [] },
});

const selectedCategory = ref(null);
const isCategoryModalOpen = ref(false);
const isSkillsExpanded = ref(false);
const isLocationsExpanded = ref(false);
const selectedGroupId = ref(null);
const mapScope = ref('direction');
const specializationCategoryId = ref(null);
const groupQuery = ref('');
const professionQuery = ref('');
const isGroupMenuOpen = ref(false);
const isProfessionMenuOpen = ref(false);
const viewMode = ref('tree');
const selectedTreeNode = ref(null);
const selectedSkillsGradeId = ref(null);
const selectedLocationsGradeId = ref(null);
const selectedSalaryGradeId = ref(null);
const selectedSkillsMonth = ref(null);
const selectedLocationsMonth = ref(null);
const selectedGradesMonth = ref(null);
const selectedEmploymentMonth = ref(null);
const selectedEmploymentGradeId = ref(null);
const sectionStats = ref({ skills: null, locations: null, grades: null, employment: null });
const requestedGroupId = Number(new URLSearchParams(window.location.search).get('group')) || null;
let modalCloseTimer = null;

const publicationTimeline = computed(() => selectedCategory.value?.publication_timeline || []);
const monthOptions = computed(() => publicationTimeline.value.map(point => ({ value: point.date.slice(0, 7), title: formatPublicationMonth(point.date) })));
const maxPublicationCount = computed(() => Math.max(1, ...publicationTimeline.value.map(point => point.count)));
const skillStats = computed(() => sectionStats.value.skills || selectedCategory.value || {});
const locationStats = computed(() => sectionStats.value.locations || selectedCategory.value || {});
const gradesStats = computed(() => sectionStats.value.grades || selectedCategory.value || {});
const employmentStatsSource = computed(() => sectionStats.value.employment || selectedCategory.value || {});
const skillGradeOptions = computed(() => skillStats.value.top_skills_by_grade || []);
const allTopSkills = computed(() => {
    if (selectedSkillsGradeId.value === null) return skillStats.value.top_skills || [];
    return skillGradeOptions.value.find(grade => Number(grade.grade_id) === Number(selectedSkillsGradeId.value))?.skills || [];
});
const visibleTopSkills = computed(() => isSkillsExpanded.value ? allTopSkills.value : allTopSkills.value.slice(0, 8));
const locationGradeOptions = computed(() => locationStats.value.top_locations_by_grade || []);
const allTopLocations = computed(() => {
    if (selectedLocationsGradeId.value === null) return locationStats.value.top_locations || [];
    return locationGradeOptions.value.find(grade => Number(grade.grade_id) === Number(selectedLocationsGradeId.value))?.locations || [];
});
const visibleTopLocations = computed(() => isLocationsExpanded.value ? allTopLocations.value : allTopLocations.value.slice(0, 8));
const gradeSalaries = computed(() => Object.values(selectedCategory.value?.salary_stats?.by_grade || {}));
const maxGradeSalary = computed(() => Math.max(1, ...gradeSalaries.value.map(item => item.avg || 0)));
const salaryGradeOptions = computed(() => selectedCategory.value?.salary_stats?.timeline_by_grade || []);
const salaryTimeline = computed(() => {
    const stats = selectedCategory.value?.salary_stats;
    if (!stats) return [];
    if (selectedSalaryGradeId.value === null) return stats.timeline || [];
    return salaryGradeOptions.value.find(grade => Number(grade.grade_id) === Number(selectedSalaryGradeId.value))?.timeline || [];
});
const maxSalaryTimelineValue = computed(() => Math.max(1, ...salaryTimeline.value.map(point => Number(point.avg_salary) || 0)));

// Фильтрация категорий - исключаем "Другое" (sort_order === 99)
const mainCategories = computed(() => {
    return props.categories.filter(c => (c.sort_order || 0) !== 99);
});
const filteredGroups = computed(() => {
    const query = groupQuery.value.trim().toLocaleLowerCase('ru-RU');
    return props.groups.filter(group => group.title.toLocaleLowerCase('ru-RU').includes(query));
});
const professionOptions = computed(() => mainCategories.value.filter(category => category.market_level));
const filteredProfessionOptions = computed(() => {
    const query = professionQuery.value.trim().toLocaleLowerCase('ru-RU');
    return professionOptions.value.filter(category => category.title.toLocaleLowerCase('ru-RU').includes(query));
});
const chooseGroup = (group) => {
    selectedGroupId.value = group.id;
    groupQuery.value = group.title;
    isGroupMenuOpen.value = false;
};
const chooseProfession = (category) => {
    specializationCategoryId.value = category.id;
    professionQuery.value = category.title;
    isProfessionMenuOpen.value = false;
};

watch(mainCategories, (categories) => {
    const available = categories.filter(category => category.market_level);
    if (!available.some(category => Number(category.id) === Number(specializationCategoryId.value))) {
        specializationCategoryId.value = available[0]?.id ?? null;
    }
}, { immediate: true });

watch(specializationCategoryId, (categoryId) => {
    const category = professionOptions.value.find(item => Number(item.id) === Number(categoryId));
    if (category) professionQuery.value = category.title;
}, { immediate: true });

// Категории "Другое" для отдельного отображения
const otherCategories = computed(() => {
    return props.categories.filter(c => (c.sort_order || 0) === 99);
});

watch(() => props.groups, (groups) => {
    if (!groups.some(group => group.id === selectedGroupId.value)) {
        selectedGroupId.value = groups.some(group => group.id === requestedGroupId)
            ? requestedGroupId
            : groups[0]?.id ?? null;
    }
}, { immediate: true });

watch(selectedGroupId, (groupId) => {
    const group = props.groups.find(item => Number(item.id) === Number(groupId));
    if (group) groupQuery.value = group.title;
}, { immediate: true });

watch(selectedGroupId, () => {
    selectedTreeNode.value = null;
});

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

const specializationCategories = computed(() => {
    if (!specializationCategoryId.value) return [];
    const ids = new Set([Number(specializationCategoryId.value)]);
    props.transitions.forEach(transition => {
        if (Number(transition.from_category_id) === Number(specializationCategoryId.value)) ids.add(Number(transition.to_category_id));
        if (Number(transition.to_category_id) === Number(specializationCategoryId.value)) ids.add(Number(transition.from_category_id));
    });
    return mainCategories.value.filter(category => category.market_level && ids.has(Number(category.id)));
});
const marketCategories = computed(() => (mapScope.value === 'specialization' ? specializationCategories.value : filteredCategories.value).filter(category => category.market_level));
const maxMarketLevel = computed(() => Math.max(0, ...marketCategories.value.map(category => Number(category.market_level))));
const diagramNodes = computed(() => {
    const categories = [...marketCategories.value];

    // В режиме специализации профессии могут быть из разных направлений.
    // Их исходные market_level рассчитаны внутри своих направлений, поэтому
    // для одной общей схемы назначаем уровни по единой шкале медианных зарплат.
    if (mapScope.value !== 'specialization') return categories;

    const sorted = [...categories].sort((first, second) => Number(first.market_salary_median) - Number(second.market_salary_median));
    const buckets = Math.min(5, sorted.length);
    const levelsById = new Map(sorted.map((category, index) => [
        Number(category.id),
        Math.min(buckets, Math.floor(index * buckets / sorted.length) + 1),
    ]));

    return categories.map(category => ({ ...category, diagram_level: levelsById.get(Number(category.id)) }));
});
const diagramTransitions = computed(() => {
    const nodesById = new Map(diagramNodes.value.map(node => [Number(node.id), node]));

    return props.transitions.filter(transition => {
        if (mapScope.value !== 'specialization' && Number(transition.group_id) !== Number(selectedGroupId.value)) return false;

        const source = nodesById.get(Number(transition.from_category_id));
        const target = nodesById.get(Number(transition.to_category_id));

        // На карте специализации направление стрелки всегда соответствует
        // росту медианной зарплаты, а не локальному номеру уровня направления.
        return Boolean(source && target && Number(target.market_salary_median) > Number(source.market_salary_median));
    });
});

const groupMap = computed(() => new Map(props.groups.map(g => [g.id, g.title])));
const getGroupTitle = (groupId) => groupMap.value.get(groupId) || 'Неизвестно';
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
const getCategoriesByMarketLevel = (level) => marketCategories.value
    .filter(category => Number(category.market_level) === Number(level))
    .sort((a, b) => Number(a.market_salary_median) - Number(b.market_salary_median));

const getIconForCategory = () => '';

const skillGradeButtonClass = (gradeId, selectedGradeId = selectedSkillsGradeId.value) => [
    'rounded-md border px-3 py-1.5 text-xs font-semibold transition',
    String(selectedGradeId) === String(gradeId) && (selectedGradeId !== null || gradeId === null)
        ? 'border-[#008060] bg-[#e3f1df] text-[#006e52]'
        : 'border-[#c9cccf] bg-white text-[#4a4f54] hover:border-[#8c9196]',
];

const formatSalaryValue = (value) => {
    if (!value || value === 0) return 'Не указано';
    return new Intl.NumberFormat('ru-RU').format(value) + ' ₽';
};
const formatSalaryShort = (value) => value ? `${Math.round(value / 1000)} тыс.` : 'Нет данных';
const loadSectionStats = async (section, month, gradeId = null) => {
    if (!selectedCategory.value) return;
    if (!month && gradeId === null) { sectionStats.value = { ...sectionStats.value, [section]: null }; return; }
    const params = new URLSearchParams();
    if (month) params.set('month', month);
    if (gradeId !== null) params.set('grade_id', gradeId);
    const response = await fetch(`/hierarchy-structure/categories/${selectedCategory.value.id}/statistics?${params}`, { headers: { Accept: 'application/json' } });
    if (response.ok) sectionStats.value = { ...sectionStats.value, [section]: await response.json() };
};
watch(selectedSkillsMonth, month => loadSectionStats('skills', month));
watch(selectedLocationsMonth, month => loadSectionStats('locations', month));
watch(selectedGradesMonth, month => loadSectionStats('grades', month));
watch([selectedEmploymentMonth, selectedEmploymentGradeId], ([month, gradeId]) => loadSectionStats('employment', month, gradeId));

const formatPublicationMonth = (value) => {
    const date = new Date(`${value}T00:00:00`);
    return new Intl.DateTimeFormat('ru-RU', { month: 'short', year: '2-digit' }).format(date).replace(/\./g, '').replace(/\s*г$/u, '');
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
    selectedSkillsGradeId.value = null;
    selectedLocationsGradeId.value = null;
    selectedSalaryGradeId.value = null;
    selectedSkillsMonth.value = null;
    selectedLocationsMonth.value = null;
    selectedGradesMonth.value = null;
    selectedEmploymentMonth.value = null;
    selectedEmploymentGradeId.value = null;
    sectionStats.value = { skills: null, locations: null, grades: null, employment: null };
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

.hierarchy-select-menu {
    position: absolute;
    z-index: 30;
    top: calc(100% + 4px);
    right: 0;
    left: 0;
    max-height: 280px;
    overflow-y: auto;
    border: 1px solid #c9cccf;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 8px 18px rgba(32, 34, 35, .14);
}

.hierarchy-select-option {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 9px 12px;
    color: #202223;
    font-size: .875rem;
    text-align: left;
}

.hierarchy-select-option:hover { background: #f1f8f5; color: #006e52; }
.hierarchy-select-option small { color: #6d7175; font-size: .75rem; }
.hierarchy-select-empty { padding: 10px 12px; color: #6d7175; font-size: .875rem; }

</style>
