<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    suppliers: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');

const filteredSuppliers = computed(() => {
    if (!searchQuery.value) return props.suppliers;
    const query = searchQuery.value.toLowerCase();
    return props.suppliers.filter(s => 
        s.nama_supplier?.toLowerCase().includes(query) ||
        s.kode_supplier?.toLowerCase().includes(query) ||
        s.telepon?.toLowerCase().includes(query) ||
        s.alamat?.toLowerCase().includes(query) ||
        s.nama_pemilik?.toLowerCase().includes(query)
    );
});
</script>

<template>
    <Head title="Laporan Data Supplier" />

    <SaeLayout>
        <template #header>Laporan Data Supplier</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Data Supplier</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Data Table Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3">local_shipping</span>
                        <h3 class="text-lg font-semibold">Daftar Supplier</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm opacity-80">{{ filteredSuppliers.length }} supplier</span>
                        <Link href="/input-data/supplier" class="bg-white/20 hover:bg-white/30 text-white px-3 py-1.5 rounded-lg text-sm flex items-center transition-colors">
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
                            placeholder="Cari supplier (nama, kode, telepon, alamat, pemilik)..." 
                            class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-primary focus:border-primary w-full dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                        >
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Nama Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Pemilik</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Alamat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="supplier in filteredSuppliers" :key="supplier.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                    {{ supplier.kode_supplier }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">
                                    {{ supplier.nama_supplier }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">
                                    {{ supplier.nama_pemilik || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div><span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">call</span> {{ supplier.telepon }}</div>
                                    <div v-if="supplier.email"><span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">mail</span> {{ supplier.email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ supplier.alamat }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ supplier.keterangan || '-' }}
                                </td>
                            </tr>
                            <tr v-if="filteredSuppliers.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">local_shipping</span>
                                    <p v-if="searchQuery">Tidak ada supplier yang cocok dengan pencarian.</p>
                                    <p v-else>Belum ada data supplier.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
