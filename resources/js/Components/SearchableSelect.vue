<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { ChevronDownIcon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, default: () => [] }, // { value, label, sublabel? }
    placeholder: { type: String, default: 'Select…' },
    disabled: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    clearable: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'change']);

const open = ref(false);
const search = ref('');
const highlighted = ref(-1);
const containerRef = ref(null);
const inputRef = ref(null);
const listRef = ref(null);

const selected = computed(() => props.options.find(o => String(o.value) === String(props.modelValue)));

const filtered = computed(() => {
    if (!search.value.trim()) return props.options;
    const q = search.value.toLowerCase();
    return props.options.filter(o =>
        o.label.toLowerCase().includes(q) ||
        (o.sublabel && o.sublabel.toLowerCase().includes(q))
    );
});

function openDropdown() {
    if (props.disabled) return;
    open.value = true;
    search.value = '';
    highlighted.value = -1;
    nextTick(() => inputRef.value?.focus());
}

function select(option) {
    emit('update:modelValue', option.value);
    emit('change', option.value);
    open.value = false;
    search.value = '';
}

function clear(e) {
    e.stopPropagation();
    emit('update:modelValue', '');
    emit('change', '');
}

function onKeydown(e) {
    if (!open.value) return;
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        highlighted.value = Math.min(highlighted.value + 1, filtered.value.length - 1);
        scrollToHighlighted();
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        highlighted.value = Math.max(highlighted.value - 1, 0);
        scrollToHighlighted();
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (highlighted.value >= 0 && filtered.value[highlighted.value]) {
            select(filtered.value[highlighted.value]);
        }
    } else if (e.key === 'Escape') {
        open.value = false;
        search.value = '';
    }
}

function scrollToHighlighted() {
    nextTick(() => {
        const list = listRef.value;
        if (!list) return;
        const item = list.children[highlighted.value];
        item?.scrollIntoView({ block: 'nearest' });
    });
}

watch(search, () => { highlighted.value = -1; });

function handleClickOutside(e) {
    if (containerRef.value && !containerRef.value.contains(e.target)) {
        open.value = false;
        search.value = '';
    }
}

onMounted(() => document.addEventListener('mousedown', handleClickOutside));
onUnmounted(() => document.removeEventListener('mousedown', handleClickOutside));
</script>

<template>
    <div ref="containerRef" class="relative" @keydown="onKeydown">
        <!-- Trigger button -->
        <button
            type="button"
            :disabled="disabled"
            @click="openDropdown"
            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm text-left focus:outline-none focus:ring-2 focus:ring-brand-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            :class="open ? 'ring-2 ring-brand-500 border-brand-500' : ''"
        >
            <span class="flex-1 min-w-0">
                <template v-if="selected">
                    <span class="block truncate text-slate-900 dark:text-white">{{ selected.label }}</span>
                    <span v-if="selected.sublabel" class="block truncate text-xs text-slate-500 dark:text-slate-400 leading-tight">{{ selected.sublabel }}</span>
                </template>
                <span v-else class="text-slate-400 dark:text-slate-500">{{ placeholder }}</span>
            </span>
            <span class="flex items-center gap-1 shrink-0">
                <button v-if="clearable && modelValue !== '' && modelValue !== null" type="button"
                    @click="clear"
                    class="p-0.5 rounded text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none">
                    <XMarkIcon class="h-3.5 w-3.5" />
                </button>
                <ChevronDownIcon class="h-4 w-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" />
            </span>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="scale-95 opacity-0"
            enter-to-class="scale-100 opacity-1"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="scale-100 opacity-1"
            leave-to-class="scale-95 opacity-0"
        >
            <div v-if="open"
                class="absolute z-50 mt-1 w-full rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xl ring-1 ring-black/5 overflow-hidden"
                style="min-width: 220px;"
            >
                <!-- Search input -->
                <div class="flex items-center gap-2 px-3 py-2 border-b border-slate-100 dark:border-slate-800">
                    <MagnifyingGlassIcon class="h-4 w-4 text-slate-400 shrink-0" />
                    <input
                        ref="inputRef"
                        v-model="search"
                        type="text"
                        placeholder="Search…"
                        class="flex-1 bg-transparent text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none"
                        autocomplete="off"
                    />
                </div>

                <!-- Options list -->
                <ul ref="listRef" class="max-h-56 overflow-y-auto py-1 scrollbar-thin">
                    <li v-if="!required || !props.modelValue"
                        @click="select({ value: '', label: placeholder })"
                        class="px-3 py-2 text-sm text-slate-400 dark:text-slate-500 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 italic">
                        {{ placeholder }}
                    </li>
                    <li
                        v-for="(option, idx) in filtered"
                        :key="option.value"
                        @click="select(option)"
                        class="px-3 py-2 cursor-pointer select-none transition-colors"
                        :class="[
                            String(option.value) === String(modelValue)
                                ? 'bg-brand-600 text-white'
                                : idx === highlighted
                                    ? 'bg-slate-100 dark:bg-slate-800'
                                    : 'hover:bg-slate-50 dark:hover:bg-slate-800/60',
                        ]"
                    >
                        <p class="text-sm font-medium leading-tight truncate"
                            :class="String(option.value) === String(modelValue) ? 'text-white' : 'text-slate-900 dark:text-white'">
                            {{ option.label }}
                        </p>
                        <p v-if="option.sublabel"
                            class="text-xs truncate leading-tight mt-0.5"
                            :class="String(option.value) === String(modelValue) ? 'text-brand-100' : 'text-slate-500 dark:text-slate-400'">
                            {{ option.sublabel }}
                        </p>
                    </li>
                    <li v-if="!filtered.length" class="px-3 py-4 text-sm text-slate-400 text-center">No results</li>
                </ul>
            </div>
        </Transition>
    </div>
</template>
