<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref, onMounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    products: Array,
    salesByProduct: Object,
    purchaseByProduct: Object,
    salesStatus: Object,
    purchaseStatus: Object,
});

const stockChart = ref(null);
const salesChart = ref(null);
const purchaseChart = ref(null);
const salesStatusChart = ref(null);
const purchaseStatusChart = ref(null);

const createChart = (ctx, type, data, options = {}) => {
    return new Chart(ctx, {
        type,
        data,
        options,
    });
};

onMounted(() => {
    // STOCK BAR CHART
    createChart(stockChart.value.getContext('2d'), 'bar', {
        labels: props.products.map(p => p.name),
        datasets: [{
            label: 'Stock Quantity',
            data: props.products.map(p => p.base_qty),
            backgroundColor: 'rgba(59, 130, 246, 0.7)', // Tailwind blue-500 with some opacity
            borderRadius: 4,
            barPercentage: 0.6,
        }]
    }, {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    });

    // SALES PIE CHART
    createChart(salesChart.value.getContext('2d'), 'doughnut', {
        labels: Object.keys(props.salesByProduct),
        datasets: [{
            label: 'Sales Quantity',
            data: Object.values(props.salesByProduct),
            backgroundColor: [
                '#f87171', '#60a5fa', '#34d399', '#fbbf24', '#a78bfa', '#f472b6', '#93c5fd'
            ]
        }]
    });

    // PURCHASES PIE CHART
    createChart(purchaseChart.value.getContext('2d'), 'doughnut', {
        labels: Object.keys(props.purchaseByProduct),
        datasets: [{
            label: 'Purchase Quantity',
            data: Object.values(props.purchaseByProduct),
            backgroundColor: [
                '#fbbf24', '#34d399', '#60a5fa', '#f87171', '#a78bfa', '#f472b6', '#93c5fd'
            ]
        }]
    });

    // SALES STATUS BAR CHART
    createChart(salesStatusChart.value.getContext('2d'), 'bar', {
        labels: Object.keys(props.salesStatus),
        datasets: [{
            label: 'Sales Status Count',
            data: Object.values(props.salesStatus),
            backgroundColor: 'rgba(249, 115, 22, 0.8)', // Tailwind orange-500 with opacity
            borderRadius: 4,
            barPercentage: 0.6,
        }]
    }, {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    });

    // PURCHASE STATUS BAR CHART
    createChart(purchaseStatusChart.value.getContext('2d'), 'bar', {
        labels: Object.keys(props.purchaseStatus),
        datasets: [{
            label: 'Purchase Status Count',
            data: Object.values(props.purchaseStatus),
            backgroundColor: 'rgba(34, 197, 94, 0.8)', // Tailwind green-500 with opacity
            borderRadius: 4,
            barPercentage: 0.6,
        }]
    }, {
        responsive: true,
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    });
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900 tracking-wide">Dashboard</h2>
        </template>

        <main class="py-8 max-w-7xl mx-auto px-6 space-y-12">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <section class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
                    <h3 class="text-xl font-semibold mb-5 border-b border-gray-200 pb-2">Product Stock Overview</h3>
                    <canvas ref="stockChart" height="220"></canvas>
                </section>

                <section class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
                    <h3 class="text-xl font-semibold mb-5 border-b border-gray-200 pb-2">Sales Quantity by Product</h3>
                    <canvas ref="salesChart" height="220"></canvas>
                </section>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <section class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
                    <h3 class="text-xl font-semibold mb-5 border-b border-gray-200 pb-2">Purchase Quantity by Product</h3>
                    <canvas ref="purchaseChart" height="220"></canvas>
                </section>

                <section class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300">
                    <h3 class="text-xl font-semibold mb-5 border-b border-gray-200 pb-2">Sales Status Distribution</h3>
                    <canvas ref="salesStatusChart" height="220"></canvas>
                </section>
            </div>

            <section
                class="bg-white p-6 rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 max-w-lg mx-auto">
                <h3 class="text-xl font-semibold mb-5 border-b border-gray-200 pb-2 text-center">Purchase Status
                    Distribution</h3>
                <canvas ref="purchaseStatusChart" height="220"></canvas>
            </section>
        </main>
    </AuthenticatedLayout>
</template>
