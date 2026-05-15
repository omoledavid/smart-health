<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import { PlusIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    patients: Array,
    services: Array,
    appointments: Array,
});

const form = useForm({
    patient_id: '',
    appointment_id: '',
    issued_on: new Date().toISOString().slice(0, 10),
    due_on: '',
    notes: '',
    items: [{ description: '', service_id: '', quantity: 1, unit_price_cents: 0 }],
});

const patientOptions = computed(() => props.patients.map(p => ({
    value: p.id,
    label: `${p.last_name}, ${p.first_name}`,
    sublabel: p.email,
})));

const appointmentOptions = computed(() => props.appointments.map(a => ({
    value: a.id,
    label: `#${a.id} — ${a.patient?.full_name ?? ''}`,
    sublabel: a.scheduled_at?.slice(0, 10),
})));

const serviceOptions = computed(() => props.services.map(s => ({
    value: s.id,
    label: s.name,
    sublabel: `$${(s.price_cents / 100).toFixed(2)}`,
})));

const subtotal = computed(() =>
    form.items.reduce((sum, i) => sum + (i.quantity || 0) * (i.unit_price_cents || 0), 0)
);
const money = (c) => '$' + ((c ?? 0) / 100).toFixed(2);

function addItem() {
    form.items.push({ description: '', service_id: '', quantity: 1, unit_price_cents: 0 });
}
function removeItem(index) {
    form.items.splice(index, 1);
}
function onServiceChange(index, serviceId) {
    if (!serviceId) return;
    const svc = props.services.find(s => s.id == serviceId);
    if (svc) {
        form.items[index].description = svc.name;
        form.items[index].unit_price_cents = svc.price_cents;
    }
}

function submit() {
    const payload = {
        ...form.data(),
        items: form.items.map(i => ({
            ...i,
            service_id: i.service_id || null,
            unit_price_cents: Math.round(parseFloat(i.unit_price_cents) || 0),
        })),
    };
    form.transform(() => payload).post(route('invoices.store'));
}
</script>

<template>
    <Head title="New Invoice" />
    <PreclinicLayout title="New Invoice" :breadcrumbs="[{ label: 'Billing', href: route('invoices.index') }, { label: 'New Invoice' }]">
        <form @submit.prevent="submit" class="space-y-5">
            <Card title="Invoice Details">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Patient <span class="text-red-500">*</span></span>
                        <SearchableSelect
                            v-model="form.patient_id"
                            :options="patientOptions"
                            placeholder="Search patients…"
                            :required="true"
                        />
                        <span v-if="form.errors.patient_id" class="text-xs text-red-600">{{ form.errors.patient_id }}</span>
                    </label>

                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Linked Appointment</span>
                        <SearchableSelect
                            v-model="form.appointment_id"
                            :options="appointmentOptions"
                            placeholder="None"
                        />
                    </label>

                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Issued On <span class="text-red-500">*</span></span>
                        <input v-model="form.issued_on" type="date" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    </label>

                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Due On</span>
                        <input v-model="form.due_on" type="date" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</span>
                        <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                    </label>
                </div>
            </Card>

            <Card title="Line Items">
                <table class="w-full text-sm mb-4">
                    <thead class="text-xs uppercase text-slate-400 border-b border-slate-100 dark:border-slate-800">
                        <tr>
                            <th class="text-left pb-2">Description</th>
                            <th class="text-left pb-2 w-44">Service</th>
                            <th class="text-left pb-2 w-20">Qty</th>
                            <th class="text-left pb-2 w-32">Unit Price ($)</th>
                            <th class="text-right pb-2 w-24">Total</th>
                            <th class="w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                        <tr v-for="(item, i) in form.items" :key="i" class="py-2">
                            <td class="py-2 pr-2">
                                <input v-model="item.description" required placeholder="Description"
                                    class="w-full px-2 py-1.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                            </td>
                            <td class="py-2 pr-2">
                                <SearchableSelect
                                    v-model="item.service_id"
                                    :options="serviceOptions"
                                    placeholder="— custom —"
                                    @change="onServiceChange(i, $event)"
                                />
                            </td>
                            <td class="py-2 pr-2">
                                <input v-model.number="item.quantity" type="number" min="1" required
                                    class="w-full px-2 py-1.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                            </td>
                            <td class="py-2 pr-2">
                                <input v-model.number="item.unit_price_cents" type="number" min="0" step="0.01" required
                                    class="w-full px-2 py-1.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                            </td>
                            <td class="py-2 text-right font-medium">
                                {{ money(item.quantity * item.unit_price_cents) }}
                            </td>
                            <td class="py-2 pl-2">
                                <button type="button" @click="removeItem(i)" :disabled="form.items.length === 1"
                                    class="text-slate-400 hover:text-red-500 disabled:opacity-30">
                                    <TrashIcon class="h-4 w-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="flex items-center justify-between">
                    <button type="button" @click="addItem"
                        class="inline-flex items-center gap-1.5 text-sm text-brand-600 hover:text-brand-700">
                        <PlusIcon class="h-4 w-4" /> Add line item
                    </button>
                    <div class="text-sm font-semibold">
                        Subtotal: <span class="text-slate-900 dark:text-white ml-2">{{ money(subtotal) }}</span>
                    </div>
                </div>
            </Card>

            <div class="flex justify-end gap-3">
                <a :href="route('invoices.index')" class="px-4 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                    Cancel
                </a>
                <button type="submit" :disabled="form.processing || !form.items.length"
                    class="px-5 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium disabled:opacity-50">
                    {{ form.processing ? 'Creating…' : 'Create Invoice' }}
                </button>
            </div>
        </form>
    </PreclinicLayout>
</template>
