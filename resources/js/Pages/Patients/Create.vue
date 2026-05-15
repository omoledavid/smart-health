<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';

const form = useForm({
    first_name: '', last_name: '', email: '', phone: '',
    dob: '', gender: '', blood_group: '',
    address: '', city: '', state: '',
});
const submit = () => form.post(route('patients.store'));
</script>

<template>
    <Head title="Add Patient" />
    <PreclinicLayout title="Add Patient" :breadcrumbs="[{ label: 'Patients', href: route('patients.index') }, { label: 'Add' }]">
        <Card>
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input v-model="form.first_name" label="First name" :error="form.errors.first_name" />
                <Input v-model="form.last_name" label="Last name" :error="form.errors.last_name" />
                <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                <Input v-model="form.phone" label="Phone" :error="form.errors.phone" />
                <Input v-model="form.dob" type="date" label="Date of birth" :error="form.errors.dob" />
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Gender</span>
                    <select v-model="form.gender" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                        <option value="">—</option>
                        <option>male</option><option>female</option><option>other</option>
                    </select>
                </label>
                <Input v-model="form.blood_group" label="Blood group" />
                <Input v-model="form.address" label="Address" />
                <Input v-model="form.city" label="City" />
                <Input v-model="form.state" label="State" />
                <div class="md:col-span-2 flex gap-2 justify-end pt-2">
                    <Link :href="route('patients.index')" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium disabled:opacity-50">
                        {{ form.processing ? 'Saving…' : 'Save Patient' }}
                    </button>
                </div>
            </form>
        </Card>
    </PreclinicLayout>
</template>
