<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { SparklesIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ patient: Object, token: String });
const step = ref(props.patient.onboarding_step ?? 0);

const stepDefs = [
    { key: 'personal', label: 'Personal' },
    { key: 'contact', label: 'Contact' },
    { key: 'insurance', label: 'Insurance & history' },
    { key: 'consent', label: 'Consent' },
];

const form = useForm({
    step: 0,
    first_name: props.patient.first_name,
    last_name: props.patient.last_name,
    dob: props.patient.dob,
    gender: props.patient.gender,
    email: props.patient.email,
    phone: props.patient.phone,
    address: props.patient.address,
    city: props.patient.city,
    state: props.patient.state,
    postal_code: props.patient.postal_code,
    emergency_contact_name: '',
    emergency_contact_phone: '',
    insurance_provider: '',
    insurance_policy: '',
    allergies: '',
    medical_history: '',
    consent: false,
});

const submitStep = () => {
    form.step = step.value;
    form.post(route('onboarding.update', props.token), {
        preserveScroll: true,
        onSuccess: () => {
            if (step.value < 3) step.value += 1;
        },
    });
};

const completed = computed(() => step.value > 3);
</script>

<template>
    <Head title="Patient Onboarding" />
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 py-10 px-4">
        <div class="max-w-3xl mx-auto">
            <div class="flex items-center gap-2 mb-8 justify-center">
                <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-white">
                    <SparklesIcon class="h-5 w-5" />
                </span>
                <span class="text-xl font-semibold text-slate-900 dark:text-white">SmartHealth Onboarding</span>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <ol class="flex border-b border-slate-200 dark:border-slate-800">
                    <li v-for="(s, i) in stepDefs" :key="s.key" class="flex-1 p-4 text-center" :class="i === step ? 'bg-brand-50 dark:bg-brand-900/20' : ''">
                        <span class="inline-grid place-items-center h-7 w-7 rounded-full text-xs font-semibold mb-1"
                              :class="i < step ? 'bg-emerald-500 text-white' : i === step ? 'bg-brand-600 text-white' : 'bg-slate-200 dark:bg-slate-700 text-slate-500'">
                            <CheckIcon v-if="i < step" class="h-4 w-4" />
                            <span v-else>{{ i + 1 }}</span>
                        </span>
                        <p class="text-xs font-medium" :class="i === step ? 'text-brand-700 dark:text-brand-300' : 'text-slate-500'">{{ s.label }}</p>
                    </li>
                </ol>

                <div v-if="completed" class="p-10 text-center">
                    <span class="inline-grid place-items-center h-16 w-16 rounded-full bg-emerald-100 text-emerald-600 mb-4">
                        <CheckIcon class="h-8 w-8" />
                    </span>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">All set!</h2>
                    <p class="text-sm text-slate-500 mt-1">Your onboarding is complete. Your clinic will reach out for next steps.</p>
                </div>

                <form v-else @submit.prevent="submitStep" class="p-6 space-y-4">
                    <div v-if="step === 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block">
                            <span class="block text-sm font-medium mb-1">First name</span>
                            <input v-model="form.first_name" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium mb-1">Last name</span>
                            <input v-model="form.last_name" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium mb-1">Date of birth</span>
                            <input v-model="form.dob" type="date" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                        </label>
                        <label class="block">
                            <span class="block text-sm font-medium mb-1">Gender</span>
                            <select v-model="form.gender" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                                <option value="">—</option><option>male</option><option>female</option><option>other</option>
                            </select>
                        </label>
                    </div>

                    <div v-if="step === 1" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block"><span class="block text-sm font-medium mb-1">Email</span><input v-model="form.email" type="email" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Phone</span><input v-model="form.phone" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                        <label class="block md:col-span-2"><span class="block text-sm font-medium mb-1">Address</span><input v-model="form.address" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                        <label class="block"><span class="block text-sm font-medium mb-1">City</span><input v-model="form.city" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                        <label class="block"><span class="block text-sm font-medium mb-1">State</span><input v-model="form.state" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Emergency contact name</span><input v-model="form.emergency_contact_name" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Emergency contact phone</span><input v-model="form.emergency_contact_phone" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                    </div>

                    <div v-if="step === 2" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="block"><span class="block text-sm font-medium mb-1">Insurance provider</span><input v-model="form.insurance_provider" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                            <label class="block"><span class="block text-sm font-medium mb-1">Policy number</span><input v-model="form.insurance_policy" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" /></label>
                        </div>
                        <label class="block"><span class="block text-sm font-medium mb-1">Allergies</span><textarea v-model="form.allergies" rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea></label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Medical history</span><textarea v-model="form.medical_history" rows="3" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea></label>
                    </div>

                    <div v-if="step === 3" class="space-y-3">
                        <p class="text-sm text-slate-600 dark:text-slate-400">Please review the consent for treatment and HIPAA privacy practices, then check the box below.</p>
                        <div class="rounded-lg bg-slate-50 dark:bg-slate-800 p-4 text-xs text-slate-600 dark:text-slate-400 max-h-48 overflow-y-auto">
                            I consent to the medical treatment provided by SmartHealth. I understand my health information will be used and disclosed in accordance with HIPAA. I acknowledge the privacy notice provided to me.
                        </div>
                        <label class="flex items-center gap-2"><input v-model="form.consent" type="checkbox" class="rounded" /> <span class="text-sm">I have read and agree to the consent and privacy practices.</span></label>
                    </div>

                    <div class="flex justify-between pt-4 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" :disabled="step === 0" @click="step -= 1" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm disabled:opacity-50">Back</button>
                        <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                            {{ step === 3 ? 'Finish' : 'Continue' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
