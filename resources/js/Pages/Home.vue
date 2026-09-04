<template>
  <AppLayout>
    <main class="flex-1">
      <section class="border-b border-[#eef0ee] bg-white">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(420px,.95fr)] lg:px-8 lg:py-20">
          <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[.14em] text-[#2e6b4f]">Навигатор развития в IT</p>
            <h1 class="mt-5 max-w-[17ch] text-4xl font-bold leading-[1.06] tracking-[-.035em] text-[#15191b] sm:text-6xl">Ваш уровень, следующая роль и понятный план развития</h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-[#545c60]">Ответьте на вопросы о реальном опыте, добавьте навыки и получите персональный ориентир: что уже подтверждает ваш уровень и куда двигаться дальше.</p>
            <div class="mt-8 flex flex-wrap items-center gap-4"><Link :href="route('recommendations.index')" class="rounded-[10px] bg-[#2e6b4f] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#22523c]">Пройти оценку</Link><Link :href="route('hierarchy.structure')" class="rounded-[10px] border border-[#c9cccf] bg-white px-6 py-4 text-sm font-semibold text-[#202223] transition hover:bg-[#f6f7f6]">Сначала посмотреть карту ролей</Link></div>
            <p class="mt-4 text-sm text-[#8b9296]">5 вопросов об опыте, затем навыки и карьерная цель. Без регистрации.</p>
            <div class="mt-10 flex flex-wrap gap-x-10 gap-y-5 border-t border-[#eef0ee] pt-6"><div v-for="fact in facts" :key="fact.value" class="max-w-[15rem]"><b class="block text-2xl tracking-[-.02em] text-[#15191b]">{{ fact.value }}</b><span class="mt-1 block text-sm leading-5 text-[#8b9296]">{{ fact.label }}</span></div></div>
          </div>

          <div class="self-center">
            <p class="mb-3 text-sm text-[#8b9296]">Пример готового отчёта</p>
            <article class="overflow-hidden rounded-[14px] border border-[#e3e6e4] bg-white shadow-[0_14px_34px_-22px_rgba(21,25,27,.28)]">
              <div class="flex items-center gap-2 border-b border-[#eef0ee] bg-[#fbfcfb] px-5 py-3"><span v-for="dot in 3" :key="dot" class="h-2 w-2 rounded-full bg-[#dfe3e0]"></span><span class="ml-1 text-xs text-[#8b9296]">Карьерный план</span></div>
              <div class="space-y-4 p-6 sm:p-7"><div class="grid gap-3 sm:grid-cols-3"><div class="rounded-lg border border-[#e3e6e4] p-3"><p class="text-xs text-[#8b9296]">Текущий уровень</p><b class="mt-1 block text-lg tracking-[-.02em]">Middle</b></div><div class="rounded-lg border border-[#e3e6e4] p-3"><p class="text-xs text-[#8b9296]">Следующий уровень</p><b class="mt-1 block text-lg tracking-[-.02em]">Senior</b></div><div class="rounded-lg border border-[#e3e6e4] p-3"><p class="text-xs text-[#8b9296]">Навыков в профиле</p><b class="mt-1 block text-lg tracking-[-.02em]">4</b></div></div><section class="rounded-lg border border-[#b7d9c8] bg-[#f1f8f5] p-4"><p class="text-sm font-semibold">Почему этот уровень</p><p class="mt-1 text-sm leading-5 text-[#545c60]">Самостоятельно ведёте задачи и умеете оценивать варианты и риски.</p></section><section class="rounded-lg border border-[#e3e6e4] p-4"><div class="flex items-center justify-between gap-3"><p class="text-sm font-semibold">Что подтянуть для Senior</p><span class="rounded-md bg-[#fbeee9] px-2 py-1 text-xs font-semibold text-[#a63d2b]">38% вакансий</span></div><p class="mt-2 text-sm text-[#545c60]">Docker и контейнеризация</p></section></div>
              <p class="border-t border-[#eef0ee] bg-[#fbfcfb] px-7 py-4 text-sm text-[#545c60]">Дальше - конкретные шаги и примеры того, что подтвердит ваш уровень.</p>
            </article>
          </div>
        </div>
      </section>

      <section class="bg-[#f6f7f6]"><div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-3 lg:px-8"><article v-for="step in steps" :key="step.number" class="flex gap-4"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full border border-[#e3e6e4] bg-white text-xs font-bold text-[#545c60]">{{ step.number }}</span><div><h2 class="font-semibold text-[#15191b]">{{ step.title }}</h2><p class="mt-1 text-sm leading-6 text-[#545c60]">{{ step.text }}</p></div></article></div></section>
    </main>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const facts = [
  { value: '3 шага', label: 'опыт, навыки и карьерная цель' },
  { value: '5 грейдов', label: 'от Intern до Lead' },
  { value: '1 отчёт', label: 'с понятным планом развития' },
];

const steps = [
  { number: '1', title: 'Оцените рабочий уровень', text: 'Ответьте на вопросы о задачах, ответственности и самостоятельности.' },
  { number: '2', title: 'Добавьте навыки и цель', text: 'Укажите инструменты, с которыми работали, и интересующее направление.' },
  { number: '3', title: 'Получите карьерный план', text: 'Увидите следующую ступень, дефициты навыков и план действий.' },
];
</script>
