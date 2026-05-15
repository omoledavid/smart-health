<script setup>
defineProps({
    modelValue: [String, Number],
    label: String,
    type: { type: String, default: 'text' },
    error: String,
    placeholder: String,
});
defineEmits(['update:modelValue']);
</script>

<template>
    <label class="block">
        <span v-if="label" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ label }}</span>
        <input
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            @input="$emit('update:modelValue', $event.target.value)"
            class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
        />
        <span v-if="error" class="block mt-1 text-xs text-red-600 dark:text-red-400">{{ error }}</span>
    </label>
</template>
