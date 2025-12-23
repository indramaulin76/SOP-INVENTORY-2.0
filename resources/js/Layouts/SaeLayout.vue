<script setup>
import { ref, watch, onMounted, computed, nextTick } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const isSidebarOpen = ref(false);
const isInputDataOpen = ref(false); // Default CLOSED
const isInputBarangMasukOpen = ref(false);
const isInputBarangKeluarOpen = ref(false);
const isLaporanOpen = ref(false);

// Inventory Method Switch (FIFO, LIFO, AVERAGE)
const inventoryMethod = ref('FIFO');
const inventoryMethods = ['FIFO', 'LIFO', 'AVERAGE'];
const savingMethod = ref(false);
const showToast = ref(false);
const toastMessage = ref('');
const isInitialized = ref(false);

const page = usePage();

// Role-based permissions from backend
const permissions = computed(() => page.props.auth?.permissions || {});
const currentUser = computed(() => page.props.auth?.user || {});

// Load inventory method from props (passed from backend)
onMounted(() => {
    if (page.props.inventoryMethod) {
        inventoryMethod.value = page.props.inventoryMethod;
    }
    // Mark as initialized after next tick to allow initial value to settle
    nextTick(() => {
        isInitialized.value = true;
    });
});

// Watch for inventory method change and save to DB
watch(inventoryMethod, async (newMethod, oldMethod) => {
    // Skip if not initialized yet (initial load from backend)
    if (!isInitialized.value) {
        return;
    }
    
    // Skip if same value (shouldn't happen but safety check)
    if (newMethod === oldMethod) {
        return;
    }
    
    savingMethod.value = true;
    try {
        // Use Inertia router for proper CSRF handling
        router.post('/api/settings/inventory-method', { method: newMethod }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toastMessage.value = `Metode Akuntansi berubah ke ${newMethod}`;
                showToast.value = true;
                setTimeout(() => {
                    showToast.value = false;
                }, 3000);
            },
            onError: () => {
                toastMessage.value = 'Gagal menyimpan metode akuntansi';
                showToast.value = true;
                setTimeout(() => {
                    showToast.value = false;
                }, 3000);
            },
            onFinish: () => {
                savingMethod.value = false;
            }
        });
    } catch (e) {
        console.error('Failed to save inventory method:', e);
        toastMessage.value = 'Gagal menyimpan metode akuntansi';
        showToast.value = true;
        setTimeout(() => {
            showToast.value = false;
        }, 3000);
        savingMethod.value = false;
    }
});

const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
};

// Close all other menus when opening one (mutual exclusive)
const closeOtherMenus = (exceptMenu) => {
    if (exceptMenu !== 'inputData') isInputDataOpen.value = false;
    if (exceptMenu !== 'barangMasuk') isInputBarangMasukOpen.value = false;
    if (exceptMenu !== 'barangKeluar') isInputBarangKeluarOpen.value = false;
    if (exceptMenu !== 'laporan') isLaporanOpen.value = false;
};

const toggleInputData = () => {
    const willOpen = !isInputDataOpen.value;
    if (willOpen) closeOtherMenus('inputData');
    isInputDataOpen.value = willOpen;
};

const toggleInputBarangMasuk = () => {
    const willOpen = !isInputBarangMasukOpen.value;
    if (willOpen) closeOtherMenus('barangMasuk');
    isInputBarangMasukOpen.value = willOpen;
};

const toggleInputBarangKeluar = () => {
    const willOpen = !isInputBarangKeluarOpen.value;
    if (willOpen) closeOtherMenus('barangKeluar');
    isInputBarangKeluarOpen.value = willOpen;
};

const toggleLaporan = () => {
    const willOpen = !isLaporanOpen.value;
    if (willOpen) closeOtherMenus('laporan');
    isLaporanOpen.value = willOpen;
};

const toggleDarkMode = () => {
    document.documentElement.classList.toggle('dark');
};
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-background-light dark:bg-background-dark text-gray-800 dark:text-gray-200 transition-colors duration-300">
        <!-- Sidebar -->
        <aside :class="[
            'w-64 flex-shrink-0 bg-surface-light dark:bg-surface-dark border-r border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-300 fixed md:static inset-y-0 z-30',
            isSidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
        ]">
            <div class="h-16 flex items-center px-6 border-b border-gray-100 dark:border-gray-700">
                <img src="/images/logo-toko.jpg" alt="Sae Bakery" class="w-10 h-10 rounded-full object-cover mr-3 shadow-sm border border-gray-200 dark:border-gray-600">
                <h1 class="text-xl font-bold tracking-tight text-gray-800 dark:text-white">Sae Bakery</h1>
                
                <!-- Mobile Close Button -->
                <button @click="toggleSidebar" class="md:hidden ml-auto text-gray-500 hover:text-gray-700">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto sidebar-scroll py-4 px-3 space-y-1">
                <Link href="/" :class="['group flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors', $page.url === '/' ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white']">
                    <span :class="['material-symbols-outlined mr-3 text-[20px] transition-colors', $page.url === '/' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">dashboard</span>
                    Dashboard
                </Link>
                
                <div class="space-y-1">
                    <button @click="toggleInputData" :class="['w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md transition-colors', isInputDataOpen || $page.url.startsWith('/input-data') ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700']">
                        <div class="flex items-center">
                            <span :class="['material-symbols-outlined mr-3 text-[20px] transition-colors', isInputDataOpen || $page.url.startsWith('/input-data') ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">add_circle</span>
                            Input Data
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': isInputDataOpen }">expand_less</span>
                    </button>
                    
                    <div v-show="isInputDataOpen" class="pl-11 pr-2 py-1 space-y-1 transition-all duration-200">
                        <Link href="/input-data/barang" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-data/barang' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-data/barang' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Input Data Barang
                        </Link>
                        <Link href="/input-data/supplier" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-data/supplier' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-data/supplier' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Input Data Supplier
                        </Link>
                        <Link href="/input-data/customer" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-data/customer' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-data/customer' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Input Data Customer
                        </Link>
                        <Link href="/input-data/departemen" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-data/departemen' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-data/departemen' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Input Data Departemen
                        </Link>
                        <Link href="/input-data/saldo-awal" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-data/saldo-awal' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-data/saldo-awal' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Input Saldo Awal
                        </Link>
                    </div>
                </div>

                <div class="space-y-1">
                    <button @click="toggleInputBarangMasuk" class="w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white transition-colors">
                        <div class="flex items-center">
                            <span :class="['material-symbols-outlined mr-3 text-[20px] transition-colors', isInputBarangMasukOpen || $page.url.startsWith('/input-barang-masuk') ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">inventory_2</span>
                            Input Barang Masuk
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': isInputBarangMasukOpen }">expand_less</span>
                    </button>
                    
                    <div v-show="isInputBarangMasukOpen" class="pl-11 pr-2 py-1 space-y-1 transition-all duration-200">
                        <Link href="/input-barang-masuk/pembelian-bahan-baku" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-barang-masuk/pembelian-bahan-baku' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-barang-masuk/pembelian-bahan-baku' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Pembelian Bahan Baku
                        </Link>
                        <Link href="/input-barang-masuk/barang-dalam-proses" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-barang-masuk/barang-dalam-proses' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-barang-masuk/barang-dalam-proses' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Barang Dalam Proses
                        </Link>
                        <Link href="/input-barang-masuk/barang-jadi" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-barang-masuk/barang-jadi' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-barang-masuk/barang-jadi' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Barang Jadi
                        </Link>
                    </div>
                </div>

                <div class="space-y-1">
                    <button @click="toggleInputBarangKeluar" :class="['w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md transition-colors', isInputBarangKeluarOpen || $page.url.startsWith('/input-barang-keluar') ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700']">
                        <div class="flex items-center">
                            <span :class="['material-symbols-outlined mr-3 text-[20px] transition-colors', isInputBarangKeluarOpen || $page.url.startsWith('/input-barang-keluar') ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">outbox</span>
                            Input Barang Keluar
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': isInputBarangKeluarOpen }">expand_less</span>
                    </button>
                    
                    <div v-show="isInputBarangKeluarOpen" class="pl-11 pr-2 py-1 space-y-1 transition-all duration-200">
                        <Link href="/input-barang-keluar/penjualan-barang-jadi" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-barang-keluar/penjualan-barang-jadi' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-barang-keluar/penjualan-barang-jadi' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Penjualan Barang Jadi
                        </Link>
                        <Link href="/input-barang-keluar/pemakaian-bahan-baku" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-barang-keluar/pemakaian-bahan-baku' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-barang-keluar/pemakaian-bahan-baku' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Pemakaian Bahan Baku
                        </Link>
                        <Link href="/input-barang-keluar/pemakaian-barang-dalam-proses" :class="['group flex items-center px-2 py-2 text-sm font-medium rounded-md transition-colors', $page.url === '/input-barang-keluar/pemakaian-barang-dalam-proses' ? 'text-primary bg-white shadow-sm border border-gray-100 dark:bg-gray-800 dark:border-gray-600 dark:text-primary' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white']">
                            <span :class="['w-1.5 h-1.5 rounded-full mr-2 transition-colors', $page.url === '/input-barang-keluar/pemakaian-barang-dalam-proses' ? 'bg-primary' : 'bg-gray-300 group-hover:bg-primary']"></span>
                            Pemakaian Barang Dalam Proses
                        </Link>
                    </div>
                </div>

                <div class="space-y-1">
                    <button @click="toggleLaporan" :class="['w-full group flex items-center justify-between px-3 py-2.5 text-sm font-medium rounded-md transition-colors', isLaporanOpen || $page.url.startsWith('/laporan') ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700']">
                        <div class="flex items-center">
                            <span :class="['material-symbols-outlined mr-3 text-[20px] transition-colors', isLaporanOpen || $page.url.startsWith('/laporan') ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">assessment</span>
                            Laporan
                        </div>
                        <span class="material-symbols-outlined text-sm transition-transform duration-200" :class="{ 'rotate-180': isLaporanOpen }">expand_less</span>
                    </button>
                    
                    <div v-show="isLaporanOpen" class="pl-4 pr-2 py-2 space-y-1 transition-all duration-200">
                        <Link href="/laporan/riwayat-stok" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/riwayat-stok' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/riwayat-stok' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">history</span>
                            Riwayat Stok
                        </Link>
                        <!-- Only show profit report for non-karyawan -->
                        <Link v-if="permissions.canViewProfitReports" href="/laporan/penjualan-laba" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/penjualan-laba' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/penjualan-laba' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">trending_up</span>
                            Penjualan & Laba
                        </Link>
                        <Link href="/laporan/kartu-stok" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/kartu-stok' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/kartu-stok' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">description</span>
                            Kartu Stok
                        </Link>
                        <Link href="/laporan/data-barang" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/data-barang' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/data-barang' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">inventory_2</span>
                            Laporan Data Barang
                        </Link>
                        <Link href="/laporan/status-barang" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/status-barang' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/status-barang' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">bar_chart</span>
                            Status Barang
                        </Link>
                        <Link href="/laporan/stock-opname" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/stock-opname' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/stock-opname' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">fact_check</span>
                            Stock Opname
                        </Link>
                        <Link href="/laporan/data-customer" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/data-customer' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/data-customer' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">group</span>
                            Data Customer
                        </Link>
                        <Link href="/laporan/data-supplier" :class="['group flex items-center px-3 py-2 text-xs font-medium rounded-md transition-colors ml-3 border-l-2 pl-4', $page.url === '/laporan/data-supplier' ? 'text-primary bg-primary/5 dark:bg-primary/10 border-primary' : 'text-gray-500 hover:text-primary hover:bg-gray-50 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800 border-transparent hover:border-primary']">
                            <span :class="['material-symbols-outlined mr-3 text-[18px]', $page.url === '/laporan/data-supplier' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">local_shipping</span>
                            Data Supplier
                        </Link>
                    </div>
                </div>

                <!-- User Management - Only for admin/pimpinan -->
                <div v-if="permissions.canManageUsers" class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-700">
                    <Link href="/manajemen-user" :class="['group flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors', $page.url === '/manajemen-user' ? 'bg-primary/10 text-primary dark:bg-primary/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white']">
                        <span :class="['material-symbols-outlined mr-3 transition-colors text-[20px]', $page.url === '/manajemen-user' ? 'text-primary' : 'text-gray-400 group-hover:text-primary']">people</span>
                        Manajemen User
                    </Link>
                </div>
            </nav>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-bold text-gray-500 dark:text-gray-300">
                        {{ currentUser.name?.charAt(0)?.toUpperCase() || 'U' }}
                    </div>
                    <div class="ml-3">
                        <p class="text-xs font-medium text-gray-700 dark:text-gray-200">{{ currentUser.name || 'User' }}</p>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400">{{ currentUser.role || 'User' }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-hidden relative">
            <!-- Header -->
            <header class="h-16 bg-primary shadow-sm flex items-center justify-between px-6 z-10 shrink-0">
                <button @click="toggleSidebar" class="md:hidden text-white mr-4">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                
                <div class="flex items-center text-white">
                    <h2 class="text-lg font-semibold tracking-wide">
                        <slot name="header">Dashboard</slot>
                    </h2>
                </div>

                <div class="flex items-center space-x-4">
                    <!-- Inventory Method Switch - Only pimpinan can change -->
                    <div class="hidden sm:flex items-center">
                        <span class="material-symbols-outlined text-white/80 text-sm mr-2">inventory</span>
                        <select 
                            v-model="inventoryMethod"
                            :disabled="!permissions.canChangeInventoryMethod"
                            :class="[
                                'text-white text-xs font-medium px-2 py-1.5 rounded-md transition-colors border-0 focus:ring-2 focus:ring-white/50',
                                permissions.canChangeInventoryMethod 
                                    ? 'bg-white/20 hover:bg-white/30 cursor-pointer' 
                                    : 'bg-white/10 cursor-not-allowed opacity-75'
                            ]"
                            :title="permissions.canChangeInventoryMethod ? 'Ubah metode inventory' : 'Hanya Pimpinan yang bisa mengubah metode'"
                        >
                            <option v-for="method in inventoryMethods" :key="method" :value="method" class="text-gray-800">
                                {{ method }}
                            </option>
                        </select>
                    </div>

                    <div class="h-6 w-px bg-white/30 mx-2 hidden sm:block"></div>

                    <div class="hidden sm:flex items-center text-white/90 text-xs">
                        <span class="material-symbols-outlined text-sm mr-1">calendar_today</span>
                        <span>{{ new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}</span>
                    </div>

                    <div class="h-6 w-px bg-white/30 mx-2 hidden sm:block"></div>

                    <Link href="/logout" method="post" as="button" class="bg-white/20 hover:bg-white/30 text-white text-xs font-medium px-3 py-1.5 rounded-md transition-colors flex items-center">
                        <span class="material-symbols-outlined text-sm mr-1">logout</span>
                        Logout
                    </Link>

                    <button class="ml-2 text-white/80 hover:text-white focus:outline-none" @click="toggleDarkMode">
                        <span class="material-symbols-outlined">dark_mode</span>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 lg:p-8 bg-background-light dark:bg-background-dark">
                <slot />
            </main>
        </div>

        <!-- Overlay for mobile sidebar -->
        <div v-if="isSidebarOpen" @click="toggleSidebar" class="fixed inset-0 bg-black/50 z-20 md:hidden"></div>

        <!-- Toast Notification -->
        <Transition name="slide-up">
            <div 
                v-if="showToast" 
                class="fixed bottom-6 right-6 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center"
            >
                <span class="material-symbols-outlined mr-2">check_circle</span>
                <span class="text-sm font-medium">{{ toastMessage }}</span>
                <button @click="showToast = false" class="ml-4 hover:bg-green-700 rounded p-1">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>
        </Transition>
    </div>
</template>

