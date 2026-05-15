<script setup>
import { computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { CalendarDaysIcon, ClockIcon, MapPinIcon, UserIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    patient:   { type: Object, default: null },
    doctors:   { type: Array,  default: () => [] },
    locations: { type: Array,  default: () => [] },
    services:  { type: Array,  default: () => [] },
});

const form = useForm({
    doctor_id:        '',
    location_id:      '',
    service_id:       '',
    scheduled_at:     '',
    duration_minutes: 30,
    visit_type:       'in_person',
    reason:           '',
});

const doctorOptions = computed(() => props.doctors.map(d => ({
    value: d.id,
    label: d.name,
})));

const locationOptions = computed(() => props.locations.map(l => ({
    value: l.id,
    label: l.name,
})));

const serviceOptions = computed(() => props.services.map(s => ({
    value: s.id,
    label: s.name,
    sublabel: `${s.duration_minutes} min · $${(s.price_cents / 100).toFixed(2)}`,
})));

const selectedService = computed(() => props.services.find(s => s.id == form.service_id));
const selectedDoctor  = computed(() => props.doctors.find(d => d.id == form.doctor_id));

watch(() => form.service_id, (id) => {
    const s = props.services.find(x => x.id == id);
    if (s) form.duration_minutes = s.duration_minutes;
});

// Minimum datetime: now + 1 hour, rounded to next 15 min
const minDateTime = computed(() => {
    const d = new Date(Date.now() + 60 * 60 * 1000);
    d.setMinutes(Math.ceil(d.getMinutes() / 15) * 15, 0, 0);
    return d.toISOString().slice(0, 16);
});

const submit = () => form.post(route('appointments.storeBooking'));
</script>

<template>
    <Head title="Book Appointment" />
    <SmartHealthLayout
        title="Book an Appointment"
        :breadcrumbs="[{ label: 'Appointments', href: route('appointments.index') }, { label: 'Book' }]"
    >
        <div class="max-w-2xl mx-auto space-y-5">
            <!-- Patient card -->
            <div v-if="patient" class="flex items-center gap-3 rounded-2xl bg-brand-50 dark:bg-brand-900/20 border border-brand-100 dark:border-brand-800 px-5 py-4">
                <span class="grid h-10 w-10 place-items-center rounded-full bg-brand-600 text-white font-semibold text-sm shrink-0">
                    {{ patient.name?.[0]?.toUpperCase() ?? '?' }}
                </span>
                <div>
                    <p class="text-sm font-semibold text-brand-900 dark:text-brand-100">{{ patient.name }}</p>
                    <p class="text-xs text-brand-600 dark:text-brand-300">Booking for yourself</p>
                </div>
            </div>

            <Card>
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Doctor -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            <UserIcon class="inline h-4 w-4 mr-1 -mt-0.5" />
                            Doctor <span class="text-red-500">*</span>
                        </label>
                        <SearchableSelect
                            v-model="form.doctor_id"
                            :options="doctorOptions"
                            placeholder="Choose a doctor…"
                            :required="true"
                        />
                        <p v-if="form.errors.doctor_id" class="mt-1 text-xs text-red-600">{{ form.errors.doctor_id }}</p>
                    </div>

                    <!-- Service -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Service</label>
                        <SearchableSelect
                            v-model="form.service_id"
                            :options="serviceOptions"
                            placeholder="Select a service…"
                        />
                    </div>

                    <!-- Location -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                            <MapPinIcon class="inline h-4 w-4 mr-1 -mt-0.5" />
                            Location
                        </label>
                        <SearchableSelect
                            v-model="form.location_id"
                            :options="locationOptions"
                            placeholder="Any location"
                        />
                    </div>

                    <!-- Date/time + visit type row -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
                                <CalendarDaysIcon class="inline h-4 w-4 mr-1 -mt-0.5" />
                                Preferred date & time <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.scheduled_at"
                                type="datetime-local"
                                :min="minDateTime"
                                required
                                class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-brand-500"
                            />
                            <p v-if="form.errors.scheduled_at" class="mt-1 text-xs text-red-600">{{ form.errors.scheduled_at }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Visit type</label>
                            <div class="flex gap-2 mt-1">
                                <button
                                    type="button"
                                    @click="form.visit_type = 'in_person'"
                                    :class="form.visit_type === 'in_person'
                                        ? 'bg-brand-600 text-white border-brand-600'
                                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-brand-400'"
                                    class="flex-1 px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                                >
                                    In Person
                                </button>
                                <button
                                    type="button"
                                    @click="form.visit_type = 'telehealth'"
                                    :class="form.visit_type === 'telehealth'
                                        ? 'bg-brand-600 text-white border-brand-600'
                                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700 hover:border-brand-400'"
                                    class="flex-1 px-3 py-2 rounded-lg border text-sm font-medium transition-colors"
                                >
                                    Telehealth
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Duration display -->
                    <div v-if="selectedService" class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
                        <ClockIcon class="h-4 w-4 shrink-0" />
                        Estimated duration: <span class="font-medium text-slate-700 dark:text-slate-200">{{ form.duration_minutes }} minutes</span>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">Reason for visit</label>
                        <textarea
                            v-model="form.reason"
                            rows="3"
                            placeholder="Briefly describe why you're booking this appointment…"
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 resize-none"
                        />
                    </div>

                    <!-- Summary preview -->
                    <div v-if="form.doctor_id && form.scheduled_at" class="rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 px-4 py-3 text-sm space-y-1">
                        <p class="font-semibold text-slate-700 dark:text-slate-200 mb-2">Booking summary</p>
                        <p class="text-slate-600 dark:text-slate-400">
                            <span class="font-medium">Doctor:</span> {{ selectedDoctor?.name ?? '—' }}
                        </p>
                        <p class="text-slate-600 dark:text-slate-400">
                            <span class="font-medium">Date:</span>
                            {{ form.scheduled_at ? new Date(form.scheduled_at).toLocaleString('en-US', { weekday:'short', month:'short', day:'numeric', year:'numeric', hour:'numeric', minute:'2-digit' }) : '—' }}
                        </p>
                        <p class="text-slate-600 dark:text-slate-400">
                            <span class="font-medium">Type:</span> {{ form.visit_type === 'in_person' ? 'In Person' : 'Telehealth' }}
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-1">
                        <Link
                            :href="route('appointments.index')"
                            class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing || !form.doctor_id || !form.scheduled_at"
                            class="px-5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium disabled:opacity-50 transition-colors"
                        >
                            {{ form.processing ? 'Booking…' : 'Confirm Booking' }}
                        </button>
                    </div>
                </form>
            </Card>
        </div>
    </SmartHealthLayout>
</template>
