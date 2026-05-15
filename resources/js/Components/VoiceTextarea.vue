<script setup>
import { computed } from 'vue';
import { useSpeechRecognition } from '../Composables/useSpeechRecognition.js';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Type or dictate your clinical notes…' },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const { isSupported, isListening, interimTranscript, error, toggle } = useSpeechRecognition({
    onFinalResult: (chunk) => {
        const current = props.modelValue?.trim() ?? '';
        const next = current ? `${current} ${chunk}` : chunk;
        emit('update:modelValue', next);
    },
});

const statusMessage = computed(() => {
    if (!isSupported) return 'Voice input not supported in this browser.';
    if (error.value === 'not-allowed') return 'Microphone access denied.';
    return isListening.value ? 'Listening… speak naturally.' : 'Voice input idle.';
});

function onInput(event) {
    emit('update:modelValue', event.target.value);
}
</script>

<template>
    <div class="space-y-2">
        <div class="relative">
            <textarea
                :value="modelValue"
                @input="onInput"
                :placeholder="placeholder"
                :disabled="disabled"
                rows="14"
                aria-label="Clinical notes"
                class="block w-full resize-y rounded-xl border-0 bg-white dark:bg-slate-800 p-4 pr-16 text-slate-900 dark:text-white placeholder:text-slate-400 dark:placeholder:text-slate-500 ring-1 ring-inset ring-slate-200 dark:ring-slate-700 focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:focus:ring-indigo-400 disabled:bg-slate-100 dark:disabled:bg-slate-900 disabled:text-slate-500 dark:disabled:text-slate-600 text-[15px] leading-relaxed shadow-sm"
            />

            <button
                v-if="isSupported"
                type="button"
                @click="toggle"
                :disabled="disabled"
                :aria-pressed="isListening"
                :aria-label="isListening ? 'Stop voice dictation' : 'Start voice dictation'"
                class="absolute right-3 top-3 inline-flex h-11 w-11 items-center justify-center rounded-full text-white shadow-sm transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 disabled:opacity-50"
                :class="isListening
                    ? 'bg-rose-600 hover:bg-rose-700 animate-pulse'
                    : 'bg-indigo-600 hover:bg-indigo-700'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5" aria-hidden="true">
                    <path d="M12 14a3 3 0 0 0 3-3V6a3 3 0 1 0-6 0v5a3 3 0 0 0 3 3Z" />
                    <path d="M19 11a1 1 0 1 0-2 0 5 5 0 0 1-10 0 1 1 0 1 0-2 0 7 7 0 0 0 6 6.92V20H8a1 1 0 1 0 0 2h8a1 1 0 1 0 0-2h-3v-2.08A7 7 0 0 0 19 11Z" />
                </svg>
            </button>
        </div>

        <div class="flex items-center justify-between text-xs">
            <span role="status" aria-live="polite" class="text-slate-500">
                <span v-if="isListening" class="inline-block h-2 w-2 rounded-full bg-rose-500 mr-1.5 align-middle animate-pulse" aria-hidden="true" />
                {{ statusMessage }}
            </span>
            <span v-if="interimTranscript" class="italic text-slate-400 truncate max-w-[60%]">{{ interimTranscript }}</span>
        </div>
    </div>
</template>
