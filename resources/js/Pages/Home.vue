<template>
    <AppLayout>
        <div class="flex-1">
            <section class="border-b border-[#e1e3e5] bg-white">
                <div class="mx-auto grid max-w-7xl gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.15fr_.85fr] lg:px-8 lg:py-24">
                    <div class="max-w-2xl">
                        <p class="mb-5 text-sm font-semibold uppercase tracking-[.16em] text-[#008060]">Навигатор развития в IT</p>
                        <h1 class="max-w-xl text-4xl font-semibold tracking-[-.03em] text-[#202223] sm:text-6xl">Поймите, где вы сейчас и как двигаться дальше.</h1>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-[#616161]">IT Compass помогает определить текущий уровень, выбрать достижимую следующую роль и составить понятный план развития навыков.</p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <Link :href="route('recommendations.index')" class="rounded-md bg-[#008060] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#006e52]">Получить карьерный план</Link>
                            <Link :href="route('hierarchy.structure')" class="rounded-md border border-[#c9cccf] bg-white px-5 py-3 text-sm font-semibold text-[#202223] transition hover:bg-[#f6f6f7]">Посмотреть карту ролей</Link>
                        </div>
                    </div>
                    <div class="self-center rounded-xl border border-[#e1e3e5] bg-[#f6f6f7] p-6 shadow-sm">
                        <p class="text-sm font-medium text-[#6d7175]">Что вы получите</p>
                        <div class="mt-5 space-y-4">
                            <div class="rounded-lg border border-[#e1e3e5] bg-white p-4"><p class="text-sm font-semibold">Точка на старте</p><p class="mt-1 text-sm text-[#6d7175]">Поймёте, какие навыки уже подтверждают ваш уровень.</p></div>
                            <div class="rounded-lg border border-[#e1e3e5] bg-white p-4"><p class="text-sm font-semibold">Следующий ориентир</p><p class="mt-1 text-sm text-[#6d7175]">Увидите доступные роли и что нужно подтянуть для перехода.</p></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="max-w-xl"><h2 class="text-3xl font-semibold tracking-tight">От самооценки — к плану действий</h2><p class="mt-3 text-[#616161]">Не просто список вакансий, а понятный маршрут профессионального роста.</p></div>
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <article v-for="feature in features" :key="feature.title" class="rounded-xl border border-[#e1e3e5] bg-white p-6 shadow-sm"><span class="grid h-10 w-10 place-items-center rounded-md bg-[#e3f1df] font-semibold text-[#008060]">{{ feature.number }}</span><h3 class="mt-5 text-lg font-semibold">{{ feature.title }}</h3><p class="mt-2 text-sm leading-6 text-[#6d7175]">{{ feature.description }}</p></article>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const features = [
    { number: '01', title: 'Определите свой уровень', description: 'Укажите навыки и опыт, чтобы увидеть свою текущую точку на карте развития.' },
    { number: '02', title: 'Выберите следующую роль', description: 'Сравните близкие карьерные переходы и поймите, к чему реально двигаться дальше.' },
    { number: '03', title: 'Закройте пробелы в навыках', description: 'Получите список компетенций для развития и последовательность следующих шагов.' },
];
</script>
