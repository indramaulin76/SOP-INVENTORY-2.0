<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    units: {
        type: Array,
        default: () => []
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Calculate total asset value
const totalAssetValue = computed(() => {
    return props.products.reduce((sum, p) => sum + (p.stok * p.harga_beli), 0);
});

const totalStock = computed(() => {
    return props.products.reduce((sum, p) => sum + p.stok, 0);
});
</script>

<template>
    <Head title="Saldo Awal" />

    <SaeLayout>
        <template #header>Saldo Awal</template>

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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Input Data</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-primary md:ml-2">Saldo Awal</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Info Banner -->
            <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg flex items-start dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-300">
                <span class="material-symbols-outlined mr-3 mt-0.5">info</span>
                <div>
                    <p class="font-medium">Halaman Saldo Awal (Read-Only)</p>
                    <p class="text-sm mt-1">Halaman ini menampilkan seluruh barang yang sudah di-input beserta stok dan harganya. Untuk menambah barang baru, gunakan menu <strong>Input Data > Barang</strong>.</p>
                </div>
            </div>

            <!-- Success Banner -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Barang</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ products.length }}</p>
                        </div>
                        <div class="bg-primary/10 p-3 rounded-lg">
                            <span class="material-symbols-outlined text-primary text-2xl">inventory_2</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Stok</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalStock.toLocaleString('id-ID') }}</p>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-lg">
                            <span class="material-symbols-outlined text-green-600 text-2xl">stacks</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Total Nilai Aset</p>
                            <p class="text-2xl font-bold text-primary">{{ formatCurrency(totalAssetValue) }}</p>
                        </div>
                        <div class="bg-primary/10 p-3 rounded-lg">
                            <span class="material-symbols-outlined text-primary text-2xl">payments</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3">payments</span>
                        <h3 class="text-lg font-semibold">Daftar Barang & Saldo</h3>
                    </div>
                    <Link href="/input-data/barang" class="bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg text-sm flex items-center transition-colors">
                        <span class="material-symbols-outlined text-lg mr-1">add</span>
                        Tambah Barang
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Kode</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Nama Barang</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Kategori</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Satuan</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Stok</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Harga Beli</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Harga Jual</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Nilai Aset</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-3 text-sm font-mono text-gray-800 dark:text-white">{{ product.kode_barang }}</td>
                                <td class="px-4 py-3 text-sm text-gray-800 dark:text-white font-medium">{{ product.nama_barang }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full bg-primary/10 text-primary font-medium">{{ product.category }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ product.unit }}</td>
                                <td class="px-4 py-3 text-sm text-right font-semibold" :class="product.stok <= product.limit_stock ? 'text-red-500' : 'text-gray-800 dark:text-white'">
                                    {{ product.stok.toLocaleString('id-ID') }}
                                    <span v-if="product.stok <= product.limit_stock" class="material-symbols-outlined text-[14px] ml-1 align-middle">warning</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600 dark:text-gray-300">{{ formatCurrency(product.harga_beli) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-green-600 dark:text-green-400 font-medium">{{ formatCurrency(product.harga_jual) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-gray-800 dark:text-white">{{ formatCurrency(product.stok * product.harga_beli) }}</td>
                            </tr>
                            <tr v-if="!products || products.length === 0">
                                <td colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-5xl mb-3 block opacity-50">inventory_2</span>
                                    <p class="mb-2">Belum ada data barang</p>
                                    <Link href="/input-data/barang" class="text-primary hover:underline">Tambah barang pertama →</Link>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="products && products.length > 0" class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-sm font-bold text-gray-800 dark:text-white">TOTAL</td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-gray-800 dark:text-white">{{ totalStock.toLocaleString('id-ID') }}</td>
                                <td colspan="2"></td>
                                <td class="px-4 py-3 text-sm text-right font-bold text-primary">{{ formatCurrency(totalAssetValue) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
