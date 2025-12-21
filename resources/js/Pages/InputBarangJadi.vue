<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    finishedProducts: {
        type: Array,
        default: () => []
    },
    rawMaterials: {
        type: Array,
        default: () => []
    },
    nextProductionNo: {
        type: String,
        default: ''
    }
});

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    nomor_produksi: props.nextProductionNo,
    product_id: '',
    quantity_produced: 0,
    keterangan: '',
    materials: [
        {
            product_id: '',
            namaBahanBaku: '', // for display
            kodeBahanBaku: '', // for display
            quantity: 0,
            satuan: '', // for display
            current_stock: 0, // for validation warning
        }
    ]
});

// Computed selected finished product
const selectedProduct = computed(() => {
    return props.finishedProducts.find(p => p.id == form.product_id) || {};
});

// Helper for raw material details
const updateMaterialDetails = (index) => {
    const item = form.materials[index];
    const material = props.rawMaterials.find(m => m.id == item.product_id);
    
    if (material) {
        item.namaBahanBaku = material.nama_barang;
        item.kodeBahanBaku = material.kode_barang;
        item.satuan = material.unit;
        item.current_stock = material.current_stock;
    } else {
        item.namaBahanBaku = '';
        item.kodeBahanBaku = '';
        item.satuan = '';
        item.current_stock = 0;
    }
};

const addItem = () => {
    form.materials.push({
        product_id: '',
        namaBahanBaku: '',
        kodeBahanBaku: '',
        quantity: 0,
        satuan: '',
        current_stock: 0,
    });
};

const removeItem = (index) => {
    if (form.materials.length > 1) {
        form.materials.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('barang-masuk.barang-jadi.store'), {
        onSuccess: () => form.reset(),
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Input Barang Jadi" />

    <SaeLayout>
        <template #header>Input Barang Jadi</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Barang Jadi</span>
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
                        <span class="material-symbols-outlined mr-3 filled">bakery_dining</span>
                        <h3 class="text-lg font-semibold">Form Input Barang Jadi</h3>
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
                                        <input v-model="form.tanggal" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" type="date" />
                                        <div v-if="form.errors.tanggal" class="text-red-500 text-xs mt-1">{{ form.errors.tanggal }}</div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nomor Produksi <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.nomor_produksi" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" readonly type="text"/>
                                    <div v-if="form.errors.nomor_produksi" class="text-red-500 text-xs mt-1">{{ form.errors.nomor_produksi }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Keterangan
                                    </label>
                                    <textarea v-model="form.keterangan" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" placeholder="Catatan tambahan produksi..." rows="3"></textarea>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Produk Jadi <span class="text-red-500">*</span>
                                    </label>
                                    <select v-model="form.product_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3">
                                        <option value="">Pilih Produk</option>
                                        <option v-for="prod in props.finishedProducts" :key="prod.id" :value="prod.id">{{ prod.nama_barang }}</option>
                                    </select>
                                    <div v-if="form.errors.product_id" class="text-red-500 text-xs mt-1">{{ form.errors.product_id }}</div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Kode Produk
                                    </label>
                                    <input :value="selectedProduct.kode_barang || ''" class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-500 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 py-2 px-3 cursor-not-allowed" readonly type="text"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Result Quantity ({{ selectedProduct.unit || 'Unit' }}) <span class="text-red-500">*</span>
                                    </label>
                                    <input v-model="form.quantity_produced" type="number" step="0.01" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3 font-semibold text-lg text-primary"/>
                                    <div v-if="form.errors.quantity_produced" class="text-red-500 text-xs mt-1">{{ form.errors.quantity_produced }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 dark:border-gray-700 my-8"></div>

                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Bahan Baku Digunakan</h4>
                        </div>

                        <div class="overflow-x-auto rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 30%;">Nama Bahan Baku</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 20%;">Kode Bahan Baku</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 20%;">Quantity Digunakan</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 20%;">Satuan</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs" style="width: 10%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                    <tr v-for="(item, index) in form.materials" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <td class="p-3">
                                            <select v-model="item.product_id" @change="updateMaterialDetails(index)" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary">
                                                <option value="">Pilih Bahan Baku</option>
                                                <option v-for="mat in props.rawMaterials" :key="mat.id" :value="mat.id">
                                                    {{ mat.nama_barang }} (Stok: {{ mat.current_stock }} {{ mat.unit }})
                                                </option>
                                            </select>
                                            <div v-if="item.product_id && item.quantity > item.current_stock" class="text-red-500 text-[10px] mt-1 flex items-center">
                                                <span class="material-symbols-outlined text-[12px] mr-1">warning</span>
                                                Stok kurang!
                                            </div>
                                        </td>
                                        <td class="p-3">
                                            <input :value="item.kodeBahanBaku" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-gray-50 text-gray-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed" placeholder="Kode otomatis" readonly type="text"/>
                                        </td>
                                        <td class="p-3">
                                            <input type="number" step="0.01" v-model="item.quantity" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"/>
                                        </td>
                                        <td class="p-3">
                                            <input :value="item.satuan" class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm bg-gray-50 text-gray-500 dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed" readonly type="text"/>
                                        </td>
                                        <td class="p-3 text-center">
                                            <button @click="removeItem(index)" type="button" class="bg-red-500 hover:bg-red-600 text-white rounded p-1.5 flex items-center justify-center transition-colors mx-auto">
                                                <span class="material-symbols-outlined text-sm">delete</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="form.errors.materials" class="text-red-500 text-xs mt-2 px-4">{{ form.errors.materials }}</div>
                        </div>

                        <div class="mt-4">
                            <button @click="addItem" type="button" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                <span class="material-symbols-outlined text-lg mr-1">add</span>
                                Tambah Bahan Baku
                            </button>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end items-center mt-8 gap-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                            <Link href="/" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                                <span class="material-symbols-outlined text-lg mr-2">close</span>
                                Batal
                            </Link>
                            <button :disabled="form.processing" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors disabled:opacity-50" type="submit">
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin mr-2 text-lg">progress_activity</span>
                                <span v-else class="material-symbols-outlined text-lg mr-2">save</span>
                                Simpan Produksi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
