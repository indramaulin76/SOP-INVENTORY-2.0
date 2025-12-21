<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    entries: {
        type: Object,
        default: () => ({ data: [], links: [] })
    }
});

const deleteEntry = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        router.delete(route('barang-masuk.barang-dalam-proses.destroy', id));
    }
};

const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

</script>

<template>
    <Head title="Riwayat Barang Dalam Proses" />

    <SaeLayout>
        <template #header>Riwayat Input Barang Dalam Proses</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Riwayat WIP</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Riwayat Input WIP</h1>
                <Link :href="route('barang-masuk.barang-dalam-proses')" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded-lg shadow transition-colors flex items-center">
                    <span class="material-symbols-outlined mr-2">add</span>
                    Input Baru
                </Link>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">No. Faktur</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Items</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Keterangan</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            <tr v-if="entries.data.length === 0">
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    Belum ada data riwayat input.
                                </td>
                            </tr>
                            <tr v-for="entry in entries.data" :key="entry.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ formatDate(entry.tanggal) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                    {{ entry.nomor_faktur }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                    {{ entry.supplier ? entry.supplier.nama_supplier : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    <ul class="list-disc list-inside">
                                        <li v-for="item in entry.items" :key="item.id">
                                            {{ item.product.nama_barang }} ({{ item.quantity }} {{ item.product.unit?.nama_satuan || 'pcs' }})
                                        </li>
                                    </ul>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300">
                                    {{ entry.keterangan || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <button @click="deleteEntry(entry.id)" class="text-red-600 hover:text-red-900 dark:hover:text-red-400 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined">delete</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="entries.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap gap-1 justify-center">
                        <Link v-for="(link, k) in entries.links" :key="k" 
                            :href="link.url || '#'" 
                            v-html="link.label"
                            class="px-3 py-1 text-sm border rounded transition-colors"
                            :class="{ 
                                'bg-primary text-white border-primary': link.active, 
                                'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 border-gray-300 dark:border-gray-600': !link.active,
                                'opacity-50 cursor-not-allowed': !link.url 
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
