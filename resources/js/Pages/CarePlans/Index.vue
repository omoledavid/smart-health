<script setup>
import { computed, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
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
    <SmartHealthLayout title="Care Plans">
        <template #actions>
            <button v-if="patients.length" @click="showForm = !showForm"
                class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-medium whitespace-nowrap">
                <PlusIcon class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0" /> New Care Plan
            </button>
        </template>

        <!-- Create form -->
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

        <!-- Mobile card list -->
        <div class="sm:hidden space-y-3">
            <div v-for="p in plans.data" :key="p.id"
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-start justify-between gap-2">
                    <p class="font-semibold text-slate-900 dark:text-white leading-snug">{{ p.title }}</p>
                    <StatusPill :status="p.status" class="shrink-0" />
                </div>
                <div class="mt-2 space-y-1 text-sm text-slate-600 dark:text-slate-400">
                    <p><span class="text-xs text-slate-400">Patient:</span> {{ p.patient }}</p>
                    <p v-if="p.doctor"><span class="text-xs text-slate-400">Doctor:</span> {{ p.doctor }}</p>
                    <div class="grid grid-cols-2 gap-2 mt-1">
                        <div>
                            <p class="text-xs text-slate-400">Tasks</p>
                            <p class="font-medium text-slate-900 dark:text-white">{{ p.tasks_count }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Review date</p>
                            <p>{{ p.review_date ?? '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3 text-right">
                    <Link :href="route('care-plans.show', p.id)" class="text-brand-600 hover:underline text-sm font-medium">View →</Link>
                </div>
            </div>
            <p v-if="!plans.data.length" class="text-center text-slate-500 text-sm py-12">No care plans yet.</p>
        </div>

        <!-- Desktop table -->
        <Card padding="p-0" class="hidden sm:block">
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
    </SmartHealthLayout>
</template>
