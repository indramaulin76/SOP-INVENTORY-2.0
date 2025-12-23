<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    departments: {
        type: Array,
        default: () => []
    },
    newCode: String,
    flash: Object
});

// Form for creating new department
const form = useForm({
    kode_departemen: props.newCode || '',
    nama_departemen: '',
    keterangan: '',
});

// For editing
const editingId = ref(null);
const editForm = useForm({
    kode_departemen: '',
    nama_departemen: '',
    keterangan: '',
});

const submit = () => {
    form.post(route('departments.store'), {
        onSuccess: () => {
            form.reset();
            form.kode_departemen = props.newCode;
        },
    });
};

const startEdit = (department) => {
    editingId.value = department.id;
    editForm.kode_departemen = department.kode_departemen;
    editForm.nama_departemen = department.nama_departemen;
    editForm.keterangan = department.keterangan || '';
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const saveEdit = (id) => {
    editForm.put(route('departments.update', id), {
        onSuccess: () => {
            editingId.value = null;
            editForm.reset();
        },
    });
};

const deleteDepartment = (id) => {
    if (confirm('Yakin ingin menghapus departemen ini?')) {
        router.delete(route('departments.destroy', id));
    }
};
</script>

<template>
    <Head title="Input Data Departemen" />

    <SaeLayout>
        <template #header>Input Data Departemen</template>

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
                            <span class="text-sm font-medium text-primary md:ml-2">Departemen</span>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Flash Message -->
            <div v-if="$page.props.flash?.success" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center">
                <span class="material-symbols-outlined mr-2">check_circle</span>
                {{ $page.props.flash.success }}
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Form Input -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-primary/5 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Form Input Departemen</h3>
                            <p class="text-xs text-gray-500 mt-1">Tambah departemen baru</p>
                        </div>
                        <div class="bg-primary/10 p-2 rounded-lg">
                            <span class="material-symbols-outlined text-primary">apartment</span>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Kode Departemen -->
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="kode_departemen">
                                    Kode Departemen <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">tag</span>
                                    </div>
                                    <input 
                                        v-model="form.kode_departemen"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white bg-gray-100" 
                                        id="kode_departemen" 
                                        type="text"
                                        readonly
                                    />
                                </div>
                                <p class="text-xs text-gray-400 italic">Kode dibuat otomatis oleh sistem</p>
                            </div>

                            <!-- Nama Departemen -->
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="nama_departemen">
                                    Nama Departemen <span class="text-red-500">*</span>
                                </label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="material-symbols-outlined text-gray-400 text-sm">badge</span>
                                    </div>
                                    <input 
                                        v-model="form.nama_departemen"
                                        class="block w-full pl-10 pr-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                        id="nama_departemen" 
                                        type="text"
                                        placeholder="Masukkan nama departemen"
                                        required
                                    />
                                </div>
                                <p v-if="form.errors.nama_departemen" class="text-xs text-red-500 mt-1">{{ form.errors.nama_departemen }}</p>
                            </div>

                            <!-- Keterangan -->
                            <div class="space-y-1">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="keterangan">
                                    Keterangan <span class="text-gray-400 text-xs">(Opsional)</span>
                                </label>
                                <textarea 
                                    v-model="form.keterangan"
                                    class="block w-full px-3 py-2.5 border-gray-300 rounded-lg focus:ring-primary focus:border-primary text-sm dark:bg-gray-800 dark:border-gray-600 dark:text-white" 
                                    id="keterangan" 
                                    rows="3"
                                    placeholder="Deskripsi departemen..."
                                ></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
                                <button 
                                    type="button" 
                                    @click="form.reset()"
                                    class="px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                >
                                    Reset
                                </button>
                                <button 
                                    type="submit" 
                                    :disabled="form.processing"
                                    class="px-6 py-2 bg-primary hover:bg-primary-dark text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center disabled:opacity-50"
                                >
                                    <span v-if="form.processing" class="material-symbols-outlined animate-spin mr-2 text-lg">progress_activity</span>
                                    <span class="material-symbols-outlined mr-2 text-lg" v-else>save</span>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Departemen -->
                <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                    <div class="bg-primary px-6 py-4 flex justify-between items-center text-white">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined mr-3">list</span>
                            <h3 class="text-lg font-semibold">Daftar Departemen</h3>
                        </div>
                        <span class="bg-white/20 px-3 py-1 rounded-full text-sm">{{ departments.length }} data</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Kode</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Nama</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Keterangan</th>
                                    <th class="bg-gray-50 dark:bg-gray-800 px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                                <tr v-for="dept in departments" :key="dept.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                    <template v-if="editingId === dept.id">
                                        <td class="px-4 py-3">
                                            <input 
                                                v-model="editForm.kode_departemen"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600"
                                                readonly
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input 
                                                v-model="editForm.nama_departemen"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600"
                                            />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input 
                                                v-model="editForm.keterangan"
                                                class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:ring-primary focus:border-primary dark:bg-gray-700 dark:border-gray-600"
                                            />
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex justify-center gap-1">
                                                <button @click="saveEdit(dept.id)" class="p-1.5 bg-green-500 text-white rounded hover:bg-green-600 transition-colors" title="Simpan">
                                                    <span class="material-symbols-outlined text-sm">check</span>
                                                </button>
                                                <button @click="cancelEdit" class="p-1.5 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors" title="Batal">
                                                    <span class="material-symbols-outlined text-sm">close</span>
                                                </button>
                                            </div>
                                        </td>
                                    </template>
                                    <template v-else>
                                        <td class="px-4 py-3 text-sm font-mono text-gray-800 dark:text-white">{{ dept.kode_departemen }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-800 dark:text-white font-medium">{{ dept.nama_departemen }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ dept.keterangan || '-' }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex justify-center gap-1">
                                                <button @click="startEdit(dept)" class="p-1.5 bg-amber-500 text-white rounded hover:bg-amber-600 transition-colors" title="Edit">
                                                    <span class="material-symbols-outlined text-sm">edit</span>
                                                </button>
                                                <button @click="deleteDepartment(dept.id)" class="p-1.5 bg-red-500 text-white rounded hover:bg-red-600 transition-colors" title="Hapus">
                                                    <span class="material-symbols-outlined text-sm">delete</span>
                                                </button>
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                                <tr v-if="!departments || departments.length === 0">
                                    <td colspan="4" class="px-4 py-12 text-center text-gray-500">
                                        <span class="material-symbols-outlined text-5xl mb-3 block opacity-50">apartment</span>
                                        <p>Belum ada data departemen</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
