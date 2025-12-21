<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => []
    },
    referenceCode: {
        type: String,
        default: ''
    }
});

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    nomor_bukti: '',
    nama_departemen: '',
    keterangan: '',
    items: [
        {
            product_id: '',
            quantity: 1,
        }
    ]
});

const addItem = () => {
    form.items.push({
        product_id: '',
        quantity: 1,
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// Get product details by ID
const getProduct = (productId) => {
    return props.products.find(p => p.id == productId) || null;
};

// Calculate item total
const getItemTotal = (item) => {
    const product = getProduct(item.product_id);
    if (!product) return 0;
    return item.quantity * product.harga_jual;
};

// Format stock numbers - removes unnecessary decimals (1 instead of 1.00)
const formatStock = (value) => {
    const num = parseFloat(value);
    if (isNaN(num)) return '0';
    return Number.isInteger(num) ? num.toString() : num.toLocaleString('id-ID');
};

// Grand total
const total = computed(() => {
    return form.items.reduce((acc, item) => acc + getItemTotal(item), 0);
});

// Auto-generate nomor bukti on mount
if (!form.nomor_bukti) {
    const today = new Date();
    const dateStr = today.toISOString().split('T')[0].replace(/-/g, '');
    form.nomor_bukti = `PBB-${dateStr}-001`;
}

const submit = () => {
    form.post(route('barang-keluar.pemakaian-bahan-baku.store'), {
        onSuccess: () => {
            // Reset form after success
            form.reset();
            form.items = [{ product_id: '', quantity: 1 }];
        }
    });
};
</script>

<template>
    <Head title="Input Pemakaian Bahan Baku" />

    <SaeLayout>
        <template #header>Input Pemakaian Bahan Baku</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Pemakaian Bahan Baku</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>
            <div v-if="form.errors.error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">error</span>
                {{ form.errors.error }}
            </div>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3 filled">widgets</span>
                        <h3 class="text-lg font-semibold">Form Input Pemakaian Bahan Baku</h3>
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
                                        Nomor Bukti <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.nomor_bukti"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" 
                                        placeholder="PBB-YYYYMMDD-001" 
                                        type="text"
                                        required
                                    />
                                    <p v-if="form.errors.nomor_bukti" class="text-red-500 text-xs mt-1">{{ form.errors.nomor_bukti }}</p>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nama Departemen/Produksi <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.nama_departemen"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" 
                                        placeholder="Contoh: Produksi Roti Manis" 
                                        type="text"
                                        required
                                    />
                                    <p v-if="form.errors.nama_departemen" class="text-red-500 text-xs mt-1">{{ form.errors.nama_departemen }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Kode Referensi
                                    </label>
                                    <input 
                                        :value="referenceCode"
                                        class="block w-full rounded-md border-gray-300 bg-gray-50 text-gray-500 shadow-sm sm:text-sm dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400 py-2 px-3 cursor-not-allowed font-mono" 
                                        readonly 
                                        type="text"
                                    />
                                    <p class="text-xs text-gray-400 mt-1">Kode otomatis dari sistem</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
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

                        <div class="border-t border-gray-100 dark:border-gray-700 my-8"></div>

                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Bahan Baku</h4>
                            <span class="text-sm text-gray-500">{{ products.length }} bahan baku tersedia</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 30%;">Nama Barang</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Stok</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Quantity</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Harga</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Jumlah</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs" style="width: 10%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                    <tr v-for="(item, index) in form.items" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                        <td class="p-3">
                                            <select 
                                                v-model="item.product_id"
                                                class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                                required
                                            >
                                                <option value="">Pilih Bahan Baku</option>
                                                <option v-for="product in products" :key="product.id" :value="product.id">
                                                    {{ product.nama_barang }} ({{ product.kode_barang }})
                                                </option>
                                            </select>
                                            <p v-if="form.errors[`items.${index}.product_id`]" class="text-red-500 text-xs mt-1">
                                                {{ form.errors[`items.${index}.product_id`] }}
                                            </p>
                                        </td>
                                        <td class="p-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span v-if="getProduct(item.product_id)" class="font-medium">
                                                {{ formatStock(getProduct(item.product_id).available_stock) }} 
                                                {{ getProduct(item.product_id).unit }}
                                            </span>
                                            <span v-else class="text-gray-400">-</span>
                                        </td>
                                        <td class="p-3">
                                            <input 
                                                v-model.number="item.quantity"
                                                type="number" 
                                                min="1" 
                                                step="1"
                                                class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                                required
                                            />
                                        </td>
                                        <td class="p-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span v-if="getProduct(item.product_id)">
                                                Rp {{ getProduct(item.product_id).harga_jual.toLocaleString('id-ID') }}
                                            </span>
                                            <span v-else>-</span>
                                        </td>
                                        <td class="p-3 text-sm font-semibold text-gray-800 dark:text-white">
                                            Rp {{ getItemTotal(item).toLocaleString('id-ID') }}
                                        </td>
                                        <td class="p-3 text-center">
                                            <button 
                                                @click="removeItem(index)" 
                                                type="button" 
                                                class="bg-red-500 hover:bg-red-600 text-white rounded p-1.5 flex items-center justify-center transition-colors mx-auto disabled:opacity-50"
                                                :disabled="form.items.length <= 1"
                                            >
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
                                Tambah Bahan Baku
                            </button>
                        </div>

                        <div class="flex flex-col sm:flex-row justify-end items-center mt-8 gap-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                            <div class="text-xl font-bold text-gray-800 dark:text-white mr-4">
                                Total: Rp {{ total.toLocaleString('id-ID') }}
                            </div>
                            <Link 
                                href="/"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700"
                            >
                                <span class="material-symbols-outlined text-lg mr-2">close</span>
                                Batal
                            </Link>
                            <button 
                                type="submit"
                                :disabled="form.processing"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors disabled:opacity-50"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined text-lg mr-2 animate-spin">refresh</span>
                                <span v-else class="material-symbols-outlined text-lg mr-2">save</span>
                                {{ form.processing ? 'Menyimpan...' : 'Simpan Pemakaian' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
