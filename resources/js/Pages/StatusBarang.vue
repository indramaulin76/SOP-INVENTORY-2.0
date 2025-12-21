<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    summary: {
        type: Object,
        default: () => ({ totalProducts: 0, totalValue: 0, lowStockCount: 0 })
    },
    filters: Object
});

// Filter state
const search = ref(props.filters?.search || '');
const categoryId = ref(props.filters?.category_id || '');
const statusFilter = ref(props.filters?.status || '');

// Apply filter
const applyFilter = () => {
    router.get(route('laporan.status-barang'), {
        search: search.value || undefined,
        category_id: categoryId.value || undefined,
        status: statusFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Clear filter
const clearFilter = () => {
    search.value = '';
    categoryId.value = '';
    statusFilter.value = '';
    router.get(route('laporan.status-barang'), {}, {
        preserveState: true,
        replace: true,
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const getStatusClass = (status) => {
    if (status === 'Rendah') return 'text-red-600 dark:text-red-400 font-bold';
    return 'text-green-600 dark:text-green-400';
};

const getProgressClass = (stok, limit) => {
    const percentage = limit > 0 ? (stok / (limit * 3)) * 100 : 100;
    if (percentage <= 30) return 'bg-red-500';
    if (percentage <= 60) return 'bg-orange-500';
    return 'bg-green-500';
};

const getProgressWidth = (stok, limit) => {
    const percentage = limit > 0 ? Math.min((stok / (limit * 3)) * 100, 100) : 100;
    return `${percentage}%`;
};
</script>


<template>
    <Head title="Status & Monitoring Stok" />

    <SaeLayout>
        <template #header>Status & Monitoring Stok</template>

        <div class="max-w-[1400px] mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb" class="flex mb-5">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <Link href="/" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary dark:text-gray-400 dark:hover:text-white">
                            <span class="material-symbols-outlined text-lg mr-2">home</span>
                            Dashboard
                        </Link>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Laporan</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-primary md:ml-2">Status & Monitoring</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Header Card -->
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-8 transition-colors flex flex-col md:flex-row justify-between items-center relative overflow-hidden">
                <div class="absolute left-0 top-0 bottom-0 w-2 bg-primary"></div>
                <div class="flex flex-col gap-1 z-10 pl-2">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Status & Monitoring Stok</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Monitor level stok dan nilai aset secara real-time</p>
                </div>
                <div class="flex items-center gap-3 mt-4 md:mt-0 z-10">
                    <button class="flex items-center gap-1 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 px-4 py-2 rounded-md text-sm hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors font-medium">
                        <span class="material-symbols-outlined text-sm">print</span>
                        <span class="hidden sm:inline">Print Report</span>
                    </button>
                    <button class="flex items-center gap-1 bg-primary border border-transparent text-white px-4 py-2 rounded-md text-sm hover:bg-primary-hover transition-colors font-medium shadow-sm">
                        <span class="material-symbols-outlined text-sm">table_view</span>
                        <span class="hidden sm:inline">Export Excel</span>
                    </button>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-surface-dark">
                    <div class="flex flex-wrap items-end gap-3 w-full">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cari Barang</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">search</span>
                                </div>
                                <input 
                                    v-model="search"
                                    type="text" 
                                    class="pl-9 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 dark:text-gray-200 focus:ring-primary focus:border-primary w-full" 
                                    placeholder="Nama atau kode barang..."
                                    @keyup.enter="applyFilter"
                                />
                            </div>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kategori</label>
                            <select 
                                v-model="categoryId"
                                class="w-full py-2 pl-3 pr-8 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 dark:text-gray-200 focus:ring-primary focus:border-primary"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.nama_kategori }}
                                </option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[140px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Status Stok</label>
                            <select 
                                v-model="statusFilter"
                                class="w-full py-2 pl-3 pr-8 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 dark:text-gray-200 focus:ring-primary focus:border-primary"
                            >
                                <option value="">Semua Status</option>
                                <option value="low">Stok Rendah</option>
                                <option value="safe">Stok Aman</option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="applyFilter"
                                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-hover transition-colors text-sm font-medium flex items-center gap-1"
                            >
                                <span class="material-symbols-outlined text-lg">filter_alt</span>
                                Filter
                            </button>
                            <button 
                                @click="clearFilter"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-left px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap" style="width: 30%;">Product</th>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-left px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap" style="width: 15%;">Category</th>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-left px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap" style="width: 25%;">Stock Health</th>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-left px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap" style="width: 15%;">Unit HPP</th>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-right px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap" style="width: 15%;">Total Asset Value</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="product in products" :key="product.id" :class="['hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors', product.status === 'Rendah' ? 'bg-red-50/30 dark:bg-red-900/10' : '']">
                                <td class="p-3 whitespace-nowrap border-b border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 bg-primary/10 rounded-lg flex items-center justify-center">
                                            <span class="material-symbols-outlined text-primary">inventory_2</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">{{ product.nama_barang }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ product.kode_barang }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap border-b border-gray-200 dark:border-gray-700">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary dark:bg-primary/20">{{ product.kategori }}</span>
                                </td>
                                <td class="p-3 whitespace-nowrap border-b border-gray-200 dark:border-gray-700">
                                    <div class="w-full max-w-xs">
                                        <div class="flex justify-between mb-1">
                                            <span :class="['text-sm font-medium', product.status === 'Rendah' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400']">{{ product.stok }} {{ product.satuan }}</span>
                                            <span :class="getStatusClass(product.status)">{{ product.status }}</span>
                                        </div>
                                        <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-2 w-full overflow-hidden">
                                            <div :class="['h-2 rounded-full', getProgressClass(product.stok, product.limit)]" :style="{ width: getProgressWidth(product.stok, product.limit) }"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">{{ formatCurrency(product.nilai / (product.stok || 1)) }}/{{ product.satuan }}</td>
                                <td class="p-3 whitespace-nowrap text-sm text-right font-bold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700">{{ formatCurrency(product.nilai) }}</td>
                            </tr>
                            <tr v-if="!products || products.length === 0">
                                <td colspan="5" class="p-8 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">inventory_2</span>
                                    Belum ada data barang
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <a href="#" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                        <a href="#" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 dark:text-gray-400">
                                Showing
                                <span class="font-medium">1</span>
                                to
                                <span class="font-medium">6</span>
                                of
                                <span class="font-medium">84</span>
                                products
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-700 dark:hover:bg-gray-800">
                                    <span class="sr-only">Previous</span>
                                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                                </a>
                                <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">1</a>
                                <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">2</a>
                                <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">3</a>
                                <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-700 dark:hover:bg-gray-800">
                                    <span class="sr-only">Next</span>
                                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
