<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    referrals: Object,
    patients: Array,
    doctors: Array,
});

const showForm = ref(false);
const form = useForm({
    patient_id: '',
    referring_doctor_id: '',
    referred_to: '',
    specialty: '',
    reason: '',
    urgency: 'routine',
    referred_on: new Date().toISOString().slice(0, 10),
    appointment_date: '',
    notes: '',
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

const urgencyColors = {
    emergency: 'text-danger font-semibold',
    urgent: 'text-orange-600 dark:text-orange-400 font-medium',
    routine: 'text-slate-600 dark:text-slate-400',
};

const submit = () => form.post(route('referrals.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });
const updateStatus = (id, status) => useForm({ status }).patch(route('referrals.status', id));
</script>

<template>
    <Head title="Referrals" />
    <SmartHealthLayout title="Patient Referrals">
        <template #actions>
            <button @click="showForm = !showForm"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> New Referral
            </button>
        </template>

        <Card v-if="showForm" title="New Referral" class="mb-5">
            <form @submit.prevent="submit" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Patient <span class="text-red-500">*</span></span>
                    <SearchableSelect v-model="form.patient_id" :options="patientOptions" placeholder="Search patients…" :required="true" />
                    <span v-if="form.errors.patient_id" class="text-xs text-red-600">{{ form.errors.patient_id }}</span>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Referring Doctor</span>
                    <SearchableSelect v-model="form.referring_doctor_id" :options="doctorOptions" placeholder="Search doctors…" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Referred To <span class="text-red-500">*</span></span>
                    <input v-model="form.referred_to" required placeholder="Specialist name or facility" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Specialty</span>
                    <input v-model="form.specialty" placeholder="e.g. Cardiology" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Urgency <span class="text-red-500">*</span></span>
                    <select v-model="form.urgency" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Referred On</span>
                    <input v-model="form.referred_on" type="date" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block sm:col-span-2 lg:col-span-3">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reason <span class="text-red-500">*</span></span>
                    <textarea v-model="form.reason" required rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-3 justify-end">
                    <button type="button" @click="showForm = false" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">Create Referral</button>
                </div>
            </form>
        </Card>

        <Card padding="p-0">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-3">Patient</th>
                        <th class="px-5 py-3">Referred To</th>
                        <th class="px-5 py-3">Specialty</th>
                        <th class="px-5 py-3">Urgency</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="r in referrals.data" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ r.patient }}</td>
                        <td class="px-5 py-3">{{ r.referred_to }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ r.specialty ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs capitalize" :class="urgencyColors[r.urgency]">{{ r.urgency }}</span>
                        </td>
                        <td class="px-5 py-3"><StatusPill :status="r.status" /></td>
                        <td class="px-5 py-3 text-slate-500">{{ r.referred_on }}</td>
                        <td class="px-5 py-3">
                            <select v-if="r.status !== 'completed' && r.status !== 'declined'"
                                @change="updateStatus(r.id, $event.target.value)"
                                :value="r.status"
                                class="text-xs px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <option value="pending">Pending</option>
                                <option value="accepted">Accepted</option>
                                <option value="completed">Completed</option>
                                <option value="declined">Declined</option>
                            </select>
                            <span v-else class="text-xs text-slate-400 capitalize">{{ r.status }}</span>
                        </td>
                    </tr>
                    <tr v-if="!referrals.data.length">
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">No referrals yet.</td>
                    </tr>
                </tbody>
            </table>
        </Card>
    </SmartHealthLayout>
</template>
