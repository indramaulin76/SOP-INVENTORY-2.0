<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: {
        type: Array,
        default: () => []
    }
});

const editingSupplier = ref(null);

const form = useForm({
    nama_supplier: '',
    alamat: '',
    telepon: '',
    email: '',
    nama_pemilik: '',
    keterangan: '',
});

const submit = () => {
    if (editingSupplier.value) {
        form.put(route('suppliers.update', editingSupplier.value.id), {
            onSuccess: () => {
                cancelEdit();
            }
        });
    } else {
        form.post(route('suppliers.store'), {
            onSuccess: () => form.reset(),
        });
    }
};

const editSupplier = (supplier) => {
    editingSupplier.value = supplier;
    form.nama_supplier = supplier.nama_supplier;
    form.alamat = supplier.alamat;
    form.telepon = supplier.telepon;
    form.email = supplier.email;
    form.nama_pemilik = supplier.nama_pemilik;
    form.keterangan = supplier.keterangan;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelEdit = () => {
    editingSupplier.value = null;
    form.reset();
    form.clearErrors();
};

const deleteSupplier = (supplier) => {
    if (confirm(`Apakah Anda yakin ingin menghapus supplier "${supplier.nama_supplier}"?`)) {
        router.delete(route('suppliers.destroy', supplier.id));
    }
};
</script>

<template>
    <Head title="Master Data Supplier" />

    <SaeLayout>
        <template #header>Master Data Supplier</template>

        <div class="max-w-7xl mx-auto">
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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Master Data</span>
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

            <!-- Success Flash Message -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>

            <!-- Form Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                <div class="bg-primary/5 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            {{ editingSupplier ? 'Edit Data Supplier' : 'Input Supplier Baru' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ editingSupplier ? 'Update informasi supplier yang sudah ada' : 'Tambahkan partner supplier baru' }}
                        </p>
                    </div>
                    <div class="bg-primary/10 p-2 rounded-lg">
                        <span class="material-symbols-outlined text-primary">local_shipping</span>
                    </div>
                </div>

                <div class="p-6 lg:p-8 space-y-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Kode Supplier (Only for New) -->
                            <div class="space-y-1" v-if="!editingSupplier">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kode Supplier
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">qr_code</span>
                                    </div>
                                    <input class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg bg-gray-50 text-gray-500 text-sm cursor-not-allowed" placeholder="Otomatis (SUP-XXX)" readonly type="text"/>
                                </div>
                            </div>
                            <!-- Kode Supplier (Display for Edit) -->
                            <div class="space-y-1" v-else>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kode Supplier
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">qr_code</span>
                                    </div>
                                    <input :value="editingSupplier.kode_supplier" class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg bg-gray-100 text-gray-700 text-sm font-bold cursor-not-allowed" readonly type="text"/>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_supplier">
                                    Nama Supplier <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">store</span>
                                    </div>
                                    <input 
                                        v-model="form.nama_supplier"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="nama_supplier" 
                                        placeholder="Nama PT/CV/Toko" 
                                        type="text"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.nama_supplier" class="text-xs text-red-500 mt-1">{{ form.errors.nama_supplier }}</p>
                            </div>

                            <div class="space-y-1 md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="alamat">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">location_on</span>
                                    </div>
                                    <input 
                                        v-model="form.alamat"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="alamat" 
                                        placeholder="Alamat lengkap" 
                                        type="text"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.alamat" class="text-xs text-red-500 mt-1">{{ form.errors.alamat }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="telepon">
                                    No. Telepon <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">call</span>
                                    </div>
                                    <input 
                                        v-model="form.telepon"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="telepon" 
                                        placeholder="08xxxxxxxx" 
                                        type="tel"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.telepon" class="text-xs text-red-500 mt-1">{{ form.errors.telepon }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">
                                    Email
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">mail</span>
                                    </div>
                                    <input 
                                        v-model="form.email"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="email" 
                                        placeholder="email@supplier.com" 
                                        type="email"
                                    />
                                </div>
                                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_pemilik">
                                    Nama Pemilik/CP
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">person</span>
                                    </div>
                                    <input 
                                        v-model="form.nama_pemilik"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="nama_pemilik" 
                                        placeholder="Contact Person" 
                                        type="text"
                                    />
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
                                    <input 
                                        v-model="form.keterangan"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="keterangan" 
                                        placeholder="Catatan tambahan" 
                                        type="text"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end space-x-3 border-t border-gray-100 dark:border-gray-700 mt-6">
                            <button 
                                v-if="editingSupplier" 
                                @click="cancelEdit" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors" 
                                type="button"
                            >
                                <span class="material-symbols-outlined text-lg mr-2">close</span>
                                Batal
                            </button>
                            <button 
                                type="submit" 
                                :disabled="form.processing"
                                class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors disabled:opacity-50"
                            >
                                <span v-if="form.processing" class="material-symbols-outlined text-lg mr-2 animate-spin">refresh</span>
                                <span v-else class="material-symbols-outlined text-lg mr-2">save</span>
                                {{ editingSupplier ? 'Update Supplier' : 'Simpan Supplier' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-white px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Supplier</h3>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
                        <input type="text" placeholder="Cari supplier..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-primary focus:border-primary w-64">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="supplier in suppliers" :key="supplier.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary">
                                    {{ supplier.kode_supplier }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-semibold">
                                    {{ supplier.nama_supplier }}
                                    <div class="text-xs text-gray-500 font-normal">{{ supplier.nama_pemilik }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <div><span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">call</span> {{ supplier.telepon }}</div>
                                    <div v-if="supplier.email"><span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">mail</span> {{ supplier.email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                    {{ supplier.alamat }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="editSupplier(supplier)" class="text-blue-600 hover:text-blue-900 mr-3 p-1 rounded hover:bg-blue-50 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button @click="deleteSupplier(supplier)" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="suppliers.length === 0">
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">inbox</span>
                                    <p>Belum ada data supplier.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
