<script setup>
import { computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({ patients: Array, doctors: Array, locations: Array, services: Array });

const form = useForm({
    patient_id: '', doctor_id: '', location_id: '', service_id: '',
    scheduled_at: '', duration_minutes: 30, visit_type: 'in_person', reason: '',
});

const patientOptions = computed(() => props.patients.map(p => ({
    value: p.id,
    label: `${p.last_name}, ${p.first_name}`,
    sublabel: p.email,
})));

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
    sublabel: `${s.duration_minutes} min`,
})));

watch(() => form.service_id, (id) => {
    const s = props.services.find(x => x.id == id);
    if (s) form.duration_minutes = s.duration_minutes;
});

const submit = () => form.post(route('appointments.store'));
</script>

<template>
    <Head title="New Appointment" />
    <PreclinicLayout title="New Appointment" :breadcrumbs="[{ label: 'Appointments', href: route('appointments.index') }, { label: 'New' }]">
        <Card>
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="block text-sm font-medium mb-1">Patient <span class="text-red-500">*</span></span>
                    <SearchableSelect
                        v-model="form.patient_id"
                        :options="patientOptions"
                        placeholder="Search patients…"
                        :required="true"
                    />
                    <span v-if="form.errors.patient_id" class="block mt-1 text-xs text-red-600">{{ form.errors.patient_id }}</span>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium mb-1">Doctor <span class="text-red-500">*</span></span>
                    <SearchableSelect
                        v-model="form.doctor_id"
                        :options="doctorOptions"
                        placeholder="Search doctors…"
                        :required="true"
                    />
                    <span v-if="form.errors.doctor_id" class="block mt-1 text-xs text-red-600">{{ form.errors.doctor_id }}</span>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium mb-1">Location</span>
                    <SearchableSelect
                        v-model="form.location_id"
                        :options="locationOptions"
                        placeholder="Any location"
                    />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium mb-1">Service</span>
                    <SearchableSelect
                        v-model="form.service_id"
                        :options="serviceOptions"
                        placeholder="Select service…"
                    />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium mb-1">Scheduled at <span class="text-red-500">*</span></span>
                    <input v-model="form.scheduled_at" type="datetime-local" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    <span v-if="form.errors.scheduled_at" class="block mt-1 text-xs text-red-600">{{ form.errors.scheduled_at }}</span>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium mb-1">Duration (min)</span>
                    <input v-model.number="form.duration_minutes" type="number" min="5" max="480" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium mb-1">Visit type</span>
                    <select v-model="form.visit_type" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                        <option value="in_person">In person</option>
                        <option value="telehealth">Telehealth</option>
                    </select>
                </label>

                <label class="block md:col-span-2">
                    <span class="block text-sm font-medium mb-1">Reason</span>
                    <input v-model="form.reason" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <div class="md:col-span-2 flex justify-end gap-2">
                    <Link :href="route('appointments.index')" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                        {{ form.processing ? 'Saving…' : 'Schedule' }}
                    </button>
                </div>
            </form>
        </Card>
    </PreclinicLayout>
</template>
