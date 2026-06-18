<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const pageKey = computed(() => page.component);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface">
        <!-- Decorative background -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 h-96 w-96 rounded-full bg-brand-200/40 blur-3xl" />
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-brand-300/30 blur-3xl" />
            <div class="absolute top-1/3 left-1/4 h-64 w-64 rounded-full bg-amber-100/20 blur-3xl" />
        </div>

        <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
            <!-- Logo area -->
            <div class="mb-10 text-center">
                <Link href="/" class="inline-flex flex-col items-center gap-2 group">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-full bg-brand-200/40 blur-md group-hover:blur-lg transition-all duration-300" />
                        <ApplicationLogo class="relative h-20 w-auto drop-shadow-sm" />
                    </div>
                    <span class="font-serif text-2xl font-normal text-stone-800 -mb-1">La Despensa</span>
                    <span class="text-xs font-medium text-stone-400 tracking-widest uppercase">Sistema de Pedidos</span>
                </Link>
            </div>

            <!-- Auth card -->
            <div class="w-full max-w-md">
                <Transition name="page" mode="out-in">
                    <div :key="pageKey">
                        <div class="overflow-hidden rounded-2xl bg-white shadow-xl shadow-stone-200/60 ring-1 ring-stone-200/50">
                            <div class="px-8 py-8">
                                <slot />
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>
