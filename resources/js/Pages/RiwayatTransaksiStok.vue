<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    transactions: {
        type: Object,
        default: () => ({ data: [] })
    },
    summary: {
        type: Object,
        default: () => ({ totalMasuk: 0, totalKeluar: 0, totalTransaksi: 0, totalNilai: 0 })
    },
    categories: {
        type: Array,
        default: () => []
    },
    filters: Object
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions || {});

// Filter form state - initialized from props
const dateFrom = ref(props.filters?.dateFrom || '');
const dateTo = ref(props.filters?.dateTo || '');
const categoryId = ref(props.filters?.category_id || '');

// Apply filter function
const applyFilter = () => {
    router.get(route('laporan.riwayat-stok'), {
        dateFrom: dateFrom.value || undefined,
        dateTo: dateTo.value || undefined,
        category_id: categoryId.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Clear filter function
const clearFilter = () => {
    dateFrom.value = '';
    dateTo.value = '';
    categoryId.value = '';
    router.get(route('laporan.riwayat-stok'), {}, {
        preserveState: true,
        replace: true,
    });
};

// Export functions
const exportPdf = () => {
    const params = new URLSearchParams({
        dateFrom: dateFrom.value || '',
        dateTo: dateTo.value || '',
        category_id: categoryId.value || '',
    }).toString();
    window.open(`/laporan/export/stock-history/pdf?${params}`, '_blank');
};

const exportExcel = () => {
    const params = new URLSearchParams({
        dateFrom: dateFrom.value || '',
        dateTo: dateTo.value || '',
        category_id: categoryId.value || '',
    }).toString();
    window.open(`/laporan/export/stock-history/excel?${params}`, '_blank');
};

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
    // If it's a whole number, show without decimals
    return Number.isInteger(num) ? num.toString() : num.toLocaleString('id-ID');
};

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const activeTab = ref('all');

const tabs = [
    { id: 'all', label: 'Semua Transaksi' },
    { id: 'inbound', label: 'Barang Masuk' },
    { id: 'wip_in', label: 'Laporan Barang Dalam Proses' },
    { id: 'usage', label: 'Pemakaian/Produksi' },
    { id: 'sales', label: 'Penjualan' }
];

const setActiveTab = (tabId) => {
    activeTab.value = tabId;
};

// Filter transactions based on active tab
const filteredTransactions = computed(() => {
    if (!props.transactions?.data) return [];
    
    const data = props.transactions.data;
    
    switch(activeTab.value) {
        case 'inbound':
            return data.filter(t => ['Pembelian', 'Saldo Awal', 'Hasil Produksi'].includes(t.jenis_transaksi));
        case 'wip_in':
            return data.filter(t => ['WIP Masuk', 'Pemakaian WIP'].includes(t.jenis_transaksi));
        case 'usage':
            return data.filter(t => ['Pemakaian', 'Produksi', 'Pemakaian WIP', 'Pemakaian Produksi'].includes(t.jenis_transaksi));
        case 'sales':
            return data.filter(t => t.jenis_transaksi === 'Penjualan');
        default:
            return data;
    }
});

// Get icon based on transaction type
const getIcon = (type) => {
    switch(type) {
        case 'Pembelian': return 'shopping_cart';
        case 'Saldo Awal': return 'account_balance';
        case 'Hasil Produksi': return 'factory';
        case 'WIP Masuk': return 'conveyor_belt';
        case 'Pemakaian': 
        case 'Pemakaian WIP':
        case 'Pemakaian Produksi':
            return 'output';
        case 'Penjualan': return 'storefront';
        default: return 'inventory_2';
    }
};

// Get badge class based on transaction type
const getBadgeClass = (type) => {
    if (['Pembelian', 'Saldo Awal', 'Hasil Produksi', 'WIP Masuk'].includes(type)) {
        return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
    } else if (['Pemakaian', 'Pemakaian WIP'].includes(type)) {
        return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400';
    } else if (['Penjualan'].includes(type)) {
        return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
    }
    return 'bg-gray-100 text-gray-800';
};

// Permissions check for edit/delete
const canEditDelete = computed(() => {
    return permissions.value?.canViewHPP || false;
});

// Edit Modal State
const showEditModal = ref(false);
const editingTransaction = ref(null);
const editForm = ref({
    notes: '',
    transaction_date: '',
    reference_number: ''
});

// Parse transaction ID to get type and numeric id
const parseTransactionId = (id) => {
    if (!id) return { type: null, numericId: null };
    const parts = id.split('-');
    const type = parts[0];
    const numericId = parts.slice(1).join('-');
    return { type, numericId: parseInt(numericId) };
};

// Open Edit Modal
const openEditModal = (transaction) => {
    editingTransaction.value = transaction;
    editForm.value = {
        notes: '',
        transaction_date: '',
        reference_number: transaction.no_referensi || ''
    };
    showEditModal.value = true;
};

// Close Edit Modal
const closeEditModal = () => {
    showEditModal.value = false;
    editingTransaction.value = null;
    editForm.value = { notes: '', transaction_date: '', reference_number: '' };
};

// Submit Edit Form
const submitEdit = () => {
    if (!editingTransaction.value) return;
    
    const { type, numericId } = parseTransactionId(editingTransaction.value.id);
    if (!type || !numericId) {
        alert('ID transaksi tidak valid');
        return;
    }

    router.put(route('laporan.riwayat-stok.update', { type, id: numericId }), editForm.value, {
        onSuccess: () => {
            closeEditModal();
        },
        onError: (errors) => {
            alert('Gagal update: ' + Object.values(errors).flat().join(', '));
        }
    });
};

// Delete Transaction
const deleteTransaction = (transaction) => {
    const { type, numericId } = parseTransactionId(transaction.id);
    if (!type || !numericId) {
        alert('ID transaksi tidak valid');
        return;
    }

    const isInbound = ['Pembelian', 'Saldo Awal', 'Hasil Produksi', 'WIP Masuk'].includes(transaction.jenis_transaksi);
    const stockEffect = isInbound ? 'MENGURANGI' : 'MENGEMBALIKAN';
    const qty = isInbound ? transaction.masuk : transaction.keluar;
    
    const confirmMessage = `Anda yakin ingin menghapus transaksi ini?\n\n` +
        `Transaksi: ${transaction.jenis_transaksi}\n` +
        `Barang: ${transaction.nama_barang}\n` +
        `Qty: ${qty} ${transaction.satuan}\n\n` +
        `⚠️ Ini akan ${stockEffect} stok sebanyak ${qty} ${transaction.satuan}`;

    if (!confirm(confirmMessage)) {
        return;
    }

    router.delete(route('laporan.riwayat-stok.destroy', { type, id: numericId }), {
        onError: (errors) => {
            alert('Gagal hapus: ' + Object.values(errors).flat().join(', '));
        }
    });
};
</script>

<template>
    <Head title="Riwayat Transaksi Stok" />

    <SaeLayout>
        <template #header>Riwayat Transaksi Stok</template>

        <div class="max-w-[1400px] mx-auto">
            <!-- Flash Messages -->
            <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash?.error" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ $page.props.flash.error }}</span>
            </div>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Riwayat Stok</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Header -->
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3 filled">history</span>
                        <h3 class="text-lg font-semibold">Riwayat Transaksi Stok</h3>
                    </div>
                    <div v-if="permissions.canViewHPP" class="flex gap-2">
                        <button @click="exportPdf" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Export PDF">
                            <span class="material-symbols-outlined text-xl">picture_as_pdf</span>
                        </button>
                        <button @click="exportExcel" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Export Excel">
                            <span class="material-symbols-outlined text-xl">table_view</span>
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 dark:border-gray-700 px-6 bg-white dark:bg-gray-900">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button 
                            v-for="tab in tabs" 
                            :key="tab.id"
                            @click="setActiveTab(tab.id)"
                            :class="[
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === tab.id 
                                    ? 'border-primary text-primary' 
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                            ]"
                        >
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- Filter Form -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Dari Tanggal</label>
                            <input 
                                v-model="dateFrom" 
                                type="date" 
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
                            />
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sampai Tanggal</label>
                            <input 
                                v-model="dateTo" 
                                type="date" 
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary focus:ring-primary text-sm"
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

                <!-- Content -->
                <div class="p-6 lg:p-8 space-y-6 bg-white dark:bg-surface-dark">
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 rounded-lg p-4 border border-green-200 dark:border-green-800">
                            <div class="flex items-center justify-between">
                                <span class="text-green-600 dark:text-green-400 text-sm font-medium">Total Masuk</span>
                                <span class="material-symbols-outlined text-green-500">arrow_downward</span>
                            </div>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-300 mt-2">
                                {{ formatStock(summary.totalMasuk) }}
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 rounded-lg p-4 border border-red-200 dark:border-red-800">
                            <div class="flex items-center justify-between">
                                <span class="text-red-600 dark:text-red-400 text-sm font-medium">Total Keluar</span>
                                <span class="material-symbols-outlined text-red-500">arrow_upward</span>
                            </div>
                            <p class="text-2xl font-bold text-red-700 dark:text-red-300 mt-2">
                                {{ formatStock(summary.totalKeluar) }}
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                            <div class="flex items-center justify-between">
                                <span class="text-blue-600 dark:text-blue-400 text-sm font-medium">Total Transaksi</span>
                                <span class="material-symbols-outlined text-blue-500">receipt_long</span>
                            </div>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300 mt-2">
                                {{ summary.totalTransaksi }}
                            </p>
                        </div>
                        <div class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 rounded-lg p-4 border border-amber-200 dark:border-amber-800">
                            <div class="flex items-center justify-between">
                                <span class="text-amber-600 dark:text-amber-400 text-sm font-medium">Total Nilai</span>
                                <span class="material-symbols-outlined text-amber-500">payments</span>
                            </div>
                            <p class="text-lg font-bold text-amber-700 dark:text-amber-300 mt-2">
                                {{ formatCurrency(summary.totalNilai) }}
                            </p>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">Tanggal</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">No. Referensi</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">Jenis</th>
                                    <th class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs">Barang</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Masuk</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Keluar</th>
                                    <th class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Saldo</th>
                                    <th class="bg-primary text-white font-semibold text-right px-4 py-3 text-xs">Harga</th>
                                    <th v-if="canEditDelete" class="bg-primary text-white font-semibold text-center px-4 py-3 text-xs">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="item in filteredTransactions" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="p-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ item.tanggal }}
                                    </td>
                                    <td class="p-4 text-sm">
                                        <span class="font-mono text-primary">{{ item.no_referensi || '-' }}</span>
                                    </td>
                                    <td class="p-4">
                                        <span :class="['inline-flex items-center rounded-md px-2 py-1 text-xs font-medium', getBadgeClass(item.jenis_transaksi)]">
                                            <span class="material-symbols-outlined text-[14px] mr-1">{{ getIcon(item.jenis_transaksi) }}</span>
                                            {{ item.jenis_transaksi }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ item.nama_barang }}</span>
                                            <span class="text-xs text-gray-500">{{ item.kode_barang }} • {{ item.kategori }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span v-if="item.masuk > 0" class="text-green-600 dark:text-green-400 font-semibold">
                                            +{{ formatStock(item.masuk) }} {{ item.satuan }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span v-if="item.keluar > 0" class="text-red-600 dark:text-red-400 font-semibold">
                                            -{{ formatStock(item.keluar) }} {{ item.satuan }}
                                        </span>
                                        <span v-else class="text-gray-400">-</span>
                                    </td>
                                    <td class="p-4 text-center text-sm font-medium text-gray-900 dark:text-white">
                                        {{ formatStock(item.saldo) }} {{ item.satuan }}
                                    </td>
                                    <td class="p-4 text-right text-sm text-gray-600 dark:text-gray-300">
                                        {{ formatCurrency(item.harga_satuan || 0) }}
                                    </td>
                                    <td v-if="canEditDelete" class="p-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button 
                                                @click="openEditModal(item)"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                                title="Edit"
                                            >
                                                <span class="material-symbols-outlined text-lg">edit</span>
                                            </button>
                                            <button 
                                                @click="deleteTransaction(item)"
                                                class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                                title="Hapus"
                                            >
                                                <span class="material-symbols-outlined text-lg">delete</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="filteredTransactions.length === 0">
                                    <td :colspan="canEditDelete ? 9 : 8" class="p-8 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">inbox</span>
                                        <p>Belum ada data transaksi stok.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="transactions.last_page > 1" class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 pt-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Menampilkan {{ transactions.from }} - {{ transactions.to }} dari {{ transactions.total }} transaksi
                        </p>
                        <div class="flex gap-2">
                            <Link 
                                v-if="transactions.prev_page_url"
                                :href="transactions.prev_page_url"
                                class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                            >Previous</Link>
                            <Link 
                                v-if="transactions.next_page_url"
                                :href="transactions.next_page_url"
                                class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50"
                            >Next</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>

    <!-- Edit Modal -->
    <Teleport to="body">
        <div v-if="showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-black/50 transition-opacity" @click="closeEditModal"></div>
                
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-auto z-10 overflow-hidden">
                    <div class="bg-primary px-6 py-4 text-white">
                        <h3 class="text-lg font-semibold flex items-center">
                            <span class="material-symbols-outlined mr-2">edit</span>
                            Edit Transaksi
                        </h3>
                        <p class="text-sm text-white/70 mt-1">{{ editingTransaction?.jenis_transaksi }} - {{ editingTransaction?.nama_barang }}</p>
                    </div>
                    
                    <form @submit.prevent="submitEdit" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">No. Referensi</label>
                            <input 
                                v-model="editForm.reference_number"
                                type="text"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                                placeholder="Nomor referensi"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                            <input 
                                v-model="editForm.transaction_date"
                                type="date"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Keterangan</label>
                            <textarea 
                                v-model="editForm.notes"
                                rows="3"
                                class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-primary focus:border-primary"
                                placeholder="Keterangan transaksi"
                            ></textarea>
                        </div>

                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-3">
                            <p class="text-xs text-yellow-700 dark:text-yellow-400">
                                <span class="material-symbols-outlined text-sm align-middle mr-1">info</span>
                                Hanya field di atas yang dapat diubah. Kuantitas & Harga tidak dapat diubah untuk menjaga integritas HPP.
                            </p>
                        </div>
                        
                        <div class="flex justify-end gap-3 pt-2">
                            <button 
                                type="button"
                                @click="closeEditModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            >
                                Batal
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-primary hover:bg-primary-hover rounded-lg transition-colors"
                            >
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </Teleport>
</template>
