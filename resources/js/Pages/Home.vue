<template>
  <AppLayout>
    <main class="flex-1">
      <section class="border-b border-[#eef0ee] bg-white">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(420px,.95fr)] lg:px-8 lg:py-20">
          <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[.14em] text-[#2e6b4f]">Навигатор развития в IT</p>
            <h1 class="mt-5 max-w-[17ch] text-4xl font-bold leading-[1.06] tracking-[-.035em] text-[#15191b] sm:text-6xl">Ваш уровень, следующая роль и понятный план развития</h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-[#545c60]">Ответьте на вопросы о реальном опыте, добавьте навыки и получите персональный ориентир: что уже подтверждает ваш уровень и куда двигаться дальше.</p>
            <div class="mt-8 flex flex-wrap items-center gap-4"><Link :href="route('recommendations.index')" class="rounded-[10px] bg-[#2e6b4f] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#22523c]">Пройти оценку</Link><Link :href="route('hierarchy.structure')" class="border-b border-[#e3e6e4] pb-0.5 text-sm text-[#545c60] transition hover:border-[#8b9296] hover:text-[#15191b]">Сначала посмотреть карту ролей</Link></div>
            <p class="mt-4 text-sm text-[#8b9296]">5 вопросов об опыте, затем навыки и карьерная цель. Без регистрации.</p>
            <div class="mt-10 flex flex-wrap gap-x-10 gap-y-5 border-t border-[#eef0ee] pt-6"><div v-for="fact in facts" :key="fact.value" class="max-w-[15rem]"><b class="block text-2xl tracking-[-.02em] text-[#15191b]">{{ fact.value }}</b><span class="mt-1 block text-sm leading-5 text-[#8b9296]">{{ fact.label }}</span></div></div>
          </div>

          <div class="self-center">
            <p class="mb-3 text-sm text-[#8b9296]">Пример готового отчёта</p>
            <article class="overflow-hidden rounded-[14px] border border-[#e3e6e4] bg-white shadow-[0_14px_34px_-22px_rgba(21,25,27,.28)]">
              <div class="flex items-center gap-2 border-b border-[#eef0ee] bg-[#fbfcfb] px-5 py-3"><span v-for="dot in 3" :key="dot" class="h-2 w-2 rounded-full bg-[#dfe3e0]"></span><span class="ml-1 text-xs text-[#8b9296]">Карьерный план</span></div>
              <div class="p-6 sm:p-7"><p class="text-sm text-[#8b9296]">Текущий уровень</p><p class="mt-1 text-2xl font-bold tracking-[-.02em]">Middle Backend</p><p class="mt-2 text-sm text-[#545c60]">Подтверждён опытом и ответами о самостоятельности.</p><div class="mt-3 flex gap-1"><i v-for="item in 5" :key="item" class="h-1.5 flex-1 rounded bg-[#2e6b4f]"></i></div><div class="my-6 h-px bg-[#eef0ee]"></div><div class="flex items-end justify-between gap-4"><div><p class="text-sm text-[#8b9296]">Следующая ступень</p><h2 class="mt-1 text-xl font-bold tracking-[-.02em]">Senior Backend</h2></div><span class="text-xs text-[#545c60]">реалистичный ориентир</span></div><div class="mt-5 divide-y divide-[#eef0ee]"><div v-for="skill in previewSkills" :key="skill.title" class="flex items-center justify-between gap-4 py-3 text-sm"><span>{{ skill.title }}</span><span :class="skill.className" class="rounded-md px-2.5 py-1 text-xs font-semibold">{{ skill.label }}</span></div></div></div>
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

const previewSkills = [
  { title: 'Архитектурные решения', label: 'развить', className: 'bg-[#fbeee9] text-[#a63d2b]' },
  { title: 'Код-ревью и наставничество', label: 'усилить', className: 'bg-[#faf3e1] text-[#87681a]' },
  { title: 'Работа с API', label: 'уже есть', className: 'bg-[#eaf2ed] text-[#22523c]' },
];

const steps = [
  { number: '1', title: 'Оцените рабочий уровень', text: 'Ответьте на вопросы о задачах, ответственности и самостоятельности.' },
  { number: '2', title: 'Добавьте навыки и цель', text: 'Укажите инструменты, с которыми работали, и интересующее направление.' },
  { number: '3', title: 'Получите карьерный план', text: 'Увидите следующую ступень, дефициты навыков и план действий.' },
];
</script>
