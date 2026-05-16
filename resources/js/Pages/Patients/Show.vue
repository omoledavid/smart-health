<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import dayjs from 'dayjs';
import { PencilSquareIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ patient: Object });
const tab = ref('overview');
const tabs = ['overview', 'insurance', 'appointments', 'notes'];

const user = computed(() => usePage().props.auth?.user);
const isAdmin = computed(() => user.value?.role === 'admin');
</script>

<template>
    <Head :title="patient.full_name" />
    <SmartHealthLayout :title="patient.first_name + ' ' + patient.last_name" :breadcrumbs="[{ label: 'Patients', href: route('patients.index') }, { label: patient.first_name + ' ' + patient.last_name }]">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Profile sidebar -->
            <Card>
                <div class="flex flex-col items-center text-center pb-4">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-brand-100 dark:bg-brand-900/40 text-brand-700 dark:text-brand-300 text-xl font-bold">
                        {{ patient.first_name?.[0] }}{{ patient.last_name?.[0] }}
                    </span>
                    <h2 class="mt-3 text-lg font-bold text-slate-900 dark:text-white">{{ patient.first_name }} {{ patient.last_name }}</h2>
                    <p class="text-xs font-mono text-slate-500">{{ patient.mrn }}</p>
                </div>
                <dl class="text-sm space-y-2 border-t border-slate-100 dark:border-slate-800 pt-3">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">DOB</dt>
                        <dd class="text-slate-700 dark:text-slate-200 text-right">{{ patient.dob ? dayjs(patient.dob).format('DD MMM YYYY') : '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Gender</dt>
                        <dd class="text-slate-700 dark:text-slate-200 capitalize text-right">{{ patient.gender ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Blood group</dt>
                        <dd class="text-slate-700 dark:text-slate-200 text-right">{{ patient.blood_group ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Phone</dt>
                        <dd class="text-slate-700 dark:text-slate-200 text-right">{{ patient.phone ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Email</dt>
                        <dd class="text-slate-700 dark:text-slate-200 break-all text-right">{{ patient.email ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500 shrink-0">Address</dt>
                        <dd class="text-slate-700 dark:text-slate-200 text-right">{{ patient.address ? `${patient.address}, ${patient.city}` : '—' }}</dd>
                    </div>
                </dl>
                <Link v-if="isAdmin" :href="route('patients.edit', patient.id)"
                    class="mt-4 w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-sm font-medium">
                    <PencilSquareIcon class="h-4 w-4" /> Edit Patient
                </Link>
            </Card>

            <!-- Tabs panel -->
            <div class="lg:col-span-2">
                <Card padding="p-0">
                    <!-- Tab nav — scrollable on mobile -->
                    <div class="border-b border-slate-200 dark:border-slate-800 px-2 sm:px-4 overflow-x-auto">
                        <nav class="flex gap-1 min-w-max">
                            <button
                                v-for="t in tabs"
                                :key="t"
                                @click="tab = t"
                                :class="['px-3 sm:px-4 py-3 text-sm font-medium capitalize border-b-2 whitespace-nowrap', tab === t ? 'border-brand-600 text-brand-600' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300']"
                            >{{ t }}</button>
                        </nav>
                    </div>

                    <div class="p-4 sm:p-5">

                        <!-- Overview -->
                        <div v-if="tab === 'overview'">
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">Allergies</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">{{ patient.allergies || 'None recorded.' }}</p>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-2">Medical history</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ patient.medical_history || 'No history on file.' }}</p>
                        </div>

                        <!-- Insurance -->
                        <div v-if="tab === 'insurance'">
                            <ul class="space-y-3">
                                <li v-for="i in patient.insurances" :key="i.id"
                                    class="rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-semibold text-slate-900 dark:text-white">{{ i.provider_name }}</p>
                                        <span v-if="i.is_primary" class="text-xs bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 px-2 py-0.5 rounded shrink-0">Primary</span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">
                                        Policy {{ i.policy_number }}{{ i.group_number ? ' · Group ' + i.group_number : '' }}
                                    </p>
                                </li>
                                <li v-if="!patient.insurances?.length" class="text-sm text-slate-500">No insurance on file.</li>
                            </ul>
                        </div>

                        <!-- Appointments -->
                        <div v-if="tab === 'appointments'">
                            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                                <li v-for="a in patient.appointments" :key="a.id"
                                    class="py-3 flex flex-wrap sm:flex-nowrap items-start sm:items-center gap-3">
                                    <div class="w-full sm:w-24 flex sm:flex-col gap-2 sm:gap-0">
                                        <p class="text-xs text-slate-500">{{ dayjs(a.scheduled_at).format('DD MMM YYYY') }}</p>
                                        <p class="text-xs text-slate-400">{{ dayjs(a.scheduled_at).format('hh:mm A') }}</p>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ a.doctor?.user?.name }}</p>
                                        <p class="text-xs text-slate-500">{{ a.service?.name ?? '—' }}</p>
                                    </div>
                                    <StatusPill :status="a.status" class="shrink-0" />
                                </li>
                                <li v-if="!patient.appointments?.length" class="py-6 text-sm text-slate-500 text-center">No appointments yet.</li>
                            </ul>
                        </div>

                        <!-- Notes -->
                        <div v-if="tab === 'notes'">
                            <ul class="space-y-3">
                                <li v-for="c in patient.consultations" :key="c.id"
                                    class="rounded-lg border border-slate-200 dark:border-slate-700 p-4">
                                    <div class="flex items-start justify-between gap-2">
                                        <Link :href="route('consultations.show', c.id)"
                                            class="font-semibold text-slate-900 dark:text-white hover:text-brand-600">
                                            Consultation #{{ c.id }}
                                        </Link>
                                        <StatusPill :status="c.status" class="shrink-0" />
                                    </div>
                                    <p class="text-xs text-slate-500 mt-1">{{ dayjs(c.created_at).format('DD MMM YYYY') }}</p>
                                </li>
                                <li v-if="!patient.consultations?.length" class="text-sm text-slate-500">No clinical notes yet.</li>
                            </ul>
                        </div>

                    </div>
                </Card>
            </div>
        </div>
    </SmartHealthLayout>
</template>
