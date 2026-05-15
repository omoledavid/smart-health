<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { DocumentTextIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';

const props = defineProps({ appointment: Object });

const setStatus = (status) => router.patch(route('appointments.status', props.appointment.id), { status });

const consultForm = useForm({});
const startConsultation = () => consultForm.post(route('appointments.start-consultation', props.appointment.id));

const canStart = !props.appointment.consultation && ['scheduled', 'confirmed', 'in_progress'].includes(props.appointment.status);
</script>

<template>
    <Head :title="`Appointment #${appointment.id}`" />
    <PreclinicLayout
        :title="`Appointment #${appointment.id}`"
        :breadcrumbs="[{ label: 'Appointments', href: route('appointments.index') }, { label: '#' + appointment.id }]"
    >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Details card -->
            <Card class="lg:col-span-2" title="Details">
                <dl class="grid grid-cols-2 gap-y-4 text-sm">
                    <dt class="text-slate-500 dark:text-slate-400">When</dt>
                    <dd class="text-slate-900 dark:text-white">
                        {{ dayjs(appointment.scheduled_at).format('ddd, DD MMM YYYY · hh:mm A') }}
                        <span class="text-slate-400 dark:text-slate-500">({{ appointment.duration_minutes }} min)</span>
                    </dd>

                    <dt class="text-slate-500 dark:text-slate-400">Patient</dt>
                    <dd>
                        <Link :href="route('patients.show', appointment.patient?.id)" class="text-brand-600 hover:underline font-medium">
                            {{ appointment.patient?.first_name }} {{ appointment.patient?.last_name }}
                        </Link>
                    </dd>

                    <dt class="text-slate-500 dark:text-slate-400">Doctor</dt>
                    <dd class="text-slate-900 dark:text-white">{{ appointment.doctor?.user?.name ?? '—' }}</dd>

                    <dt class="text-slate-500 dark:text-slate-400">Location</dt>
                    <dd class="text-slate-900 dark:text-white">{{ appointment.location?.name ?? '—' }}</dd>

                    <dt class="text-slate-500 dark:text-slate-400">Service</dt>
                    <dd class="text-slate-900 dark:text-white">{{ appointment.service?.name ?? '—' }}</dd>

                    <dt class="text-slate-500 dark:text-slate-400">Visit type</dt>
                    <dd class="text-slate-900 dark:text-white capitalize">{{ appointment.visit_type?.replace('_', ' ') ?? '—' }}</dd>

                    <dt class="text-slate-500 dark:text-slate-400">Status</dt>
                    <dd><StatusPill :status="appointment.status" /></dd>

                    <dt class="text-slate-500 dark:text-slate-400">Reason</dt>
                    <dd class="text-slate-900 dark:text-white">{{ appointment.reason || '—' }}</dd>
                </dl>
            </Card>

            <!-- Actions card -->
            <Card title="Actions">
                <div class="space-y-2.5">
                    <!-- START CONSULTATION — primary action -->
                    <button
                        v-if="canStart"
                        @click="startConsultation"
                        :disabled="consultForm.processing"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold shadow-sm shadow-brand-600/30 transition-colors disabled:opacity-50"
                    >
                        <DocumentTextIcon class="h-4 w-4" />
                        {{ consultForm.processing ? 'Opening…' : 'Start Consultation' }}
                    </button>

                    <!-- Open existing consultation -->
                    <Link
                        v-if="appointment.consultation"
                        :href="route('consultations.show', appointment.consultation.id)"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-sm font-medium hover:bg-indigo-100 dark:hover:bg-indigo-900/50 transition-colors"
                    >
                        <DocumentTextIcon class="h-4 w-4" />
                        Open SOAP Note
                    </Link>

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-2.5 space-y-2">
                        <button
                            v-if="appointment.status !== 'completed'"
                            @click="setStatus('completed')"
                            class="w-full px-3 py-2 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 text-sm font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors"
                        >
                            Mark Complete
                        </button>
                        <button
                            v-if="!appointment.consultation && appointment.status !== 'no_show'"
                            @click="setStatus('no_show')"
                            class="w-full px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors"
                        >
                            No-show
                        </button>
                        <button
                            v-if="!appointment.consultation && appointment.status !== 'cancelled'"
                            @click="setStatus('cancelled')"
                            class="w-full px-3 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 text-sm font-medium hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </Card>
        </div>
    </PreclinicLayout>
</template>
