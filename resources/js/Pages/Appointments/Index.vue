<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import dayjs from 'dayjs';
import { PlusIcon, CalendarIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ appointments: Object, filters: Object });

const setStatus = (s) => router.get(route('appointments.index'), { status: s }, { preserveState: true });
</script>

<template>
    <Head title="Appointments" />
    <PreclinicLayout title="Appointments">
        <template #actions>
            <Link :href="route('appointments.calendar')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm font-medium">
                <CalendarIcon class="h-4 w-4" /> Calendar
            </Link>
            <Link :href="route('appointments.create')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> New Appointment
            </Link>
        </template>
        <Card padding="p-0">
            <div class="px-5 py-3 border-b border-slate-200 dark:border-slate-800 flex gap-2 flex-wrap">
                <button v-for="s in ['', 'scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show']" :key="s"
                    @click="setStatus(s)"
                    :class="['px-3 py-1 rounded-lg text-xs font-medium', filters.status === s ? 'bg-brand-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300']">
                    {{ s === '' ? 'All' : s.replace('_',' ') }}
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-5 py-3">Date / time</th>
                            <th class="px-5 py-3">Patient</th>
                            <th class="px-5 py-3">Doctor</th>
                            <th class="px-5 py-3">Service</th>
                            <th class="px-5 py-3">Visit</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="a in appointments.data" :key="a.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-5 py-3">{{ dayjs(a.scheduled_at).format('DD MMM, hh:mm A') }}</td>
                            <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ a.patient?.first_name }} {{ a.patient?.last_name }}</td>
                            <td class="px-5 py-3">{{ a.doctor?.user?.name }}</td>
                            <td class="px-5 py-3">{{ a.service?.name }}</td>
                            <td class="px-5 py-3 capitalize">{{ a.visit_type?.replace('_',' ') }}</td>
                            <td class="px-5 py-3"><StatusPill :status="a.status" /></td>
                            <td class="px-5 py-3 text-right"><Link :href="route('appointments.show', a.id)" class="text-brand-600 hover:underline text-sm">View</Link></td>
                        </tr>
                        <tr v-if="!appointments.data.length"><td colspan="7" class="px-5 py-10 text-center text-slate-500">No appointments.</td></tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </PreclinicLayout>
</template>
