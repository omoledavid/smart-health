<script setup>
import { computed, ref, nextTick, watch } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import SearchableSelect from '@/Components/SearchableSelect.vue';
import {
    PlusIcon, MagnifyingGlassIcon, PhoneIcon, VideoCameraIcon,
    InformationCircleIcon, PaperAirplaneIcon, FaceSmileIcon,
    PaperClipIcon, EllipsisVerticalIcon,
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import isToday from 'dayjs/plugin/isToday';
import isYesterday from 'dayjs/plugin/isYesterday';

dayjs.extend(relativeTime);
dayjs.extend(isToday);
dayjs.extend(isYesterday);

const props = defineProps({
    threads: { type: Array, default: () => [] },
    active_thread: { type: Object, default: null },
    patients: { type: Array, default: () => [] },
    doctors: { type: Array, default: () => [] },
});

const page = usePage();
const me = computed(() => page.props.auth?.user);
const user = computed(() => page.props.auth?.user);

// Thread search
const threadSearch = ref('');
const filteredThreads = computed(() => {
    if (!threadSearch.value) return props.threads;
    const q = threadSearch.value.toLowerCase();
    return props.threads.filter(t =>
        (t.patient ?? '').toLowerCase().includes(q) ||
        (t.subject ?? '').toLowerCase().includes(q) ||
        (t.last_message ?? '').toLowerCase().includes(q)
    );
});

// Compose
const showCompose = ref(false);
const composeForm = useForm({ patient_id: '', doctor_id: '', subject: '', body: '' });

const patientOptions = computed(() => props.patients.map(p => ({
    value: p.id,
    label: `${p.last_name}, ${p.first_name}`,
    sublabel: p.email,
})));
const doctorOptions = computed(() => props.doctors.map(d => ({
    value: d.id,
    label: d.name,
})));

function sendCompose() {
    composeForm.post(route('messages.store'), {
        onSuccess: () => { composeForm.reset(); showCompose.value = false; },
    });
}

// Reply
const replyForm = useForm({ body: '' });
const messagesEl = ref(null);

function sendReply() {
    if (!replyForm.body.trim() || !props.active_thread) return;
    replyForm.post(route('messages.reply', props.active_thread.id), {
        onSuccess: () => replyForm.reset(),
    });
}

// Auto-scroll to bottom when messages change
watch(() => props.active_thread?.messages?.length, () => {
    nextTick(() => {
        if (messagesEl.value) {
            messagesEl.value.scrollTop = messagesEl.value.scrollHeight;
        }
    });
}, { immediate: true });

// Group messages by date
const groupedMessages = computed(() => {
    if (!props.active_thread?.messages) return [];
    const groups = [];
    let lastDate = null;
    for (const msg of props.active_thread.messages) {
        const d = dayjs(msg.created_at);
        const dateKey = d.format('YYYY-MM-DD');
        if (dateKey !== lastDate) {
            let label;
            if (d.isToday()) label = 'Today';
            else if (d.isYesterday()) label = 'Yesterday';
            else label = d.format('DD MMM YYYY');
            groups.push({ type: 'separator', label });
            lastDate = dateKey;
        }
        groups.push({ type: 'message', ...msg });
    }
    return groups;
});

function formatTime(iso) {
    return dayjs(iso).format('hh:mm A');
}
function formatThreadTime(iso) {
    if (!iso) return '';
    const d = dayjs(iso);
    if (d.isToday()) return d.format('hh:mm A');
    if (d.isYesterday()) return 'yesterday';
    return d.format('DD MMM');
}

// Avatar color hash
const avatarColors = [
    'bg-violet-500', 'bg-sky-500', 'bg-emerald-500',
    'bg-rose-500', 'bg-amber-500', 'bg-teal-500', 'bg-pink-500',
];
function avatarColor(name) {
    let h = 0;
    for (let i = 0; i < (name ?? '').length; i++) h += (name ?? '').charCodeAt(i);
    return avatarColors[h % avatarColors.length];
}
</script>

<template>
    <Head title="Messages" />
    <PreclinicLayout title="" :full-bleed="true">
        <div class="flex h-[calc(100vh-4rem)] overflow-hidden">

                <!-- ── LEFT PANEL ── -->
                <div class="flex flex-col w-80 shrink-0 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">

                    <!-- Panel header -->
                    <div class="flex items-center justify-between px-4 py-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-600 text-white font-semibold text-sm">
                                {{ (user?.name?.[0] ?? '?').toUpperCase() }}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white leading-tight">{{ user?.name }}</p>
                                <p class="text-xs text-slate-500 capitalize leading-tight">{{ user?.role }}</p>
                            </div>
                        </div>
                        <button @click="showCompose = !showCompose"
                            class="grid h-8 w-8 place-items-center rounded-lg bg-brand-600 hover:bg-brand-700 text-white transition-colors">
                            <PlusIcon class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Compose form (inline) -->
                    <div v-if="showCompose" class="border-b border-slate-100 dark:border-slate-800 px-4 py-3 space-y-2 bg-slate-50 dark:bg-slate-800/50">
                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">New Conversation</p>
                        <SearchableSelect v-if="patients.length" v-model="composeForm.patient_id" :options="patientOptions" placeholder="Patient…" />
                        <SearchableSelect v-if="doctors.length" v-model="composeForm.doctor_id" :options="doctorOptions" placeholder="Doctor (optional)" />
                        <input v-model="composeForm.subject" placeholder="Subject…"
                            class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs" />
                        <textarea v-model="composeForm.body" rows="2" required placeholder="Message…"
                            class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs resize-none"></textarea>
                        <div class="flex gap-2 justify-end">
                            <button type="button" @click="showCompose = false" class="px-3 py-1 text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">Cancel</button>
                            <button type="button" @click="sendCompose" :disabled="composeForm.processing"
                                class="px-3 py-1 rounded-lg bg-brand-600 text-white text-xs font-medium disabled:opacity-50">Send</button>
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                        <div class="relative">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" />
                            <input v-model="threadSearch" type="text" placeholder="Search Keyword"
                                class="w-full pl-9 pr-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        </div>
                    </div>

                    <!-- Thread list -->
                    <div class="overflow-y-auto flex-1 scrollbar-thin">
                        <p class="px-4 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">All Messages</p>
                        <ul>
                            <li v-for="t in filteredThreads" :key="t.id">
                                <Link :href="route('messages.show', t.id)"
                                    class="flex items-center gap-3 px-4 py-3 transition-colors"
                                    :class="active_thread?.id === t.id
                                        ? 'bg-brand-600 text-white'
                                        : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'">
                                    <!-- Avatar -->
                                    <span class="relative grid h-10 w-10 shrink-0 place-items-center rounded-full text-white text-sm font-semibold"
                                        :class="active_thread?.id === t.id ? 'bg-white/20' : avatarColor(t.patient)">
                                        {{ t.patient_initials }}
                                        <span v-if="t.unread_count > 0"
                                            class="absolute -top-0.5 -right-0.5 h-4 w-4 grid place-items-center rounded-full bg-danger text-white text-[9px] font-bold">
                                            {{ t.unread_count > 9 ? '9+' : t.unread_count }}
                                        </span>
                                    </span>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-baseline justify-between gap-1">
                                            <p class="text-sm font-semibold truncate"
                                                :class="active_thread?.id === t.id ? 'text-white' : 'text-slate-900 dark:text-white'">
                                                {{ t.patient || t.subject || 'Conversation' }}
                                            </p>
                                            <span class="text-[11px] shrink-0"
                                                :class="active_thread?.id === t.id ? 'text-white/70' : 'text-slate-400'">
                                                {{ formatThreadTime(t.last_message_at) }}
                                            </span>
                                        </div>
                                        <p class="text-xs truncate mt-0.5"
                                            :class="active_thread?.id === t.id ? 'text-white/80' : 'text-slate-500 dark:text-slate-400'">
                                            {{ t.last_message || t.subject || '—' }}
                                        </p>
                                    </div>

                                    <!-- Read tick (no unread) -->
                                    <span v-if="!t.unread_count" class="text-xs shrink-0"
                                        :class="active_thread?.id === t.id ? 'text-white/60' : 'text-emerald-500'">✓✓</span>
                                </Link>
                            </li>
                            <li v-if="!filteredThreads.length" class="px-4 py-8 text-center text-sm text-slate-400">
                                No conversations yet.
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- ── RIGHT PANEL ── -->
                <div class="flex flex-col flex-1 min-w-0 bg-white dark:bg-slate-950">

                    <!-- Empty state -->
                    <div v-if="!active_thread" class="flex-1 flex flex-col items-center justify-center gap-4 text-center p-8">
                        <div class="grid h-20 w-20 place-items-center rounded-full bg-brand-50 dark:bg-brand-900/20">
                            <svg class="h-10 w-10 text-brand-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-semibold text-slate-700 dark:text-slate-300">Select a conversation</p>
                            <p class="text-sm text-slate-400 mt-1">Choose a thread from the left to start messaging</p>
                        </div>
                    </div>

                    <template v-else>
                        <!-- Conversation header -->
                        <div class="flex items-center justify-between px-6 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                            <div class="flex items-center gap-3">
                                <span class="relative grid h-10 w-10 shrink-0 place-items-center rounded-full text-white text-sm font-semibold"
                                    :class="avatarColor(active_thread.patient)">
                                    {{ active_thread.patient_initials }}
                                    <span class="absolute bottom-0 right-0 h-3 w-3 rounded-full bg-emerald-400 border-2 border-white dark:border-slate-900"></span>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white leading-tight">
                                        {{ active_thread.patient || active_thread.subject || 'Conversation' }}
                                    </p>
                                    <p class="text-xs text-emerald-500 leading-tight">Online</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1">
                                <button class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    <PhoneIcon class="h-4.5 w-4.5 h-[18px] w-[18px]" />
                                </button>
                                <button class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    <VideoCameraIcon class="h-[18px] w-[18px]" />
                                </button>
                                <button class="grid h-9 w-9 place-items-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    <InformationCircleIcon class="h-[18px] w-[18px]" />
                                </button>
                            </div>
                        </div>

                        <!-- Messages area -->
                        <div ref="messagesEl" class="flex-1 overflow-y-auto px-6 py-4 space-y-1 scrollbar-thin">
                            <template v-for="item in groupedMessages" :key="item.id ?? item.label">

                                <!-- Date separator -->
                                <div v-if="item.type === 'separator'" class="flex items-center gap-3 py-3">
                                    <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                                    <span class="text-xs font-medium text-slate-400 dark:text-slate-500 px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800">
                                        {{ item.label }}
                                    </span>
                                    <div class="flex-1 h-px bg-slate-100 dark:bg-slate-800"></div>
                                </div>

                                <!-- Message bubble -->
                                <div v-else
                                    class="flex items-end gap-3 group"
                                    :class="item.sender_id === me?.id ? 'flex-row-reverse' : 'flex-row'">

                                    <!-- Avatar -->
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full text-white text-xs font-semibold mb-1"
                                        :class="avatarColor(item.sender_name)">
                                        {{ item.sender_initials }}
                                    </span>

                                    <!-- Bubble + meta -->
                                    <div class="max-w-sm lg:max-w-md" :class="item.sender_id === me?.id ? 'items-end' : 'items-start'" style="display:flex;flex-direction:column;">
                                        <!-- Sender name + time (others only) -->
                                        <div v-if="item.sender_id !== me?.id" class="flex items-baseline gap-2 mb-1 px-1">
                                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ item.sender_name }}</span>
                                            <span class="text-[11px] text-slate-400">{{ formatTime(item.created_at) }}</span>
                                        </div>

                                        <div class="flex items-end gap-2"
                                            :class="item.sender_id === me?.id ? 'flex-row-reverse' : 'flex-row'">
                                            <!-- Bubble -->
                                            <div class="relative px-4 py-2.5 rounded-2xl text-sm leading-relaxed"
                                                :class="item.sender_id === me?.id
                                                    ? 'bg-brand-600 text-white rounded-br-sm'
                                                    : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 rounded-bl-sm'">
                                                {{ item.body }}
                                            </div>

                                            <!-- Three-dot menu (visible on hover) -->
                                            <button class="opacity-0 group-hover:opacity-100 transition-opacity grid h-6 w-6 place-items-center rounded-full text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                                                <EllipsisVerticalIcon class="h-4 w-4" />
                                            </button>
                                        </div>

                                        <!-- Time + read status (own messages) -->
                                        <div v-if="item.sender_id === me?.id" class="flex items-center gap-1 mt-1 px-1">
                                            <span class="text-[11px] text-slate-400">{{ formatTime(item.created_at) }}</span>
                                            <span class="text-[11px]" :class="item.read_at ? 'text-brand-500' : 'text-slate-400'">
                                                {{ item.read_at ? '✓✓' : '✓' }}
                                            </span>
                                            <span class="text-[11px] text-slate-400">· You</span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div v-if="!groupedMessages.length" class="py-12 text-center text-sm text-slate-400">
                                No messages yet. Say hello!
                            </div>
                        </div>

                        <!-- Reply input -->
                        <div class="border-t border-slate-200 dark:border-slate-800 px-4 py-3 bg-white dark:bg-slate-900">
                            <form @submit.prevent="sendReply" class="flex items-center gap-2">
                                <button type="button" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    <PaperClipIcon class="h-5 w-5" />
                                </button>
                                <input
                                    v-model="replyForm.body"
                                    type="text"
                                    placeholder="Type Something..."
                                    @keydown.enter.prevent="sendReply"
                                    class="flex-1 px-4 py-2.5 rounded-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500 transition-colors"
                                />
                                <button type="button" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    <FaceSmileIcon class="h-5 w-5" />
                                </button>
                                <button
                                    type="submit"
                                    :disabled="replyForm.processing || !replyForm.body.trim()"
                                    class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-brand-600 hover:bg-brand-700 text-white transition-colors disabled:opacity-40"
                                >
                                    <PaperAirplaneIcon class="h-4 w-4 -rotate-45" />
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
        </div>
    </PreclinicLayout>
</template>
