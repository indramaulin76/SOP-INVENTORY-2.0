<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    selectedProduct: Object,
    movements: {
        type: Array,
        default: () => []
    },
    filters: Object
});

const selectedProductId = ref(props.filters?.product_id || '');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

// Format stock numbers - removes unnecessary decimals and leading zeros
const formatStock = (value) => {
    const num = parseFloat(value) || 0;
    // If it's a whole number, show without decimals
    if (Number.isInteger(num)) {
        return num.toLocaleString('id-ID');
    }
    // Otherwise show with max 2 decimals
    return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

const loadProduct = () => {
    if (selectedProductId.value) {
        router.get(route('laporan.kartu-stok'), {
            product_id: selectedProductId.value
        }, {
            preserveState: true,
            preserveScroll: true
        });
    }
};

// Calculate running balance
const movementsWithBalance = computed(() => {
    let runningBalance = 0;
    return props.movements.map(m => {
        runningBalance += (m.masuk || 0) - (m.keluar || 0);
        return {
            ...m,
            running_balance: runningBalance
        };
    });
});

// Get total stock
const currentStock = computed(() => {
    return props.movements.reduce((sum, m) => sum + ((m.masuk || 0) - (m.keluar || 0)), 0);
});

const totalMasuk = computed(() => {
    return props.movements.reduce((sum, m) => sum + (m.masuk || 0), 0);
});

const totalKeluar = computed(() => {
    return props.movements.reduce((sum, m) => sum + (m.keluar || 0), 0);
});

// Export functions
const exportPdf = () => {
    if (!selectedProductId.value) {
        alert('Pilih produk terlebih dahulu');
        return;
    }
    const params = new URLSearchParams({
        product_id: selectedProductId.value,
    });
    if (props.filters?.dateFrom) params.append('start_date', props.filters.dateFrom);
    if (props.filters?.dateTo) params.append('end_date', props.filters.dateTo);
    
    window.location.href = route('laporan.export.kartu-stok.pdf') + '?' + params.toString();
};

const exportExcel = () => {
    if (!selectedProductId.value) {
        alert('Pilih produk terlebih dahulu');
        return;
    }
    const params = new URLSearchParams({
        product_id: selectedProductId.value,
    });
    if (props.filters?.dateFrom) params.append('start_date', props.filters.dateFrom);
    if (props.filters?.dateTo) params.append('end_date', props.filters.dateTo);
    
    window.location.href = route('laporan.export.kartu-stok.excel') + '?' + params.toString();
};
</script>

<template>
    <Head title="Kartu Stok" />

    <SaeLayout>
        <template #header>Kartu Stok</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Kartu Stok</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Product Selector Card -->
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 mb-8 transition-colors">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-5">
                        <div class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-100 dark:border-gray-600 shadow-sm flex-shrink-0 bg-primary/10 flex items-center justify-center">
                            <span class="material-symbols-outlined text-4xl text-primary">inventory_2</span>
                        </div>
                        <div v-if="selectedProduct">
                            <div class="flex items-center gap-2 mb-1">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ selectedProduct.nama_barang }}</h2>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 border border-green-200 dark:border-green-800">
                                    ACTIVE
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-mono mb-2">SKU: {{ selectedProduct.kode_barang }}</p>
                            <div class="text-xs text-gray-400 flex items-center">
                                <span class="material-symbols-outlined text-sm mr-1">category</span>
                                {{ selectedProduct.category?.nama_kategori || '-' }}
                            </div>
                        </div>
                        <div v-else>
                            <h2 class="text-xl font-bold text-gray-500 dark:text-gray-400">Pilih produk untuk melihat kartu stok</h2>
                            <p class="text-sm text-gray-400 mt-1">Gunakan dropdown di samping untuk memilih produk</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row md:flex-col lg:flex-row items-start sm:items-center gap-4 w-full md:w-auto">
                        <div class="relative w-full sm:w-64">
                            <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1.5 uppercase tracking-wide">Pilih Produk</label>
                            <div class="flex gap-2">
                                <select 
                                    v-model="selectedProductId"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm pl-3 pr-10 py-2.5"
                                >
                                    <option value="">-- Pilih Produk --</option>
                                    <option v-for="product in products" :key="product.id" :value="product.id">
                                        {{ product.nama_barang }} ({{ product.kode_barang }})
                                    </option>
                                </select>
                                <button 
                                    @click="loadProduct"
                                    :disabled="!selectedProductId"
                                    class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-hover disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    <span class="material-symbols-outlined text-lg">search</span>
                                </button>
                            </div>
                        </div>
                        <div class="h-10 w-px bg-gray-200 dark:bg-gray-700 hidden lg:block mx-2"></div>
                        <div class="bg-primary/5 dark:bg-primary/10 rounded-lg p-3 px-5 border border-primary/20 flex flex-col items-center sm:items-end min-w-[140px]">
                            <p class="text-xs font-bold text-primary uppercase tracking-wide mb-1">Stok Saat Ini</p>
                            <p class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ currentStock.toLocaleString('id-ID') }}
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ selectedProduct?.unit?.singkatan || 'Pcs' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Movement Summary -->
            <div v-if="selectedProduct" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                    <div class="flex items-center justify-between">
                        <span class="text-green-600 dark:text-green-400 text-sm font-medium">Total Masuk</span>
                        <span class="material-symbols-outlined text-green-500">arrow_downward</span>
                    </div>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-2">{{ formatStock(totalMasuk) }}</p>
                </div>
                <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                    <div class="flex items-center justify-between">
                        <span class="text-red-600 dark:text-red-400 text-sm font-medium">Total Keluar</span>
                        <span class="material-symbols-outlined text-red-500">arrow_upward</span>
                    </div>
                    <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-2">{{ formatStock(totalKeluar) }}</p>
                </div>
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center justify-between">
                        <span class="text-blue-600 dark:text-blue-400 text-sm font-medium">Total Mutasi</span>
                        <span class="material-symbols-outlined text-blue-500">swap_vert</span>
                    </div>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-2">{{ movements.length }}</p>
                </div>
            </div>

            <!-- Detail Mutasi Stok -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3">receipt_long</span>
                        <h3 class="text-lg font-semibold">Detail Mutasi Stok</h3>
                    </div>
                    <div class="flex gap-2">
                        <button 
                            @click="exportPdf"
                            :disabled="!selectedProductId"
                            class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
                            title="Export PDF"
                        >
                            <span class="material-symbols-outlined text-xl">picture_as_pdf</span>
                        </button>
                        <button 
                            @click="exportExcel"
                            :disabled="!selectedProductId"
                            class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed" 
                            title="Export Excel"
                        >
                            <span class="material-symbols-outlined text-xl">table_view</span>
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-left px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap">Tanggal</th>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-left px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap">Keterangan</th>
                                <th scope="col" class="bg-gray-50 text-green-600 dark:text-green-400 font-semibold text-right px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:border-gray-700 whitespace-nowrap">Masuk</th>
                                <th scope="col" class="bg-gray-50 text-red-500 dark:text-red-400 font-semibold text-right px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:border-gray-700 whitespace-nowrap">Keluar</th>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-right px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap">Saldo</th>
                                <th scope="col" class="bg-gray-50 text-gray-700 font-semibold text-right px-4 py-3 text-xs border-b-2 border-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 whitespace-nowrap">Harga</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="(mutation, index) in movementsWithBalance" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                                <td class="p-3 text-sm text-gray-600 dark:text-gray-300 font-medium">{{ mutation.tanggal }}</td>
                                <td class="p-3 text-sm text-gray-800 dark:text-gray-200">{{ mutation.keterangan }}</td>
                                <td class="p-3 text-sm text-right font-medium">
                                    <span v-if="mutation.masuk > 0" class="text-green-600 dark:text-green-400">
                                        +{{ formatStock(mutation.masuk) }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="p-3 text-sm text-right font-medium">
                                    <span v-if="mutation.keluar > 0" class="text-red-500 dark:text-red-400">
                                        -{{ formatStock(mutation.keluar) }}
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="p-3 text-sm text-right font-bold text-gray-900 dark:text-white bg-gray-50/50 dark:bg-gray-800/50">
                                    {{ formatStock(mutation.saldo) }}
                                </td>
                                <td class="p-3 text-sm text-right text-gray-600 dark:text-gray-300">
                                    {{ formatCurrency(mutation.harga) }}
                                </td>
                            </tr>
                            <tr v-if="movements.length === 0">
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">inventory_2</span>
                                    <p v-if="selectedProduct">Belum ada mutasi stok untuk produk ini.</p>
                                    <p v-else>Pilih produk untuk melihat kartu stok.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
