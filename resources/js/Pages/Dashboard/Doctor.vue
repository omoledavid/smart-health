<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusPill from '@/Components/StatusPill.vue';
import dayjs from 'dayjs';
import { CalendarDaysIcon, UsersIcon, DocumentTextIcon, ClockIcon } from '@heroicons/vue/24/outline';

defineProps({ stats: Object, schedule: Array });
</script>

<template>
    <Head title="Doctor Dashboard" />
    <SmartHealthLayout title="Doctor Dashboard">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            <StatCard label="Today's appointments" :value="stats.today_appointments" :icon="CalendarDaysIcon" icon-bg="bg-brand-600" />
            <StatCard label="Upcoming" :value="stats.upcoming" :icon="ClockIcon" icon-bg="bg-sky-500" />
            <StatCard label="My patients" :value="stats.total_patients" :icon="UsersIcon" icon-bg="bg-emerald-500" />
            <StatCard label="Draft notes" :value="stats.pending_consultations" :icon="DocumentTextIcon" icon-bg="bg-amber-500" />
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div class="lg:col-span-2">
                <Card title="Today's Schedule">
                    <template #actions>
                        <Link :href="route('appointments.index')" class="text-sm text-brand-600 hover:underline">View all</Link>
                    </template>
                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="a in schedule" :key="a.id" class="py-3 flex items-center gap-3">
                            <div class="text-center w-16">
                                <p class="text-xs text-slate-500">{{ dayjs(a.scheduled_at).format('hh:mm') }}</p>
                                <p class="text-xs text-slate-400">{{ dayjs(a.scheduled_at).format('A') }}</p>
                            </div>
                            <div class="flex-1">
                                <Link :href="route('appointments.show', a.id)" class="text-sm font-semibold text-slate-900 dark:text-white hover:text-brand-600">{{ a.patient }}</Link>
                                <p class="text-xs text-slate-500">{{ a.service }}</p>
                            </div>
                            <StatusPill :status="a.status" />
                        </li>
                        <li v-if="!schedule.length" class="py-10 text-sm text-slate-500 text-center">No appointments today.</li>
                    </ul>
                </Card>
            </div>
            <Card title="Quick Actions">
                <div class="space-y-2">
                    <Link :href="route('consultations.index')" class="block px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-sm">New consultation note</Link>
                    <Link :href="route('appointments.create')" class="block px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-sm">Schedule appointment</Link>
                    <Link :href="route('messages.index')" class="block px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-sm">View messages</Link>
                </div>
            </Card>
        </div>
    </SmartHealthLayout>
</template>
