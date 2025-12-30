<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    customers: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');

const filteredCustomers = computed(() => {
    if (!searchQuery.value) return props.customers;
    const query = searchQuery.value.toLowerCase();
    return props.customers.filter(c => 
        c.nama_customer?.toLowerCase().includes(query) ||
        c.kode_customer?.toLowerCase().includes(query) ||
        c.telepon?.toLowerCase().includes(query) ||
        c.alamat?.toLowerCase().includes(query)
    );
});

const getTipeClass = (tipe) => {
    switch(tipe) {
        case 'corporate': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
        case 'reseller': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        default: return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
    }
};
</script>

<template>
    <Head title="Laporan Data Customer" />

    <SaeLayout>
        <template #header>Laporan Data Customer</template>

        <div class="max-w-7xl mx-auto">
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
                            <span class="text-sm font-medium text-primary md:ml-2">Data Customer</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Data Table Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3">group</span>
                        <h3 class="text-lg font-semibold">Daftar Customer</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm opacity-80">{{ filteredCustomers.length }} customer</span>
                        <Link href="/input-data/customer" class="bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg text-sm flex items-center transition-colors">
                            <span class="material-symbols-outlined text-sm mr-1">add</span>
                            Tambah
                        </Link>
                    </div>
                </div>
                
                <!-- Search Bar -->
                <div class="bg-white px-6 py-4 border-b border-gray-100 dark:border-gray-700 dark:bg-gray-900">
                    <div class="relative max-w-md">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari customer (nama, kode, telepon, alamat)..." 
                            class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-primary focus:border-primary w-full dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                        >
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Nama Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Alamat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="customer in filteredCustomers" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                    {{ customer.kode_customer }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">
                                    {{ customer.nama_customer }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getTipeClass(customer.tipe_customer)">
                                        {{ customer.tipe_customer || 'Retail' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div><span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">call</span> {{ customer.telepon }}</div>
                                    <div v-if="customer.email"><span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">mail</span> {{ customer.email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ customer.alamat }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ customer.keterangan || '-' }}
                                </td>
                            </tr>
                            <tr v-if="filteredCustomers.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">groups</span>
                                    <p v-if="searchQuery">Tidak ada customer yang cocok dengan pencarian.</p>
                                    <p v-else>Belum ada data customer.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
