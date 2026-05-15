<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import { PlusIcon, MagnifyingGlassIcon, UsersIcon, UserPlusIcon, HeartIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';

const props = defineProps({ patients: Object, filters: Object, stats: Object });
const q = ref(props.filters?.q ?? '');

const search = () => router.get(route('patients.index'), { q: q.value }, { preserveState: true, replace: true });
</script>

<template>
    <Head title="Patients" />
    <SmartHealthLayout title="Patients">
        <template #actions>
            <Link :href="route('patients.create')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> Add Patient
            </Link>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
            <StatCard label="Total Patients" :value="stats.total" :icon="UsersIcon" icon-bg="bg-brand-600" />
            <StatCard label="New This Month" :value="stats.new_this_month" :icon="UserPlusIcon" icon-bg="bg-emerald-500" />
            <StatCard label="Male / Female" :value="`${stats.male} / ${stats.female}`" :icon="HeartIcon" icon-bg="bg-pink-500" />
            <StatCard label="Active" :value="stats.active" :icon="CheckBadgeIcon" icon-bg="bg-sky-500" />
        </div>

        <Card padding="p-0">
            <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                <form @submit.prevent="search" class="relative max-w-md">
                    <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                    <input v-model="q" placeholder="Search by name, MRN, email…" class="w-full pl-10 pr-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-slate-400 bg-slate-50 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-5 py-3">Patient</th>
                            <th class="px-5 py-3">MRN</th>
                            <th class="px-5 py-3">Contact</th>
                            <th class="px-5 py-3">DOB</th>
                            <th class="px-5 py-3">Onboarding</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr v-for="p in patients.data" :key="p.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-100 text-brand-700 font-semibold text-xs">{{ p.first_name?.[0] }}{{ p.last_name?.[0] }}</span>
                                    <div>
                                        <p class="font-medium text-slate-900 dark:text-white">{{ p.first_name }} {{ p.last_name }}</p>
                                        <p class="text-xs text-slate-500">{{ p.gender }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs">{{ p.mrn }}</td>
                            <td class="px-5 py-3">
                                <p class="text-slate-700 dark:text-slate-300">{{ p.email }}</p>
                                <p class="text-xs text-slate-500">{{ p.phone }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">{{ p.dob ? dayjs(p.dob).format('DD MMM YYYY') : '—' }}</td>
                            <td class="px-5 py-3 capitalize">{{ p.onboarding_status?.replace('_',' ') }}</td>
                            <td class="px-5 py-3 text-right">
                                <Link :href="route('patients.show', p.id)" class="text-brand-600 hover:underline text-sm">View</Link>
                            </td>
                        </tr>
                        <tr v-if="!patients.data.length">
                            <td colspan="6" class="px-5 py-10 text-center text-slate-500">No patients found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="patients.links?.length > 3" class="px-5 py-3 border-t border-slate-200 dark:border-slate-800 flex justify-between items-center text-sm">
                <span class="text-slate-500">Page {{ patients.current_page }} of {{ patients.last_page }}</span>
                <div class="flex gap-1">
                    <Link
                        v-for="link in patients.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 rounded border text-xs"
                        :class="link.active ? 'bg-brand-600 text-white border-brand-600' : 'border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300'"
                    />
                </div>
            </div>
        </Card>
    </SmartHealthLayout>
</template>
