<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    opnameNumber: {
        type: String,
        default: ''
    }
});

// Initialize items with product data
const initializeItems = () => {
    return props.products.map(product => ({
        product_id: product.id,
        kode_barang: product.kode_barang,
        nama_barang: product.nama_barang,
        kategori: product.kategori,
        satuan: product.satuan,
        qty_system: parseFloat(product.qty_system || 0),
        qty_physical: parseFloat(product.qty_system || 0), // Default to system qty
        price_per_unit: parseFloat(product.avg_price || 0),
        notes: '',
    }));
};

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    keterangan: '',
    items: initializeItems(),
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value || 0);
};

// Calculate difference for each item
const itemsWithDiff = computed(() => {
    return form.items.map(item => ({
        ...item,
        selisih: parseFloat(item.qty_physical || 0) - parseFloat(item.qty_system || 0),
        value_diff: (parseFloat(item.qty_physical || 0) - parseFloat(item.qty_system || 0)) * parseFloat(item.price_per_unit || 0),
    }));
});

// Filter to show only items with differences
const showOnlyDifferences = ref(false);
const filteredItems = computed(() => {
    if (showOnlyDifferences.value) {
        return itemsWithDiff.value.filter(item => item.selisih !== 0);
    }
    return itemsWithDiff.value;
});

// Summary statistics
const totalProducts = computed(() => form.items.length);
const matchedProducts = computed(() => itemsWithDiff.value.filter(p => p.selisih === 0).length);
const discrepancyProducts = computed(() => itemsWithDiff.value.filter(p => p.selisih !== 0).length);
const totalSurplus = computed(() => itemsWithDiff.value.filter(p => p.selisih > 0).reduce((sum, p) => sum + p.value_diff, 0));
const totalLoss = computed(() => Math.abs(itemsWithDiff.value.filter(p => p.selisih < 0).reduce((sum, p) => sum + p.value_diff, 0)));

const getDiffClass = (diff) => {
    if (diff < 0) return 'text-red-600 font-semibold';
    if (diff > 0) return 'text-green-600 font-semibold';
    return 'text-gray-500';
};

const getStatusBadge = (diff) => {
    if (diff === 0) {
        return { text: 'Match', class: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' };
    }
    if (diff < 0) {
        return { text: 'Kurang', class: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' };
    }
    return { text: 'Lebih', class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300' };
};

const submit = () => {
    form.post(route('laporan.stock-opname.store'), {
        onSuccess: () => {
            // Will redirect automatically
        }
    });
};
</script>

<template>
    <Head title="Stock Opname" />

    <SaeLayout>
        <template #header>Stock Opname</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Stock Opname</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <form @submit.prevent="submit">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                    <div class="bg-white dark:bg-surface-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <span class="material-symbols-outlined text-blue-500">inventory</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-800 dark:text-white mt-2">{{ totalProducts }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Produk</p>
                    </div>
                    <div class="bg-white dark:bg-surface-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                        </div>
                        <p class="text-2xl font-bold text-green-600 mt-2">{{ matchedProducts }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Match</p>
                    </div>
                    <div class="bg-white dark:bg-surface-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <span class="material-symbols-outlined text-red-500">warning</span>
                        </div>
                        <p class="text-2xl font-bold text-red-600 mt-2">{{ discrepancyProducts }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selisih</p>
                    </div>
                    <div class="bg-white dark:bg-surface-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <span class="material-symbols-outlined text-green-500">trending_up</span>
                        </div>
                        <p class="text-lg font-bold text-green-600 mt-2">{{ formatCurrency(totalSurplus) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Surplus</p>
                    </div>
                    <div class="bg-white dark:bg-surface-dark rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between">
                            <span class="material-symbols-outlined text-red-500">trending_down</span>
                        </div>
                        <p class="text-lg font-bold text-red-600 mt-2">{{ formatCurrency(totalLoss) }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Loss</p>
                    </div>
                </div>

                <!-- Opname Form Header -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-6">
                    <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined mr-3 filled">fact_check</span>
                            <h3 class="text-lg font-semibold">Form Stock Opname</h3>
                        </div>
                        <span class="bg-white/20 px-3 py-1 rounded text-sm">{{ opnameNumber }}</span>
                    </div>

                    <div class="p-6 bg-white dark:bg-surface-dark">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal Opname <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.tanggal"
                                    type="date" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                                    required
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Keterangan
                                </label>
                                <input 
                                    v-model="form.keterangan"
                                    type="text" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                                    placeholder="Catatan opname..."
                                />
                            </div>
                        </div>

                        <!-- Filter Toggle -->
                        <div class="flex items-center mb-4">
                            <input 
                                id="showDiff" 
                                v-model="showOnlyDifferences" 
                                type="checkbox" 
                                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                            />
                            <label for="showDiff" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                                Tampilkan hanya produk dengan selisih
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">Kode</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">Nama Barang</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">Kategori</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Stok Sistem</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Stok Fisik</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Satuan</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Selisih</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Status</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">Catatan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="(item, index) in filteredItems" :key="item.product_id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="p-3 text-sm font-mono text-primary">{{ item.kode_barang }}</td>
                                    <td class="p-3 text-sm font-medium text-gray-900 dark:text-white">{{ item.nama_barang }}</td>
                                    <td class="p-3 text-sm text-gray-500">{{ item.kategori }}</td>
                                    <td class="p-3 text-center text-sm text-gray-600 dark:text-gray-400">
                                        {{ parseFloat(item.qty_system).toLocaleString('id-ID') }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <input 
                                            v-model.number="form.items[props.products.findIndex(p => p.id === item.product_id)].qty_physical"
                                            type="number" 
                                            step="0.01"
                                            min="0"
                                            class="w-24 text-center rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-1 px-2"
                                        />
                                    </td>
                                    <td class="p-3 text-center text-sm text-gray-500">{{ item.satuan }}</td>
                                    <td class="p-3 text-center">
                                        <span :class="getDiffClass(item.selisih)">
                                            {{ item.selisih > 0 ? '+' : '' }}{{ item.selisih.toLocaleString('id-ID') }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusBadge(item.selisih).class]">
                                            {{ getStatusBadge(item.selisih).text }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <input 
                                            v-model="form.items[props.products.findIndex(p => p.id === item.product_id)].notes"
                                            type="text" 
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-1 px-2"
                                            placeholder="Catatan..."
                                        />
                                    </td>
                                </tr>
                                <tr v-if="filteredItems.length === 0">
                                    <td colspan="9" class="p-8 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">inventory_2</span>
                                        <p>Tidak ada produk dengan selisih.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Submit Button -->
                    <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <Link 
                            :href="route('dashboard')" 
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                        >
                            Batal
                        </Link>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 bg-primary hover:bg-primary-dark text-white rounded-lg shadow-sm transition-colors flex items-center disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="material-symbols-outlined animate-spin mr-2">progress_activity</span>
                            <span class="material-symbols-outlined mr-2" v-else>save</span>
                            Finalisasi Opname
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </SaeLayout>
</template>
