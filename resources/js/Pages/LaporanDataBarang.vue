<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    products: Object,
    categories: {
        type: Array,
        default: () => []
    },
    filters: Object
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions || {});

// Filter state
const search = ref(props.filters?.search || '');
const categoryId = ref(props.filters?.category_id || '');

// Apply filter
const applyFilter = () => {
    router.get(route('laporan.data-barang'), {
        search: search.value || undefined,
        category_id: categoryId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Clear filter
const clearFilter = () => {
    search.value = '';
    categoryId.value = '';
    router.get(route('laporan.data-barang'), {}, {
        preserveState: true,
        replace: true,
    });
};

// Export Excel function
const exportExcel = () => {
    const params = new URLSearchParams({
        search: search.value || '',
        category_id: categoryId.value || '',
    }).toString();
    window.open(`/laporan/export/data-barang/excel?${params}`, '_blank');
};

// Export PDF function
const exportPdf = () => {
    const params = new URLSearchParams({
        search: search.value || '',
        category_id: categoryId.value || '',
    }).toString();
    window.location.href = `/laporan/export/data-barang/pdf?${params}`;
};

const showDeleteModal = ref(false);
const showConfirmChoiceModal = ref(false);
const showForceDeleteWarning = ref(false);
const productToDelete = ref(null);
const deleteConfirmData = ref(null);

// Watch for delete_confirmation_needed flash message
watch(() => page.props.flash.delete_confirmation_needed, (data) => {
    if (data) {
        deleteConfirmData.value = data;
        showConfirmChoiceModal.value = true;
    }
}, { deep: true });

const confirmDelete = (product) => {
    productToDelete.value = product;
    showDeleteModal.value = true;
};

const deleteProduct = () => {
    if (productToDelete.value) {
        router.delete(route('laporan.data-barang.delete', productToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                productToDelete.value = null;
            },
        });
    }
};

const archiveProduct = () => {
    if (deleteConfirmData.value) {
        router.put(route('products.archive', deleteConfirmData.value.id), {}, {
            onSuccess: () => {
                showConfirmChoiceModal.value = false;
                deleteConfirmData.value = null;
            },
        });
    }
};

const confirmForceDelete = () => {
    showConfirmChoiceModal.value = false;
    showForceDeleteWarning.value = true;
};

const forceDeleteProduct = () => {
    if (deleteConfirmData.value) {
        router.delete(route('products.force-destroy', deleteConfirmData.value.id), {
            onSuccess: () => {
                showForceDeleteWarning.value = false;
                deleteConfirmData.value = null;
            },
        });
    }
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Laporan Data Barang" />

    <SaeLayout>
        <template #header>Laporan Data Barang</template>

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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Laporan</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-primary md:ml-2">Data Barang</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.error }}</span>
            </div>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3">inventory_2</span>
                        <h3 class="text-lg font-semibold">Daftar Seluruh Data Barang</h3>
                    </div>
                    <div class="flex items-center space-x-3">
                        <span class="text-sm opacity-80">Total: {{ products.total }} items</span>
                        <div v-if="permissions.canViewHPP" class="flex gap-2">
                            <button 
                                @click="exportPdf" 
                                class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors"
                                title="Export PDF"
                            >
                                <span class="material-symbols-outlined text-xl">picture_as_pdf</span>
                            </button>
                            <button 
                                @click="exportExcel" 
                                class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors"
                                title="Export Excel"
                            >
                                <span class="material-symbols-outlined text-xl">table_view</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cari Barang</label>
                            <input 
                                v-model="search" 
                                type="text" 
                                placeholder="Nama atau kode barang..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                                @keyup.enter="applyFilter"
                            />
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kategori</label>
                            <select 
                                v-model="categoryId"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ cat.nama_kategori }}
                                </option>
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button 
                                @click="applyFilter"
                                class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary-hover transition-colors text-sm font-medium flex items-center gap-1"
                            >
                                <span class="material-symbols-outlined text-lg">filter_alt</span>
                                Filter
                            </button>
                            <button 
                                @click="clearFilter"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm font-medium"
                            >
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Tgl Input</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Kode</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Nama Barang</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Kategori</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Satuan</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Kuantitas</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Harga Beli</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Harga Jual</th>
                                <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ product.tanggal_input }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-800 dark:text-white">
                                    {{ product.kode_barang }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-800 dark:text-white font-medium">
                                    {{ product.nama_barang }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                    <span class="px-2 py-1 text-xs rounded-full bg-primary/10 text-primary font-medium">
                                        {{ product.kategori }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ product.satuan }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold" :class="product.is_low_stock ? 'text-red-500' : 'text-gray-800 dark:text-white'">
                                    {{ product.current_stock }}
                                    <span v-if="product.is_low_stock" class="material-symbols-outlined text-[14px] ml-1 align-middle">warning</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">
                                    {{ formatCurrency(product.harga_beli) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600 dark:text-green-400 font-medium">
                                    {{ formatCurrency(product.harga_jual) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <button @click="confirmDelete(product)" class="text-red-500 hover:text-red-700 transition-colors p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20" title="Hapus Data">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!products.data || products.data.length === 0">
                                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 block">inventory_2</span>
                                    Belum ada data barang
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="products.last_page > 1" class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <span class="text-sm text-gray-500">
                        Menampilkan {{ products.from }} - {{ products.to }} dari {{ products.total }} data
                    </span>
                    <div class="flex space-x-2">
                        <Link v-if="products.prev_page_url" :href="products.prev_page_url" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded transition-colors dark:bg-gray-800 dark:hover:bg-gray-700">
                            &laquo; Sebelumnya
                        </Link>
                        <Link v-if="products.next_page_url" :href="products.next_page_url" class="px-3 py-1.5 text-sm bg-gray-100 hover:bg-gray-200 rounded transition-colors dark:bg-gray-800 dark:hover:bg-gray-700">
                            Selanjutnya &raquo;
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="showDeleteModal = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="material-symbols-outlined text-red-600">warning</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    Hapus Data Barang
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Apakah Anda yakin ingin menghapus <strong>{{ productToDelete?.nama_barang }}</strong>?
                                        <br><br>
                                        <span class="text-red-500 font-semibold">PERHATIAN:</span> Tindakan ini akan menghapus secara permanen dan mempengaruhi semua data stok terkait.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="deleteProduct" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus Permanen
                        </button>
                        <button @click="showDeleteModal = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Choice Modal: Archive or Force Delete -->
        <div v-if="showConfirmChoiceModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="showConfirmChoiceModal = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="material-symbols-outlined text-yellow-600">link_off</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    Data Terikat Transaksi!
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                        {{ deleteConfirmData?.message }}
                                    </p>
                                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-3">
                                        <p class="text-xs text-blue-800 dark:text-blue-300 font-semibold mb-1">📂 ARSIPKAN (Direkomendasikan)</p>
                                        <p class="text-xs text-blue-700 dark:text-blue-400">
                                            Produk disembunyikan dari input baru, tapi data lama tetap aman untuk laporan.
                                        </p>
                                    </div>
                                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                        <p class="text-xs text-red-800 dark:text-red-300 font-semibold mb-1">⚠️ HAPUS PAKSA (Berbahaya)</p>
                                        <p class="text-xs text-red-700 dark:text-red-400">
                                            Hapus produk + SEMUA transaksi terkait. Laporan keuangan akan berubah!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 flex flex-col sm:flex-row gap-2">
                        <button @click="archiveProduct" type="button" class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:text-sm">
                            <span class="material-symbols-outlined text-sm mr-1">archive</span>
                            Arsipkan (Aman)
                        </button>
                        <button @click="confirmForceDelete" type="button" class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm">
                            <span class="material-symbols-outlined text-sm mr-1">delete_forever</span>
                            Hapus Paksa
                        </button>
                        <button @click="showConfirmChoiceModal = false" type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Force Delete Warning Modal (Double Confirmation) -->
        <div v-if="showForceDeleteWarning" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" @click="showForceDeleteWarning = false">
                    <div class="absolute inset-0 bg-gray-900 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="material-symbols-outlined text-red-600">dangerous</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-red-600 dark:text-red-400">
                                    Yakin Hapus Paksa?
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Produk <strong>{{ deleteConfirmData?.name }}</strong> dan <strong class="text-red-600">SEMUA transaksi terkait</strong> akan dihapus permanen:
                                    </p>
                                    <ul class="mt-2 text-xs text-gray-500 dark:text-gray-400 list-disc list-inside space-y-1">
                                        <li>Saldo Awal</li>
                                        <li>Pembelian Bahan Baku</li>
                                        <li>Pemakaian & Produksi WIP</li>
                                        <li>Inventory Batches</li>
                                        <li>Penjualan (jika ada)</li>
                                    </ul>
                                    <p class="mt-3 text-sm font-semibold text-red-600">
                                        ⚠️ Laporan keuangan dan audit trail akan berubah! Tidak dapat dibatalkan!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="forceDeleteProduct" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Ya, Hapus Semuanya!
                        </button>
                        <button @click="showForceDeleteWarning = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm dark:bg-gray-600 dark:text-white dark:border-gray-500 dark:hover:bg-gray-500">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
