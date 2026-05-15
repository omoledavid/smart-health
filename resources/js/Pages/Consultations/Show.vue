<script setup>
import { ref, computed, watch } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import SmartHealthLayout from '@/Layouts/SmartHealthLayout.vue';
import VoiceTextarea from '@/Components/VoiceTextarea.vue';
import SoapPreview from '@/Components/SoapPreview.vue';

const props = defineProps({
    consultation: { type: Object, required: true },
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const form = useForm({
    patient_name: props.consultation.patient_name,
    raw_notes: props.consultation.raw_notes,
});

const generating = ref(false);
const editing = ref(false);
const savingSoap = ref(false);

watch(() => props.consultation.status, (status) => {
    if (status !== 'generating') generating.value = false;
});

function saveAndGenerate() {
    form.put(route('consultations.update', props.consultation.id), {
        preserveScroll: true,
        onSuccess: generate,
    });
}

function generate() {
    editing.value = false;
    generating.value = true;
    router.post(route('consultations.generate', props.consultation.id), {}, {
        preserveScroll: true,
        onFinish: () => (generating.value = false),
    });
}

function saveSoap(updated) {
    savingSoap.value = true;
    router.patch(
        route('consultations.update', props.consultation.id),
        { structured_soap: updated },
        {
            preserveScroll: true,
            onSuccess: () => (editing.value = false),
            onFinish: () => (savingSoap.value = false),
        },
    );
}

const hasSoap = computed(() => !!props.consultation.structured_soap);
const canExport = computed(() => props.consultation.status === 'completed' && hasSoap.value);
const generateLabel = computed(() => (generating.value
    ? 'Generating…'
    : hasSoap.value ? 'Regenerate' : 'Generate SOAP'));
</script>

<template>
    <Head :title="`${consultation.patient_name} · Consultation`" />
    <SmartHealthLayout>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <Link :href="route('consultations.index')" class="text-sm text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400">
                    ← All consultations
                </Link>
                <h1 class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ consultation.patient_name }}</h1>
            </div>
            <a
                v-if="canExport"
                :href="route('consultations.export', consultation.id)"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 rounded-lg bg-slate-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-slate-900 hover:bg-slate-800 dark:hover:bg-slate-100 transition-colors"
            >
                Export PDF
            </a>
        </div>

        <!-- <div v-if="flash.error" class="mb-4 rounded-lg bg-rose-50 dark:bg-rose-900/20 px-4 py-3 text-sm text-rose-700 dark:text-rose-400 ring-1 ring-rose-100 dark:ring-rose-800">
            {{ flash.error }}
        </div>
        <div v-if="flash.success" class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400 ring-1 ring-emerald-100 dark:ring-emerald-800">
            {{ flash.success }}
        </div> -->

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Raw notes panel -->
            <section aria-label="Raw clinical notes" class="space-y-4">
                <div class="rounded-2xl bg-white dark:bg-slate-900 p-6 ring-1 ring-slate-200 dark:ring-slate-700 shadow-sm space-y-4">
                    <div>
                        <label for="patient_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Patient</label>
                        <input
                            id="patient_name"
                            v-model="form.patient_name"
                            type="text"
                            class="mt-1 block w-full rounded-lg border-0 bg-white dark:bg-slate-800 ring-1 ring-inset ring-slate-200 dark:ring-slate-700 px-3 py-2 text-slate-900 dark:text-white placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Raw notes</label>
                        <VoiceTextarea v-model="form.raw_notes" :disabled="generating" />
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <p class="text-xs text-slate-400 dark:text-slate-500">{{ form.raw_notes.length }} chars</p>
                        <button
                            type="button"
                            @click="saveAndGenerate"
                            :disabled="generating || form.processing || !form.raw_notes.trim()"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 disabled:opacity-50 transition-colors"
                        >
                            <svg v-if="generating" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25" />
                                <path d="M22 12a10 10 0 0 1-10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                            </svg>
                            {{ generateLabel }}
                        </button>
                    </div>
                </div>
            </section>

            <!-- SOAP preview panel -->
            <section aria-label="Structured SOAP preview">
                <SoapPreview
                    :soap="consultation.structured_soap"
                    :loading="generating"
                    :editing="editing"
                    :saving="savingSoap"
                    @edit="editing = true"
                    @cancel="editing = false"
                    @save="saveSoap"
                />
            </section>
        </div>
    </SmartHealthLayout>
</template>
