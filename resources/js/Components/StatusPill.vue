<script setup>
import { computed } from 'vue';

const props = defineProps({ status: String });
const cls = computed(() => {
    const map = {
        scheduled: 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        confirmed: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
        in_progress: 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        completed: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        cancelled: 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        no_show: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        rescheduled: 'bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
        draft: 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
        generating: 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        failed: 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        paid: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
        sent: 'bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
        completed_onboarding: 'bg-emerald-50 text-emerald-700',
        pending: 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        in_progress_onboarding: 'bg-amber-50 text-amber-700',
    };
    return map[props.status] ?? 'bg-slate-100 text-slate-700';
});
const label = computed(() => (props.status ?? '').replace(/_/g, ' '));
</script>

<template>
    <span :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize', cls]">{{ label }}</span>
</template>
