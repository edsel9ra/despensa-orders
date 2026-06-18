<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

const props = defineProps({
    categories: { type: Array, required: true },
});

const confirmDialog = ref(null);

function confirmDelete(category) {
    confirmDialog.value.show({
        title: 'Eliminar categoría',
        message: `¿Eliminar "${category.nombre}"? Los productos asociados se quedarán sin categoría.`,
        confirmText: 'Eliminar',
        method: 'delete',
        url: route('categories.destroy', category.id),
    });
}
</script>

<template>
    <Head title="Categorías" />
    <ConfirmDialog ref="confirmDialog" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Categorías</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ categories.length }} categorías registradas</p>
                </div>
                <Link :href="route('categories.create')" class="btn-primary btn-sm">
                    <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nueva Categoría
                </Link>
            </div>
        </template>

        <div class="card">
            <div v-if="categories.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/80">
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nombre</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Orden</th>
                            <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Aplica IVA</th>
                            <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="whitespace-nowrap px-5 py-4 text-sm font-medium text-gray-900">{{ category.nombre }}</td>
                            <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600">{{ category.orden }}</td>
                            <td class="whitespace-nowrap px-5 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="category.aplica_iva ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ category.aplica_iva ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-right">
                                <Link :href="route('categories.edit', category.id)" class="btn-ghost btn-sm mr-1">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Editar
                                </Link>
                                <button @click="confirmDelete(category)" class="btn-danger btn-sm">
                                    <svg class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <p class="mt-4 text-sm text-gray-500">No hay categorías registradas.</p>
                <Link :href="route('categories.create')" class="mt-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                    Crear la primera categoría →
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
