<template>
  <AppLayout>
    <main class="flex-1">
      <section class="border-b border-[#eef0ee] bg-white">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-[minmax(0,1fr)_minmax(420px,.95fr)] lg:px-8 lg:py-12">
          <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-[.14em] text-[#2e6b4f]">Персональный карьерный навигатор в IT</p>
            <h1 class="mt-3 max-w-[17ch] text-4xl font-bold leading-[1.06] tracking-[-.035em] text-[#15191b] sm:text-5xl">Ваш уровень, следующая профессия и понятный план развития</h1>
            <p class="mt-4 max-w-xl text-lg leading-7 text-[#545c60]">Ответьте на вопросы о реальном опыте, добавьте навыки и получите персональный ориентир: что уже подтверждает ваш уровень и куда двигаться дальше.</p>
            <div class="mt-6 flex flex-wrap items-center gap-4"><Link :href="route('recommendations.index')" class="rounded-[10px] bg-[#2e6b4f] px-6 py-4 text-sm font-semibold text-white transition hover:bg-[#22523c]">Пройти карьерную оценку</Link><Link :href="route('hierarchy.structure')" class="rounded-[10px] border border-[#c9cccf] bg-white px-6 py-4 text-sm font-semibold text-[#202223] transition hover:bg-[#f6f7f6]">Открыть карту профессий</Link></div>
          </div>

          <div class="self-center">
            <p class="mb-3 text-sm text-[#8b9296]">Пример готового отчёта</p>
            <article class="overflow-hidden rounded-[14px] border border-[#e3e6e4] bg-white shadow-[0_14px_34px_-22px_rgba(21,25,27,.28)]">
              <div class="flex items-center gap-2 border-b border-[#eef0ee] bg-[#fbfcfb] px-5 py-3"><span v-for="dot in 3" :key="dot" class="h-2 w-2 rounded-full bg-[#dfe3e0]"></span><span class="ml-1 text-xs text-[#8b9296]">Карьерный план</span></div>
              <div class="space-y-4 p-6 sm:p-7"><div class="grid gap-3 sm:grid-cols-3"><div class="rounded-lg border border-[#e3e6e4] p-3"><p class="text-xs text-[#8b9296]">Рабочий уровень</p><b class="mt-1 block text-lg tracking-[-.02em]">Middle</b><p class="mt-1 text-xs text-[#6d7477]">по опыту и самостоятельности</p></div><div class="rounded-lg border border-[#e3e6e4] p-3"><p class="text-xs text-[#8b9296]">Следующий ориентир</p><b class="mt-1 block text-lg tracking-[-.02em]">Senior</b><p class="mt-1 text-xs text-[#6d7477]">больше влияния на решения</p></div><div class="rounded-lg border border-[#e3e6e4] p-3"><p class="text-xs text-[#8b9296]">Рынок направления</p><b class="mt-1 block text-lg tracking-[-.02em]">124 вакансии</b><p class="mt-1 text-xs text-[#6d7477]">Middle и Senior</p></div></div><section class="rounded-lg border border-[#b7d9c8] bg-[#f1f8f5] p-4"><div class="flex items-center justify-between gap-3"><p class="text-sm font-semibold">Разработка</p><span class="rounded-md bg-white px-2 py-1 text-xs font-semibold text-[#2e6b4f]">2 из 2 навыков</span></div><p class="mt-2 text-sm font-semibold text-[#202223]">Бэкенд-разработчик</p><p class="mt-1 text-sm leading-5 text-[#545c60]">PHP и Laravel встречаются в вакансиях профессии. Медианная зарплата - 284 500 ₽.</p></section><section class="rounded-lg border border-[#e3e6e4] p-4"><div class="flex items-center justify-between gap-3"><p class="text-sm font-semibold">Следующий фокус</p><span class="rounded-md bg-[#fbeee9] px-2 py-1 text-xs font-semibold text-[#a63d2b]">Senior</span></div><p class="mt-2 text-sm text-[#545c60]">PostgreSQL - 26% вакансий, Docker - 18%. Покажите сложную задачу целиком: решение, риски и результат.</p></section></div>
              <p class="border-t border-[#eef0ee] bg-[#fbfcfb] px-7 py-4 text-sm text-[#545c60]">Полный отчёт показывает подходящие направления, навыки профессий, данные вакансий и персональный план развития.</p>
            </article>
          </div>
        </div>
      </section>

      <section class="bg-[#f6f7f6]"><div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"><p class="mb-5 text-sm font-semibold text-[#2e6b4f]">Как проходит карьерная оценка</p><div class="grid gap-8 md:grid-cols-3"><article v-for="step in steps" :key="step.number" class="flex gap-4"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full border border-[#e3e6e4] bg-white text-xs font-bold text-[#545c60]">{{ step.number }}</span><div><h2 class="font-semibold text-[#15191b]">{{ step.title }}</h2><p class="mt-1 text-sm leading-6 text-[#545c60]">{{ step.text }}</p></div></article></div></div></section>
    </main>
  </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const steps = [
  { number: '1', title: 'Оцените рабочий уровень', text: 'Ответьте на вопросы о задачах, ответственности и самостоятельности.' },
  { number: '2', title: 'Добавьте навыки и цель', text: 'Укажите инструменты, с которыми работали, и интересующее направление.' },
  { number: '3', title: 'Получите карьерный план', text: 'Увидите следующую ступень, дефициты навыков и план действий.' },
];
</script>
