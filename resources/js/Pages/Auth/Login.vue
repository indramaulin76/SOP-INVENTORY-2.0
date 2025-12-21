<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Masuk - Sae Bakery" />

    <div class="bg-background-light dark:bg-background-dark text-[#181611] dark:text-white min-h-screen flex items-center justify-center p-4 font-display">
        <!-- Main Container -->
        <div class="w-full max-w-[480px] flex flex-col items-center gap-6 relative z-10">
            <!-- Logo / Brand Header Outside Card -->
            <div class="flex items-center gap-3 mb-2">
                <img src="/images/logo-toko.jpg" alt="Sae Bakery" class="w-16 h-16 rounded-full object-cover shadow-lg border-2 border-primary/20">
                <h1 class="text-2xl font-bold tracking-tight text-[#181611] dark:text-white">Sae Bakery</h1>
            </div>

            <!-- Login Card -->
            <div class="w-full bg-white dark:bg-[#1c1914] rounded-2xl shadow-xl border border-[#e6e2db] dark:border-stone-800 overflow-hidden">
                <!-- Card Header -->
                <div class="px-8 pt-10 pb-2 text-center">
                    <h2 class="text-[#181611] dark:text-white tracking-tight text-[28px] font-bold leading-tight mb-2">Selamat Datang Kembali!</h2>
                    <p class="text-[#897d61] dark:text-stone-400 text-sm font-normal leading-normal">
                        Silakan masukkan detail akun Anda untuk mengakses dashboard inventaris.
                    </p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="px-8 mb-0 mt-4">
                    <div class="p-3 bg-green-50 text-green-700 text-sm rounded-lg text-center border border-green-200">
                        {{ status }}
                    </div>
                </div>

                <!-- Card Body / Form -->
                <form @submit.prevent="submit" class="px-8 py-6 flex flex-col gap-5">
                    <!-- Email Field -->
                    <div class="flex flex-col flex-1">
                        <p class="text-[#181611] dark:text-stone-200 text-sm font-semibold leading-normal pb-2">Email</p>
                        <div class="relative">
                            <input
                                v-model="form.email"
                                type="email"
                                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#181611] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#e6e2db] dark:border-stone-700 bg-white dark:bg-stone-900 focus:border-primary h-12 placeholder:text-[#897d61] dark:placeholder:text-stone-600 px-4 text-base font-normal leading-normal transition-all"
                                placeholder="nama@saebakery.com"
                                required
                            />
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-[#897d61]">
                                <span class="material-symbols-outlined text-[20px]">mail</span>
                            </div>
                        </div>
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password Field -->
                    <div class="flex flex-col flex-1">
                        <div class="flex justify-between items-center pb-2">
                            <p class="text-[#181611] dark:text-stone-200 text-sm font-semibold leading-normal">Password</p>
                        </div>
                        <div class="flex w-full flex-1 items-stretch rounded-lg relative">
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#181611] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/50 border border-[#e6e2db] dark:border-stone-700 bg-white dark:bg-stone-900 focus:border-primary h-12 placeholder:text-[#897d61] dark:placeholder:text-stone-600 px-4 pr-12 text-base font-normal leading-normal transition-all"
                                placeholder="•••••••••"
                                required
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-0 top-0 h-full px-4 text-[#897d61] hover:text-primary transition-colors flex items-center justify-center rounded-r-lg"
                            >
                                <span class="material-symbols-outlined text-[20px]">{{ showPassword ? 'visibility_off' : 'visibility' }}</span>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>



                    <!-- Submit Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full cursor-pointer items-center justify-center overflow-hidden rounded-lg h-12 px-5 bg-primary hover:bg-primary-hover text-[#181611] text-base font-bold leading-normal tracking-[0.015em] transition-all shadow-sm hover:shadow-md mt-2 disabled:opacity-75 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-[#181611]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                        <span v-else class="truncate">Masuk</span>
                    </button>
                </form>


            </div>

            <!-- Copyright / Meta -->
            <p class="text-[#897d61] dark:text-stone-600 text-xs text-center">
                © {{ new Date().getFullYear() }} Sae Bakery Systems. All rights reserved.
            </p>
        </div>

        <!-- Background Decorative Elements -->
        <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
            <!-- Abstract gradient blob for warmth -->
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-primary/10 blur-[100px]"></div>
            <div class="absolute top-[20%] right-[10%] w-[20%] h-[20%] rounded-full bg-primary/5 blur-[80px]"></div>
        </div>
    </div>
</template>
