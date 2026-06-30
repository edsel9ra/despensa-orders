<script setup>
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const canAccessCatalog = computed(() => page.props.auth.user?.id !== 3);

function formatPrice(value) {
    return Number(value).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-stone-800">Dashboard</h2>
                <Link :href="route('orders.create')" class="btn-primary btn-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Nuevo Pedido
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="card p-5 animate-fade-in-up animate-fade-in-up-1">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-brand-100 to-brand-200">
                            <svg class="h-6 w-6 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-stone-500">Categorías</p>
                            <p class="text-2xl font-bold text-stone-900">{{ stats.categories_count }}</p>
                        </div>
                    </div>
                    <div v-if="canAccessCatalog" class="mt-4">
                        <Link :href="route('categories.index')" class="text-sm font-medium text-brand-600 hover:text-brand-800 transition-colors">
                            Ver todas →
                        </Link>
                    </div>
                </div>

                <div class="card p-5 animate-fade-in-up animate-fade-in-up-2">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-200">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-stone-500">Productos</p>
                            <p class="text-2xl font-bold text-stone-900">{{ stats.items_count }}</p>
                        </div>
                    </div>
                    <div v-if="canAccessCatalog" class="mt-4">
                        <Link :href="route('items.index')" class="text-sm font-medium text-brand-600 hover:text-brand-800 transition-colors">
                            Ver todos →
                        </Link>
                    </div>
                </div>

                <div class="card p-5 animate-fade-in-up animate-fade-in-up-3">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200">
                            <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-stone-500">Pedidos</p>
                            <p class="text-2xl font-bold text-stone-900">{{ stats.orders_count }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent orders -->
            <div class="card">
                <div class="px-5 py-4 border-b border-stone-100">
                    <h3 class="text-base font-semibold text-stone-900">Pedidos Recientes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table v-if="stats.recent_orders.length > 0" class="min-w-full divide-y divide-stone-100">
                        <thead>
                            <tr class="bg-stone-50/50">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Remisión</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Sede</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Realizado por</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Fecha</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Items</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Total</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr v-for="order in stats.recent_orders" :key="order.id" class="hover:bg-stone-50 transition-colors">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-stone-900">#{{ order.remision }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ order.sede }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ order.user_name }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ order.fecha }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ order.items_count }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-stone-900">{{ formatPrice(order.total) }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    <Link :href="route('orders.show', order.id)" class="text-sm font-medium text-brand-600 hover:text-brand-800">
                                        Ver →
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else class="px-5 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-4 text-sm text-stone-500">No hay pedidos registrados aún.</p>
                        <Link :href="route('orders.create')" class="mt-3 inline-flex items-center text-sm font-medium text-brand-600 hover:text-brand-800">
                            Crear el primer pedido →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
