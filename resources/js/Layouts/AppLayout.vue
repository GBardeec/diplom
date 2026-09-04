<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const menuOpen = ref(false);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[#f6f6f7] text-[#202223]">
        <header class="sticky top-0 z-30 border-b border-[#e1e3e5] bg-white">
            <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                <Link :href="route('home')" class="flex items-center gap-2 text-[#202223]">
                    <span class="grid h-8 w-8 place-items-center rounded-md bg-[#008060] text-sm text-white">IC</span>
                    <span class="text-lg font-semibold tracking-tight">IT Compass</span>
                </Link>

                <nav aria-label="Основная навигация" class="hidden flex-wrap justify-end gap-x-4 gap-y-2 text-sm sm:flex sm:text-base">
                    <Link :href="route('home')" class="font-medium text-[#616161] transition hover:text-[#008060]">Главная</Link>
                    <Link :href="route('recommendations.index')" class="font-medium text-[#616161] transition hover:text-[#008060]">Рекомендации</Link>
                    <Link :href="route('hierarchy.structure')" class="font-medium text-[#616161] transition hover:text-[#008060]">Иерархия</Link>
                </nav>

                <button type="button" class="grid h-10 w-10 place-items-center rounded-md border border-[#c9cccf] text-[#202223] transition hover:bg-[#f6f6f7] sm:hidden" :aria-expanded="menuOpen" aria-controls="mobile-navigation" aria-label="Открыть меню" @click="menuOpen = !menuOpen">
                    <svg v-if="!menuOpen" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
                </button>
            </div>
            <nav v-if="menuOpen" id="mobile-navigation" aria-label="Основная навигация" class="border-t border-[#e1e3e5] bg-white px-4 py-3 sm:hidden">
                <div class="mx-auto grid max-w-7xl gap-1">
                    <Link :href="route('home')" class="rounded-md px-3 py-2.5 font-medium text-[#616161] transition hover:bg-[#f6f6f7] hover:text-[#008060]" @click="menuOpen = false">Главная</Link>
                    <Link :href="route('recommendations.index')" class="rounded-md px-3 py-2.5 font-medium text-[#616161] transition hover:bg-[#f6f6f7] hover:text-[#008060]" @click="menuOpen = false">Рекомендации</Link>
                    <Link :href="route('hierarchy.structure')" class="rounded-md px-3 py-2.5 font-medium text-[#616161] transition hover:bg-[#f6f6f7] hover:text-[#008060]" @click="menuOpen = false">Иерархия</Link>
                </div>
            </nav>
        </header>

        <main class="flex flex-1 flex-col">
            <slot />
        </main>

        <footer class="border-t border-[#e1e3e5] bg-white py-5">
            <div class="mx-auto flex max-w-7xl flex-col gap-1 px-4 text-sm text-[#6d7175] sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <p>IT Compass</p>
                <p>Разработал Мухаматдинов И. Р. · Научный руководитель Лашкарев А. Н.</p>
            </div>
        </footer>
    </div>
</template>
