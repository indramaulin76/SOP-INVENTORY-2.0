<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    customers: {
        type: Array,
        default: () => []
    }
});

const editingCustomer = ref(null);

const form = useForm({
    nama_customer: '',
    alamat: '',
    telepon: '',
    email: '',
    tipe_customer: '',
    keterangan: '',
});

const submit = () => {
    if (editingCustomer.value) {
        form.put(route('customers.update', editingCustomer.value.id), {
            onSuccess: () => {
                cancelEdit();
            }
        });
    } else {
        form.post(route('customers.store'), {
            onSuccess: () => form.reset(),
        });
    }
};

const editCustomer = (customer) => {
    editingCustomer.value = customer;
    form.nama_customer = customer.nama_customer;
    form.alamat = customer.alamat;
    form.telepon = customer.telepon;
    form.email = customer.email;
    form.tipe_customer = customer.tipe_customer;
    form.keterangan = customer.keterangan;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelEdit = () => {
    editingCustomer.value = null;
    form.reset();
    form.clearErrors();
};

const deleteCustomer = (customer) => {
    if (confirm(`Apakah Anda yakin ingin menghapus customer "${customer.nama_customer}"?`)) {
        router.delete(route('customers.destroy', customer.id));
    }
};

const getTipeClass = (tipe) => {
    switch(tipe) {
        case 'corporate': return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300';
        case 'reseller': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300';
        default: return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'; // Retail
    }
};
</script>

<template>
    <Head title="Master Data Customer" />

    <SaeLayout>
        <template #header>Master Data Customer</template>

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
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Master Data</span>
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

            <!-- Success Flash Message -->
            <div v-if="$page.props.flash.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>

            <!-- Form Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-8">
                <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                    <div class="flex items-center">
                        <span class="material-symbols-outlined mr-3">groups</span>
                        <h3 class="text-lg font-semibold">
                            {{ editingCustomer ? 'Edit Data Customer' : 'Form Input Customer' }}
                        </h3>
                    </div>
                </div>
                
                <div class="p-6 lg:p-8 space-y-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <!-- Kode Customer -->
                            <div class="space-y-1" v-if="!editingCustomer">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kode Customer
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input class="block w-full px-3 py-2.5 border-gray-300 rounded-lg bg-gray-50 text-gray-500 text-sm cursor-not-allowed" placeholder="Otomatis" readonly type="text"/>
                                </div>
                            </div>
                            <div class="space-y-1" v-else>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Kode Customer
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input :value="editingCustomer.kode_customer" class="block w-full px-3 py-2.5 border-gray-300 rounded-lg bg-gray-100 text-gray-700 font-bold text-sm cursor-not-allowed" readonly type="text"/>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_customer">
                                    Nama Customer <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input 
                                        v-model="form.nama_customer"
                                        class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="nama_customer" 
                                        placeholder="Nama customer" 
                                        type="text"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.nama_customer" class="text-xs text-red-500 mt-1">{{ form.errors.nama_customer }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="alamat">
                                    Alamat <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input 
                                        v-model="form.alamat"
                                        class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="alamat" 
                                        placeholder="Alamat" 
                                        type="text"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.alamat" class="text-xs text-red-500 mt-1">{{ form.errors.alamat }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="telepon">
                                    Telepon <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input 
                                        v-model="form.telepon"
                                        class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="telepon" 
                                        placeholder="Nomor telepon" 
                                        type="tel"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.telepon" class="text-xs text-red-500 mt-1">{{ form.errors.telepon }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="email">
                                    Email
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input 
                                        v-model="form.email"
                                        class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="email" 
                                        placeholder="Email" 
                                        type="email"
                                    />
                                </div>
                                <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">{{ form.errors.email }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="tipe_customer">
                                    Tipe Customer
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <select 
                                        v-model="form.tipe_customer"
                                        class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="tipe_customer"
                                    >
                                        <option value="">Pilih Tipe</option>
                                        <option value="retail">Retail</option>
                                        <option value="reseller">Reseller</option>
                                        <option value="corporate">Corporate</option>
                                    </select>
                                </div>
                                <p v-if="form.errors.tipe_customer" class="text-xs text-red-500 mt-1">{{ form.errors.tipe_customer }}</p>
                            </div>
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="keterangan">
                                    Keterangan
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <input 
                                        v-model="form.keterangan"
                                        class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="keterangan" 
                                        placeholder="Keterangan" 
                                        type="text"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 flex items-center justify-end space-x-3 mt-6">
                            <button 
                                v-if="editingCustomer"
                                class="inline-flex items-center px-6 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700 transition-colors" 
                                type="button" 
                                @click="cancelEdit"
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
                                {{ form.processing ? 'Menyimpan...' : (editingCustomer ? 'Update Customer' : 'Simpan Customer') }}
                                <span v-if="!form.processing" class="material-symbols-outlined text-lg ml-2">save</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- List Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-white px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Daftar Customer</h3>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-gray-400 text-sm">search</span>
                        <input type="text" placeholder="Cari customer..." class="pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:ring-primary focus:border-primary w-64">
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="customer in customers" :key="customer.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
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
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="editCustomer(customer)" class="text-blue-600 hover:text-blue-900 mr-3 p-1 rounded hover:bg-blue-50 transition-colors" title="Edit">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>
                                    <button @click="deleteCustomer(customer)" class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors" title="Hapus">
                                        <span class="material-symbols-outlined text-[20px]">delete</span>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="customers.length === 0">
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">groups</span>
                                    <p>Belum ada data customer.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
