<script setup>
import { computed, reactive, watch } from 'vue';

const props = defineProps({
    soap: { type: Object, default: null },
    loading: { type: Boolean, default: false },
    editing: { type: Boolean, default: false },
    saving: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'cancel', 'save']);

const sectionDefs = [
    {
        key: 'subjective',
        label: 'S — Subjective',
        tone: 'text-indigo-700 bg-indigo-50 ring-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/30 dark:ring-indigo-800',
        fields: [
            { name: 'chief_complaint', label: 'Chief complaint', type: 'text' },
            { name: 'history_of_present_illness', label: 'History of present illness', type: 'textarea' },
            { name: 'review_of_systems', label: 'Review of systems', type: 'textarea' },
        ],
    },
    {
        key: 'objective',
        label: 'O — Objective',
        tone: 'text-sky-700 bg-sky-50 ring-sky-100 dark:text-sky-300 dark:bg-sky-900/30 dark:ring-sky-800',
        fields: [
            { name: 'vitals', label: 'Vitals', type: 'text' },
            { name: 'physical_exam', label: 'Physical exam', type: 'textarea' },
            { name: 'diagnostics', label: 'Diagnostics', type: 'textarea' },
        ],
    },
    {
        key: 'assessment',
        label: 'A — Assessment',
        tone: 'text-emerald-700 bg-emerald-50 ring-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/30 dark:ring-emerald-800',
        fields: [
            { name: 'primary_diagnosis', label: 'Primary diagnosis', type: 'text' },
            { name: 'differential_diagnoses', label: 'Differential diagnoses', type: 'list' },
            { name: 'clinical_reasoning', label: 'Clinical reasoning', type: 'textarea' },
        ],
    },
    {
        key: 'plan',
        label: 'P — Plan',
        tone: 'text-amber-700 bg-amber-50 ring-amber-100 dark:text-amber-300 dark:bg-amber-900/30 dark:ring-amber-800',
        fields: [
            { name: 'medications', label: 'Medications', type: 'list' },
            { name: 'investigations', label: 'Investigations', type: 'list' },
            { name: 'follow_up', label: 'Follow-up', type: 'textarea' },
            { name: 'patient_education', label: 'Patient education', type: 'textarea' },
        ],
    },
];

const draft = reactive({});

function loadDraft() {
    sectionDefs.forEach(({ key, fields }) => {
        draft[key] = {};
        fields.forEach((field) => {
            const value = props.soap?.[key]?.[field.name];
            if (field.type === 'list') {
                draft[key][field.name] = Array.isArray(value) ? [...value] : [];
            } else {
                draft[key][field.name] = value ?? '';
            }
        });
    });
}

watch(() => [props.editing, props.soap], loadDraft, { immediate: true });

function addItem(sectionKey, fieldName) {
    draft[sectionKey][fieldName].push('');
}

function removeItem(sectionKey, fieldName, index) {
    draft[sectionKey][fieldName].splice(index, 1);
}

function onSave() {
    const payload = JSON.parse(JSON.stringify(draft));
    for (const section of sectionDefs) {
        for (const field of section.fields) {
            if (field.type === 'list') {
                payload[section.key][field.name] = payload[section.key][field.name]
                    .map((v) => v.trim())
                    .filter((v) => v.length > 0);
            }
        }
    }
    emit('save', payload);
}

const sections = computed(() =>
    sectionDefs.map((s) => ({
        ...s,
        rows: s.fields.map((f) => ({ ...f, value: props.soap?.[s.key]?.[f.name] })),
    })),
);

function isList(v) {
    return Array.isArray(v);
}
</script>

<template>
    <article class="rounded-2xl bg-white dark:bg-slate-900 p-6 ring-1 ring-slate-200 dark:ring-slate-700 shadow-sm">
        <header class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-slate-900 dark:text-white">Structured SOAP Note</h2>
            <div v-if="soap && !loading" class="flex items-center gap-2">
                <template v-if="!editing">
                    <button
                        type="button"
                        @click="emit('edit')"
                        class="rounded-md px-3 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-300 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 ring-1 ring-indigo-100 dark:ring-indigo-800"
                    >
                        Edit
                    </button>
                </template>
                <template v-else>
                    <button
                        type="button"
                        @click="emit('cancel')"
                        :disabled="saving"
                        class="rounded-md px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700 disabled:opacity-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        @click="onSave"
                        :disabled="saving"
                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{ saving ? 'Saving…' : 'Save changes' }}
                    </button>
                </template>
            </div>
            <span v-else-if="soap" class="text-xs text-slate-400 dark:text-slate-500">Auto-generated</span>
        </header>

        <div v-if="loading" class="space-y-4" aria-busy="true" aria-live="polite">
            <div v-for="i in 4" :key="i" class="space-y-2">
                <div class="h-4 w-24 rounded bg-slate-200 dark:bg-slate-700 animate-pulse" />
                <div class="h-3 w-full rounded bg-slate-100 dark:bg-slate-800 animate-pulse" />
                <div class="h-3 w-5/6 rounded bg-slate-100 dark:bg-slate-800 animate-pulse" />
            </div>
        </div>

        <div v-else-if="!soap" class="py-16 text-center text-sm text-slate-500 dark:text-slate-400">
            <div class="mx-auto mb-3 grid h-10 w-10 place-items-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500">+</div>
            Enter notes on the left and click <span class="font-medium text-slate-700 dark:text-slate-300">Generate SOAP</span>.
        </div>

        <div v-else-if="editing" class="space-y-6">
            <section v-for="section in sectionDefs" :key="section.key">
                <h3 :class="['inline-flex rounded-md px-2.5 py-1 text-xs font-semibold ring-1', section.tone]">
                    {{ section.label }}
                </h3>
                <div class="mt-3 space-y-4">
                    <div v-for="field in section.fields" :key="field.name">
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-1">{{ field.label }}</label>

                        <input
                            v-if="field.type === 'text'"
                            v-model="draft[section.key][field.name]"
                            type="text"
                            class="block w-full rounded-lg border-0 bg-white dark:bg-slate-800 ring-1 ring-inset ring-slate-200 dark:ring-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                        />

                        <textarea
                            v-else-if="field.type === 'textarea'"
                            v-model="draft[section.key][field.name]"
                            rows="3"
                            class="block w-full rounded-lg border-0 bg-white dark:bg-slate-800 ring-1 ring-inset ring-slate-200 dark:ring-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                        />

                        <div v-else class="space-y-2">
                            <div
                                v-for="(_, idx) in draft[section.key][field.name]"
                                :key="idx"
                                class="flex items-center gap-2"
                            >
                                <input
                                    v-model="draft[section.key][field.name][idx]"
                                    type="text"
                                    class="block w-full rounded-lg border-0 bg-white dark:bg-slate-800 ring-1 ring-inset ring-slate-200 dark:ring-slate-700 px-3 py-2 text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                                />
                                <button
                                    type="button"
                                    @click="removeItem(section.key, field.name, idx)"
                                    :aria-label="`Remove ${field.label} item`"
                                    class="text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 text-sm px-2"
                                >
                                    ×
                                </button>
                            </div>
                            <button
                                type="button"
                                @click="addItem(section.key, field.name)"
                                class="text-xs text-indigo-600 hover:text-indigo-700"
                            >
                                + Add item
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div v-else class="space-y-6">
            <section v-for="section in sections" :key="section.key">
                <h3 :class="['inline-flex rounded-md px-2.5 py-1 text-xs font-semibold ring-1', section.tone]">
                    {{ section.label }}
                </h3>
                <dl class="mt-3 space-y-3 text-sm">
                    <div v-for="row in section.rows" :key="row.name">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ row.label }}</dt>
                        <dd class="mt-1 text-slate-700 dark:text-slate-300">
                            <ul v-if="isList(row.value)" class="list-disc pl-5 space-y-1">
                                <li v-for="(item, i) in row.value" :key="i">{{ item }}</li>
                                <li v-if="!row.value.length" class="list-none italic text-slate-400 dark:text-slate-500">None.</li>
                            </ul>
                            <p v-else class="whitespace-pre-line">{{ row.value || 'Not documented.' }}</p>
                        </dd>
                    </div>
                </dl>
            </section>
        </div>
    </article>
</template>
