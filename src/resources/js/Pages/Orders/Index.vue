<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    orders: { type: Object, required: true },
    canDeleteOrders: { type: Boolean, required: true },
});

const confirmDialog = ref(null);

const ordersCount = computed(() => props.orders.total ?? props.orders.data?.length ?? 0);

function confirmDelete(order) {
    confirmDialog.value.show({
        title: 'Eliminar pedido',
        message: `¿Eliminar el pedido de ${order.sede} del ${order.fecha}? Esta acción no se puede deshacer.`,
        confirmText: 'Eliminar',
        method: 'delete',
        url: route('orders.destroy', order.id),
    });
}

function formatPrice(value) {
    return Number(value || 0).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
}
</script>

<template>
    <Head title="Pedidos" />
    <ConfirmDialog ref="confirmDialog" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Historial operativo</p>
                    <h2 class="mt-1 text-xl font-semibold text-stone-800">Pedidos realizados</h2>
                    <p class="mt-1 text-sm text-stone-500">{{ ordersCount }} pedidos registrados en el sistema</p>
                </div>
                <Link :href="route('orders.create')" class="btn-primary btn-sm">
                    <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Pedido
                </Link>
            </div>
        </template>

        <div class="card">
            <div v-if="orders.data && orders.data.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-100">
                    <thead>
                        <tr class="bg-stone-50/80">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Sede</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Remisión</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Fecha</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Realizado por</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Productos</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Total</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="order in orders.data" :key="order.id" class="transition-colors hover:bg-stone-50/70">
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-stone-900">{{ order.sede }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">#{{ order.remision }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ order.fecha }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ order.user_name }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ order.items_count }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-stone-900">{{ formatPrice(order.total) }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <Link :href="route('orders.show', order.id)" class="btn-ghost btn-sm mr-1">
                                    Ver
                                </Link>
                                <button v-if="canDeleteOrders" type="button" class="btn-danger btn-sm" @click="confirmDelete(order)">
                                    <svg class="mr-1 h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="px-5 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-4 text-sm text-stone-500">No hay pedidos registrados aún.</p>
                <Link :href="route('orders.create')" class="mt-3 inline-flex items-center text-sm font-medium text-brand-600 hover:text-brand-800">
                    Crear el primer pedido →
                </Link>
            </div>

            <div v-if="orders.links && orders.links.length > 3" class="flex flex-col gap-3 border-t border-stone-100 bg-stone-50/50 px-5 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-stone-600">
                    Mostrando {{ orders.from }}-{{ orders.to }} de {{ orders.total }} resultados
                </p>
                <nav class="flex flex-wrap gap-1">
                    <template v-for="(link, index) in orders.links" :key="index">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="inline-flex items-center justify-center rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                            :class="link.active ? 'bg-brand-600 text-white' : 'text-stone-600 hover:bg-stone-200'"
                        />
                        <span v-else v-html="link.label" class="inline-flex items-center justify-center rounded-md px-3 py-1.5 text-sm text-stone-400" />
                    </template>
                </nav>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
