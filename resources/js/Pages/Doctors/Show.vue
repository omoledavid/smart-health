<script setup>
import { Head } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';

defineProps({ doctor: Object });
const DAYS = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
</script>

<template>
    <Head :title="doctor.user?.name" />
    <PreclinicLayout :title="doctor.user?.name">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <Card>
                <div class="flex flex-col items-center text-center">
                    <span class="grid h-20 w-20 place-items-center rounded-full bg-brand-100 text-brand-700 text-xl font-bold">{{ doctor.user?.name?.[0] }}</span>
                    <h2 class="mt-3 text-lg font-bold text-slate-900 dark:text-white">{{ doctor.user?.name }}</h2>
                    <p class="text-sm text-slate-500">{{ doctor.specialization?.name }}</p>
                </div>
                <dl class="text-sm space-y-2 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="flex justify-between"><dt class="text-slate-500">License</dt><dd>{{ doctor.license_number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Experience</dt><dd>{{ doctor.years_experience }} yrs</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Email</dt><dd class="break-all">{{ doctor.user?.email }}</dd></div>
                </dl>
            </Card>
            <Card title="About" class="lg:col-span-2">
                <p class="text-sm text-slate-600 dark:text-slate-400">{{ doctor.bio || 'No bio provided.' }}</p>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mt-4 mb-2">Services</h4>
                <div class="flex flex-wrap gap-1">
                    <span v-for="s in doctor.services" :key="s.id" class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">{{ s.name }}</span>
                </div>
                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mt-4 mb-2">Availability</h4>
                <ul class="text-sm space-y-1">
                    <li v-for="a in doctor.availabilities" :key="a.id">{{ DAYS[a.day_of_week] }} · {{ a.start_time }} – {{ a.end_time }}</li>
                    <li v-if="!doctor.availabilities?.length" class="text-slate-500">No availability set.</li>
                </ul>
            </Card>
        </div>
    </PreclinicLayout>
</template>
