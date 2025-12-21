<script setup>
import SaeLayout from '@/Layouts/SaeLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

// Props from controller
const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_products: 0,
            low_stock_count: 0,
            total_sales: 0,
            total_profit: 0,
            today_sales: 0,
            today_profit: 0,
            month_sales: 0,
        })
    },
    chartData: {
        type: Object,
        default: () => ({
            daily: [],
            weekly: [],
            monthly: [],
        })
    },
    topProducts: {
        type: Array,
        default: () => []
    }
});

// Reactive state
const showSuccessNotification = ref(true);
const activeChart = ref('daily');

const dismissNotification = () => {
    showSuccessNotification.value = false;
};

// Format currency helper
const formatCurrency = (value) => {
    if (value === 0) return 'Rp 0';
    if (value >= 1000000000) {
        return `Rp ${(value / 1000000000).toFixed(1)}M`;
    } else if (value >= 1000000) {
        return `Rp ${(value / 1000000).toFixed(1)}jt`;
    } else if (value >= 1000) {
        return `Rp ${(value / 1000).toFixed(1)}rb`;
    }
    return `Rp ${value.toLocaleString('id-ID')}`;
};

// Get active chart data
const activeChartData = computed(() => {
    return props.chartData[activeChart.value] || [];
});

// Calculate max value for chart scaling
const maxChartValue = computed(() => {
    const values = activeChartData.value.map(d => d.value);
    const max = Math.max(...values, 1);
    return max;
});

// Generate SVG path for chart
const chartPath = computed(() => {
    const data = activeChartData.value;
    if (data.length === 0) return '';
    
    const width = 1000;
    const height = 200;
    const maxVal = maxChartValue.value;
    
    const points = data.map((d, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - (d.value / maxVal) * height;
        return `${x},${y}`;
    });
    
    return 'M' + points.join(' L');
});

// Generate fill area path
const fillPath = computed(() => {
    const data = activeChartData.value;
    if (data.length === 0) return '';
    
    const width = 1000;
    const height = 200;
    const maxVal = maxChartValue.value;
    
    const points = data.map((d, i) => {
        const x = (i / (data.length - 1)) * width;
        const y = height - (d.value / maxVal) * height;
        return `${x},${y}`;
    });
    
    return `M0,${height} L${points.join(' L')} L${width},${height} Z`;
});

// Check if has sales data
const hasSalesData = computed(() => {
    return activeChartData.value.some(d => d.value > 0);
});

// X-axis labels
const xLabels = computed(() => {
    const data = activeChartData.value;
    if (data.length === 0) return [];
    const step = Math.ceil(data.length / 7);
    return data.filter((_, i) => i % step === 0 || i === data.length - 1).map(d => d.date);
});

// Y-axis labels
const yLabels = computed(() => {
    const max = maxChartValue.value;
    return [
        formatCurrency(max),
        formatCurrency(max * 0.75),
        formatCurrency(max * 0.5),
        formatCurrency(max * 0.25),
        '0',
    ];
});
</script>

<template>
    <Head title="Dashboard" />

    <SaeLayout>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Notifications / Welcome -->
            <div class="space-y-4">
                <!-- Dismissible Success Notification -->
                <div 
                    v-if="showSuccessNotification && $page.props.flash?.success" 
                    class="bg-green-100 dark:bg-green-900/30 border-l-4 border-green-500 p-4 rounded-r-md shadow-sm flex items-center justify-between"
                >
                    <div class="flex items-center">
                        <span class="material-symbols-outlined text-green-600 dark:text-green-400 mr-2">check_circle</span>
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">{{ $page.props.flash.success }}</p>
                    </div>
                    <button 
                        @click="dismissNotification" 
                        class="text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200"
                    >
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>

                <div class="bg-surface-light dark:bg-surface-dark rounded-lg shadow-sm overflow-hidden border border-gray-100 dark:border-gray-700">
                    <div class="bg-primary px-6 py-3 border-b border-primary-hover">
                        <div class="flex items-center text-white">
                            <span class="material-symbols-outlined mr-2">celebration</span>
                            <h3 class="font-medium">Selamat Datang di Dashboard</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                            Anda telah berhasil login ke sistem <span class="font-semibold text-primary">Sae Bakery</span>. Dashboard ini adalah halaman utama Anda untuk mengakses semua fitur dan layanan. Mulai dengan menjelajahi menu di sebelah kiri untuk memulai.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-8">
                <!-- Card 1: Total Sales -->
                <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="material-symbols-outlined text-6xl text-primary">payments</span>
                    </div>
                    <div class="flex flex-col relative z-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Omzet</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ formatCurrency(stats.total_sales) }}</h3>
                        <div class="flex items-center mt-2 text-xs">
                            <span v-if="stats.today_sales > 0" class="text-green-500 font-medium">
                                Hari ini: {{ formatCurrency(stats.today_sales) }}
                            </span>
                            <span v-else class="text-gray-400">No transactions yet</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Total Profit -->
                <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="material-symbols-outlined text-6xl text-green-500">trending_up</span>
                    </div>
                    <div class="flex flex-col relative z-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Profit Analysis</p>
                        <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ formatCurrency(stats.total_profit) }}</h3>
                        <div class="flex items-center mt-2 text-xs">
                            <span v-if="stats.today_profit > 0" class="text-green-500 font-medium">
                                Hari ini: {{ formatCurrency(stats.today_profit) }}
                            </span>
                            <span v-else class="text-gray-400">No transactions yet</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Total Products -->
                <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="material-symbols-outlined text-6xl text-primary">inventory</span>
                    </div>
                    <div class="flex flex-col relative z-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Products</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ stats.total_products }} Items</h3>
                        <div class="flex items-center mt-2 text-xs">
                            <span class="text-primary font-medium">Master data products</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Low Stock Alert -->
                <div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-red-100 dark:border-red-900/30 hover:shadow-md transition-shadow relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <span class="material-symbols-outlined text-6xl text-red-500">warning</span>
                    </div>
                    <div class="flex flex-col relative z-10">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Low Stock Alert</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ stats.low_stock_count }} Items</h3>
                        <div class="mt-3 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                            <div 
                                class="bg-red-500 h-1.5 rounded-full" 
                                :style="{ width: stats.total_products > 0 ? `${Math.min((stats.low_stock_count / stats.total_products) * 100, 100)}%` : '0%' }"
                            ></div>
                        </div>
                        <p class="text-xs text-red-500 mt-2 font-medium" v-if="stats.low_stock_count > 0">Restock needed soon</p>
                        <p class="text-xs text-green-500 mt-2 font-medium" v-else>All stock levels OK</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">Sales Trend</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ activeChart === 'daily' ? 'Daily sales (last 30 days)' : activeChart === 'weekly' ? 'Weekly sales (last 8 weeks)' : 'Monthly sales (last 6 months)' }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <button 
                            @click="activeChart = 'daily'"
                            :class="[
                                'px-3 py-1 text-xs rounded-md transition-colors',
                                activeChart === 'daily' ? 'bg-primary text-white' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >Daily</button>
                        <button 
                            @click="activeChart = 'weekly'"
                            :class="[
                                'px-3 py-1 text-xs rounded-md transition-colors',
                                activeChart === 'weekly' ? 'bg-primary text-white' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >Weekly</button>
                        <button 
                            @click="activeChart = 'monthly'"
                            :class="[
                                'px-3 py-1 text-xs rounded-md transition-colors',
                                activeChart === 'monthly' ? 'bg-primary text-white' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                        >Monthly</button>
                    </div>
                </div>
                
                <div class="relative h-64 w-full">
                    <!-- Y-Axis Grid -->
                    <div class="absolute inset-0 flex flex-col justify-between text-xs text-gray-400 pointer-events-none">
                        <div v-for="(label, idx) in yLabels" :key="idx" class="border-b border-dashed border-gray-200 dark:border-gray-700 h-0 w-full flex items-end pb-1">
                            <span>{{ label }}</span>
                        </div>
                    </div>
                    
                    <!-- SVG Chart -->
                    <svg class="absolute inset-0 h-full w-full pt-4 pb-6 px-8" preserveAspectRatio="none" viewBox="0 0 1000 200">
                        <defs>
                            <linearGradient id="gradient" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#C59D5F" stop-opacity="0.3"></stop>
                                <stop offset="100%" stop-color="#C59D5F" stop-opacity="0"></stop>
                            </linearGradient>
                        </defs>
                        
                        <!-- Fill Area -->
                        <path v-if="hasSalesData" :d="fillPath" fill="url(#gradient)" stroke="none"></path>
                        
                        <!-- Line -->
                        <path v-if="hasSalesData" :d="chartPath" fill="none" stroke="#C59D5F" stroke-linecap="round" stroke-linejoin="round" stroke-width="3"></path>
                        
                        <!-- No data message -->
                        <text v-if="!hasSalesData" x="500" y="100" text-anchor="middle" class="fill-gray-400" font-size="14">No sales data yet</text>
                    </svg>
                </div>
                
                <!-- X-Axis Labels -->
                <div class="flex justify-between px-8 text-xs text-gray-400 mt-2">
                    <span v-for="label in xLabels" :key="label">{{ label }}</span>
                </div>
            </div>

            <!-- Top Products Section -->
            <div v-if="topProducts.length > 0" class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="bg-primary px-6 py-4 text-white flex items-center">
                    <span class="material-symbols-outlined mr-3 filled">leaderboard</span>
                    <h3 class="text-lg font-semibold">Top Selling Products</h3>
                </div>
                <div class="p-4">
                    <div v-for="(product, index) in topProducts" :key="index" class="flex items-center justify-between p-3 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg">
                        <div class="flex items-center">
                            <span class="material-symbols-outlined text-amber-500 mr-3" v-if="index < 3">emoji_events</span>
                            <span class="w-6 h-6 flex items-center justify-center bg-gray-200 dark:bg-gray-700 rounded-full text-xs font-bold mr-3" v-else>{{ index + 1 }}</span>
                            <span class="font-medium text-gray-800 dark:text-white">{{ product.name }}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-primary">{{ formatCurrency(product.revenue) }}</p>
                            <p class="text-xs text-gray-500">{{ parseFloat(product.qty).toLocaleString('id-ID') }} pcs sold</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SaeLayout>
</template>
