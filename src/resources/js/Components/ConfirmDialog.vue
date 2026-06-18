<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const emit = defineEmits(['confirmed', 'cancelled']);

const open = ref(false);
const resolving = ref(null);

const title = ref('');
const message = ref('');
const confirmText = ref('Eliminar');
const cancelText = ref('Cancelar');
const method = ref('delete');
const actionUrl = ref('');
const data = ref({});
const loading = ref(false);

function show(opts = {}) {
    title.value = opts.title || 'Confirmar acción';
    message.value = opts.message || '¿Estás seguro de realizar esta acción?';
    confirmText.value = opts.confirmText || 'Eliminar';
    cancelText.value = opts.cancelText || 'Cancelar';
    method.value = opts.method || 'delete';
    actionUrl.value = opts.url || '';
    data.value = opts.data || {};
    open.value = true;
    loading.value = false;

    return new Promise((resolve) => {
        resolving.value = resolve;
    });
}

function confirm() {
    loading.value = true;
    if (actionUrl.value) {
        router[method.value](actionUrl.value, data.value, {
            onFinish: () => {
                open.value = false;
                if (resolving.value) resolving.value(true);
            },
            onError: () => {
                loading.value = false;
                if (resolving.value) resolving.value(false);
            },
        });
    } else {
        if (resolving.value) resolving.value(true);
        open.value = false;
    }
}

function cancel() {
    open.value = false;
    if (resolving.value) resolving.value(false);
}

defineExpose({ show });
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-black/40 transition-opacity" @click="cancel"></div>
                <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl transition-all">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 shrink-0">
                            <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ message }}</p>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button @click="cancel" :disabled="loading" class="btn-ghost">{{ cancelText }}</button>
                        <button @click="confirm" :disabled="loading" class="btn-danger btn-sm flex items-center gap-2">
                            <svg v-if="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>
                            {{ confirmText }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
