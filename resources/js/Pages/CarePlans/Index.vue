<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    plans: Object,
    patients: { type: Array, default: () => [] },
    doctors: { type: Array, default: () => [] },
});

const showForm = ref(false);
const form = useForm({
    patient_id: '',
    doctor_id: '',
    title: '',
    goals: '',
    interventions: '',
    start_date: new Date().toISOString().slice(0, 10),
    review_date: '',
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

const submit = () => form.post(route('care-plans.store'), { onSuccess: () => { form.reset(); showForm.value = false; } });
</script>

<template>
    <Head title="Care Plans" />
    <PreclinicLayout title="Care Plans">
        <template #actions>
            <button v-if="patients.length" @click="showForm = !showForm"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> New Care Plan
            </button>
        </template>

        <Card v-if="showForm" title="New Care Plan" class="mb-5">
            <form @submit.prevent="submit" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Patient <span class="text-red-500">*</span></span>
                    <SearchableSelect v-model="form.patient_id" :options="patientOptions" placeholder="Search patients…" :required="true" />
                    <span v-if="form.errors.patient_id" class="text-xs text-red-600">{{ form.errors.patient_id }}</span>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Assigned Doctor</span>
                    <SearchableSelect v-model="form.doctor_id" :options="doctorOptions" placeholder="Unassigned" />
                </label>

                <label class="block sm:col-span-2">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title <span class="text-red-500">*</span></span>
                    <input v-model="form.title" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Goals</span>
                    <textarea v-model="form.goals" rows="3" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Interventions</span>
                    <textarea v-model="form.interventions" rows="3" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Start Date</span>
                    <input v-model="form.start_date" type="date" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Review Date</span>
                    <input v-model="form.review_date" type="date" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>

                <div class="flex gap-2 sm:col-span-2 justify-end">
                    <button type="button" @click="showForm = false" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">Create Plan</button>
                </div>
            </form>
        </Card>

        <Card padding="p-0">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-3">Title</th>
                        <th class="px-5 py-3">Patient</th>
                        <th class="px-5 py-3">Doctor</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Tasks</th>
                        <th class="px-5 py-3">Review</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="p in plans.data" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ p.title }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ p.patient }}</td>
                        <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ p.doctor ?? '—' }}</td>
                        <td class="px-5 py-3"><StatusPill :status="p.status" /></td>
                        <td class="px-5 py-3 text-slate-500">{{ p.tasks_count }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ p.review_date ?? '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <Link :href="route('care-plans.show', p.id)" class="text-brand-600 hover:underline">View →</Link>
                        </td>
                    </tr>
                    <tr v-if="!plans.data.length">
                        <td colspan="7" class="px-5 py-12 text-center text-slate-500">No care plans yet.</td>
                    </tr>
                </tbody>
            </table>
        </Card>
    </PreclinicLayout>
</template>
