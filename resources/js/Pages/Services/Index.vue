<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ services: Object });
const showAdd = ref(false);
const form = useForm({ name: '', description: '', duration_minutes: 30, price_cents: 0 });
const submit = () => form.post(route('services.store'), { onSuccess: () => { form.reset(); showAdd.value = false; } });
const money = (c) => '$' + (c / 100).toFixed(2);
</script>

<template>
    <Head title="Services" />
    <PreclinicLayout title="Services">
        <template #actions>
            <button @click="showAdd = !showAdd" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> Add Service
            </button>
        </template>
        <Card v-if="showAdd" title="New Service" class="mb-5">
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input v-model="form.name" label="Name" />
                <Input v-model.number="form.duration_minutes" type="number" label="Duration (min)" />
                <Input v-model.number="form.price_cents" type="number" label="Price (cents)" />
                <label class="block md:col-span-2"><span class="block text-sm font-medium mb-1">Description</span>
                    <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>
                <div class="md:col-span-2 flex justify-end"><button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium">Save</button></div>
            </form>
        </Card>
        <Card padding="p-0">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr><th class="px-5 py-3">Service</th><th class="px-5 py-3">Duration</th><th class="px-5 py-3">Price</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="s in services.data" :key="s.id">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ s.name }}</td>
                        <td class="px-5 py-3">{{ s.duration_minutes }} min</td>
                        <td class="px-5 py-3">{{ money(s.price_cents) }}</td>
                    </tr>
                    <tr v-if="!services.data.length"><td colspan="3" class="px-5 py-10 text-center text-slate-500">No services yet.</td></tr>
                </tbody>
            </table>
        </Card>
    </PreclinicLayout>
</template>
