<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    suppliers: {
        type: Array,
        default: () => []
    },
    nextInvoice: {
        type: String,
        default: ''
    }
});

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    nomor_faktur: props.nextInvoice,
    supplier_id: '',
    keterangan: '',
    items: [
        {
            product_id: '',
            namaBarang: '', // For display
            kodeBarang: '', // For display
            quantity: 0,
            unit: '', // For display
            harga_beli: 0,
        }
    ]
});

// Helper to find product details
const updateProductDetails = (index) => {
    const item = form.items[index];
    const product = props.products.find(p => p.id == item.product_id);
    
    if (product) {
        item.namaBarang = product.nama_barang;
        item.kodeBarang = product.kode_barang;
        item.unit = product.unit;
    } else {
        item.namaBarang = '';
        item.kodeBarang = '';
        item.unit = '';
    }
};

// Helper to find supplier details
const selectedSupplier = computed(() => {
    return props.suppliers.find(s => s.id == form.supplier_id) || {};
});

const addItem = () => {
    form.items.push({
        product_id: '',
        namaBarang: '',
        kodeBarang: '',
        quantity: 0,
        unit: '',
        harga_beli: 0,
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('barang-masuk.pembelian-bahan-baku.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Input Pembelian Bahan Baku" />

    <SaeLayout>
        <template #header>Input Pembelian Bahan Baku</template>

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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Input Barang Masuk</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-primary md:ml-2">Pembelian Bahan Baku</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.error }}</span>
            </div>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3 filled">shopping_cart</span>
                        <h3 class="text-lg font-semibold">Form Input Pembelian Bahan Baku</h3>
                    </div>
                </div>
                
                <div class="p-6 lg:p-8 space-y-8">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tanggal <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                            <span class="material-symbols-outlined text-gray-400 text-lg">calendar_today</span>
                                        </div>
                                        <input v-model="form.tanggal" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" type="date"/>
                                        <div v-if="form.errors.tanggal" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal }}</div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nomor Faktur <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.nomor_faktur" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" type="text" readonly/>
                                    <div v-if="form.errors.nomor_faktur" class="text-red-500 text-xs mt-1">{{ form.errors.nomor_faktur }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Keterangan
                                    </label>
                                    <textarea v-model="form.keterangan" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" placeholder="Catatan tambahan..." rows="3"></textarea>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Supplier <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="form.supplier_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3">
                                        <option value="">Pilih Supplier</option>
                                        <option v-for="supplier in props.suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.nama_supplier }}</option>
                                    </select>
                                    <div v-if="form.errors.supplier_id" class="text-red-500 text-xs mt-1">{{ form.errors.supplier_id }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Kode Supplier
                                    </label>
                                    <input :value="selectedSupplier.kode_supplier || ''" class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-500 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 py-2 px-3 cursor-not-allowed" readonly type="text"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Alamat & Telepon
                                    </label>
                                    <div class="text-sm text-gray-500 border border-gray-200 rounded-md p-2 bg-gray-50 h-[86px] overflow-y-auto">
                                        {{ selectedSupplier.alamat || '-' }} <br/>
                                        {{ selectedSupplier.telepon || '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 my-8"></div>

                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Barang</h4>
                            <button type="button" @click="addItem" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-primary bg-primary/10 hover:bg-primary/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                <span class="material-symbols-outlined text-sm mr-1">add</span>
                                Tambah Baris
                            </button>
                        </div>

                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 20%;">Nama Barang</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Kode Barang</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 10%;">Quantity</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Satuan</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Harga Beli</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Jumlah (Rp)</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs" style="width: 10%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                    <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <td class="p-3">
                                            <select v-model="item.product_id" @change="updateProductDetails(index)" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary">
                                                <option value="">Pilih Barang</option>
                                                <option v-for="prod in props.products" :key="prod.id" :value="prod.id">{{ prod.nama_barang }}</option>
                                            </select>
                                        </td>
                                        <td class="p-3">
                                            <input :value="item.kodeBarang" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-gray-50 text-gray-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed" readonly type="text"/>
                                        </td>
                                        <td class="p-3">
                                            <input type="number" step="0.01" v-model="item.quantity" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"/>
                                        </td>
                                        <td class="p-3">
                                            <input :value="item.unit" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-gray-50 text-gray-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed" readonly type="text"/>
                                        </td>
                                        <td class="p-3">
                                            <input type="number" v-model="item.harga_beli" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"/>
                                        </td>
                                        <td class="p-3">
                                            <input class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-gray-50 text-gray-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed" readonly type="number" :value="(item.quantity || 0) * (item.harga_beli || 0)"/>
                                        </td>
                                        <td class="p-3 text-center">
                                            <button @click="removeItem(index)" type="button" class="bg-red-500 hover:bg-red-600 text-white rounded p-1.5 flex items-center justify-center transition-colors mx-auto">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="form.errors.items" class="text-red-500 text-xs mt-2 px-4">{{ form.errors.items }}</div>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end items-center mt-8 gap-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                            <Link href="/" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                <span class="material-symbols-outlined text-lg mr-2">close</span>
                                Batal
                            </Link>
                            <button :disabled="form.processing" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors disabled:opacity-50" type="submit">
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin mr-2 text-lg">progress_activity</span>
                                <span v-else class="material-symbols-outlined text-lg mr-2">save</span>
                                Simpan Pembelian
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
