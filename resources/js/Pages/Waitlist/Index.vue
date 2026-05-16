<script setup>
import { computed, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    entries: Array,
    patients: Array,
    doctors: Array,
    services: Array,
});

const showForm = ref(false);
const form = useForm({
    patient_id: '',
    doctor_id: '',
    service_id: '',
    priority: 'normal',
    requested_on: new Date().toISOString().slice(0, 10),
    available_from: '',
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

const serviceOptions = computed(() => props.services.map(s => ({
    value: s.id,
    label: s.name,
})));

const priorityColors = {
    urgent: 'bg-danger/10 text-danger',
    high: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    normal: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
    low: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400',
};

const submit = () => form.post(route('waitlist.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });

const cancelEntry = (id) => {
    if (confirm('Cancel this waitlist entry?')) {
        useForm({}).patch(route('waitlist.cancel', id));
    }
};
</script>

<template>
    <Head title="Waitlist" />
    <SmartHealthLayout title="Patient Waitlist">
        <template #actions>
            <button @click="showForm = !showForm"
                class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-medium whitespace-nowrap">
                <PlusIcon class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0" /> Add to Waitlist
            </button>
        </template>

        <!-- Add form -->
        <Card v-if="showForm" title="Add Patient to Waitlist" class="mb-5">
            <form @submit.prevent="submit" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Patient <span class="text-red-500">*</span></span>
                    <SearchableSelect v-model="form.patient_id" :options="patientOptions" placeholder="Search patients…" :required="true" />
                    <span v-if="form.errors.patient_id" class="text-xs text-red-600">{{ form.errors.patient_id }}</span>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Preferred Doctor</span>
                    <SearchableSelect v-model="form.doctor_id" :options="doctorOptions" placeholder="Any available" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Service</span>
                    <SearchableSelect v-model="form.service_id" :options="serviceOptions" placeholder="Any service" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Priority <span class="text-red-500">*</span></span>
                    <select v-model="form.priority" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                        <option value="urgent">Urgent</option>
                        <option value="high">High</option>
                        <option value="normal">Normal</option>
                        <option value="low">Low</option>
                    </select>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Requested On</span>
                    <input v-model="form.requested_on" type="date" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Available From</span>
                    <input v-model="form.available_from" type="date" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block sm:col-span-2 lg:col-span-3">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</span>
                    <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>

                <div class="flex gap-2 sm:col-span-2 lg:col-span-3 justify-end">
                    <button type="button" @click="showForm = false" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">Add to Waitlist</button>
                </div>
            </form>
        </Card>

        <!-- Mobile card list -->
        <div class="sm:hidden space-y-3">
            <div v-for="e in entries" :key="e.id"
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-start justify-between gap-2">
                    <p class="font-semibold text-slate-900 dark:text-white">{{ e.patient }}</p>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize shrink-0" :class="priorityColors[e.priority]">
                        {{ e.priority }}
                    </span>
                </div>
                <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-400">
                    <p v-if="e.doctor"><span class="text-xs text-slate-400">Doctor:</span> {{ e.doctor }}</p>
                    <p v-if="e.service"><span class="text-xs text-slate-400">Service:</span> {{ e.service }}</p>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <div>
                            <p class="text-xs text-slate-400">Requested</p>
                            <p>{{ e.requested_on ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Available from</p>
                            <p>{{ e.available_from ?? '—' }}</p>
                        </div>
                    </div>
                    <p v-if="e.notes" class="text-xs text-slate-500 mt-1 line-clamp-2">{{ e.notes }}</p>
                </div>
                <div class="mt-3 text-right">
                    <button @click="cancelEntry(e.id)" class="text-xs text-red-500 hover:underline font-medium">Cancel entry</button>
                </div>
            </div>
            <p v-if="!entries.length" class="text-center text-slate-500 text-sm py-12">Waitlist is empty.</p>
        </div>

        <!-- Desktop table -->
        <Card padding="p-0" class="hidden sm:block">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-3">Priority</th>
                        <th class="px-5 py-3">Patient</th>
                        <th class="px-5 py-3">Doctor</th>
                        <th class="px-5 py-3">Service</th>
                        <th class="px-5 py-3">Requested</th>
                        <th class="px-5 py-3">Available From</th>
                        <th class="px-5 py-3">Notes</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="e in entries" :key="e.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium capitalize" :class="priorityColors[e.priority]">
                                {{ e.priority }}
                            </span>
                        </td>
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ e.patient }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ e.doctor ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ e.service ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ e.requested_on }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ e.available_from ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-500 max-w-xs truncate">{{ e.notes ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <button @click="cancelEntry(e.id)" class="text-xs text-slate-500 hover:text-red-600 dark:hover:text-red-400">Cancel</button>
                        </td>
                    </tr>
                    <tr v-if="!entries.length">
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">Waitlist is empty.</td>
                    </tr>
                </tbody>
            </table>
        </Card>
    </SmartHealthLayout>
</template>
