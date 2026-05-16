<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { DocumentTextIcon, BanknotesIcon, ClockIcon, ExclamationTriangleIcon } from '@heroicons/vue/24/outline';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import dayjs from 'dayjs';

defineProps({ invoices: Object, stats: Object });
const money = (c) => '$' + ((c ?? 0) / 100).toFixed(2);
const user = computed(() => usePage().props.auth?.user);
const isAdmin   = computed(() => user.value?.role === 'admin')
const isPatient = computed(() => user.value?.role === 'patient');
</script>

<template>
    <Head title="Billing" />
    <SmartHealthLayout title="Billing & Invoices">
        <template #actions>
            <div v-if="isAdmin" class="flex gap-2">
                <Link :href="route('claims.index')" class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs sm:text-sm font-medium whitespace-nowrap">
                    Insurance Claims
                </Link>
                <Link :href="route('invoices.create')" class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-medium whitespace-nowrap">
                    + New Invoice
                </Link>
            </div>
        </template>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <StatCard label="Total Invoices" :value="stats.total" :icon="DocumentTextIcon" icon-bg="bg-brand-600" />
            <StatCard label="Paid" :value="money(stats.paid_cents)" :icon="BanknotesIcon" icon-bg="bg-emerald-500" />
            <StatCard label="Pending" :value="money(stats.pending_cents)" :icon="ClockIcon" icon-bg="bg-amber-500" />
            <StatCard label="Overdue" :value="stats.overdue" :icon="ExclamationTriangleIcon" icon-bg="bg-red-500" />
        </div>

        <!-- Mobile card list -->
        <div class="sm:hidden space-y-3">
            <div v-for="i in invoices.data" :key="i.id"
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-mono text-xs text-slate-500">{{ i.number }}</p>
                        <p v-if="!isPatient" class="text-sm font-semibold text-slate-900 dark:text-white truncate mt-0.5">
                            {{ i.patient?.first_name }} {{ i.patient?.last_name }}
                        </p>
                    </div>
                    <StatusPill :status="i.status" class="shrink-0" />
                </div>
                <div class="mt-2 grid grid-cols-3 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-slate-400">Issued</p>
                        <p class="text-slate-700 dark:text-slate-300">{{ i.issued_on ? dayjs(i.issued_on).format('DD MMM YY') : '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Total</p>
                        <p class="font-medium text-slate-900 dark:text-white">{{ money(i.total_cents) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Paid</p>
                        <p class="text-slate-700 dark:text-slate-300">{{ money(i.paid_cents) }}</p>
                    </div>
                </div>
                <div class="mt-3 text-right">
                    <Link :href="route('invoices.show', i.id)" class="text-brand-600 hover:underline text-sm font-medium">View →</Link>
                </div>
            </div>
            <p v-if="!invoices.data.length" class="text-center text-slate-500 text-sm py-10">No invoices yet.</p>
        </div>

        <!-- Desktop table -->
        <Card padding="p-0" class="hidden sm:block">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-3">#</th>
                        <th v-if="!isPatient" class="px-5 py-3">Patient</th>
                        <th class="px-5 py-3">Issued</th>
                        <th class="px-5 py-3">Total</th>
                        <th class="px-5 py-3">Paid</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="i in invoices.data" :key="i.id">
                        <td class="px-5 py-3 font-mono text-xs">{{ i.number }}</td>
                        <td v-if="!isPatient" class="px-5 py-3">{{ i.patient?.first_name }} {{ i.patient?.last_name }}</td>
                        <td class="px-5 py-3">{{ i.issued_on ? dayjs(i.issued_on).format('DD MMM YYYY') : '—' }}</td>
                        <td class="px-5 py-3">{{ money(i.total_cents) }}</td>
                        <td class="px-5 py-3">{{ money(i.paid_cents) }}</td>
                        <td class="px-5 py-3"><StatusPill :status="i.status" /></td>
                        <td class="px-5 py-3 text-right"><Link :href="route('invoices.show', i.id)" class="text-brand-600 hover:underline text-sm">View</Link></td>
                    </tr>
                    <tr v-if="!invoices.data.length">
                        <td :colspan="isPatient ? 6 : 7" class="px-5 py-10 text-center text-slate-500">No invoices yet.</td>
                    </tr>
                </tbody>
            </table>
        </Card>
    </SmartHealthLayout>
</template>
