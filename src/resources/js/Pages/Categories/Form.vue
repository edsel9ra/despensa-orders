<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    category: { type: Object, default: null },
});

const form = useForm({
    nombre: props.category?.nombre ?? '',
    orden: props.category?.orden ?? 0,
    aplica_iva: props.category?.aplica_iva ?? false,
});

const submit = () => {
    if (props.category) {
        form.put(route('categories.update', props.category.id));
    } else {
        form.post(route('categories.store'));
    }
};
</script>

<template>
    <Head :title="category ? 'Editar Categoría' : 'Nueva Categoría'" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ category ? 'Editar Categoría' : 'Nueva Categoría' }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ category ? 'Modifica los datos de la categoría' : 'Registra una nueva categoría de productos' }}</p>
            </div>
        </template>

        <div class="max-w-lg mx-auto space-y-6">
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <div>
                    <label class="label-field" for="nombre">Nombre</label>
                    <input id="nombre" v-model="form.nombre" type="text" class="input-field" placeholder="Ej: SALSAS ALITAS" required />
                    <InputError :message="form.errors.nombre" />
                </div>

                <div>
                    <label class="label-field" for="orden">Orden</label>
                    <input id="orden" v-model="form.orden" type="number" class="input-field" min="0" required />
                    <InputError :message="form.errors.orden" />
                </div>

                <div class="flex items-center gap-3">
                    <input id="aplica_iva" v-model="form.aplica_iva" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    <label for="aplica_iva" class="text-sm font-medium text-gray-700">Aplica IVA (19%)</label>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" :disabled="form.processing" class="btn-primary flex items-center gap-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        {{ category ? 'Actualizar' : 'Guardar' }}
                    </button>
                    <Link :href="route('categories.index')" class="btn-ghost">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
