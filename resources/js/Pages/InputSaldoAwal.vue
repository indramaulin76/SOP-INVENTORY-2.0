<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

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

// Form for submitting opening balance
const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    keterangan: 'Saldo Awal Persediaan',
    items: [],
});

// Track which products are selected for saldo awal
const selectedProducts = ref([]);

// Initialize editable product data
const editableProducts = ref(
    props.products.map(p => ({
        ...p,
        stok_input: 0,
        harga_beli_input: p.harga_beli || 0,
        harga_jual_input: p.harga_jual || 0,
        selected: false,
    }))
);

// Watch for changes in editableProducts to update form items
watch(editableProducts, (newVal) => {
    form.items = newVal
        .filter(p => p.selected && p.stok_input > 0)
        .map(p => ({
            product_id: p.id,
            jumlah_unit: parseFloat(p.stok_input) || 0,
            harga_beli: parseFloat(p.harga_beli_input) || 0,
            harga_jual: parseFloat(p.harga_jual_input) || 0,
        }));
}, { deep: true });

// Toggle select all
const selectAll = ref(false);
const toggleSelectAll = () => {
    editableProducts.value.forEach(p => {
        p.selected = selectAll.value;
    });
};

// Count selected items
const selectedCount = computed(() => {
    return editableProducts.value.filter(p => p.selected && p.stok_input > 0).length;
});

// Calculate totals for selected items
const totalSelectedValue = computed(() => {
    return editableProducts.value
        .filter(p => p.selected)
        .reduce((sum, p) => sum + (parseFloat(p.stok_input) || 0) * (parseFloat(p.harga_beli_input) || 0), 0);
});

const totalSelectedStock = computed(() => {
    return editableProducts.value
        .filter(p => p.selected)
        .reduce((sum, p) => sum + (parseFloat(p.stok_input) || 0), 0);
});

// Submit form
const submitForm = () => {
    if (form.items.length === 0) {
        alert('Pilih minimal 1 barang dengan stok > 0 untuk disimpan!');
        return;
    }
    
    form.post(route('opening-balances.store'), {
        onSuccess: () => {
            // Reset form
            editableProducts.value.forEach(p => {
                p.stok_input = 0;
                p.selected = false;
            });
            selectAll.value = false;
        },
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Calculate existing total
const totalAssetValue = computed(() => {
    return props.products.reduce((sum, p) => sum + (p.stok * p.harga_beli), 0);
});

const totalStock = computed(() => {
    return props.products.reduce((sum, p) => sum + p.stok, 0);
});
</script>

<template>
    <Head title="Input Saldo Awal" />

    <SaeLayout>
        <template #header>Input Saldo Awal</template>

        <div class="max-w-[1600px] mx-auto">
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

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">error</span>
                {{ $page.props.flash.error }}
            </div>

            <!-- Info Banner -->
            <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg flex items-start dark:bg-amber-900/30 dark:border-amber-700 dark:text-amber-300">
                <span class="material-symbols-outlined mr-3 mt-0.5">tips_and_updates</span>
                <div>
                    <p class="font-medium">Input Saldo Awal Persediaan</p>
                    <p class="text-sm mt-1">Pilih barang yang ingin diisi saldo awalnya, masukkan jumlah stok dan harga beli, lalu klik <strong>Simpan Saldo Awal</strong>.</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
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
                            <p class="text-sm text-gray-500 dark:text-gray-400">Stok Tersimpan</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ totalStock.toLocaleString('id-ID') }}</p>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900/30 p-3 rounded-lg">
                            <span class="material-symbols-outlined text-green-600 text-2xl">stacks</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-amber-200 dark:border-amber-700 shadow-sm bg-amber-50 dark:bg-amber-900/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-amber-600 dark:text-amber-400">Dipilih untuk Input</p>
                            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ selectedCount }} item</p>
                        </div>
                        <div class="bg-amber-100 dark:bg-amber-900/30 p-3 rounded-lg">
                            <span class="material-symbols-outlined text-amber-600 text-2xl">checklist</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Nilai Input Baru</p>
                            <p class="text-xl font-bold text-primary">{{ formatCurrency(totalSelectedValue) }}</p>
                        </div>
                        <div class="bg-primary/10 p-3 rounded-lg">
                            <span class="material-symbols-outlined text-primary text-2xl">payments</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <form @submit.prevent="submitForm">
                <!-- Date and Description -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-100 dark:border-gray-700 shadow-sm mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Saldo Awal *</label>
                            <input 
                                v-model="form.tanggal" 
                                type="date" 
                                required
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-primary focus:border-primary"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                            <input 
                                v-model="form.keterangan" 
                                type="text" 
                                placeholder="Contoh: Saldo Awal Bulan Januari"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:ring-primary focus:border-primary"
                            />
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined mr-3">edit_note</span>
                            <h3 class="text-lg font-semibold">Input Stok Awal Per Barang</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center text-sm cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    v-model="selectAll" 
                                    @change="toggleSelectAll"
                                    class="rounded border-white/50 text-white bg-white/20 focus:ring-white mr-2"
                                />
                                Pilih Semua
                            </label>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-3 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase w-12">Pilih</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Kode</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Nama Barang</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Kategori</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Satuan</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Stok Saat Ini</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase bg-amber-50 dark:bg-amber-900/30">Stok Awal *</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase bg-amber-50 dark:bg-amber-900/30">Harga Beli *</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Harga Jual</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="(product, index) in editableProducts" :key="product.id" 
                                    :class="[
                                        'transition-colors',
                                        product.selected ? 'bg-amber-50 dark:bg-amber-900/10' : 'hover:bg-gray-50 dark:hover:bg-gray-800'
                                    ]">
                                    <td class="px-3 py-3 text-center">
                                        <input 
                                            type="checkbox" 
                                            v-model="product.selected"
                                            class="rounded border-gray-300 text-primary focus:ring-primary"
                                        />
                                    </td>
                                    <td class="px-4 py-3 text-sm font-mono text-gray-800 dark:text-white">{{ product.kode_barang }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 dark:text-white font-medium">{{ product.nama_barang }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 text-xs rounded-full bg-primary/10 text-primary font-medium">{{ product.category }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ product.unit }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold" :class="product.stok <= product.limit_stock ? 'text-red-500' : 'text-gray-800 dark:text-white'">
                                        {{ product.stok.toLocaleString('id-ID') }}
                                    </td>
                                    <td class="px-4 py-3 bg-amber-50/50 dark:bg-amber-900/10">
                                        <input 
                                            v-model.number="product.stok_input"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            placeholder="0"
                                            :disabled="!product.selected"
                                            class="w-24 text-right rounded-lg border-amber-300 dark:border-amber-600 dark:bg-gray-800 dark:text-white shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                        />
                                    </td>
                                    <td class="px-4 py-3 bg-amber-50/50 dark:bg-amber-900/10">
                                        <div class="relative">
                                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                            <input 
                                                v-model.number="product.harga_beli_input"
                                                type="number"
                                                min="0"
                                                placeholder="0"
                                                :disabled="!product.selected"
                                                class="w-28 text-right pl-7 rounded-lg border-amber-300 dark:border-amber-600 dark:bg-gray-800 dark:text-white shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="relative">
                                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-gray-400 text-xs">Rp</span>
                                            <input 
                                                v-model.number="product.harga_jual_input"
                                                type="number"
                                                min="0"
                                                placeholder="0"
                                                :disabled="!product.selected"
                                                class="w-28 text-right pl-7 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:ring-primary focus:border-primary text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                            />
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-gray-800 dark:text-white">
                                        <span v-if="product.selected && product.stok_input > 0">
                                            {{ formatCurrency(product.stok_input * product.harga_beli_input) }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                </tr>
                                <tr v-if="!products || products.length === 0">
                                    <td colspan="10" class="px-4 py-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-5xl mb-3 block opacity-50">inventory_2</span>
                                        <p class="mb-2">Belum ada data barang</p>
                                        <Link href="/input-data/barang" class="text-primary hover:underline">Tambah barang pertama →</Link>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-if="selectedCount > 0" class="bg-amber-100 dark:bg-amber-900/30">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-sm font-bold text-amber-800 dark:text-amber-300">
                                        TOTAL SALDO AWAL YANG AKAN DISIMPAN ({{ selectedCount }} item)
                                    </td>
                                    <td class="px-4 py-3 text-sm text-center font-bold text-amber-800 dark:text-amber-300">
                                        {{ totalSelectedStock.toLocaleString('id-ID') }}
                                    </td>
                                    <td colspan="2"></td>
                                    <td class="px-4 py-3 text-sm text-right font-bold text-amber-800 dark:text-amber-300">
                                        {{ formatCurrency(totalSelectedValue) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Submit Button -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            <span class="text-amber-600 font-medium">*</span> Kolom wajib diisi untuk barang yang dipilih
                        </div>
                        <div class="flex gap-3">
                            <Link 
                                href="/input-data/barang" 
                                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-sm font-medium"
                            >
                                Tambah Barang Baru
                            </Link>
                            <button 
                                type="submit"
                                :disabled="form.processing || selectedCount === 0"
                                class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-hover transition-colors text-sm font-medium flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin text-lg">refresh</span>
                                <span v-else class="material-symbols-outlined text-lg">save</span>
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Saldo Awal' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Form Errors -->
                <div v-if="form.errors && Object.keys(form.errors).length > 0" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <p class="font-medium mb-2">Terjadi kesalahan:</p>
                    <ul class="list-disc list-inside text-sm">
                        <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                    </ul>
                </div>
            </form>
        </div>
    </SaeLayout>
</template>
