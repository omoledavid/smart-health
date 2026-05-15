<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import dayjs from 'dayjs';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ month: String, appointments: Array });
const cursor = ref(dayjs(props.month + '-01'));

const days = computed(() => {
    const start = cursor.value.startOf('month').startOf('week');
    return Array.from({ length: 42 }, (_, i) => start.add(i, 'day'));
});

const byDate = computed(() => {
    const map = {};
    props.appointments.forEach(a => {
        const k = dayjs(a.start).format('YYYY-MM-DD');
        if (!map[k]) map[k] = [];
        map[k].push(a);
    });
    return map;
});

const navigate = (delta) => {
    const next = cursor.value.add(delta, 'month');
    router.get(route('appointments.calendar'), { month: next.format('YYYY-MM') }, { preserveState: true });
};

const colorFor = (status) => ({
    scheduled: 'bg-blue-100 text-blue-700',
    confirmed: 'bg-indigo-100 text-indigo-700',
    in_progress: 'bg-amber-100 text-amber-700',
    completed: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-red-100 text-red-700',
}[status] ?? 'bg-slate-100 text-slate-700');
</script>

<template>
    <Head title="Calendar" />
    <PreclinicLayout title="Calendar">
        <Card padding="p-0">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200 dark:border-slate-800">
                <div class="flex items-center gap-2">
                    <button @click="navigate(-1)" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800"><ChevronLeftIcon class="h-4 w-4" /></button>
                    <p class="text-base font-semibold text-slate-900 dark:text-white">{{ cursor.format('MMMM YYYY') }}</p>
                    <button @click="navigate(1)" class="p-1.5 rounded hover:bg-slate-100 dark:hover:bg-slate-800"><ChevronRightIcon class="h-4 w-4" /></button>
                </div>
            </div>
            <div class="grid grid-cols-7 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-200 dark:border-slate-800">
                <div v-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="d" class="px-2 py-2 text-center">{{ d }}</div>
            </div>
            <div class="grid grid-cols-7">
                <div v-for="d in days" :key="d.format('YYYY-MM-DD')"
                     class="min-h-28 p-2 border-r border-b border-slate-100 dark:border-slate-800"
                     :class="d.isSame(cursor, 'month') ? '' : 'bg-slate-50 dark:bg-slate-900/40'">
                    <p class="text-xs text-slate-500 mb-1" :class="d.isSame(dayjs(), 'day') ? 'text-brand-600 font-bold' : ''">{{ d.date() }}</p>
                    <ul class="space-y-1">
                        <li v-for="a in (byDate[d.format('YYYY-MM-DD')] || []).slice(0, 3)" :key="a.id"
                            class="text-xs px-1.5 py-0.5 rounded truncate" :class="colorFor(a.status)" :title="a.title">
                            {{ dayjs(a.start).format('hh:mm') }} {{ a.title }}
                        </li>
                        <li v-if="(byDate[d.format('YYYY-MM-DD')] || []).length > 3" class="text-xs text-slate-500">+{{ (byDate[d.format('YYYY-MM-DD')] || []).length - 3 }} more</li>
                    </ul>
                </div>
            </div>
        </Card>
    </PreclinicLayout>
</template>
