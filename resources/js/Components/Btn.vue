<script setup>
import { computed } from 'vue';
const props = defineProps({
    variant: { type: String, default: 'primary' },
    size: { type: String, default: 'md' },
    as: { type: String, default: 'button' },
});
const cls = computed(() => {
    const v = {
        primary: 'bg-brand-600 hover:bg-brand-700 text-white shadow-sm shadow-brand-600/30',
        secondary: 'bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700',
        ghost: 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800',
        danger: 'bg-red-600 hover:bg-red-700 text-white',
        success: 'bg-emerald-600 hover:bg-emerald-700 text-white',
    };
    const s = {
        sm: 'px-3 py-1.5 text-sm',
        md: 'px-4 py-2 text-sm',
        lg: 'px-5 py-2.5 text-base',
    };
    return [v[props.variant], s[props.size], 'inline-flex items-center gap-2 rounded-lg font-medium transition-colors'].join(' ');
});
</script>

<template>
    <component :is="as" :class="cls">
        <slot />
    </component>
</template>
