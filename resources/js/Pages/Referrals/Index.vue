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

const urgencyBadge = {
    emergency: 'bg-danger/10 text-danger',
    urgent: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    routine: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
};

const submit = () => form.post(route('referrals.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });
const updateStatus = (id, status) => useForm({ status }).patch(route('referrals.status', id));
</script>

<template>
    <Head title="Referrals" />
    <SmartHealthLayout title="Patient Referrals">
        <template #actions>
            <button @click="showForm = !showForm"
                class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-medium whitespace-nowrap">
                <PlusIcon class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0" /> New Referral
            </button>
        </template>

        <!-- Create form -->
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
                    <input v-model="form.referred_to" required placeholder="Specialist name or facility"
                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Specialty</span>
                    <input v-model="form.specialty" placeholder="e.g. Cardiology"
                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Urgency <span class="text-red-500">*</span></span>
                    <select v-model="form.urgency" required
                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                        <option value="routine">Routine</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Referred On</span>
                    <input v-model="form.referred_on" type="date"
                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block sm:col-span-2 lg:col-span-3">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Reason <span class="text-red-500">*</span></span>
                    <textarea v-model="form.reason" required rows="2"
                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-3 justify-end">
                    <button type="button" @click="showForm = false" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">Create Referral</button>
                </div>
            </form>
        </Card>

        <!-- Mobile card list -->
        <div class="sm:hidden space-y-3">
            <div v-for="r in referrals.data" :key="r.id"
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 dark:text-white truncate">{{ r.patient }}</p>
                        <p class="text-sm text-slate-600 dark:text-slate-400 truncate">→ {{ r.referred_to }}</p>
                    </div>
                    <StatusPill :status="r.status" class="shrink-0" />
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-xs text-slate-400">Specialty</p>
                        <p class="text-slate-700 dark:text-slate-300">{{ r.specialty ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Date</p>
                        <p class="text-slate-700 dark:text-slate-300">{{ r.referred_on }}</p>
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between gap-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize" :class="urgencyBadge[r.urgency]">
                        {{ r.urgency }}
                    </span>
                    <select v-if="r.status !== 'completed' && r.status !== 'declined'"
                        @change="updateStatus(r.id, $event.target.value)"
                        :value="r.status"
                        class="text-xs px-2 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="completed">Completed</option>
                        <option value="declined">Declined</option>
                    </select>
                    <span v-else class="text-xs text-slate-400 capitalize">{{ r.status }}</span>
                </div>
            </div>
            <p v-if="!referrals.data.length" class="text-center text-slate-500 text-sm py-12">No referrals yet.</p>
        </div>

        <!-- Desktop table -->
        <Card padding="p-0" class="hidden sm:block">
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
