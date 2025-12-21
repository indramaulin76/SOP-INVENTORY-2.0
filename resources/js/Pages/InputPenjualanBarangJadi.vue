<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    customers: {
        type: Array,
        default: () => []
    },
    receiptNumber: {
        type: String,
        default: ''
    }
});

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    customer_id: null,
    keterangan: '',
    items: []
});

// Add item
const addItem = () => {
    form.items.push({
        product_id: null,
        quantity: 1,
        harga_jual: 0,
    });
};

// Remove item
const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// Get product details
const getProduct = (productId) => {
    if (!productId) return null;
    return props.products.find(p => p.id == productId);
};

// Update harga jual when product selected
const onProductChange = (item) => {
    const product = getProduct(item.product_id);
    if (product) {
        item.harga_jual = product.harga_jual;
    }
};

// Calculate item total
const calculateItemTotal = (item) => {
    return (item.quantity || 0) * (item.harga_jual || 0);
};

// Grand total
const grandTotal = computed(() => {
    return form.items.reduce((sum, item) => sum + calculateItemTotal(item), 0);
});

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// Format stock numbers - removes unnecessary decimals (1 instead of 1.00)
const formatStock = (value) => {
    const num = parseFloat(value);
    if (isNaN(num)) return '0';
    return Number.isInteger(num) ? num.toString() : num.toLocaleString('id-ID');
};

// Submit
const submit = () => {
    form.post(route('barang-keluar.penjualan-barang-jadi.store'), {
        onSuccess: () => {
            form.reset();
            addItem();
        },
    });
};

// Initialize with one item
if (form.items.length === 0) {
    addItem();
}
</script>

<template>
    <Head title="Input Penjualan Barang Jadi" />

    <SaeLayout>
        <template #header>Input Penjualan Barang Jadi</template>

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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Input Barang Keluar</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-primary md:ml-2">Penjualan Barang Jadi</span>
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

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3 filled">storefront</span>
                        <h3 class="text-lg font-semibold">Form Input Penjualan Barang Jadi</h3>
                    </div>
                    <span class="text-sm opacity-80">{{ receiptNumber }}</span>
                </div>
                
                <form @submit.prevent="submit" class="p-6 lg:p-8 space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Tanggal <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    v-model="form.tanggal"
                                    type="date" 
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                                    required
                                />
                                <p v-if="form.errors.tanggal" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Nomor Bukti
                                </label>
                                <input 
                                    :value="receiptNumber"
                                    type="text" 
                                    class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 py-2 px-3 cursor-not-allowed"
                                    readonly
                                />
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Customer
                                </label>
                                <select 
                                    v-model="form.customer_id"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                                >
                                    <option :value="null">-- Pilih Customer (Opsional) --</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.nama_customer }} ({{ c.kode_customer }})</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Keterangan
                                </label>
                                <textarea 
                                    v-model="form.keterangan"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" 
                                    placeholder="Catatan tambahan..." 
                                    rows="2"
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-700 my-8"></div>

                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Barang</h4>
                    </div>

                    <!-- Validation Error -->
                    <div v-if="form.errors.items" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                        {{ form.errors.items }}
                    </div>

                    <div class="overflow-x-auto rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 35%;">Nama Barang</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs" style="width: 15%;">Stok</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Quantity</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Harga Jual</th>
                                    <th class="bg-primary text-white font-semibold text-right px-4 py-3 text-xs" style="width: 15%;">Jumlah</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs" style="width: 5%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="p-3">
                                        <select 
                                            v-model="item.product_id"
                                            @change="onProductChange(item)"
                                            class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                            required
                                        >
                                            <option :value="null">-- Pilih Barang --</option>
                                            <option v-for="p in products" :key="p.id" :value="p.id">
                                                {{ p.nama_barang }} ({{ p.kode_barang }})
                                            </option>
                                        </select>
                                        <p v-if="form.errors[`items.${index}.product_id`]" class="text-red-500 text-xs mt-1">Produk harus dipilih</p>
                                    </td>
                                    <td class="p-3 text-center">
                                        <span class="text-sm font-medium" :class="getProduct(item.product_id)?.available_stock > 0 ? 'text-green-600' : 'text-gray-400'">
                                            {{ formatStock(getProduct(item.product_id)?.available_stock ?? 0) }} {{ getProduct(item.product_id)?.satuan ?? '' }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <input 
                                            v-model="item.quantity"
                                            type="number" 
                                            min="1"
                                            step="1"
                                            :max="getProduct(item.product_id)?.available_stock ?? 0"
                                            class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                            required
                                        />
                                    </td>
                                    <td class="p-3">
                                        <input 
                                            v-model="item.harga_jual"
                                            type="number" 
                                            min="0"
                                            class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                        />
                                    </td>
                                    <td class="p-3 text-right">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-white">{{ formatCurrency(calculateItemTotal(item)) }}</span>
                                    </td>
                                    <td class="p-3 text-center">
                                        <button @click="removeItem(index)" type="button" class="bg-red-500 hover:bg-red-600 text-white rounded p-1.5 flex items-center justify-center transition-colors mx-auto" :disabled="form.items.length <= 1">
                                            <span class="material-symbols-outlined text-sm">delete</span>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <button @click="addItem" type="button" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                            <span class="material-symbols-outlined text-lg mr-1">add</span>
                            Tambah Barang
                        </button>
                    </div>

                    <div class="flex justify-end mt-4 px-4 py-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                        <span class="text-lg font-bold text-primary">Total: {{ formatCurrency(grandTotal) }}</span>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end items-center mt-8 gap-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                        <Link href="/" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                            <span class="material-symbols-outlined text-lg mr-2">close</span>
                            Batal
                        </Link>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors disabled:opacity-50"
                        >
                            <span v-if="form.processing" class="material-symbols-outlined animate-spin text-lg mr-2">refresh</span>
                            <span v-else class="material-symbols-outlined text-lg mr-2">save</span>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Penjualan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </SaeLayout>
</template>
