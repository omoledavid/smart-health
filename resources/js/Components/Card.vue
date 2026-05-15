<script setup>
defineProps({
    title: String,
    padding: { type: String, default: 'p-5' },
});
</script>

<template>
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
        <div v-if="title || $slots.actions" class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ title }}</h3>
            <slot name="actions" />
        </div>
        <div :class="padding">
            <slot />
        </div>
    </div>
</template>
