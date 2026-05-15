<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { PlusIcon, TrashIcon, CogIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ provider: Object });

// Edit provider
const editing = ref(false);
const editForm = useForm({
    name: props.provider.name,
    email: props.provider.email || '',
    phone: props.provider.phone || '',
    address: props.provider.address || '',
    status: props.provider.status,
});
const saveProvider = () => editForm.put(route('insurance-providers.update', props.provider.id), {
    onSuccess: () => { editing.value = false; },
});

// Add plan
const showPlanForm = ref(false);
const planForm = useForm({ name: '', description: '', status: 'active' });
const submitPlan = () => planForm.post(route('insurance-plans.store', props.provider.id), {
    onSuccess: () => { planForm.reset(); showPlanForm.value = false; },
});
const deletePlan = (id) => {
    if (confirm('Delete this plan and all its pricing rules?')) {
        router.delete(route('insurance-plans.destroy', id));
    }
};

// Service pricing per plan
const activePlanId = ref(null);
const services = ref([]);
const loadingServices = ref(false);

async function openPricing(planId) {
    if (activePlanId.value === planId) {
        activePlanId.value = null;
        return;
    }
    activePlanId.value = planId;
    loadingServices.value = true;
    try {
        const resp = await fetch(route('insurance-plans.services', planId));
        const data = await resp.json();
        services.value = data.services.map(s => reactive({ ...s }));
    } finally {
        loadingServices.value = false;
    }
}

function computePrice(s) {
    if (s.fixed_price !== null && s.fixed_price !== '' && s.fixed_price !== undefined) {
        return s.fixed_price;
    }
    if (s.adjustment_type === 'percentage') {
        return Math.round(s.price_cents * (1 + s.adjustment_value / 100));
    }
    return s.price_cents + Math.round(s.adjustment_value);
}

const savingPricing = ref(false);
async function savePricing() {
    const enabled = services.value.filter(s => s.enabled);
    savingPricing.value = true;
    router.post(route('insurance-plans.sync-services', activePlanId.value), {
        services: enabled.map(s => ({
            service_id: s.id,
            adjustment_type: s.adjustment_type,
            adjustment_value: s.adjustment_value,
            fixed_price: s.fixed_price || null,
        })),
    }, {
        onFinish: () => { savingPricing.value = false; },
    });
}

const money = (c) => '$' + ((c ?? 0) / 100).toFixed(2);
</script>

<template>
    <Head :title="provider.name" />
    <SmartHealthLayout
        :title="provider.name"
        :breadcrumbs="[{ label: 'Insurance Providers', href: route('insurance-providers.index') }, { label: provider.name }]"
    >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Provider details -->
            <Card title="Provider Details">
                <template v-if="!editing">
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between"><dt class="text-slate-500">Name</dt><dd class="font-medium text-slate-900 dark:text-white">{{ provider.name }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd>{{ provider.email || '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Phone</dt><dd>{{ provider.phone || '—' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Status</dt><dd><StatusPill :status="provider.status" /></dd></div>
                        <div v-if="provider.address"><dt class="text-slate-500 mb-1">Address</dt><dd class="text-slate-700 dark:text-slate-300">{{ provider.address }}</dd></div>
                    </dl>
                    <button @click="editing = true" class="mt-4 w-full px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-sm font-medium">Edit</button>
                </template>
                <template v-else>
                    <form @submit.prevent="saveProvider" class="space-y-3">
                        <label class="block"><span class="block text-sm font-medium mb-1">Name</span>
                            <input v-model="editForm.name" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                        </label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Email</span>
                            <input v-model="editForm.email" type="email" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                        </label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Phone</span>
                            <input v-model="editForm.phone" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                        </label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Status</span>
                            <select v-model="editForm.status" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </label>
                        <label class="block"><span class="block text-sm font-medium mb-1">Address</span>
                            <textarea v-model="editForm.address" rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                        </label>
                        <div class="flex gap-2">
                            <button type="button" @click="editing = false" class="flex-1 px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-sm font-medium">Cancel</button>
                            <button type="submit" class="flex-1 px-3 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium">Save</button>
                        </div>
                    </form>
                </template>
            </Card>

            <!-- Plans -->
            <div class="lg:col-span-2 space-y-5">
                <Card title="Plans">
                    <template #actions>
                        <button @click="showPlanForm = !showPlanForm" class="inline-flex items-center gap-1 text-sm text-brand-600 hover:text-brand-700 font-medium">
                            <PlusIcon class="h-4 w-4" /> Add Plan
                        </button>
                    </template>

                    <div v-if="showPlanForm" class="mb-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50">
                        <form @submit.prevent="submitPlan" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <input v-model="planForm.name" placeholder="Plan name" required class="px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                            <input v-model="planForm.description" placeholder="Description (optional)" class="px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                            <div class="flex gap-2">
                                <button type="submit" :disabled="planForm.processing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium">Create</button>
                                <button type="button" @click="showPlanForm = false" class="px-4 py-2 rounded-lg bg-slate-200 dark:bg-slate-700 text-sm font-medium">Cancel</button>
                            </div>
                        </form>
                    </div>

                    <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                        <li v-for="plan in provider.plans" :key="plan.id" class="py-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ plan.name }}</p>
                                    <p class="text-xs text-slate-500">{{ plan.patient_insurances_count ?? 0 }} enrollees · {{ plan.description || '' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <StatusPill :status="plan.status" />
                                    <button @click="openPricing(plan.id)" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800" title="Service Pricing">
                                        <CogIcon class="h-4 w-4 text-slate-500" />
                                    </button>
                                    <button @click="deletePlan(plan.id)" class="p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete">
                                        <TrashIcon class="h-4 w-4 text-red-500" />
                                    </button>
                                </div>
                            </div>

                            <!-- Service pricing panel -->
                            <div v-if="activePlanId === plan.id" class="mt-4 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/40 space-y-3">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Service Pricing for "{{ plan.name }}"</p>
                                <p v-if="loadingServices" class="text-sm text-slate-500">Loading services…</p>
                                <div v-else class="overflow-x-auto">
                                    <table class="w-full text-xs">
                                        <thead class="text-left uppercase tracking-wider text-slate-400">
                                            <tr>
                                                <th class="py-2 pr-2">
                                                    <input type="checkbox" class="rounded" @change="services.forEach(s => s.enabled = $event.target.checked)" />
                                                </th>
                                                <th class="py-2 pr-4">Service</th>
                                                <th class="py-2 pr-4">Base Price</th>
                                                <th class="py-2 pr-4">Type</th>
                                                <th class="py-2 pr-4">Value</th>
                                                <th class="py-2 pr-4">Fixed Override</th>
                                                <th class="py-2">Final Price</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                                            <tr v-for="s in services" :key="s.id" :class="{ 'opacity-40': !s.enabled }">
                                                <td class="py-2 pr-2">
                                                    <input type="checkbox" v-model="s.enabled" class="rounded" />
                                                </td>
                                                <td class="py-2 pr-4 font-medium text-slate-900 dark:text-white">{{ s.name }}</td>
                                                <td class="py-2 pr-4">{{ money(s.price_cents) }}</td>
                                                <td class="py-2 pr-4">
                                                    <select v-model="s.adjustment_type" :disabled="!s.enabled" class="px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs">
                                                        <option value="percentage">% Markup</option>
                                                        <option value="fixed">Fixed Add</option>
                                                    </select>
                                                </td>
                                                <td class="py-2 pr-4">
                                                    <input v-model.number="s.adjustment_value" :disabled="!s.enabled" type="number" min="0" step="0.01"
                                                        class="w-20 px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs" />
                                                </td>
                                                <td class="py-2 pr-4">
                                                    <input v-model.number="s.fixed_price" :disabled="!s.enabled" type="number" min="0" placeholder="—"
                                                        class="w-24 px-2 py-1 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs" />
                                                </td>
                                                <td class="py-2 font-semibold text-emerald-600">{{ money(computePrice(s)) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="flex justify-end">
                                    <button @click="savePricing" :disabled="savingPricing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">
                                        {{ savingPricing ? 'Saving…' : 'Save Pricing' }}
                                    </button>
                                </div>
                            </div>
                        </li>
                        <li v-if="!provider.plans?.length" class="py-6 text-center text-sm text-slate-500">No plans added yet.</li>
                    </ul>
                </Card>
            </div>
        </div>
    </SmartHealthLayout>
</template>
