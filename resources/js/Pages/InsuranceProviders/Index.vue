<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { PlusIcon, BuildingOffice2Icon } from '@heroicons/vue/24/outline';

const props = defineProps({ providers: Object });

const showAdd = ref(false);
const form = useForm({ name: '', email: '', phone: '', address: '', status: 'active' });

const submit = () => form.post(route('insurance-providers.store'), {
    onSuccess: () => { form.reset(); showAdd.value = false; },
});

const destroy = (id) => {
    if (confirm('Delete this provider?')) {
        router.delete(route('insurance-providers.destroy', id));
    }
};
</script>

<template>
    <Head title="Insurance Providers" />
    <SmartHealthLayout title="Insurance Providers">
        <template #actions>
            <button @click="showAdd = !showAdd" class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs sm:text-sm font-medium whitespace-nowrap">
                <PlusIcon class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0" /> Add Provider
            </button>
        </template>

        <Card v-if="showAdd" title="New Insurance Provider" class="mb-5">
            <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Name *</span>
                    <input v-model="form.name" required class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                    <span v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</span>
                </label>
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</span>
                    <input v-model="form.email" type="email" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phone</span>
                    <input v-model="form.phone" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                </label>
                <label class="block">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Status</span>
                    <select v-model="form.status" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Address</span>
                    <textarea v-model="form.address" rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>
                <div class="md:col-span-2 flex justify-end gap-2">
                    <button type="button" @click="showAdd = false" class="px-4 py-2 rounded-lg bg-slate-100 dark:bg-slate-800 text-sm font-medium">Cancel</button>
                    <button type="submit" :disabled="form.processing" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium disabled:opacity-50">Save</button>
                </div>
            </form>
        </Card>

        <!-- Mobile card list -->
        <div class="sm:hidden space-y-3">
            <div v-for="p in providers.data" :key="p.id"
                class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <BuildingOffice2Icon class="h-5 w-5 text-slate-400 shrink-0" />
                        <Link :href="route('insurance-providers.show', p.id)"
                            class="font-semibold text-slate-900 dark:text-white hover:text-brand-600 truncate">
                            {{ p.name }}
                        </Link>
                    </div>
                    <StatusPill :status="p.status" class="shrink-0" />
                </div>
                <div class="mt-2 space-y-0.5 pl-7">
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ p.email || '—' }}</p>
                    <p class="text-xs text-slate-500">{{ p.phone || '' }}</p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-xs font-medium mt-1">
                        {{ p.plans_count }} {{ p.plans_count === 1 ? 'plan' : 'plans' }}
                    </span>
                </div>
                <div class="mt-3 flex gap-4 pl-7">
                    <Link :href="route('insurance-providers.show', p.id)" class="text-brand-600 hover:underline text-sm font-medium">Manage</Link>
                    <button @click="destroy(p.id)" class="text-red-500 hover:underline text-sm">Delete</button>
                </div>
            </div>
            <p v-if="!providers.data.length" class="text-center text-slate-500 text-sm py-10">No insurance providers yet.</p>
        </div>

        <!-- Desktop table -->
        <Card padding="p-0" class="hidden sm:block">
            <table class="w-full text-sm">
                <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                    <tr>
                        <th class="px-5 py-3">Provider</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Plans</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr v-for="p in providers.data" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <BuildingOffice2Icon class="h-5 w-5 text-slate-400" />
                                <Link :href="route('insurance-providers.show', p.id)" class="font-medium text-slate-900 dark:text-white hover:text-brand-600">
                                    {{ p.name }}
                                </Link>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <p class="text-slate-700 dark:text-slate-300">{{ p.email || '—' }}</p>
                            <p class="text-xs text-slate-500">{{ p.phone || '' }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-xs font-medium">
                                {{ p.plans_count }} {{ p.plans_count === 1 ? 'plan' : 'plans' }}
                            </span>
                        </td>
                        <td class="px-5 py-3"><StatusPill :status="p.status" /></td>
                        <td class="px-5 py-3 text-right">
                            <Link :href="route('insurance-providers.show', p.id)" class="text-brand-600 hover:underline text-sm mr-3">Manage</Link>
                            <button @click="destroy(p.id)" class="text-red-500 hover:underline text-sm">Delete</button>
                        </td>
                    </tr>
                    <tr v-if="!providers.data.length">
                        <td colspan="5" class="px-5 py-10 text-center text-slate-500">No insurance providers yet.</td>
                    </tr>
                </tbody>
            </table>
        </Card>
    </SmartHealthLayout>
</template>
