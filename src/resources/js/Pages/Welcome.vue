<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

defineProps({
    canLogin: { type: Boolean },
    canRegister: { type: Boolean },
});
</script>

<template>
    <Head title="Bienvenido" />

    <div class="relative min-h-screen overflow-hidden bg-gradient-to-br from-stone-950 via-stone-900 to-amber-950">
        <!-- Grain texture -->
        <div class="absolute inset-0 opacity-[0.04] pointer-events-none" style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E&quot;); background-repeat: repeat;" />

        <!-- Decorative blobs -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-48 -right-48 h-[600px] w-[600px] rounded-full bg-brand-600/5 blur-3xl" />
            <div class="absolute -bottom-48 -left-48 h-[600px] w-[600px] rounded-full bg-amber-500/5 blur-3xl" />
            <div class="absolute top-1/3 right-1/4 h-80 w-80 rounded-full bg-amber-600/5 blur-3xl" />
        </div>

        <!-- Nav -->
        <nav class="relative z-10 flex items-center justify-between px-6 py-6 sm:px-12 lg:px-20">
            <div class="flex items-center gap-3">
                <ApplicationLogo class="h-8 w-auto" />
                <span class="font-serif text-xl text-white">La Despensa</span>
            </div>
            <div v-if="canLogin" class="flex items-center gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="rounded-lg border border-white/15 bg-white/5 px-5 py-2 text-sm font-medium text-white/90 backdrop-blur-sm transition-all hover:bg-white/15 hover:border-white/25"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="text-sm font-medium text-stone-400 transition-colors hover:text-white"
                    >
                        Iniciar Sesión
                    </Link>
                    <!-- <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-lg bg-brand-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-900/30 transition-all hover:bg-brand-500 hover:shadow-xl hover:shadow-brand-800/30 active:scale-[0.97]"
                    >
                        Registrarse
                    </Link> -->
                </template>
            </div>
        </nav>

        <!-- Hero -->
        <main class="relative z-10 flex flex-1 items-center px-6 pb-24 sm:px-12 lg:px-20">
            <div class="mx-auto max-w-5xl w-full">
                <div class="grid gap-12 lg:grid-cols-5 lg:gap-16 items-center">
                    <!-- Left column: text -->
                    <div class="lg:col-span-3">
                        <div class="inline-flex items-center gap-2 rounded-full border border-brand-700/30 bg-brand-900/20 px-4 py-1.5 text-xs font-medium text-brand-300/90 backdrop-blur-sm mb-8">
                            <span class="flex h-1.5 w-1.5 rounded-full bg-brand-400 animate-pulso-slow" />
                            Sistema de gestión de pedidos
                        </div>

                        <h1 class="font-serif text-5xl font-normal leading-tight text-white sm:text-6xl lg:text-7xl">
                            Pedidos de
                            <span class="text-brand-400">despensa</span>
                            <br />sin complicaciones
                        </h1>

                        <p class="mt-6 max-w-lg text-base leading-relaxed text-stone-400 sm:text-lg">
                            Sube tu archivo Excel, revisa la vista previa con cálculos automáticos de IVA y descarga el pedido en XLSX o PDF. Todo en minutos.
                        </p>

                        <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                            <Link
                                :href="$page.props.auth.user ? route('dashboard') : route('login')"
                                class="group inline-flex items-center gap-2.5 rounded-xl bg-brand-600 px-7 py-3.5 text-base font-semibold text-white shadow-xl shadow-brand-900/30 transition-all hover:bg-brand-500 hover:shadow-2xl hover:shadow-brand-800/30 active:scale-[0.98]"
                            >
                                {{ $page.props.auth.user ? 'Ir al Dashboard' : 'Comenzar ahora' }}
                                <svg class="h-5 w-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Right column: feature cards -->
                    <div class="lg:col-span-2 grid gap-4">
                        <div class="rounded-xl border border-white/5 bg-white/[0.03] backdrop-blur-sm p-6 transition-all hover:bg-white/[0.06] hover:border-white/10">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-600/20">
                                    <svg class="h-5 w-5 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 17.25v3.375c0 .621.504 1.125 1.125 1.125h18.75c.621 0 1.125-.504 1.125-1.125V7.378c0-.621-.504-1.125-1.125-1.125H3.75c-.621 0-1.125.504-1.125 1.125V17.25zm0 0V15M3.75 17.25h.008M21.75 6.75V4.875c0-.621-.504-1.125-1.125-1.125H3.75c-.621 0-1.125.504-1.125 1.125V6.75" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-serif text-lg text-white">Carga tu Excel</p>
                                    <p class="text-sm text-stone-500">Archivos .xlsx o .xls con los ítems y cantidades</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/5 bg-white/[0.03] backdrop-blur-sm p-6 transition-all hover:bg-white/[0.06] hover:border-white/10">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-600/20">
                                    <svg class="h-5 w-5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-serif text-lg text-white">Vista previa</p>
                                    <p class="text-sm text-stone-500">Cálculos automáticos de subtotal, IVA y total</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl border border-white/5 bg-white/[0.03] backdrop-blur-sm p-6 transition-all hover:bg-white/[0.06] hover:border-white/10">
                            <div class="flex items-center gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-600/20">
                                    <svg class="h-5 w-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-serif text-lg text-white">Exporta</p>
                                    <p class="text-sm text-stone-500">Descarga en XLSX o PDF con un solo clic</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
