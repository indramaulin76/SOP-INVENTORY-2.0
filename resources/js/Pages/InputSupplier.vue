<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Input Data Supplier" />

    <SaeLayout>
        <template #header>Input Data Supplier</template>

        <div class="max-w-5xl mx-auto">
            <!-- Breadcrumb -->
            <nav aria-label="Breadcrumb" class="flex mb-5">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <Link class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-primary dark:text-gray-400 dark:hover:text-white" href="/">
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
                            <span class="text-sm font-medium text-primary md:ml-2">Supplier</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary/5 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Form Input Supplier</h3>
                        <p class="text-xs text-gray-500 mt-1">Isi data supplier baru dengan lengkap</p>
                    </div>
                    <div class="bg-primary/10 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary">local_shipping</span>
                    </div>
                </div>

                <div class="p-6 lg:p-8 space-y-6">
                    <form @submit.prevent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="kode_supplier">
                                    Kode Supplier <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">qr_code</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg bg-gray-50 text-gray-500 text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 cursor-not-allowed" id="kode_supplier" name="kode_supplier" placeholder="SUP-003" readonly="" type="text"/>
                                </div>
                                <p class="text-xs text-gray-400 italic mt-1">Kode supplier akan otomatis dibuat oleh sistem</p>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_supplier">
                                    Nama Supplier <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">store</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="nama_supplier" name="nama_supplier" placeholder="Masukkan nama supplier" type="text"/>
                                </div>
                            </div>

                            <div class="space-y-1 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="alamat">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">location_on</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="alamat" name="alamat" placeholder="Masukkan alamat lengkap supplier" type="text"/>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="telepon">
                                    No. Telepon <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">call</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="telepon" name="telepon" placeholder="Nomor telepon" type="tel"/>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">
                                    Email
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">mail</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="email" name="email" placeholder="email@supplier.com" type="email"/>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_pemilik">
                                    Nama Pemilik
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">person</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="nama_pemilik" name="nama_pemilik" placeholder="Nama pemilik" type="text"/>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="keterangan">
                                    Keterangan
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">description</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" id="keterangan" name="keterangan" placeholder="Keterangan tambahan" type="text"/>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end space-x-3 border-t border-gray-100 dark:border-gray-700 mt-6">
                            <button class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors" type="button">
                                <span class="material-symbols-outlined text-lg mr-2">arrow_back</span>
                                Kembali
                            </button>
                            <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors" type="submit">
                                <span class="material-symbols-outlined text-lg mr-2">save</span>
                                Simpan Supplier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
