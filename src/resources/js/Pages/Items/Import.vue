<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const form = useForm({ file: null });
const dragging = ref(false);

function submit() {
    form.post(route('items.import'), {
        onFinish: () => form.reset('file'),
    });
}

function onDrop(e) {
    dragging.value = false;
    const file = e.dataTransfer?.files[0];
    if (file) form.file = file;
}
</script>

<template>
    <Head title="Importar Productos" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Importar Productos</h2>
                <p class="mt-1 text-sm text-gray-500">Carga un archivo Excel con los productos a importar</p>
            </div>
        </template>

        <div class="max-w-xl mx-auto">
            <form @submit.prevent="submit" class="card p-6 space-y-6">
                <div
                    @drop.prevent="onDrop"
                    @dragover.prevent="dragging = true"
                    @dragleave.prevent="dragging = false"
                    class="relative rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                    :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 hover:border-gray-400'"
                >
                    <div v-if="!form.file">
                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <p class="mt-3 text-sm text-gray-600">
                            Arrastra un archivo aquí o
                            <label class="cursor-pointer font-medium text-indigo-600 hover:text-indigo-800">
                                selecciona uno
                                <input type="file" accept=".xlsx,.xls,.csv" class="hidden" @input="form.file = $event.target.files[0]" />
                            </label>
                        </p>
                        <p class="mt-1 text-xs text-gray-400">XLSX, XLS o CSV</p>
                    </div>
                    <div v-else class="flex items-center justify-center gap-3">
                        <svg class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-900">{{ form.file.name }}</p>
                            <p class="text-xs text-gray-500">{{ (form.file.size / 1024).toFixed(1) }} KB</p>
                        </div>
                        <button type="button" @click="form.file = null" class="btn-ghost btn-sm text-xs">Cambiar</button>
                    </div>
                    <InputError :message="form.errors.file" />
                </div>

                <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                    <button type="submit" :disabled="form.processing || !form.file" class="btn-primary flex items-center gap-2">
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                        </svg>
                        <svg v-else class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Importar
                    </button>
                    <Link :href="route('items.index')" class="btn-ghost">Cancelar</Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
