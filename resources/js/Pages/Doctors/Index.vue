<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import { PlusIcon, UserGroupIcon, AcademicCapIcon, ClockIcon, MapPinIcon } from '@heroicons/vue/24/outline';

defineProps({ doctors: Object, stats: Object });
</script>

<template>
    <Head title="Doctors" />
    <PreclinicLayout title="Doctors">
        <template #actions>
            <Link :href="route('doctors.create')" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-sm font-medium">
                <PlusIcon class="h-4 w-4" /> Add Doctor
            </Link>
        </template>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
            <StatCard label="Total Doctors" :value="stats.total" :icon="UserGroupIcon" icon-bg="bg-brand-600" />
            <StatCard label="Specializations" :value="stats.specializations" :icon="AcademicCapIcon" icon-bg="bg-amber-500" />
            <StatCard label="Avg Experience" :value="`${stats.avg_experience} yrs`" :icon="ClockIcon" icon-bg="bg-sky-500" />
            <StatCard label="Locations" :value="stats.locations" :icon="MapPinIcon" icon-bg="bg-emerald-500" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            <Card v-for="d in doctors.data" :key="d.id">
                <div class="flex items-start gap-3">
                    <span class="grid h-14 w-14 place-items-center rounded-full bg-brand-100 text-brand-700 text-lg font-bold">{{ d.user?.name?.[0] }}</span>
                    <div class="flex-1 min-w-0">
                        <Link :href="route('doctors.show', d.id)" class="font-semibold text-slate-900 dark:text-white hover:text-brand-600 truncate block">{{ d.user?.name }}</Link>
                        <p class="text-xs text-slate-500">{{ d.specialization?.name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ d.years_experience }} yrs experience</p>
                    </div>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-800 flex flex-wrap gap-1">
                    <span v-for="loc in d.locations" :key="loc.id" class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">{{ loc.name }}</span>
                </div>
            </Card>
            <p v-if="!doctors.data.length" class="text-sm text-slate-500 col-span-full text-center py-10">No doctors yet.</p>
        </div>
    </PreclinicLayout>
</template>
