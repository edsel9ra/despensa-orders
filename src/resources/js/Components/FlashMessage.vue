<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
const progress = ref(100);
let timer = null;

const flash = computed(() => page.props.flash || {});

watch(flash, (val) => {
    if (val.success) {
        show(val.success, 'success');
    } else if (val.error) {
        show(val.error, 'error');
    } else if (val.info) {
        show(val.info, 'info');
    } else if (val.warning) {
        show(val.warning, 'warning');
    }
}, { immediate: true, deep: true });

function show(msg, t) {
    if (timer) clearTimeout(timer);
    message.value = msg;
    type.value = t;
    visible.value = true;
    progress.value = 100;
    const start = Date.now();
    const duration = 4000;
    timer = setInterval(() => {
        const elapsed = Date.now() - start;
        progress.value = Math.max(0, 100 - (elapsed / duration) * 100);
        if (elapsed >= duration) {
            close();
        }
    }, 50);
}

function close() {
    visible.value = false;
    if (timer) { clearTimeout(timer); timer = null; }
}

const colors = {
    success: 'bg-emerald-50 border-emerald-400 text-emerald-800',
    error: 'bg-red-50 border-red-400 text-red-800',
    info: 'bg-sky-50 border-sky-400 text-sky-800',
    warning: 'bg-amber-50 border-amber-400 text-amber-800',
};
const progressColors = {
    success: 'bg-emerald-400',
    error: 'bg-red-400',
    info: 'bg-blue-400',
    warning: 'bg-yellow-400',
};
const icons = {
    success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
    error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
    info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
};
</script>

<template>
    <Teleport to="body">
        <Transition name="slide">
            <div
                v-if="visible"
                :class="colors[type]"
                class="fixed top-4 right-4 z-50 flex items-center gap-3 rounded-lg border-l-4 px-4 py-3 shadow-lg max-w-md"
                role="alert"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons[type]" />
                </svg>
                <p class="text-sm font-medium flex-1">{{ message }}</p>
                <button @click="close" class="shrink-0 rounded-md p-1 opacity-70 hover:opacity-100 transition-opacity">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="absolute bottom-0 left-0 h-0.5 w-full bg-black/10 rounded-full overflow-hidden">
                    <div :class="progressColors[type]" class="h-full transition-none" :style="{ width: progress + '%' }"></div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
