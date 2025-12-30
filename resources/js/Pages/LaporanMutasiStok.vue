<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    reportData: {
        type: Array,
        default: () => []
    },
    summary: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Array,
        default: () => []
    },
    availableMonths: {
        type: Array,
        default: () => []
    },
    availableYears: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({ month: new Date().getMonth() + 1, year: new Date().getFullYear(), category_id: '' })
    },
    periodLabel: String
});

// Filter form state
const selectedMonth = ref(props.filters.month);
const selectedYear = ref(props.filters.year);
const categoryId = ref(props.filters.category_id || '');

// Apply filter function
const applyFilter = () => {
    router.get(route('laporan.mutasi-stok'), {
        month: selectedMonth.value,
        year: selectedYear.value,
        category_id: categoryId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Clear filter function
const clearFilter = () => {
    selectedMonth.value = new Date().getMonth() + 1;
    selectedYear.value = new Date().getFullYear();
    categoryId.value = '';
    applyFilter();
};

// Export Excel
const exportExcel = () => {
    const params = new URLSearchParams({
        month: selectedMonth.value,
        year: selectedYear.value,
        category_id: categoryId.value || '',
    }).toString();
    window.open(`/laporan/export/mutasi-stok/excel?${params}`, '_blank');
};

// Print function
const printReport = () => {
    window.print();
};

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

// Format stock numbers
const formatStock = (value) => {
    const num = parseFloat(value);
    if (isNaN(num)) return '0';
    return Number.isInteger(num) ? num.toString() : num.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

// Tab for dual valuation view
const valuationType = ref('hpp'); // 'hpp' or 'jual'
</script>

<template>
    <Head title="Laporan Mutasi Stok Bulanan" />

    <SaeLayout>
        <template #header>Laporan Mutasi Stok Bulanan</template>

        <div class="max-w-[1600px] mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb" class="flex mb-5 print:hidden">
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
                            <span class="text-sm font-medium text-primary md:ml-2">Mutasi Stok</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white print:bg-white print:text-gray-900 print:border-b-2 print:border-gray-300">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3 filled print:hidden">assessment</span>
                        <div>
                            <h3 class="text-lg font-semibold">Laporan Mutasi Stok Bulanan</h3>
                            <p class="text-sm text-white/80 print:text-gray-600 mt-0.5">Periode: {{ periodLabel }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2 print:hidden">
                        <button @click="printReport" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Print">
                            <span class="material-symbols-outlined text-xl">print</span>
                        </button>
                        <button @click="exportExcel" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Export Excel">
                            <span class="material-symbols-outlined text-xl">table_view</span>
                        </button>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 print:hidden">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[120px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Bulan</label>
                            <select 
                                v-model="selectedMonth"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                            >
                                <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                                    {{ month.label }}
                                </option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[100px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Tahun</label>
                            <select 
                                v-model="selectedYear"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                            >
                                <option v-for="year in availableYears" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kategori</label>
                            <select 
                                v-model="categoryId"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.nama_kategori }}
                                </option>
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

                <!-- Content -->
                <div class="p-6 lg:p-8 space-y-6 bg-white dark:bg-surface-dark">
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 print:grid-cols-4 print:gap-3">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800 print:border-2">
                            <div class="flex items-center justify-between">
                                <span class="text-blue-600 dark:text-blue-400 text-sm font-medium">Total Produk</span>
                                <span class="material-symbols-outlined text-blue-500 print:hidden">inventory_2</span>
                            </div>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-2 print:text-xl">
                                {{ summary.totalProducts }}
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4 border border-green-200 dark:border-green-800 print:border-2">
                            <div class="flex items-center justify-between">
                                <span class="text-green-600 dark:text-green-400 text-sm font-medium">Total Masuk</span>
                                <span class="material-symbols-outlined text-green-500 print:hidden">arrow_downward</span>
                            </div>
                            <p class="text-lg font-bold text-green-700 dark:text-green-300 mt-2 print:text-base">
                                {{ formatCurrency(summary.totalIncomingValue) }}
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-lg p-4 border border-orange-200 dark:border-orange-800 print:border-2">
                            <div class="flex items-center justify-between">
                                <span class="text-orange-600 dark:text-orange-400 text-sm font-medium">Total Keluar (HPP)</span>
                                <span class="material-symbols-outlined text-orange-500 print:hidden">arrow_upward</span>
                            </div>
                            <p class="text-lg font-bold text-orange-700 dark:text-orange-300 mt-2 print:text-base">
                                {{ formatCurrency(summary.totalOutgoingValueHPP) }}
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg p-4 border border-purple-200 dark:border-purple-800 print:border-2">
                            <div class="flex items-center justify-between">
                                <span class="text-purple-600 dark:text-purple-400 text-sm font-medium">Nilai Stok Akhir</span>
                                <span class="material-symbols-outlined text-purple-500 print:hidden">account_balance_wallet</span>
                            </div>
                            <p class="text-lg font-bold text-purple-700 dark:text-purple-300 mt-2 print:text-base">
                                {{ formatCurrency(summary.totalClosingValue) }}
                            </p>
                        </div>
                    </div>

                    <!-- Valuation Type Toggle -->
                    <div class="flex gap-2 print:hidden">
                        <button 
                            @click="valuationType = 'hpp'"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                valuationType === 'hpp' 
                                    ? 'bg-primary text-white' 
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                            ]"
                        >
                            Tampilkan HPP
                        </button>
                        <button 
                            @click="valuationType = 'jual'"
                            :class="[
                                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                valuationType === 'jual' 
                                    ? 'bg-primary text-white' 
                                    : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300'
                            ]"
                        >
                            Tampilkan Harga Jual
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 print:border-2">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm print:text-xs">
                            <thead>
                                <tr>
                                    <th class="bg-primary text-white font-semibold text-left px-3 py-2 print:px-2 print:py-1 print:bg-gray-200 print:text-gray-900 whitespace-nowrap">Kode</th>
                                    <th class="bg-primary text-white font-semibold text-left px-3 py-2 print:px-2 print:py-1 print:bg-gray-200 print:text-gray-900 whitespace-nowrap">Nama Barang</th>
                                    <th class="bg-primary text-white font-semibold text-center px-3 py-2 print:px-2 print:py-1 print:bg-gray-200 print:text-gray-900 whitespace-nowrap">Satuan</th>
                                    <th class="bg-primary text-white font-semibold text-center px-3 py-2 print:px-2 print:py-1 print:bg-gray-200 print:text-gray-900 whitespace-nowrap">Saldo Awal</th>
                                    <th colspan="2" class="bg-green-600 text-white font-semibold text-center px-3 py-2 print:px-2 print:py-1 print:bg-green-100 print:text-gray-900 whitespace-nowrap">Masuk</th>
                                    <th colspan="2" class="bg-orange-600 text-white font-semibold text-center px-3 py-2 print:px-2 print:py-1 print:bg-orange-100 print:text-gray-900 whitespace-nowrap">Keluar</th>
                                    <th colspan="2" class="bg-purple-600 text-white font-semibold text-center px-3 py-2 print:px-2 print:py-1 print:bg-purple-100 print:text-gray-900 whitespace-nowrap">Saldo Akhir</th>
                                </tr>
                                <tr>
                                    <th class="bg-gray-100 dark:bg-gray-800 text-xs px-3 py-1 print:px-2"></th>
                                    <th class="bg-gray-100 dark:bg-gray-800 text-xs px-3 py-1 print:px-2"></th>
                                    <th class="bg-gray-100 dark:bg-gray-800 text-xs px-3 py-1 print:px-2"></th>
                                    <th class="bg-gray-100 dark:bg-gray-800 text-xs px-3 py-1 print:px-2"></th>
                                    <th class="bg-green-50 dark:bg-green-900/20 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1.5 print:px-2">Qty</th>
                                    <th class="bg-green-50 dark:bg-green-900/20 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1.5 print:px-2">Nilai (HPP)</th>
                                    <th class="bg-orange-50 dark:bg-orange-900/20 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1.5 print:px-2">Qty</th>
                                    <th class="bg-orange-50 dark:bg-orange-900/20 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1.5 print:px-2">
                                        Nilai ({{ valuationType === 'hpp' ? 'HPP' : 'Jual' }})
                                    </th>
                                    <th class="bg-purple-50 dark:bg-purple-900/20 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1.5 print:px-2">Qty</th>
                                    <th class="bg-purple-50 dark:bg-purple-900/20 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1.5 print:px-2">Nilai</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="item in reportData" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors print:hover:bg-white">
                                    <td class="px-3 py-2 text-sm font-mono text-gray-600 dark:text-gray-300 print:px-2 print:py-1 whitespace-nowrap">{{ item.kode_barang }}</td>
                                    <td class="px-3 py-2 print:px-2 print:py-1 min-w-[200px]">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.nama_barang }}</span>
                                            <span class="text-xs text-gray-500">{{ item.kategori }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 text-center text-sm text-gray-600 dark:text-gray-300 print:px-2 print:py-1">{{ item.satuan }}</td>
                                    <td class="px-3 py-2 text-center text-sm font-semibold text-gray-900 dark:text-white print:px-2 print:py-1">
                                        {{ formatStock(item.saldo_awal) }}
                                    </td>
                                    <!-- Masuk -->
                                    <td class="px-3 py-2 text-center bg-green-50/50 dark:bg-green-900/10 text-sm font-medium text-green-700 dark:text-green-400 print:px-2 print:py-1">
                                        {{ item.masuk_qty > 0 ? formatStock(item.masuk_qty) : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right bg-green-50/50 dark:bg-green-900/10 text-sm text-green-600 dark:text-green-400 print:px-2 print:py-1">
                                        {{ item.masuk_nilai > 0 ? formatCurrency(item.masuk_nilai) : '-' }}
                                    </td>
                                    <!-- Keluar -->
                                    <td class="px-3 py-2 text-center bg-orange-50/50 dark:bg-orange-900/10 text-sm font-medium text-orange-700 dark:text-orange-400 print:px-2 print:py-1">
                                        {{ item.keluar_qty > 0 ? formatStock(item.keluar_qty) : '-' }}
                                    </td>
                                    <td class="px-3 py-2 text-right bg-orange-50/50 dark:bg-orange-900/10 text-sm text-orange-600 dark:text-orange-400 print:px-2 print:py-1">
                                        {{ valuationType === 'hpp' 
                                            ? (item.keluar_nilai_hpp > 0 ? formatCurrency(item.keluar_nilai_hpp) : '-')
                                            : (item.keluar_nilai_jual > 0 ? formatCurrency(item.keluar_nilai_jual) : '-')
                                        }}
                                    </td>
                                    <!-- Saldo Akhir -->
                                    <td class="px-3 py-2 text-center bg-purple-50/50 dark:bg-purple-900/10 text-sm font-bold text-purple-700 dark:text-purple-400 print:px-2 print:py-1">
                                        {{ formatStock(item.saldo_akhir_qty) }}
                                    </td>
                                    <td class="px-3 py-2 text-right bg-purple-50/50 dark:bg-purple-900/10 text-sm font-medium text-purple-600 dark:text-purple-400 print:px-2 print:py-1">
                                        {{ formatCurrency(item.saldo_akhir_nilai) }}
                                    </td>
                                </tr>
                                <tr v-if="reportData.length === 0">
                                    <td colspan="10" class="p-8 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-4xl mb-2 text-gray-300 print:hidden">inbox</span>
                                        <p>Tidak ada data mutasi stok untuk periode {{ periodLabel }}.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Info Note -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 print:hidden">
                        <div class="flex items-start">
                            <span class="material-symbols-outlined text-blue-500 mr-3 mt-0.5">info</span>
                            <div class="text-sm text-blue-700 dark:text-blue-300">
                                <p class="font-semibold mb-1">Catatan Penting:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li><strong>Saldo Awal</strong> dihitung otomatis dari saldo akhir bulan sebelumnya (auto carry-forward)</li>
                                    <li><strong>Nilai Masuk</strong> menggunakan Harga Beli (HPP) dari batch pembelian/produksi</li>
                                    <li><strong>Nilai Keluar</strong> bisa ditampilkan dalam HPP (untuk tracking aset) atau Harga Jual (untuk tracking revenue)</li>
                                    <li><strong>Saldo Akhir</strong> = Saldo Awal + Masuk - Keluar</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>

<style scoped>
@media print {
    @page {
        size: landscape;
        margin: 1cm;
    }
    
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
</style>
