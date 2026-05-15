<script setup>
import { computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';

const props = defineProps({ specializations: Array, locations: Array, services: Array });

const specializationOptions = computed(() => props.specializations.map(s => ({
    value: s.id,
    label: s.name,
})));

const form = useForm({
    name: '', email: '', phone: '', password: '',
    specialization_id: '', license_number: '', bio: '',
    years_experience: 0, consultation_fee_cents: 0,
    location_ids: [], service_ids: [],
});
const submit = () => form.post(route('doctors.store'));
</script>

<template>
    <Head title="Add Doctor" />
    <SmartHealthLayout title="Add Doctor" :breadcrumbs="[{ label: 'Doctors', href: route('doctors.index') }, { label: 'Add' }]">
        <Card>
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input v-model="form.name" label="Full name" :error="form.errors.name" />
                <Input v-model="form.email" type="email" label="Email" :error="form.errors.email" />
                <Input v-model="form.phone" label="Phone" />
                <Input v-model="form.password" type="password" label="Initial password" :error="form.errors.password" />
                <label class="block">
                    <span class="block text-sm font-medium mb-1">Specialization</span>
                    <SearchableSelect v-model="form.specialization_id" :options="specializationOptions" placeholder="Search specializations…" />
                </label>
                <Input v-model="form.license_number" label="License #" />
                <Input v-model.number="form.years_experience" type="number" label="Years experience" />
                <Input v-model.number="form.consultation_fee_cents" type="number" label="Consultation fee (cents)" />
                <label class="md:col-span-2 block">
                    <span class="block text-sm font-medium mb-1">Bio</span>
                    <textarea v-model="form.bio" rows="3" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>
                <fieldset>
                    <legend class="block text-sm font-medium mb-1">Locations</legend>
                    <div class="space-y-1">
                        <label v-for="l in locations" :key="l.id" class="flex items-center gap-2 text-sm">
                            <input type="checkbox" :value="l.id" v-model="form.location_ids" class="rounded" /> {{ l.name }}
                        </label>
                    </div>
                </fieldset>
                <fieldset>
                    <legend class="block text-sm font-medium mb-1">Services</legend>
                    <div class="space-y-1">
                        <label v-for="s in services" :key="s.id" class="flex items-center gap-2 text-sm">
                            <input type="checkbox" :value="s.id" v-model="form.service_ids" class="rounded" /> {{ s.name }}
                        </label>
                    </div>
                </fieldset>
                <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                    <Link :href="route('doctors.index')" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm">Cancel</Link>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">{{ form.processing ? 'Saving…' : 'Save' }}</button>
                </div>
            </form>
        </Card>
    </SmartHealthLayout>
</template>
