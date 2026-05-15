<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import dayjs from 'dayjs';
import { UserGroupIcon, UsersIcon, CalendarDaysIcon, BanknotesIcon, PlusIcon, ClockIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: Object,
    monthly: Array,
    apptCounts: Object,
    upcoming: Array,
});

const fmtMoney = (cents) => '$' + ((cents ?? 0) / 100).toLocaleString('en-US', { minimumFractionDigits: 0 });

const barOptions = computed(() => ({
    chart: { type: 'bar', stacked: true, toolbar: { show: false }, fontFamily: 'Inter' },
    plotOptions: { bar: { columnWidth: '40%', borderRadius: 4 } },
    colors: ['#10b981', '#3b82f6', '#6366f1'],
    dataLabels: { enabled: false },
    legend: { position: 'bottom', markers: { radius: 12 } },
    xaxis: { categories: props.monthly.map(m => m.label), labels: { style: { colors: '#64748b' } } },
    yaxis: { labels: { style: { colors: '#64748b' } } },
    grid: { borderColor: 'rgba(148,163,184,0.2)', strokeDashArray: 3 },
    tooltip: { theme: 'light' },
}));
const barSeries = computed(() => ([
    { name: 'Completed', data: props.monthly.map(m => m.completed) },
    { name: 'Ongoing', data: props.monthly.map(m => m.ongoing) },
    { name: 'Rescheduled', data: props.monthly.map(m => m.rescheduled) },
]));

// Calendar widget
const calRef = ref(dayjs());
const daysInGrid = computed(() => {
    const start = calRef.value.startOf('month').startOf('week');
    return Array.from({ length: 42 }, (_, i) => start.add(i, 'day'));
});
const isToday = (d) => d.isSame(dayjs(), 'day');
const inMonth = (d) => d.isSame(calRef.value, 'month');
</script>

<template>
    <Head title="Admin Dashboard" />
    <PreclinicLayout title="Admin Dashboard">
        <template #actions>
            <Link :href="route('appointments.create')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> New Appointment
            </Link>
            <Link :href="route('doctors.index')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-medium">
                <ClockIcon class="h-4 w-4" /> Schedule Availability
            </Link>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <StatCard label="Doctors" :value="stats.doctors" delta="+95%" :icon="UserGroupIcon" icon-bg="bg-brand-600" :spark="[3,8,5,9,6,10,12]" spark-color="#2e37a4" />
            <StatCard label="Patients" :value="stats.patients" delta="+25%" :icon="UsersIcon" icon-bg="bg-red-500" :spark="[5,7,6,9,8,11,10]" spark-color="#ef4444" />
            <StatCard label="Appointments" :value="stats.appointments" delta="-15%" :delta-positive="false" :icon="CalendarDaysIcon" icon-bg="bg-sky-500" :spark="[10,8,6,4,7,5,3]" spark-color="#0ea5e9" />
            <StatCard label="Revenue" :value="fmtMoney(stats.revenue_cents)" delta="+25%" :icon="BanknotesIcon" icon-bg="bg-emerald-500" :spark="[2,4,3,6,5,8,9]" spark-color="#10b981" />
        </div>

        <div class="mt-6 grid grid-cols-1 xl:grid-cols-3 gap-5">
            <div class="xl:col-span-2 space-y-5">
                <Card title="Appointment Statistics">
                    <template #actions>
                        <select class="text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-1.5">
                            <option>Monthly</option>
                            <option>Weekly</option>
                        </select>
                    </template>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3">
                            <p class="text-xs text-slate-500">All Appointments</p>
                            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ apptCounts.all }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3">
                            <p class="text-xs text-slate-500">Cancelled</p>
                            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ apptCounts.cancelled }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3">
                            <p class="text-xs text-slate-500">Rescheduled</p>
                            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ apptCounts.rescheduled }}</p>
                        </div>
                        <div class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-3">
                            <p class="text-xs text-slate-500">Completed</p>
                            <p class="text-xl font-bold text-slate-900 dark:text-white">{{ apptCounts.completed }}</p>
                        </div>
                    </div>
                    <apexchart type="bar" height="300" :options="barOptions" :series="barSeries" />
                </Card>
            </div>

            <div class="space-y-5">
                <Card>
                    <div class="flex items-center justify-between mb-3">
                        <button class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800" @click="calRef = calRef.subtract(1, 'month')">
                            <ChevronLeftIcon class="h-4 w-4" />
                        </button>
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ calRef.format('MMMM YYYY') }}</p>
                        <button class="p-1 rounded hover:bg-slate-100 dark:hover:bg-slate-800" @click="calRef = calRef.add(1, 'month')">
                            <ChevronRightIcon class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center">
                        <span v-for="d in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="d" class="text-xs font-medium text-slate-400 py-1">{{ d }}</span>
                        <button
                            v-for="d in daysInGrid"
                            :key="d.format('YYYY-MM-DD')"
                            class="text-xs rounded-lg py-1.5"
                            :class="[
                                isToday(d) ? 'bg-brand-600 text-white font-semibold' : inMonth(d) ? 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800' : 'text-slate-300 dark:text-slate-600',
                            ]"
                        >{{ d.date() }}</button>
                    </div>
                </Card>

                <Card title="Appointments">
                    <template #actions>
                        <select class="text-sm rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-2 py-1">
                            <option>All Type</option>
                        </select>
                    </template>
                    <ul class="space-y-3 max-h-80 overflow-y-auto scrollbar-thin pr-1">
                        <li v-for="a in upcoming" :key="a.id" class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 text-xs font-semibold flex-shrink-0">
                                {{ a.patient?.[0] ?? '?' }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ a.service }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ a.patient }} with {{ a.doctor }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ dayjs(a.scheduled_at).format('ddd, DD MMM YYYY, hh:mm A') }}</p>
                            </div>
                            <StatusPill :status="a.status" />
                        </li>
                        <li v-if="!upcoming.length" class="text-sm text-slate-500 text-center py-6">No upcoming appointments</li>
                    </ul>
                </Card>
            </div>
        </div>
    </PreclinicLayout>
</template>
