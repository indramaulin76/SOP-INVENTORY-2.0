<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    users: {
        type: Object,
        default: () => ({ data: [] })
    },
    roles: {
        type: Array,
        default: () => ['Pimpinan', 'Admin', 'Karyawan']
    }
});

const isModalOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    role: 'Karyawan',
    password: '',
    password_confirmation: '',
    active: true
});

const openModal = (user = null) => {
    editingUser.value = user;
    if (user) {
        form.name = user.name;
        form.email = user.email;
        form.role = user.role;
        form.active = user.active;
        form.password = '';
        form.password_confirmation = '';
    } else {
        form.reset();
    }
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingUser.value = null;
    form.reset();
    form.clearErrors();
};

const saveUser = () => {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => closeModal(),
            preserveScroll: true,
        });
    }
};

const deleteUser = (user) => {
    if (confirm(`Hapus user "${user.name}"?`)) {
        router.delete(route('users.destroy', user.id), {
            preserveScroll: true,
        });
    }
};

const toggleActive = (user) => {
    router.post(route('users.toggle-active', user.id), {}, {
        preserveScroll: true,
    });
};

const getRoleClass = (role) => {
    switch(role) {
        case 'Pimpinan':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300';
        case 'Admin':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300';
        default:
            return 'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-300';
    }
};

const getInitials = (name) => {
    if (!name) return '??';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};
</script>


<template>
    <Head title="Manajemen User" />

    <SaeLayout>
        <template #header>Manajemen User</template>

        <div class="max-w-[1400px] mx-auto space-y-8">
            <!-- Breadcrumb: Clean & Subtle -->
            <nav aria-label="Breadcrumb" class="flex">
                <ol class="inline-flex items-center space-x-1 md:space-x-2">
                    <li class="inline-flex items-center">
                        <Link href="/" class="inline-flex items-center text-sm font-medium text-gray-400 hover:text-[#C59D5F] transition-colors">
                            <span class="material-symbols-outlined text-lg mr-2">home</span>
                            Dashboard
                        </Link>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-gray-300 text-lg mx-1">chevron_right</span>
                            <span class="text-sm font-medium text-[#c59d5f]">Manajemen User</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Header Card: Premium & Spacious -->
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 flex flex-col md:flex-row justify-between items-center relative overflow-hidden group">
                <!-- Gold Accent Line -->
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#C59D5F]"></div>
                
                <div class="flex flex-col gap-2 z-10 w-full md:w-auto">
                    <h2 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Manajemen User</h2>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">Manage access and permissions for your team</p>
                </div>
                
                <div class="flex items-center gap-4 mt-6 md:mt-0 z-10 w-full md:w-auto">
                    <button @click="openModal()" class="w-full md:w-auto flex items-center justify-center gap-2 bg-[#C59D5F] hover:bg-[#b08d55] text-white px-6 py-3 rounded-lg text-sm font-semibold shadow-md hover:shadow-lg transition-all transform active:scale-95 duration-200">
                        <span class="material-symbols-outlined text-[20px]">add</span>
                        <span>Add New User</span>
                    </button>
                </div>
            </div>

            <!-- Table Card: Ultra-Clean -->
            <div class="bg-white dark:bg-surface-dark rounded-xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <!-- Toolbar -->
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="relative w-full sm:w-72 group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-gray-400 group-focus-within:text-[#C59D5F] transition-colors">search</span>
                        </div>
                        <input 
                            type="text" 
                            class="pl-10 pr-4 py-2.5 w-full border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-[#C59D5F]/20 focus:border-[#C59D5F] transition-all" 
                            placeholder="Search users..." 
                        />
                    </div>
                    <select class="w-full sm:w-auto py-2.5 pl-4 pr-10 border border-gray-200 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-[#C59D5F]/20 focus:border-[#C59D5F] cursor-pointer">
                        <option>All Roles</option>
                        <option>Pimpinan</option>
                        <option>Admin</option>
                        <option>Karyawan</option>
                    </select>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-800/50">
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User Profile</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Role</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Last Login</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-amber-50/30 dark:hover:bg-gray-800/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-11 w-11 flex-shrink-0 rounded-full flex items-center justify-center font-bold text-sm shadow-sm ring-2 ring-white bg-stone-200 text-stone-600 dark:bg-stone-700 dark:text-stone-300">
                                            {{ getInitials(user.name) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white">{{ user.name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="['inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold shadow-sm', getRoleClass(user.role)]">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <label class="relative inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" :checked="user.active" @change="toggleActive(user)" class="sr-only peer">
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-[#C59D5F]/30 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#C59D5F]"></div>
                                        <span class="ml-3 text-sm font-medium text-gray-600 dark:text-gray-400 group-hover:text-gray-900 transition-colors">
                                            {{ user.active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </label>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ user.last_login || 'Never' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openModal(user)" class="p-2 text-gray-400 hover:text-[#C59D5F] hover:bg-amber-50 rounded-full transition-all">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </button>
                                        <button @click="deleteUser(user)" class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-full transition-all">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!users.data || users.data.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 block opacity-50">people</span>
                                    Belum ada user
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Simpler Pagination -->
                <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Showing <span class="font-bold text-gray-900 dark:text-white">1-5</span> of 12</span>
                    <div class="flex gap-2">
                        <button class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-white hover:text-[#C59D5F] hover:border-[#C59D5F] transition-all disabled:opacity-50">Previous</button>
                        <button class="px-3 py-1.5 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-white hover:text-[#C59D5F] hover:border-[#C59D5F] transition-all">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add User Modal -->
        <div v-show="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-30 transition-opacity backdrop-filter backdrop-blur-sm" @click="closeModal" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                Add New User
                            </h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <span class="material-symbols-outlined">close</span>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <form @submit.prevent="saveUser">
                            <!-- Global Error Display -->
                            <div v-if="Object.keys(form.errors).length > 0" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-red-600 text-sm font-medium">Gagal menyimpan:</p>
                                <ul class="text-red-500 text-sm mt-1 list-disc list-inside">
                                    <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                                </ul>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <input type="text" v-model="form.name" id="name" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-[#C59D5F] focus:border-[#C59D5F] sm:text-sm py-2.5 px-3 dark:bg-gray-700 dark:text-white" placeholder="e.g. Indra Wijaya" required>
                                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address <span class="text-red-500">*</span></label>
                                    <input type="email" v-model="form.email" id="email" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-[#C59D5F] focus:border-[#C59D5F] sm:text-sm py-2.5 px-3 dark:bg-gray-700 dark:text-white" placeholder="name@company.com" required>
                                    <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                                </div>
                                
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role & Access Level <span class="text-red-500">*</span></label>
                                    <select v-model="form.role" id="role" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-[#C59D5F] focus:border-[#C59D5F] sm:text-sm py-2.5 px-3 dark:bg-gray-700 dark:text-white">
                                        <option value="Karyawan">Karyawan (Staff)</option>
                                        <option value="Admin">Admin</option>
                                        <option value="Pimpinan">Pimpinan</option>
                                    </select>
                                    <p v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password <span class="text-red-500">*</span></label>
                                        <input type="password" v-model="form.password" id="password" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-[#C59D5F] focus:border-[#C59D5F] sm:text-sm py-2.5 px-3 dark:bg-gray-700 dark:text-white" placeholder="••••••••" required>
                                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                                    </div>
                                    <div>
                                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Confirm Password <span class="text-red-500">*</span></label>
                                        <input type="password" v-model="form.password_confirmation" id="password_confirmation" class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-[#C59D5F] focus:border-[#C59D5F] sm:text-sm py-2.5 px-3 dark:bg-gray-700 dark:text-white" placeholder="••••••••" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-8 flex justify-end gap-3">
                                <button type="button" @click="closeModal" :disabled="form.processing" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C59D5F] disabled:opacity-50">
                                    Cancel
                                </button>
                                <button type="submit" :disabled="form.processing" class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-[#C59D5F] border border-transparent rounded-md shadow-sm hover:bg-[#b08d55] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C59D5F] disabled:opacity-50">
                                    <span v-if="form.processing" class="material-symbols-outlined animate-spin mr-2 text-[18px]">refresh</span>
                                    {{ form.processing ? 'Saving...' : (editingUser ? 'Update User' : 'Save User') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
