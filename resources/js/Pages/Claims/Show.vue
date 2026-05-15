<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import dayjs from 'dayjs';

const props = defineProps({ claim: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});
const money = (c) => '$' + ((c ?? 0) / 100).toFixed(2);

const statusForm = useForm({
    status: props.claim.status,
    approved_cents: props.claim.approved_cents ? (props.claim.approved_cents / 100).toFixed(2) : '',
    decided_on: props.claim.decided_on ?? '',
    notes: props.claim.notes ?? '',
});

function submitStatus() {
    const payload = {
        ...statusForm.data(),
        approved_cents: statusForm.approved_cents ? Math.round(parseFloat(statusForm.approved_cents) * 100) : null,
    };
    statusForm.transform(() => payload).patch(route('claims.status', props.claim.id));
}

const statusOptions = [
    { value: 'submitted', label: 'Submitted' },
    { value: 'under_review', label: 'Under Review' },
    { value: 'approved', label: 'Approved' },
    { value: 'partially_approved', label: 'Partially Approved' },
    { value: 'denied', label: 'Denied' },
    { value: 'appealed', label: 'Appealed' },
];
</script>

<template>
    <Head :title="`Claim ${claim.claim_number || claim.id}`" />
    <SmartHealthLayout
        :title="`Claim ${claim.claim_number || '#' + claim.id}`"
        :breadcrumbs="[{ label: 'Insurance Claims', href: route('claims.index') }, { label: claim.claim_number || '#' + claim.id }]"
    >
        <div v-if="flash.success" class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-800">
            {{ flash.success }}
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Claim details -->
            <Card class="lg:col-span-2" title="Claim Details">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-slate-500 mb-1">Patient</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ claim.invoice?.patient?.first_name }} {{ claim.invoice?.patient?.last_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-1">Insurance Provider</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ claim.insurance?.provider_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-1">Policy Number</dt>
                        <dd class="font-mono text-slate-700 dark:text-slate-300">{{ claim.insurance?.policy_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-1">Group Number</dt>
                        <dd class="font-mono text-slate-700 dark:text-slate-300">{{ claim.insurance?.group_number ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-1">Claimed Amount</dt>
                        <dd class="font-semibold text-slate-900 dark:text-white">{{ money(claim.claimed_cents) }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-1">Approved Amount</dt>
                        <dd class="font-semibold" :class="claim.approved_cents ? 'text-emerald-600' : 'text-slate-400'">
                            {{ claim.approved_cents ? money(claim.approved_cents) : 'Pending' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-1">Submitted On</dt>
                        <dd>{{ claim.submitted_on ? dayjs(claim.submitted_on).format('DD MMM YYYY') : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-1">Decision Date</dt>
                        <dd>{{ claim.decided_on ? dayjs(claim.decided_on).format('DD MMM YYYY') : '—' }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-slate-500 mb-1">Status</dt>
                        <dd><StatusPill :status="claim.status" /></dd>
                    </div>
                    <div v-if="claim.notes" class="col-span-2">
                        <dt class="text-slate-500 mb-1">Notes</dt>
                        <dd class="text-slate-700 dark:text-slate-300 whitespace-pre-line">{{ claim.notes }}</dd>
                    </div>
                </dl>

                <!-- Invoice items -->
                <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">Invoice
                        <Link :href="route('invoices.show', claim.invoice_id)" class="text-brand-600 hover:underline ml-1">{{ claim.invoice?.number }}</Link>
                    </p>
                    <table class="w-full text-sm">
                        <thead class="text-xs uppercase text-slate-400">
                            <tr>
                                <th class="text-left py-2">Item</th>
                                <th class="text-left py-2">Qty</th>
                                <th class="text-right py-2">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="item in claim.invoice?.items" :key="item.id">
                                <td class="py-2">{{ item.description }}</td>
                                <td class="py-2">{{ item.quantity }}</td>
                                <td class="py-2 text-right">{{ money(item.total_cents) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-slate-200 dark:border-slate-700">
                                <td colspan="2" class="py-2 text-right font-semibold">Total</td>
                                <td class="py-2 text-right font-semibold">{{ money(claim.invoice?.total_cents) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </Card>

            <!-- Update status panel -->
            <Card title="Update Claim Status">
                <form @submit.prevent="submitStatus" class="space-y-4">
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</span>
                        <select v-model="statusForm.status" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                            <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>
                    </label>

                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Approved Amount ($)</span>
                        <input v-model="statusForm.approved_cents" type="number" min="0" step="0.01" placeholder="0.00"
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    </label>

                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Decision Date</span>
                        <input v-model="statusForm.decided_on" type="date"
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    </label>

                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Notes</span>
                        <textarea v-model="statusForm.notes" rows="3"
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                    </label>

                    <button type="submit" :disabled="statusForm.processing"
                        class="w-full px-3 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium disabled:opacity-50">
                        {{ statusForm.processing ? 'Saving…' : 'Update Status' }}
                    </button>
                </form>
            </Card>
        </div>
    </SmartHealthLayout>
</template>
