<script setup>
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';

const props = defineProps({ patient: Object, insuranceProviders: Array });

const primaryInsurance = props.patient.insurances?.find(i => i.is_primary);

const form = useForm({
    first_name: props.patient.first_name || '',
    last_name: props.patient.last_name || '',
    email: props.patient.email || '',
    phone: props.patient.phone || '',
    dob: props.patient.dob ? props.patient.dob.slice(0, 10) : '',
    gender: props.patient.gender || '',
    blood_group: props.patient.blood_group || '',
    address: props.patient.address || '',
    city: props.patient.city || '',
    state: props.patient.state || '',
    insurance_provider_id: primaryInsurance?.insurance_provider_id || '',
    insurance_plan_id: primaryInsurance?.insurance_plan_id || '',
    policy_number: primaryInsurance?.policy_number || '',
    group_number: primaryInsurance?.group_number || '',
});

const submit = () => form.put(route('patients.update', props.patient.id));

const selectedProvider = computed(() =>
    props.insuranceProviders?.find(p => p.id == form.insurance_provider_id)
);
const plans = computed(() => selectedProvider.value?.plans || []);
</script>

<template>
    <Head :title="`Edit ${patient.first_name} ${patient.last_name}`" />
    <SmartHealthLayout
        :title="`Edit ${patient.first_name} ${patient.last_name}`"
        :breadcrumbs="[{ label: 'Patients', href: route('patients.index') }, { label: patient.first_name + ' ' + patient.last_name, href: route('patients.show', patient.id) }, { label: 'Edit' }]"
    >
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

                <!-- Insurance section -->
                <div class="md:col-span-2 border-t border-slate-200 dark:border-slate-800 pt-4 mt-2">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Insurance Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block">
                            <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Insurance Provider</span>
                            <select v-model="form.insurance_provider_id" @change="form.insurance_plan_id = ''" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                                <option value="">— None —</option>
                                <option v-for="p in insuranceProviders" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Plan</span>
                            <select v-model="form.insurance_plan_id" :disabled="!plans.length" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm disabled:opacity-50">
                                <option value="">— Select plan —</option>
                                <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
                            </select>
                        </label>
                        <Input v-model="form.policy_number" label="Policy number" :error="form.errors.policy_number" />
                        <Input v-model="form.group_number" label="Group number" :error="form.errors.group_number" />
                    </div>
                </div>

                <div class="md:col-span-2 flex gap-2 justify-end pt-2">
                    <Link :href="route('patients.show', patient.id)" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium disabled:opacity-50">
                        {{ form.processing ? 'Saving…' : 'Update Patient' }}
                    </button>
                </div>
            </form>
        </Card>
    </SmartHealthLayout>
</template>
