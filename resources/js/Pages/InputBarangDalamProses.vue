<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    suppliers: {
        type: Array,
        default: () => []
    },
    products: {
        type: Array,
        default: () => []
    },
    rawMaterials: {
        type: Array,
        default: () => []
    },
    invoiceNumber: {
        type: String,
        default: ''
    },
    departments: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    tanggal: new Date().toISOString().split('T')[0],
    nomor_faktur: props.invoiceNumber,
    source_mode: 'purchase', // 'purchase' or 'production'
    supplier_id: '',
    department_id: '',
    keterangan: '',
    items: [
        {
            product_id: '',
            quantity: 1,
            harga_beli: 0,
        }
    ],
    materials: [
        {
            product_id: '',
            namaBarang: '',
            kodeBarang: '',
            quantity: 0,
            satuan: '',
            current_stock: 0,
        }
    ]
});

// Watch source_mode changes to reset supplier
watch(() => form.source_mode, (newMode) => {
    if (newMode === 'production') {
        form.supplier_id = '';
    }
});

// WIP Items functions
const addItem = () => {
    form.items.push({
        product_id: '',
        quantity: 1,
        harga_beli: 0,
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

// Auto-fill harga_beli when product is selected (only for purchase mode)
const onProductChange = (item) => {
    const product = getProduct(item.product_id);
    if (product && item.harga_beli === 0 && form.source_mode === 'purchase') {
        item.harga_beli = product.harga_beli_default;
    }
};

// Calculate item total
const getItemTotal = (item) => {
    if (form.source_mode === 'purchase') {
        return item.quantity * item.harga_beli;
    }
    return 0; // For production, calculated from materials
};

// Grand total for WIP items
const total = computed(() => {
    if (form.source_mode === 'purchase') {
        return form.items.reduce((acc, item) => acc + getItemTotal(item), 0);
    }
    // For production, total is based on materials used
    return totalMaterialsCost.value;
});

// ========== Raw Materials Section (for Production mode) ==========

const addMaterial = () => {
    form.materials.push({
        product_id: '',
        namaBarang: '',
        kodeBarang: '',
        quantity: 0,
        satuan: '',
        current_stock: 0,
    });
};

const removeMaterial = (index) => {
    if (form.materials.length > 1) {
        form.materials.splice(index, 1);
    }
};

// Get raw material details by ID
const getRawMaterial = (productId) => {
    return props.rawMaterials.find(m => m.id == productId) || null;
};

// Update material details when selected
const updateMaterialDetails = (index) => {
    const item = form.materials[index];
    const material = getRawMaterial(item.product_id);
    
    if (material) {
        item.namaBarang = material.nama_barang;
        item.kodeBarang = material.kode_barang;
        item.satuan = material.unit;
        item.current_stock = material.current_stock;
    } else {
        item.namaBarang = '';
        item.kodeBarang = '';
        item.satuan = '';
        item.current_stock = 0;
    }
};

// Get estimated cost for a material
const getMaterialCost = (item) => {
    const material = getRawMaterial(item.product_id);
    if (!material || !item.quantity) return 0;
    return item.quantity * (material.avg_price || 0);
};

// Total materials cost (estimated HPP)
const totalMaterialsCost = computed(() => {
    return form.materials.reduce((acc, item) => acc + getMaterialCost(item), 0);
});

// Estimated HPP per unit
const estimatedHppPerUnit = computed(() => {
    const totalWipQty = form.items.reduce((acc, item) => acc + (parseFloat(item.quantity) || 0), 0);
    if (totalWipQty <= 0) return 0;
    return totalMaterialsCost.value / totalWipQty;
});

const submit = () => {
    form.post(route('barang-masuk.barang-dalam-proses.store'), {
        onSuccess: () => {
            form.reset();
            form.items = [{ product_id: '', quantity: 1, harga_beli: 0 }];
            form.materials = [{ product_id: '', namaBarang: '', kodeBarang: '', quantity: 0, satuan: '', current_stock: 0 }];
        }
    });
};
</script>

<template>
    <Head title="Input Barang Dalam Proses" />

    <SaeLayout>
        <template #header>Input Barang Dalam Proses</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Barang Dalam Proses</span>
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
                        <span class="material-symbols-outlined mr-3 filled">conveyor_belt</span>
                        <h3 class="text-lg font-semibold">Form Input Barang Dalam Proses</h3>
                    </div>
                    <Link :href="route('barang-masuk.barang-dalam-proses.index')" class="bg-white/20 hover:bg-white/30 text-white text-sm px-3 py-1.5 rounded-lg transition-colors flex items-center backdrop-blur-sm">
                        <span class="material-symbols-outlined text-base mr-1.5">history</span>
                        Riwayat Input
                    </Link>
                </div>
                
                <div class="p-6 lg:p-8 space-y-8">
                    <form @submit.prevent="submit">
                        
                        <!-- Source Mode Toggle -->
                        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                <span class="material-symbols-outlined text-lg mr-1 align-middle">source</span>
                                Sumber Asal WIP
                            </label>
                            <div class="flex flex-wrap gap-4">
                                <label class="flex items-center cursor-pointer group">
                                    <input 
                                        type="radio" 
                                        v-model="form.source_mode" 
                                        value="purchase" 
                                        class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                    />
                                    <span class="ml-2 flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary">
                                        <span class="material-symbols-outlined text-lg mr-1">local_shipping</span>
                                        Beli dari Vendor
                                    </span>
                                </label>
                                <label class="flex items-center cursor-pointer group">
                                    <input 
                                        type="radio" 
                                        v-model="form.source_mode" 
                                        value="production" 
                                        class="w-4 h-4 text-primary border-gray-300 focus:ring-primary"
                                    />
                                    <span class="ml-2 flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-primary">
                                        <span class="material-symbols-outlined text-lg mr-1">skillet</span>
                                        Produksi Sendiri
                                    </span>
                                </label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                <span v-if="form.source_mode === 'purchase'">
                                    WIP dibeli dari vendor/supplier dengan harga manual.
                                </span>
                                <span v-else class="text-primary">
                                    WIP diproduksi internal menggunakan bahan baku. HPP otomatis dihitung dari biaya bahan baku.
                                </span>
                            </p>
                        </div>

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
                                        Nomor Faktur
                                    </label>
                                    <input 
                                        v-model="form.nomor_faktur"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" 
                                        placeholder="WIP-YYYYMMDD-001" 
                                        type="text"
                                    />
                                    <p class="text-xs text-gray-400 mt-1">Kosongkan untuk auto-generate</p>
                                    <p v-if="form.errors.nomor_faktur" class="text-red-500 text-xs mt-1">{{ form.errors.nomor_faktur }}</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Keterangan
                                    </label>
                                    <textarea 
                                        v-model="form.keterangan"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3" 
                                        placeholder="Catatan tambahan..." 
                                        rows="3"
                                    ></textarea>
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Pilih Departemen
                                    </label>
                                    <select 
                                        v-model="form.department_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                                    >
                                        <option value="">-- Pilih Departemen --</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.nama_departemen }}
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-400 mt-1">Departemen yang akan menggunakan/memproses WIP</p>
                                    <p v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</p>
                                </div>
                                
                                <!-- Supplier - Only show for Purchase mode -->
                                <div v-if="form.source_mode === 'purchase'">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Supplier (Opsional)
                                    </label>
                                    <select 
                                        v-model="form.supplier_id"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary sm:text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white py-2 px-3"
                                    >
                                        <option value="">-- Pilih Supplier (Optional) --</option>
                                        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                                            {{ supplier.nama_supplier }} ({{ supplier.kode_supplier }})
                                        </option>
                                    </select>
                                    <p class="text-xs text-gray-400 mt-1">Pilih supplier jika WIP dari vendor luar</p>
                                </div>

                                <!-- Production Mode Info -->
                                <div v-else class="p-3 bg-primary/5 border border-primary/20 rounded-lg">
                                    <div class="flex items-center text-primary">
                                        <span class="material-symbols-outlined mr-2">info</span>
                                        <span class="text-sm font-medium">Mode Produksi Internal</span>
                                    </div>
                                    <p class="text-xs text-primary/80 mt-1">
                                        HPP akan dihitung otomatis dari bahan baku yang digunakan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION: WIP Items Output -->
                        <div class="border-t border-gray-100 dark:border-gray-700 my-8"></div>

                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                                <span class="material-symbols-outlined mr-2 text-primary">inventory_2</span>
                                Daftar Barang Dalam Proses (Output)
                            </h4>
                            <span class="text-sm text-gray-500">{{ products.length }} produk WIP tersedia</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 30%;">Nama Barang</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Quantity</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Satuan</th>
                                        <th v-if="form.source_mode === 'purchase'" scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Harga Beli</th>
                                        <th v-if="form.source_mode === 'purchase'" scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Jumlah</th>
                                        <th v-if="form.source_mode === 'production'" scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 20%;">Est. HPP/Unit</th>
                                        <th scope="col" class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs" style="width: 10%;">Aksi</th>
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
                                                <option value="">Pilih Barang</option>
                                                <option v-for="product in products" :key="product.id" :value="product.id">
                                                    {{ product.nama_barang }} ({{ product.kode_barang }})
                                                </option>
                                            </select>
                                            <p v-if="form.errors[`items.${index}.product_id`]" class="text-red-500 text-xs mt-1">
                                                {{ form.errors[`items.${index}.product_id`] }}
                                            </p>
                                        </td>
                                        <td class="p-3">
                                            <input 
                                                v-model.number="item.quantity"
                                                type="number" 
                                                min="0.01" 
                                                step="0.01"
                                                class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                                required
                                            />
                                        </td>
                                        <td class="p-3 text-sm text-gray-600 dark:text-gray-400">
                                            <span v-if="getProduct(item.product_id)">
                                                {{ getProduct(item.product_id).unit }}
                                            </span>
                                            <span v-else>-</span>
                                        </td>
                                        <td v-if="form.source_mode === 'purchase'" class="p-3">
                                            <input 
                                                v-model.number="item.harga_beli"
                                                type="number" 
                                                min="0" 
                                                step="0.01"
                                                class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                                required
                                            />
                                        </td>
                                        <td v-if="form.source_mode === 'purchase'" class="p-3 text-sm font-semibold text-gray-800 dark:text-white">
                                            Rp {{ getItemTotal(item).toLocaleString('id-ID') }}
                                        </td>
                                        <td v-if="form.source_mode === 'production'" class="p-3 text-sm font-semibold text-primary">
                                            Rp {{ estimatedHppPerUnit.toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}
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
                                Tambah Barang WIP
                            </button>
                        </div>

                        <!-- SECTION: Raw Materials (only for Production mode) -->
                        <div v-if="form.source_mode === 'production'">
                            <div class="border-t border-gray-100 dark:border-gray-700 my-8"></div>

                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                                    <span class="material-symbols-outlined mr-2 text-primary">widgets</span>
                                    Daftar Bahan Baku Digunakan (Input)
                                </h4>
                                <span class="text-sm text-gray-500">{{ rawMaterials.length }} bahan baku tersedia</span>
                            </div>

                            <div v-if="rawMaterials.length === 0" class="bg-gray-50 border border-gray-200 text-gray-700 px-4 py-3 rounded-lg text-sm">
                                <div class="flex items-center">
                                    <span class="material-symbols-outlined mr-2">warning</span>
                                    Tidak ada Bahan Baku dengan stok tersedia saat ini.
                                </div>
                            </div>

                            <div v-else class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 30%;">Nama Bahan Baku</th>
                                            <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Stok</th>
                                            <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Quantity</th>
                                            <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Satuan</th>
                                            <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs" style="width: 15%;">Est. Biaya</th>
                                            <th scope="col" class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs" style="width: 10%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                        <tr v-for="(item, index) in form.materials" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                            <td class="p-3">
                                                <select 
                                                    v-model="item.product_id"
                                                    @change="updateMaterialDetails(index)"
                                                    class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                                    required
                                                >
                                                    <option value="">Pilih Bahan Baku</option>
                                                    <option v-for="mat in rawMaterials" :key="mat.id" :value="mat.id">
                                                        {{ mat.nama_barang }} ({{ mat.kode_barang }})
                                                    </option>
                                                </select>
                                                <div v-if="item.product_id && item.quantity > item.current_stock" class="text-red-500 text-[10px] mt-1 flex items-center">
                                                    <span class="material-symbols-outlined text-[12px] mr-1">warning</span>
                                                    Stok kurang!
                                                </div>
                                            </td>
                                            <td class="p-3 text-sm text-gray-600 dark:text-gray-400">
                                                <span v-if="item.product_id" class="font-medium">
                                                    {{ item.current_stock }} {{ item.satuan }}
                                                </span>
                                                <span v-else>-</span>
                                            </td>
                                            <td class="p-3">
                                                <input 
                                                    v-model.number="item.quantity"
                                                    type="number" 
                                                    min="0.01" 
                                                    step="0.01"
                                                    class="block w-full px-2 py-1.5 border border-gray-300 rounded text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white focus:border-primary focus:ring-primary"
                                                    required
                                                />
                                            </td>
                                            <td class="p-3 text-sm text-gray-600 dark:text-gray-400">
                                                {{ item.satuan || '-' }}
                                            </td>
                                            <td class="p-3 text-sm font-semibold text-gray-800 dark:text-white">
                                                Rp {{ getMaterialCost(item).toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}
                                            </td>
                                            <td class="p-3 text-center">
                                                <button 
                                                    @click="removeMaterial(index)" 
                                                    type="button" 
                                                    class="bg-red-500 hover:bg-red-600 text-white rounded p-1.5 flex items-center justify-center transition-colors mx-auto disabled:opacity-50"
                                                    :disabled="form.materials.length <= 1"
                                                >
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="rawMaterials.length > 0" class="mt-4 flex justify-between items-center">
                                <button @click="addMaterial" type="button" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                                    <span class="material-symbols-outlined text-lg mr-1">add</span>
                                    Tambah Bahan Baku
                                </button>
                                
                                <div class="text-right">
                                    <span class="text-sm text-gray-500">Total Biaya Bahan Baku:</span>
                                    <span class="ml-2 text-lg font-bold text-primary">Rp {{ totalMaterialsCost.toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="flex flex-col sm:flex-row justify-end items-center mt-8 gap-4 border-t border-gray-100 dark:border-gray-700 pt-6">
                            <div class="text-xl font-bold text-gray-800 dark:text-white mr-4">
                                Total: Rp {{ total.toLocaleString('id-ID', { maximumFractionDigits: 0 }) }}
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
                                {{ form.processing ? 'Menyimpan...' : 'Simpan WIP Entry' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
