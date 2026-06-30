<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import FlashMessage from '@/Components/FlashMessage.vue';

const showingSidebar = ref(false);
const page = usePage();

const navItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Categorías', route: 'categories.index', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { name: 'Productos', route: 'items.index', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
    { name: 'Nuevo Pedido', route: 'orders.create', icon: 'M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { name: 'Reportes', route: 'reports.index', icon: 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055zM20.488 9H15V3.512A9.025 9.025 0 0120.488 9z' },
];

const visibleNavItems = computed(() => {
    if (page.props.auth.user?.id !== 3) return navItems;

    return navItems.filter(item => !['categories.index', 'items.index'].includes(item.route));
});

const pageKey = computed(() => page.component);

function isActive(name) {
    return route().current(name);
}
</script>

<template>
    <div class="grain-overlay min-h-screen bg-surface">
        <FlashMessage />

        <!-- Mobile sidebar overlay -->
        <Transition name="overlay">
            <div v-if="showingSidebar" class="fixed inset-0 z-40 bg-stone-900/50 backdrop-blur-sm lg:hidden" @click="showingSidebar = false" />
        </Transition>

        <!-- Mobile sidebar -->
        <Transition name="sidebar">
            <aside
                v-if="showingSidebar"
                class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-white shadow-2xl lg:hidden"
            >
                <div class="flex h-16 items-center justify-between border-b border-stone-100 px-4">
                    <Link :href="route('dashboard')" class="flex items-center gap-2.5" @click="showingSidebar = false">
                        <ApplicationLogo class="h-8 w-auto" />
                        <span class="text-sm font-bold text-stone-800 tracking-tight">La Despensa</span>
                    </Link>
                    <button @click="showingSidebar = false" class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <nav class="flex-1 space-y-0.5 p-3">
                    <Link
                        v-for="item in visibleNavItems"
                        :key="item.name"
                        :href="route(item.route)"
                        @click="showingSidebar = false"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150"
                        :class="isActive(item.route)
                            ? 'bg-brand-50 text-brand-700 shadow-xs'
                            : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                        </svg>
                        {{ item.name }}
                    </Link>
                </nav>
            </aside>
        </Transition>

        <!-- Desktop sidebar -->
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-stone-200 bg-white shadow-sm lg:flex">
            <div class="flex h-16 items-center gap-2.5 border-b border-stone-100 px-6">
                <ApplicationLogo class="h-8 w-auto" />
                <span class="text-sm font-bold text-stone-800 tracking-tight">La Despensa</span>
            </div>
            <nav class="flex-1 space-y-0.5 p-3">
                <Link
                    v-for="item in visibleNavItems"
                    :key="item.name"
                    :href="route(item.route)"
                    class="group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150"
                    :class="isActive(item.route)
                        ? 'bg-brand-50 text-brand-700 shadow-xs'
                        : 'text-stone-600 hover:bg-stone-100 hover:text-stone-900'"
                >
                    <svg
                        class="h-5 w-5 shrink-0 transition-transform duration-150 group-hover:scale-110"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                    </svg>
                    {{ item.name }}
                </Link>
            </nav>
            <div class="border-t border-stone-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-700 text-xs font-bold text-white shadow-xs font-serif italic">
                        {{ (page.props.auth.user.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-stone-900 truncate">{{ page.props.auth.user.name }}</p>
                        <p class="text-xs text-stone-500 truncate">{{ page.props.auth.user.email }}</p>
                    </div>
                    <Link
                        :href="route('profile.edit')"
                        class="rounded-lg p-1.5 text-stone-400 hover:bg-stone-100 hover:text-stone-600"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </Link>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-lg p-1.5 text-stone-400 hover:bg-rose-50 hover:text-rose-600"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top bar (mobile) -->
            <div class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-stone-200 bg-white/90 backdrop-blur-md px-4 lg:hidden">
                <button @click="showingSidebar = true" class="rounded-lg p-1.5 text-stone-500 hover:bg-stone-100">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <ApplicationLogo class="h-7 w-auto" />
                <span class="text-sm font-bold text-stone-800 tracking-tight">La Despensa</span>
                <div class="flex-1" />
                <div class="flex items-center gap-2">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-brand-400 to-brand-600 text-[10px] font-bold text-white font-serif italic">
                        {{ (page.props.auth.user.name || 'U').charAt(0).toUpperCase() }}
                    </div>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="rounded-lg p-1.5 text-stone-400 hover:text-rose-600"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Page header -->
            <Transition name="fade">
                <header v-if="$slots.header" class="border-b border-stone-200 bg-white/90 backdrop-blur-md">
                    <div class="px-4 py-5 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>
            </Transition>

            <!-- Page content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <Transition name="page" mode="out-in">
                    <div :key="pageKey">
                        <slot />
                    </div>
                </Transition>
            </main>
        </div>
    </div>
</template>
