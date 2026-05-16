<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusPill from '@/Components/StatusPill.vue';
import dayjs from 'dayjs';
import { CalendarDaysIcon, ClockIcon, BanknotesIcon, CheckCircleIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ stats: Object, upcoming: Array, patient: Object });

const onboardingPct = computed(() => Math.min(100, ((props.patient?.onboarding_step ?? 0) / 4) * 100));
</script>

<template>
    <Head title="My Health Dashboard" />
    <SmartHealthLayout :title="`Hello, ${patient?.full_name ?? 'Patient'}`">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <StatCard label="Upcoming" :value="stats.upcoming" :icon="ClockIcon" icon-bg="bg-brand-600" />
            <StatCard label="Past visits" :value="stats.past" :icon="CalendarDaysIcon" icon-bg="bg-sky-500" />
            <StatCard label="Open invoices" :value="stats.open_invoices" :icon="BanknotesIcon" icon-bg="bg-amber-500" />
            <StatCard label="Onboarding" :value="stats.onboarding_status?.replace('_',' ') ?? '—'" :icon="CheckCircleIcon" icon-bg="bg-emerald-500" />
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2">
                <Card title="Upcoming appointments">
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="a in upcoming" :key="a.id" class="py-3 flex items-center gap-3">
                            <div class="w-20">
                                <p class="text-xs text-slate-500">{{ dayjs(a.scheduled_at).format('DD MMM') }}</p>
                                <p class="text-xs text-slate-400">{{ dayjs(a.scheduled_at).format('hh:mm A') }}</p>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ a.doctor }}</p>
                                <p class="text-xs text-slate-500">{{ a.service }}</p>
                            </div>
                            <StatusPill :status="a.status" />
                        </li>
                        <li v-if="!upcoming.length" class="py-10 text-sm text-slate-500 text-center">No upcoming appointments.</li>
                    </ul>
                </Card>
            </div>
            <Card title="Onboarding progress">
                <p class="text-xs text-slate-500 mb-2">{{ stats.onboarding_status?.replace('_',' ') ?? '—' }}</p>
                <div class="h-2 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-600 transition-all" :style="{ width: onboardingPct + '%' }"></div>
                </div>
                <p class="text-xs text-slate-500 mt-2">{{ patient?.onboarding_step ?? 0 }} of 4 steps complete</p>
            </Card>
        </div>
    </SmartHealthLayout>
</template>
