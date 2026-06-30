<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    filters: { type: Object, required: true },
    sedes: { type: Array, required: true },
    categories: { type: Array, required: true },
    items: { type: Array, required: true },
    report: { type: Object, default: null },
});

const form = useForm({
    fecha_inicio: props.filters.fecha_inicio || '',
    fecha_fin: props.filters.fecha_fin || '',
    sede: props.filters.sede || '',
    category_id: props.filters.category_id ? String(props.filters.category_id) : '',
    item_ids: (props.filters.item_ids || []).map(String),
});

const itemSearch = ref('');

const selectedItems = computed(() => {
    const selected = new Set(form.item_ids.map(String));
    return props.items.filter(item => selected.has(String(item.id)));
});

const filteredItems = computed(() => {
    const search = itemSearch.value.trim().toLowerCase();
    if (!search) return [];

    const selected = new Set(form.item_ids.map(String));
    return props.items.filter(item => {
        const matchesCategory = !form.category_id || String(item.categoria_id) === String(form.category_id);
        const matchesSearch = item.codigo_item.toLowerCase().includes(search)
            || item.descripcion.toLowerCase().includes(search);

        return matchesCategory && matchesSearch && !selected.has(String(item.id));
    }).slice(0, 12);
});

const filterDescription = computed(() => {
    if (!props.report) return 'Selecciona una sede y un rango de fechas para calcular el reporte.';
    if (selectedItems.value.length) return `${selectedItems.value.length} producto(s) seleccionado(s)`;

    const category = props.categories.find(cat => String(cat.id) === String(form.category_id));
    return category ? `Grupo: ${category.nombre}` : 'Todos los productos de la sede';
});

const exportParams = computed(() => {
    const params = {
        fecha_inicio: props.filters.fecha_inicio,
        fecha_fin: props.filters.fecha_fin,
        sede: props.filters.sede,
    };

    if (props.filters.category_id) {
        params.category_id = props.filters.category_id;
    }

    if (props.filters.item_ids?.length) {
        params.item_ids = props.filters.item_ids;
    }

    return params;
});

const xlsxExportUrl = computed(() => route('reports.export-xlsx', exportParams.value));
const pdfExportUrl = computed(() => route('reports.export-pdf', exportParams.value));

function submit() {
    form.get(route('reports.index'), { preserveScroll: true, preserveState: true, replace: true });
}

function resetFilters() {
    router.get(route('reports.index'));
}

function addItem(item) {
    form.item_ids.push(String(item.id));
    itemSearch.value = '';
}

function removeItem(itemId) {
    form.item_ids = form.item_ids.filter(id => String(id) !== String(itemId));
}

function formatPrice(value) {
    return Number(value || 0).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
}

function formatNumber(value) {
    return Number(value || 0).toLocaleString('es-CO', { maximumFractionDigits: 2 });
}
</script>

<template>
    <Head title="Reportes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Analítica de compras</p>
                    <h2 class="mt-1 text-xl font-semibold text-stone-800">Reportes de pedidos</h2>
                    <p class="mt-1 text-sm text-stone-500">Totales por sede, periodo y productos específicos.</p>
                </div>
                <div class="rounded-full bg-white px-4 py-2 text-sm font-medium text-stone-600 shadow-sm ring-1 ring-stone-200">
                    {{ filterDescription }}
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <form @submit.prevent="submit" class="card p-5">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                    <div>
                        <label class="label-field" for="fecha_inicio">Fecha inicio</label>
                        <input id="fecha_inicio" v-model="form.fecha_inicio" type="date" class="input-field" required />
                        <p v-if="form.errors.fecha_inicio" class="mt-1 text-sm text-rose-600">{{ form.errors.fecha_inicio }}</p>
                    </div>
                    <div>
                        <label class="label-field" for="fecha_fin">Fecha fin</label>
                        <input id="fecha_fin" v-model="form.fecha_fin" type="date" class="input-field" required />
                        <p v-if="form.errors.fecha_fin" class="mt-1 text-sm text-rose-600">{{ form.errors.fecha_fin }}</p>
                    </div>
                    <div>
                        <label class="label-field" for="sede">Sede</label>
                        <select id="sede" v-model="form.sede" class="select-field" required>
                            <option value="">Selecciona una sede</option>
                            <option v-for="sede in sedes" :key="sede" :value="sede">{{ sede }}</option>
                        </select>
                        <p v-if="form.errors.sede" class="mt-1 text-sm text-rose-600">{{ form.errors.sede }}</p>
                    </div>
                    <div>
                        <label class="label-field" for="category_id">Grupo</label>
                        <select id="category_id" v-model="form.category_id" class="select-field">
                            <option value="">Todos los grupos</option>
                            <option v-for="category in categories" :key="category.id" :value="String(category.id)">{{ category.nombre }}</option>
                        </select>
                        <p v-if="form.errors.category_id" class="mt-1 text-sm text-rose-600">{{ form.errors.category_id }}</p>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" :disabled="form.processing" class="btn-primary w-full">
                            Calcular
                        </button>
                        <button type="button" class="btn-ghost shrink-0" @click="resetFilters">Limpiar</button>
                    </div>
                </div>

                <div class="mt-5 border-t border-stone-100 pt-5">
                    <label class="label-field" for="item_search">Productos específicos <span class="font-normal text-stone-400">(opcional)</span></label>
                    <div class="relative">
                        <input
                            id="item_search"
                            v-model="itemSearch"
                            type="text"
                            class="input-field"
                            placeholder="Buscar por código o descripción..."
                        />
                        <div
                            v-if="itemSearch && filteredItems.length"
                            class="absolute z-20 mt-1 max-h-72 w-full overflow-y-auto rounded-xl border border-stone-200 bg-white shadow-lg"
                        >
                            <button
                                v-for="item in filteredItems"
                                :key="item.id"
                                type="button"
                                class="flex w-full items-start justify-between gap-3 border-b border-stone-100 px-4 py-3 text-left text-sm transition-colors last:border-0 hover:bg-brand-50"
                                @click="addItem(item)"
                            >
                                <span>
                                    <span class="font-mono font-semibold text-stone-900">{{ item.codigo_item }}</span>
                                    <span class="ml-2 text-stone-700">{{ item.descripcion }}</span>
                                </span>
                                <span class="shrink-0 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-500">{{ item.category?.nombre }}</span>
                            </button>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-stone-500">Puedes seleccionar un solo producto o varios. Si también eliges grupo, el reporte queda limitado a ese grupo.</p>
                    <div v-if="selectedItems.length" class="mt-3 flex flex-wrap gap-2">
                        <span
                            v-for="item in selectedItems"
                            :key="item.id"
                            class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-800 ring-1 ring-brand-200"
                        >
                            {{ item.codigo_item }} · {{ item.descripcion }}
                            <button type="button" class="text-brand-500 hover:text-brand-800" @click="removeItem(item.id)">×</button>
                        </span>
                    </div>
                </div>
            </form>

            <div v-if="report" class="space-y-6">
                <div class="card flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-stone-900">Descargar reporte</p>
                        <p class="mt-1 text-sm text-stone-500">Los archivos usan los filtros aplicados en el calculo actual.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a :href="xlsxExportUrl" class="btn-success btn-sm">
                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar XLSX
                        </a>
                        <a :href="pdfExportUrl" class="btn-danger btn-sm">
                            <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Descargar PDF
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="card p-5">
                        <p class="text-sm font-medium text-stone-500">Pedidos incluidos</p>
                        <p class="mt-2 text-3xl font-bold text-stone-900">{{ report.summary.orders_count }}</p>
                        <p class="mt-1 text-xs text-stone-400">{{ report.summary.lines_count }} líneas · {{ formatNumber(report.summary.quantity) }} unidades</p>
                    </div>
                    <div class="card p-5">
                        <p class="text-sm font-medium text-stone-500">Subtotal</p>
                        <p class="mt-2 text-3xl font-bold text-stone-900">{{ formatPrice(report.summary.subtotal) }}</p>
                    </div>
                    <div class="card p-5">
                        <p class="text-sm font-medium text-stone-500">IVA calculado</p>
                        <p class="mt-2 text-3xl font-bold text-stone-900">{{ formatPrice(report.summary.iva) }}</p>
                    </div>
                    <div class="card bg-gradient-to-br from-brand-600 to-brand-800 p-5 text-white">
                        <p class="text-sm font-medium text-brand-100">Valor total</p>
                        <p class="mt-2 text-3xl font-bold">{{ formatPrice(report.summary.total) }}</p>
                    </div>
                </div>

                <div class="card">
                    <div class="border-b border-stone-100 px-5 py-4">
                        <h3 class="text-base font-semibold text-stone-900">Detalle por pedido</h3>
                    </div>
                    <div v-if="report.orders.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-100">
                            <thead>
                                <tr class="bg-stone-50/80">
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Remisión</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Fecha</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Realizado por</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Productos</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Cantidad</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Total</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <tr v-for="order in report.orders" :key="order.id" class="hover:bg-stone-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-stone-900">#{{ order.remision }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ order.fecha }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ order.user_name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ order.items_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ formatNumber(order.quantity) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-stone-900">{{ formatPrice(order.total) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right">
                                        <Link :href="route('orders.show', order.id)" class="text-sm font-medium text-brand-600 hover:text-brand-800">Ver →</Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-5 py-10 text-center text-sm text-stone-500">No hay pedidos para los filtros seleccionados.</div>
                </div>

                <div class="card">
                    <div class="border-b border-stone-100 px-5 py-4">
                        <h3 class="text-base font-semibold text-stone-900">Detalle por producto</h3>
                    </div>
                    <div v-if="report.items.length" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-stone-100">
                            <thead>
                                <tr class="bg-stone-50/80">
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Código</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Producto</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Grupo</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Pedidos</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Cantidad</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Subtotal</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">IVA</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider text-stone-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-100">
                                <tr v-for="item in report.items" :key="item.id" class="hover:bg-stone-50/70">
                                    <td class="whitespace-nowrap px-5 py-4 text-sm font-mono font-semibold text-stone-900">{{ item.codigo_item }}</td>
                                    <td class="px-5 py-4 text-sm text-stone-700">{{ item.descripcion }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-stone-600">{{ item.category_name }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ item.orders_count }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ formatNumber(item.quantity) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ formatPrice(item.subtotal) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm text-stone-600">{{ formatPrice(item.iva) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-semibold text-stone-900">{{ formatPrice(item.total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="px-5 py-10 text-center text-sm text-stone-500">No hay productos para los filtros seleccionados.</div>
                </div>
            </div>

            <div v-else class="card p-10 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                    </svg>
                </div>
                <h3 class="mt-4 text-base font-semibold text-stone-900">Genera un reporte de pedidos</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-stone-500">Elige fecha inicio, fecha fin y sede. Luego puedes limitar el cálculo a un grupo o a productos específicos.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
