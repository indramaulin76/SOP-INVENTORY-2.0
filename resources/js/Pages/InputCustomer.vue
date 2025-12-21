<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Input Data Customer" />

    <SaeLayout>
        <template #header>Input Data Customer</template>

        <div class="max-w-6xl mx-auto">
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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Input Data</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-400 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-primary md:ml-2">Customer</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3">groups</span>
                        <h3 class="text-lg font-semibold">Daftar Data Customer</h3>
                    </div>
                </div>
                
                <div class="p-6 lg:p-8 space-y-6">
                    <form @submit.prevent>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="kode_customer">
                                    Kode Customer <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input class="block w-full px-3 py-2.5 border-gray-300 rounded-lg bg-gray-50 text-gray-500 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed" id="kode_customer" name="kode_customer" placeholder="CUS004" readonly="" type="text"/>
                                </div>
                                <p class="text-xs text-gray-400 italic mt-1">Kode customer otomatis dibuat</p>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_customer">
                                    Nama Customer <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="nama_customer" name="nama_customer" placeholder="Nama customer" type="text"/>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="alamat">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="alamat" name="alamat" placeholder="Alamat" type="text"/>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="telepon">
                                    Telepon <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="telepon" name="telepon" placeholder="Nomor telepon" type="tel"/>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">
                                    Email
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="email" name="email" placeholder="Email" type="email"/>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="tipe_customer">
                                    Tipe Customer
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="tipe_customer" name="tipe_customer">
                                        <option value="">Pilih Tipe</option>
                                        <option value="retail">Retail</option>
                                        <option value="reseller">Reseller</option>
                                        <option value="corporate">Corporate</option>
                                    </select>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="keterangan">
                                    Keterangan
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="keterangan" name="keterangan" placeholder="Keterangan" type="text"/>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 flex items-center space-x-3 mt-6">
                            <button class="inline-flex items-center px-6 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-gray-100 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors" type="button">
                                <span class="material-symbols-outlined text-lg mr-2">arrow_back</span>
                                Kembali
                            </button>
                            <button class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors" type="submit">
                                Simpan Customer
                                <span class="material-symbols-outlined text-lg ml-2">save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
