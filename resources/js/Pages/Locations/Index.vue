<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ locations: Object });
const showAdd = ref(false);
const form = useForm({ name: '', address: '', city: '', state: '', postal_code: '', phone: '', timezone: 'America/Los_Angeles' });
const submit = () => form.post(route('locations.store'), { onSuccess: () => { form.reset(); showAdd.value = false; } });
</script>

<template>
    <Head title="Locations" />
    <SmartHealthLayout title="Locations">
        <template #actions>
            <button @click="showAdd = !showAdd" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> Add Location
            </button>
        </template>
        <Card v-if="showAdd" title="New Location" class="mb-5">
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input v-model="form.name" label="Name" />
                <Input v-model="form.phone" label="Phone" />
                <Input v-model="form.address" label="Address" />
                <Input v-model="form.city" label="City" />
                <Input v-model="form.state" label="State" />
                <Input v-model="form.postal_code" label="Postal code" />
                <div class="md:col-span-2 flex justify-end">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium">Save</button>
                </div>
            </form>
        </Card>

        <Card padding="p-0">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr><th class="px-5 py-3">Name</th><th class="px-5 py-3">Address</th><th class="px-5 py-3">Phone</th><th class="px-5 py-3">Timezone</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="l in locations.data" :key="l.id">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">{{ l.name }}</td>
                        <td class="px-5 py-3">{{ l.address }}, {{ l.city }} {{ l.state }}</td>
                        <td class="px-5 py-3">{{ l.phone }}</td>
                        <td class="px-5 py-3">{{ l.timezone }}</td>
                    </tr>
                    <tr v-if="!locations.data.length"><td colspan="4" class="px-5 py-10 text-center text-slate-500">No locations yet.</td></tr>
                </tbody>
            </table>
        </Card>
    </SmartHealthLayout>
</template>
