<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const assets = [
    {
        name: 'Premium Dark Chocolate Block',
        category: 'Bahan Baku',
        categoryClass: 'bg-brown-100 text-brown-800 dark:bg-orange-900/30 dark:text-orange-200',
        quantity: '150 kg',
        hpp: 'Rp 120.000',
        total: 'Rp 18.000.000'
    },
    {
        name: 'High Protein Flour (Sack)',
        category: 'Bahan Baku',
        categoryClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-200',
        quantity: '500 sacks',
        hpp: 'Rp 250.000',
        total: 'Rp 125.000.000'
    },
    {
        name: 'Signature Cheese Cake (Whole)',
        category: 'Produk Jadi',
        categoryClass: 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-200',
        quantity: '25 pcs',
        hpp: 'Rp 180.000',
        total: 'Rp 4.500.000'
    },
    {
        name: 'Almond Slice Premium',
        category: 'Bahan Baku',
        categoryClass: 'bg-brown-100 text-brown-800 dark:bg-orange-900/30 dark:text-orange-200',
        quantity: '20 kg',
        hpp: 'Rp 210.000',
        total: 'Rp 4.200.000'
    },
    {
        name: 'Paper Bag Branding (M)',
        category: 'Packaging',
        categoryClass: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        quantity: '2,000 pcs',
        hpp: 'Rp 1.500',
        total: 'Rp 3.000.000'
    },
    {
        name: 'Fresh Milk UHT',
        category: 'Bahan Baku',
        categoryClass: 'bg-primary/10 text-primary dark:bg-primary/20 dark:text-gray-200',
        quantity: '100 L',
        hpp: 'Rp 18.000',
        total: 'Rp 1.800.000'
    },
    {
        name: 'Butter Croissant',
        category: 'Produk Jadi',
        categoryClass: 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-200',
        quantity: '40 pcs',
        hpp: 'Rp 12.000',
        total: 'Rp 480.000'
    },
    {
        name: 'Vanilla Essence',
        category: 'Bahan Tambahan',
        categoryClass: 'bg-brown-100 text-brown-800 dark:bg-orange-900/30 dark:text-orange-200',
        quantity: '10 btl',
        hpp: 'Rp 45.000',
        total: 'Rp 450.000'
    }
];
</script>

<template>
    <Head title="Laporan Penilaian Aset Inventaris" />

    <SaeLayout>
        <template #header>Laporan Penilaian Aset Inventaris</template>

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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Laporan</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-primary md:ml-2">Laporan Penilaian Aset</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3 filled">account_balance_wallet</span>
                        <h3 class="text-lg font-semibold">Laporan Penilaian Aset Inventaris</h3>
                    </div>
                    <div class="flex gap-2">
                        <button class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Export PDF">
                            <span class="material-symbols-outlined text-xl">picture_as_pdf</span>
                        </button>
                        <button class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded transition-colors" title="Export Excel">
                            <span class="material-symbols-outlined text-xl">table_view</span>
                        </button>
                    </div>
                </div>

                <div class="p-6 lg:p-8 space-y-8">
                    <!-- Summary Cards -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-primary/5 dark:bg-primary/10 p-6 rounded-lg border border-primary/20">
                        <div>
                            <h4 class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider mb-1">Total Nilai Aset Inventaris</h4>
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl lg:text-4xl font-bold text-primary">Rp 150.000.000</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 font-medium">Updated: Just now</span>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex gap-3">
                            <div class="bg-white dark:bg-gray-800 p-3 rounded shadow-sm border border-gray-100 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Items</p>
                                <p class="text-lg font-semibold text-gray-800 dark:text-white">1,245</p>
                            </div>
                            <div class="bg-white dark:bg-gray-800 p-3 rounded shadow-sm border border-gray-100 dark:border-gray-700">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Categories</p>
                                <p class="text-lg font-semibold text-gray-800 dark:text-white">8</p>
                            </div>
                        </div>
                    </div>

                    <!-- Filter and Search -->
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-lg font-semibold text-gray-800 dark:text-white">Rincian Penilaian Aset</h4>
                        <div class="flex items-center space-x-2">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="material-symbols-outlined text-gray-400">search</span>
                                </div>
                                <input class="pl-10 pr-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-800 dark:text-gray-200 focus:ring-primary focus:border-primary" placeholder="Cari produk..." type="text"/>
                            </div>
                            <button class="flex items-center space-x-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span class="material-symbols-outlined text-lg">filter_list</span>
                                <span>Filter</span>
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs whitespace-nowrap" style="width: 25%;">Nama Produk</th>
                                    <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs whitespace-nowrap" style="width: 15%;">Kategori</th>
                                    <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs whitespace-nowrap" style="width: 15%;">Kuantitas Fisik</th>
                                    <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs whitespace-nowrap" style="width: 20%;">Harga Pokok (HPP)</th>
                                    <th scope="col" class="bg-primary text-white font-semibold text-left px-4 py-3 text-xs whitespace-nowrap" style="width: 25%;">Total Nilai (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="(asset, index) in assets" :key="index" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <td class="p-3 font-medium text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-700">{{ asset.name }}</td>
                                    <td class="p-3 border-b border-gray-200 dark:border-gray-700">
                                        <span :class="['px-2 py-1 rounded-full text-xs font-medium', asset.categoryClass]">{{ asset.category }}</span>
                                    </td>
                                    <td class="p-3 border-b border-gray-200 dark:border-gray-700">{{ asset.quantity }}</td>
                                    <td class="p-3 font-mono text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">{{ asset.hpp }}</td>
                                    <td class="p-3 font-mono font-bold text-gray-800 dark:text-white border-b border-gray-200 dark:border-gray-700">{{ asset.total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 sm:px-6 rounded-b-lg">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <a href="#" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                            <a href="#" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                        </div>
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-400">
                                    Showing
                                    <span class="font-medium">1</span>
                                    to
                                    <span class="font-medium">8</span>
                                    of
                                    <span class="font-medium">245</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                    <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-700 dark:hover:bg-gray-800">
                                        <span class="sr-only">Previous</span>
                                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                                    </a>
                                    <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-primary px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">1</a>
                                    <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">2</a>
                                    <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-700 dark:hover:bg-gray-800">3</a>
                                    <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 dark:ring-gray-700 dark:hover:bg-gray-800">
                                        <span class="sr-only">Next</span>
                                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                                    </a>
                                </nav>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </SaeLayout>
</template>
