<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { ShieldCheckIcon, BanknotesIcon, ClockIcon, XCircleIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';

defineProps({ claims: Object, stats: Object });

const money = (c) => '$' + ((c ?? 0) / 100).toFixed(2);
</script>

<template>
    <Head title="Insurance Claims" />
    <SmartHealthLayout title="Insurance Claims">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <StatCard label="Total Claims" :value="stats.total" :icon="ShieldCheckIcon" icon-bg="bg-brand-600" />
            <StatCard label="Approved" :value="money(stats.approved_cents)" :icon="BanknotesIcon" icon-bg="bg-emerald-500" />
            <StatCard label="Pending" :value="stats.pending" :icon="ClockIcon" icon-bg="bg-amber-500" />
            <StatCard label="Denied" :value="stats.denied" :icon="XCircleIcon" icon-bg="bg-red-500" />
        </div>

        <Card padding="p-0">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-3">Claim #</th>
                        <th class="px-5 py-3">Patient</th>
                        <th class="px-5 py-3">Provider</th>
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Claimed</th>
                        <th class="px-5 py-3">Approved</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Submitted</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="c in claims.data" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-mono text-slate-700 dark:text-slate-300">{{ c.claim_number || '—' }}</td>
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ c.patient }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ c.provider }}</td>
                        <td class="px-5 py-3">
                            <Link :href="route('invoices.show', c.invoice_id)" class="text-brand-600 hover:underline">
                                {{ c.invoice_number }}
                            </Link>
                        </td>
                        <td class="px-5 py-3">{{ money(c.claimed_cents) }}</td>
                        <td class="px-5 py-3">{{ c.approved_cents ? money(c.approved_cents) : '—' }}</td>
                        <td class="px-5 py-3"><StatusPill :status="c.status" /></td>
                        <td class="px-5 py-3 text-slate-500">{{ c.submitted_on ? dayjs(c.submitted_on).format('DD MMM YYYY') : '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <Link :href="route('claims.show', c.id)" class="text-brand-600 hover:underline">View →</Link>
                        </td>
                    </tr>
                    <tr v-if="!claims.data.length">
                        <td colspan="9" class="px-5 py-12 text-center text-slate-500">No insurance claims found.</td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="claims.last_page > 1" class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-5 py-3 text-sm">
                <span class="text-slate-500">{{ claims.from }}–{{ claims.to }} of {{ claims.total }}</span>
                <div class="flex gap-1">
                    <Link v-for="link in claims.links" :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        :class="['px-3 py-1 rounded', link.active ? 'bg-brand-600 text-white' : 'text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800', !link.url && 'opacity-40 pointer-events-none']"
                    />
                </div>
            </div>
        </Card>
    </SmartHealthLayout>
</template>
