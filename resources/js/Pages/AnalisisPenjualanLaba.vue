<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sales: {
        type: Array,
        default: () => []
    },
    productAnalysis: {
        type: Array,
        default: () => []
    },
    totalRevenue: {
        type: Number,
        default: 0
    },
    totalCost: {
        type: Number,
        default: 0
    },
    totalProfit: {
        type: Number,
        default: 0
    },
    profitMargin: {
        type: Number,
        default: 0
    },
    transactionCount: {
        type: Number,
        default: 0
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

// Date filters
const dateFrom = ref(props.filters.dateFrom || '');
const dateTo = ref(props.filters.dateTo || '');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

const applyFilters = () => {
    router.get(route('laporan.penjualan-laba'), {
        dateFrom: dateFrom.value,
        dateTo: dateTo.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('laporan.penjualan-laba'));
};

// Export functions
const exportPdf = () => {
    const params = new URLSearchParams({
        start_date: dateFrom.value || '',
        end_date: dateTo.value || ''
    }).toString();
    window.open(`/laporan/export/profit-loss/pdf?${params}`, '_blank');
};

const exportExcel = () => {
    const params = new URLSearchParams({
        start_date: dateFrom.value || '',
        end_date: dateTo.value || ''
    }).toString();
    window.open(`/laporan/export/profit-loss/excel?${params}`, '_blank');
};
</script>

<template>
    <Head title="Analisis Penjualan & Laba" />

    <SaeLayout>
        <template #header>Analisis Penjualan & Laba</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Analisis Penjualan & Laba</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-blue-500">payments</span>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Pendapatan</p>
                    <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ formatCurrency(totalRevenue) }}</h3>
                    <p class="text-xs text-gray-400 mt-2">Revenue dari penjualan</p>
                </div>
                <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-red-500">shopping_cart</span>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total HPP (COGS)</p>
                    <h3 class="text-2xl font-bold text-red-600 dark:text-red-400">{{ formatCurrency(totalCost) }}</h3>
                    <p class="text-xs text-gray-400 mt-2">Harga Pokok Penjualan</p>
                </div>
                <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-green-500">trending_up</span>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Laba Kotor</p>
                    <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(totalProfit) }}</h3>
                    <p class="text-xs text-gray-400 mt-2">Gross Profit</p>
                </div>
                <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 relative overflow-hidden group hover:shadow-md transition-shadow">
                    <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                        <span class="material-symbols-outlined text-6xl text-amber-500">percent</span>
                    </div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Margin Profit</p>
                    <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ profitMargin }}%</h3>
                    <p class="text-xs text-gray-400 mt-2">{{ transactionCount }} transaksi</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-6">
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                        <input 
                            v-model="dateFrom"
                            type="date" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                        <input 
                            v-model="dateTo"
                            type="date" 
                            class="rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                        />
                    </div>
                    <button 
                        @click="applyFilters"
                        class="px-4 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg shadow-sm transition-colors flex items-center"
                    >
                        <span class="material-symbols-outlined mr-2 text-lg">filter_alt</span>
                        Filter
                    </button>
                    <button 
                        @click="clearFilters"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        Reset
                    </button>
                </div>
            </div>

            <!-- Sales Table -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3 filled">analytics</span>
                        <h3 class="text-lg font-semibold">Detail Penjualan & Laba</h3>
                    </div>
                    <div class="flex gap-2">
                        <button @click="exportPdf" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Export PDF">
                            <span class="material-symbols-outlined text-xl">picture_as_pdf</span>
                        </button>
                        <button @click="exportExcel" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Export Excel">
                            <span class="material-symbols-outlined text-xl">table_view</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 dark:bg-gray-800 text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Tanggal</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">No. Bukti</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Customer</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Barang</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-center px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Qty</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-right px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Harga Jual</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-right px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Revenue</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-right px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">HPP</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-right px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Laba Kotor</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-center px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ sale.tanggal }}</td>
                                <td class="px-4 py-3 text-sm font-mono text-primary">{{ sale.nomor_bukti }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ sale.customer }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ sale.nama_barang }}</span>
                                        <span class="text-xs text-gray-500">{{ sale.kode_barang }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-300">
                                    {{ parseFloat(sale.quantity).toLocaleString('id-ID') }} {{ sale.satuan }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-300">
                                    {{ formatCurrency(sale.harga_jual) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-blue-600 dark:text-blue-400">
                                    {{ formatCurrency(sale.revenue) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-red-600 dark:text-red-400">
                                    {{ formatCurrency(sale.cogs) }}
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-green-600 dark:text-green-400">
                                    {{ formatCurrency(sale.gross_profit) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span :class="[
                                        'px-2 py-1 rounded-full text-xs font-medium',
                                        sale.profit_margin >= 30 ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' :
                                        sale.profit_margin >= 15 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300' :
                                        'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'
                                    ]">
                                        {{ parseFloat(sale.profit_margin).toFixed(1) }}%
                                    </span>
                                </td>
                            </tr>
                            <tr v-if="sales.length === 0">
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">receipt_long</span>
                                    <p>Belum ada data penjualan.</p>
                                </td>
                            </tr>
                        </tbody>
                        <!-- Footer Totals -->
                        <tfoot v-if="sales.length > 0" class="bg-gray-100 dark:bg-gray-800">
                            <tr class="font-semibold">
                                <td colspan="6" class="px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">TOTAL</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-600 dark:text-blue-400">{{ formatCurrency(totalRevenue) }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600 dark:text-red-400">{{ formatCurrency(totalCost) }}</td>
                                <td class="px-4 py-3 text-right text-sm text-green-600 dark:text-green-400">{{ formatCurrency(totalProfit) }}</td>
                                <td class="px-4 py-3 text-center text-sm text-amber-600 dark:text-amber-400">{{ profitMargin }}%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Product Analysis (Optional Section) -->
            <div v-if="productAnalysis.length > 0" class="mt-8 bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-amber-600 px-6 py-4 flex items-center text-white">
                    <span class="material-symbols-outlined mr-3 filled">leaderboard</span>
                    <h3 class="text-lg font-semibold">Analisis per Produk (Top Profit)</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 dark:bg-gray-800 text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Kode</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-left px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Nama Barang</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-center px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Total Qty</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-right px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Total Revenue</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-right px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Total HPP</th>
                                <th class="bg-gray-50 dark:bg-gray-800 text-right px-4 py-3 text-xs font-semibold text-gray-600 dark:text-gray-300">Total Profit</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="(product, index) in productAnalysis" :key="product.kode_barang" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-3 text-sm font-mono text-primary">{{ product.kode_barang }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                                    <div class="flex items-center">
                                        <span v-if="index < 3" class="material-symbols-outlined text-amber-500 mr-2">emoji_events</span>
                                        {{ product.nama_barang }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-sm text-gray-600 dark:text-gray-300">{{ parseFloat(product.total_qty).toLocaleString('id-ID') }}</td>
                                <td class="px-4 py-3 text-right text-sm text-blue-600 dark:text-blue-400">{{ formatCurrency(product.total_revenue) }}</td>
                                <td class="px-4 py-3 text-right text-sm text-red-600 dark:text-red-400">{{ formatCurrency(product.total_cogs) }}</td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-green-600 dark:text-green-400">{{ formatCurrency(product.total_profit) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
