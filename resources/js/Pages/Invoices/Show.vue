<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import dayjs from 'dayjs';

const props = defineProps({ invoice: Object });
const money = (c) => '$' + ((c ?? 0) / 100).toFixed(2);

const form = useForm({ amount_cents: 0, method: 'cash', reference: '', paid_on: new Date().toISOString().slice(0,10), notes: '' });
const submit = () => form.post(route('invoices.payments', props.invoice.id), { onSuccess: () => form.reset() });
</script>

<template>
    <Head :title="`Invoice ${invoice.number}`" />
    <SmartHealthLayout :title="`Invoice ${invoice.number}`" :breadcrumbs="[{ label: 'Billing', href: route('invoices.index') }, { label: invoice.number }]">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <!-- Invoice detail -->
            <Card class="lg:col-span-2" title="Invoice">
                <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
                    <div>
                        <p class="text-sm text-slate-500">Billed to</p>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ invoice.patient?.first_name }} {{ invoice.patient?.last_name }}</p>
                    </div>
                    <StatusPill :status="invoice.status" />
                </div>

                <!-- Mobile line-items -->
                <div class="sm:hidden space-y-3 border-t border-slate-100 dark:border-slate-800 pt-3">
                    <div v-for="it in invoice.items" :key="it.id"
                        class="flex items-start justify-between gap-2 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-900 dark:text-white truncate">{{ it.description }}</p>
                            <p class="text-xs text-slate-500">{{ it.quantity }} × {{ money(it.unit_price_cents) }}</p>
                        </div>
                        <p class="font-semibold text-slate-900 dark:text-white shrink-0">{{ money(it.total_cents) }}</p>
                    </div>
                </div>

                <!-- Desktop line-items table -->
                <table class="hidden sm:table w-full text-sm border-t border-slate-100 dark:border-slate-800">
                    <thead class="text-left text-xs uppercase text-slate-400">
                        <tr>
                            <th class="py-2">Item</th>
                            <th class="py-2">Qty</th>
                            <th class="py-2">Price</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="it in invoice.items" :key="it.id" class="border-t border-slate-100 dark:border-slate-800">
                            <td class="py-2">{{ it.description }}</td>
                            <td class="py-2">{{ it.quantity }}</td>
                            <td class="py-2">{{ money(it.unit_price_cents) }}</td>
                            <td class="py-2 text-right">{{ money(it.total_cents) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Totals -->
                <div class="mt-4 ml-auto w-full sm:w-64 text-sm space-y-1 border-t border-slate-100 dark:border-slate-800 pt-3">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span><span>{{ money(invoice.subtotal_cents) }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Paid</span><span>{{ money(invoice.paid_cents) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-slate-900 dark:text-white border-t border-slate-200 dark:border-slate-700 pt-1">
                        <span>Balance</span><span>{{ money(invoice.total_cents - invoice.paid_cents) }}</span>
                    </div>
                </div>
            </Card>

            <!-- Record payment -->
            <Card title="Record payment">
                <form @submit.prevent="submit" class="space-y-3">
                    <label class="block">
                        <span class="block text-sm font-medium mb-1">Amount (cents)</span>
                        <input v-model.number="form.amount_cents" type="number" min="1" required
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    </label>
                    <label class="block">
                        <span class="block text-sm font-medium mb-1">Method</span>
                        <select v-model="form.method"
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                            <option>cash</option>
                            <option>card</option>
                            <option>insurance</option>
                            <option>bank_transfer</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="block text-sm font-medium mb-1">Reference</span>
                        <input v-model="form.reference"
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    </label>
                    <label class="block">
                        <span class="block text-sm font-medium mb-1">Paid on</span>
                        <input v-model="form.paid_on" type="date"
                            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    </label>
                    <button type="submit" :disabled="form.processing"
                        class="w-full px-3 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">
                        Record
                    </button>
                </form>

                <div v-if="invoice.payments?.length" class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Payments</p>
                    <ul class="space-y-2 text-sm">
                        <li v-for="p in invoice.payments" :key="p.id"
                            class="flex justify-between gap-2">
                            <span class="text-slate-600 dark:text-slate-400 truncate">
                                {{ p.paid_on ? dayjs(p.paid_on).format('DD MMM YYYY') : '—' }} · {{ p.method }}
                            </span>
                            <span class="font-medium text-slate-900 dark:text-white shrink-0">{{ money(p.amount_cents) }}</span>
                        </li>
                    </ul>
                </div>
            </Card>

        </div>
    </SmartHealthLayout>
</template>
