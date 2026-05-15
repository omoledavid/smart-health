import { ref, onBeforeUnmount } from 'vue';

export function useSpeechRecognition({ lang = 'en-US', onFinalResult } = {}) {
    const SpeechRecognition =
        typeof window !== 'undefined'
            ? window.SpeechRecognition || window.webkitSpeechRecognition
            : null;

    const isSupported = !!SpeechRecognition;
    const isListening = ref(false);
    const interimTranscript = ref('');
    const error = ref(null);

    let recognition = null;
    let userIntent = false;

    if (isSupported) {
        recognition = new SpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = lang;

        recognition.onresult = (event) => {
            let interim = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                const result = event.results[i];
                const transcript = result[0].transcript;
                if (result.isFinal) {
                    onFinalResult?.(transcript.trim());
                } else {
                    interim += transcript;
                }
            }
            interimTranscript.value = interim;
        };

        recognition.onerror = (event) => {
            error.value = event.error;
            if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                userIntent = false;
                isListening.value = false;
            }
        };

        recognition.onend = () => {
            if (userIntent) {
                try {
                    recognition.start();
                } catch {
                    isListening.value = false;
                }
            } else {
                isListening.value = false;
                interimTranscript.value = '';
            }
        };
    }

    function start() {
        if (!isSupported || isListening.value) return;
        error.value = null;
        userIntent = true;
        try {
            recognition.start();
            isListening.value = true;
        } catch (e) {
            error.value = e.message;
            userIntent = false;
        }
    }

    function stop() {
        if (!isSupported) return;
        userIntent = false;
        recognition.stop();
    }

    function toggle() {
        isListening.value ? stop() : start();
    }

    onBeforeUnmount(() => {
        if (recognition) {
            userIntent = false;
            recognition.onresult = null;
            recognition.onerror = null;
            recognition.onend = null;
            try { recognition.stop(); } catch {}
        }
    });

    return { isSupported, isListening, interimTranscript, error, start, stop, toggle };
}
