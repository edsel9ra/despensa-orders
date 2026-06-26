<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    archivo: null,
    remision: '',
    sede: '',
    fecha: new Date().toISOString().split('T')[0],
});
const dragging = ref(false);

defineProps({
    sedes: { type: Array, required: true },
});

const submit = () => {
    form.post(route('orders.preview'), { preserveScroll: true });
};

function onDrop(e) {
    dragging.value = false;
    const file = e.dataTransfer?.files[0];
    if (file) form.archivo = file;
}
</script>

<template>
    <Head title="Nuevo Pedido" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Nuevo Pedido</h2>
                <p class="mt-1 text-sm text-gray-500">Carga el archivo Excel con los items y cantidades a pedir</p>
            </div>
        </template>

        <div class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <!-- File upload zone -->
                <div>
                    <label class="label-field">Archivo Excel</label>
                    <div
                        @drop.prevent="onDrop"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        class="relative rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                        :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 hover:border-gray-400'"
                    >
                        <div v-if="!form.archivo">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="mt-3 text-sm text-gray-600">
                                Arrastra el archivo aquí o
                                <label class="cursor-pointer font-medium text-indigo-600 hover:text-indigo-800">
                                    selecciónalo
                                    <input type="file" accept=".xlsx,.xls" class="hidden" @input="form.archivo = $event.target.files[0]" />
                                </label>
                            </p>
                            <p class="mt-1 text-xs text-gray-400">Archivos XLSX o XLS</p>
                        </div>
                        <div v-else class="flex items-center justify-center gap-3">
                            <svg class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-left">
                                <p class="text-sm font-medium text-gray-900">{{ form.archivo.name }}</p>
                                <p class="text-xs text-gray-500">{{ (form.archivo.size / 1024).toFixed(1) }} KB</p>
                            </div>
                            <button type="button" @click="form.archivo = null" class="btn-ghost btn-sm text-xs">Cambiar</button>
                        </div>
                    </div>
                    <p v-if="form.errors.archivo" class="mt-2 text-sm text-red-600">{{ form.errors.archivo }}</p>
                </div>

                <!-- Meta fields -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <label class="label-field" for="remision">Remisión</label>
                        <input id="remision" v-model="form.remision" type="text" class="input-field" placeholder="N° de remisión" required />
                        <p v-if="form.errors.remision" class="mt-1 text-sm text-red-600">{{ form.errors.remision }}</p>
                    </div>
                    <div>
                        <label class="label-field" for="sede">Sede</label>
                        <select id="sede" v-model="form.sede" class="select-field" required>
                            <option value="">Selecciona una sede</option>
                            <option v-for="sede in sedes" :key="sede.name" :value="sede.name">
                                {{ sede.name }}{{ sede.operation_center ? ` (C.O. ${sede.operation_center})` : '' }}
                            </option>
                        </select>
                        <p v-if="form.errors.sede" class="mt-1 text-sm text-red-600">{{ form.errors.sede }}</p>
                    </div>
                    <div>
                        <label class="label-field" for="fecha">Fecha</label>
                        <input id="fecha" v-model="form.fecha" type="date" class="input-field" required />
                        <p v-if="form.errors.fecha" class="mt-1 text-sm text-red-600">{{ form.errors.fecha }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" :disabled="form.processing || !form.archivo" class="btn-primary flex items-center gap-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Vista Previa
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
