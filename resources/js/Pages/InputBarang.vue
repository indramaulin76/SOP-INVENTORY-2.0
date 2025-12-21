<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: Array,
    units: Array,
    flash: Object
});

const form = useForm({
    nama_barang: '',
    category_id: '',
    unit_id: '',
    limit_stock: 0,
    harga_jual_default: 0, // Optional selling price
});

const submit = () => {
    form.post(route('products.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Input Data Barang" />

    <SaeLayout>
        <template #header>Input Data Barang</template>

        <div class="max-w-2xl mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb" class="flex mb-5">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <Link class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary dark:text-gray-400 dark:hover:text-white" href="/">
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
                            <span class="text-sm font-medium text-primary md:ml-2">Barang</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Flash Message -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary/5 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Form Input Barang</h3>
                        <p class="text-xs text-gray-500 mt-1">Daftarkan barang baru ke sistem</p>
                    </div>
                    <div class="bg-primary/10 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary">inventory_2</span>
                    </div>
                </div>
                
                <div class="p-6 lg:p-8">
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Nama Barang -->
                        <div class="space-y-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_barang">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-gray-400 text-sm">label</span>
                                </div>
                                <input 
                                    v-model="form.nama_barang"
                                    class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                    id="nama_barang" 
                                    type="text"
                                    placeholder="Masukkan nama barang" 
                                    required
                                />
                            </div>
                            <p v-if="form.errors.nama_barang" class="text-xs text-red-500 mt-1">{{ form.errors.nama_barang }}</p>
                            <p class="text-xs text-gray-400 italic mt-1">Kode barang akan otomatis dibuat oleh sistem</p>
                        </div>

                        <!-- Kategori & Satuan (Grid) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Kategori -->
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="category_id">
                                    Kategori <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">category</span>
                                    </div>
                                    <select 
                                        v-model="form.category_id"
                                        class="block w-full pl-10 pr-10 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="category_id"
                                        required
                                    >
                                        <option value="" disabled>Pilih Kategori</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.nama_kategori }} ({{ category.kode_kategori }})
                                        </option>
                                    </select>
                                </div>
                                <p v-if="form.errors.category_id" class="text-xs text-red-500 mt-1">{{ form.errors.category_id }}</p>
                            </div>

                            <!-- Satuan -->
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="unit_id">
                                    Satuan <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">straighten</span>
                                    </div>
                                    <select 
                                        v-model="form.unit_id"
                                        class="block w-full pl-10 pr-10 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="unit_id"
                                        required
                                    >
                                        <option value="" disabled>Pilih Satuan</option>
                                        <option v-for="unit in units" :key="unit.id" :value="unit.id">
                                            {{ unit.nama_satuan }} ({{ unit.singkatan }})
                                        </option>
                                    </select>
                                </div>
                                <p v-if="form.errors.unit_id" class="text-xs text-red-500 mt-1">{{ form.errors.unit_id }}</p>
                            </div>
                        </div>

                        <!-- Limit Stock & Harga Jual (Grid) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Limit Stock -->
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="limit_stock">
                                    Limit Stock <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">warning</span>
                                    </div>
                                    <input 
                                        v-model="form.limit_stock"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="limit_stock" 
                                        type="number"
                                        min="0"
                                        placeholder="0" 
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.limit_stock" class="text-xs text-red-500 mt-1">{{ form.errors.limit_stock }}</p>
                                <p class="text-xs text-gray-400 mt-1">Batas minimal untuk peringatan restock</p>
                            </div>

                            <!-- Harga Jual -->
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="harga_jual_default">
                                    Harga Jual <span class="text-gray-400 text-xs">(Opsional)</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 text-sm">Rp</span>
                                    </div>
                                    <input 
                                        v-model="form.harga_jual_default"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="harga_jual_default" 
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        placeholder="0" 
                                    />
                                </div>
                                <p v-if="form.errors.harga_jual_default" class="text-xs text-red-500 mt-1">{{ form.errors.harga_jual_default }}</p>
                                <p class="text-xs text-gray-400 mt-1">Default harga jual per satuan</p>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex">
                                <span class="material-symbols-outlined text-blue-500 mr-3 flex-shrink-0">info</span>
                                <div class="text-sm text-blue-700 dark:text-blue-300">
                                    <p class="font-medium mb-1">Catatan Penting:</p>
                                    <p>Form ini hanya untuk mendaftarkan data barang. Untuk menambah stok, gunakan menu:</p>
                                    <ul class="list-disc list-inside mt-1 text-xs">
                                        <li><strong>Input Saldo Awal</strong> - untuk stok awal</li>
                                        <li><strong>Input Barang Masuk</strong> - untuk pembelian/produksi</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                            <button 
                                type="button" 
                                @click="form.reset()"
                                class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            >
                                Reset
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="px-6 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center disabled:opacity-50"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined animate-spin mr-2 text-lg">progress_activity</span>
                                <span class="material-symbols-outlined mr-2 text-lg" v-else>save</span>
                                Simpan Barang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
