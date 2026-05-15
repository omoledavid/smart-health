<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';

defineProps({ consultations: { type: Array, default: () => [] } });

const showForm = ref(false);
const form = useForm({ patient_name: '', raw_notes: '' });
const submit = () => form.post(route('consultations.store'), { onSuccess: () => form.reset() });
</script>

<template>
    <Head title="Consultations" />
    <PreclinicLayout title="Consultations">
        <template #actions>
            <button @click="showForm = !showForm" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> {{ showForm ? 'Cancel' : 'New consultation' }}
            </button>
        </template>

        <Card v-if="showForm" title="New consultation" class="mb-5">
            <form @submit.prevent="submit" class="space-y-3">
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Patient name</span>
                    <input v-model="form.patient_name" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    <span v-if="form.errors.patient_name" class="text-xs text-red-600">{{ form.errors.patient_name }}</span>
                </label>
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Initial notes</span>
                    <textarea v-model="form.raw_notes" rows="4" required placeholder="Short summary or shorthand — you can dictate more on the next screen."
                        class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                    <span v-if="form.errors.raw_notes" class="text-xs text-red-600">{{ form.errors.raw_notes }}</span>
                </label>
                <div class="flex justify-end">
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">
                        {{ form.processing ? 'Saving…' : 'Create' }}
                    </button>
                </div>
            </form>
        </Card>

        <Card padding="p-0">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr><th class="px-5 py-3">Patient</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Created</th><th class="px-5 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="c in consultations" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ c.patient_name }}</td>
                        <td class="px-5 py-3"><StatusPill :status="c.status" /></td>
                        <td class="px-5 py-3 text-slate-500">{{ dayjs(c.created_at).format('DD MMM YYYY, hh:mm A') }}</td>
                        <td class="px-5 py-3 text-right"><Link :href="route('consultations.show', c.id)" class="text-brand-600 hover:underline">Open →</Link></td>
                    </tr>
                    <tr v-if="!consultations.length"><td colspan="4" class="px-5 py-12 text-center text-slate-500">No consultations yet.</td></tr>
                </tbody>
            </table>
        </Card>
    </PreclinicLayout>
</template>
