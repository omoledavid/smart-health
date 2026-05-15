<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import VueApexCharts from 'vue3-apexcharts';

const props = defineProps({
    noShowData: Array,
    doctorUtilization: Array,
    revenueData: Array,
    statusBreakdown: Object,
    topServices: Array,
    patientGrowth: Array,
    summary: Object,
});

const money = (c) => '$' + ((c ?? 0) / 100).toLocaleString('en-US', { minimumFractionDigits: 2 });

// No-show rate chart
const noShowChartOptions = computed(() => ({
    chart: { type: 'line', toolbar: { show: false }, background: 'transparent' },
    stroke: { curve: 'smooth', width: 2 },
    colors: ['#e70d0d'],
    xaxis: { categories: props.noShowData.map(d => d.month), labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => v + '%' } },
    tooltip: { y: { formatter: v => v + '%' } },
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
}));
const noShowSeries = computed(() => [{ name: 'No-show Rate', data: props.noShowData.map(d => d.no_show_rate) }]);

// Revenue chart
const revenueChartOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent', stacked: false },
    colors: ['#2e37a4', '#1abe17'],
    xaxis: { categories: props.revenueData.map(d => d.month), labels: { style: { fontSize: '11px' } } },
    yaxis: { labels: { formatter: v => '$' + (v / 100).toFixed(0) } },
    legend: { position: 'top' },
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
    dataLabels: { enabled: false },
}));
const revenueSeries = computed(() => [
    { name: 'Billed', data: props.revenueData.map(d => d.billed) },
    { name: 'Collected', data: props.revenueData.map(d => d.revenue) },
]);

// Doctor utilization chart
const utilChartOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false }, background: 'transparent' },
    colors: ['#2e37a4', '#1abe17', '#e70d0d'],
    xaxis: { categories: props.doctorUtilization.map(d => d.doctor?.split(' ').pop() ?? 'Dr'), labels: { style: { fontSize: '11px' } } },
    legend: { position: 'top' },
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
    dataLabels: { enabled: false },
    plotOptions: { bar: { columnWidth: '55%' } },
}));
const utilSeries = computed(() => [
    { name: 'Scheduled', data: props.doctorUtilization.map(d => d.scheduled) },
    { name: 'Completed', data: props.doctorUtilization.map(d => d.completed) },
    { name: 'No-show', data: props.doctorUtilization.map(d => d.no_show) },
]);

// Status donut
const donutOptions = computed(() => ({
    chart: { type: 'donut', background: 'transparent' },
    labels: Object.keys(props.statusBreakdown).map(s => s.replace(/_/g, ' ')),
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    legend: { position: 'bottom', fontSize: '12px' },
    dataLabels: { enabled: false },
}));
const donutSeries = computed(() => Object.values(props.statusBreakdown).map(Number));

// Patient growth
const growthOptions = computed(() => ({
    chart: { type: 'area', toolbar: { show: false }, background: 'transparent' },
    colors: ['#2e37a4'],
    fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0 } },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: { categories: props.patientGrowth.map(d => d.month), labels: { style: { fontSize: '11px' } } },
    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' },
    grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
    dataLabels: { enabled: false },
}));
const growthSeries = computed(() => [{ name: 'New Patients', data: props.patientGrowth.map(d => d.new_patients) }]);
</script>

<template>
    <Head title="Analytics" />
    <PreclinicLayout title="Analytics & Reports">
        <!-- Summary cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 ring-1 ring-slate-200 dark:ring-slate-800">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Total Patients</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ summary.total_patients.toLocaleString() }}</p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 ring-1 ring-slate-200 dark:ring-slate-800">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Appts This Month</p>
                <p class="text-3xl font-bold text-slate-900 dark:text-white">{{ summary.appointments_this_month.toLocaleString() }}</p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 ring-1 ring-slate-200 dark:ring-slate-800">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Revenue This Month</p>
                <p class="text-3xl font-bold text-brand-600">{{ money(summary.revenue_this_month) }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ money(summary.outstanding_balance) }} outstanding</p>
            </div>
            <div class="rounded-2xl bg-white dark:bg-slate-900 p-5 ring-1 ring-slate-200 dark:ring-slate-800">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">No-show Rate (30d)</p>
                <p class="text-3xl font-bold" :class="summary.no_show_rate_30d > 15 ? 'text-danger' : 'text-slate-900 dark:text-white'">
                    {{ summary.no_show_rate_30d }}%
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <!-- Revenue chart -->
            <Card title="Revenue: Billed vs Collected (Last 6 Months)">
                <VueApexCharts v-if="revenueData.length" type="bar" height="220" :options="revenueChartOptions" :series="revenueSeries" />
                <p v-else class="text-sm text-slate-500 text-center py-8">No revenue data yet.</p>
            </Card>

            <!-- No-show trend -->
            <Card title="No-show Rate Trend (Last 6 Months)">
                <VueApexCharts v-if="noShowData.length" type="line" height="220" :options="noShowChartOptions" :series="noShowSeries" />
                <p v-else class="text-sm text-slate-500 text-center py-8">No data yet.</p>
            </Card>

            <!-- Doctor utilization -->
            <Card title="Doctor Utilization (Last 30 Days)">
                <VueApexCharts v-if="doctorUtilization.length" type="bar" height="260" :options="utilChartOptions" :series="utilSeries" />
                <p v-else class="text-sm text-slate-500 text-center py-8">No appointment data.</p>
            </Card>

            <!-- Appointment status donut -->
            <Card title="Appointment Status Breakdown">
                <VueApexCharts v-if="donutSeries.length" type="donut" height="260" :options="donutOptions" :series="donutSeries" />
                <p v-else class="text-sm text-slate-500 text-center py-8">No appointments yet.</p>
            </Card>

            <!-- Patient growth -->
            <Card title="New Patient Registrations (Last 6 Months)">
                <VueApexCharts v-if="patientGrowth.length" type="area" height="200" :options="growthOptions" :series="growthSeries" />
                <p v-else class="text-sm text-slate-500 text-center py-8">No data yet.</p>
            </Card>

            <!-- Top services -->
            <Card title="Top Services (Last 90 Days)">
                <div v-if="topServices.length" class="space-y-2">
                    <div v-for="(s, i) in topServices" :key="i" class="flex items-center gap-3">
                        <div class="h-2 rounded-full bg-brand-600" :style="{ width: (s.count / topServices[0].count * 100) + '%', minWidth: '4px' }"></div>
                        <span class="text-sm text-slate-700 dark:text-slate-300 truncate flex-1">{{ s.name }}</span>
                        <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ s.count }}</span>
                    </div>
                </div>
                <p v-else class="text-sm text-slate-500 text-center py-8">No service data yet.</p>
            </Card>
        </div>
    </PreclinicLayout>
</template>
