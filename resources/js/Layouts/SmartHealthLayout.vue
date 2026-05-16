<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useDark, useToggle, onClickOutside } from '@vueuse/core';
import {
    HomeIcon, UserGroupIcon, UsersIcon, CalendarDaysIcon, MapPinIcon,
    BriefcaseIcon, AcademicCapIcon, DocumentTextIcon, BanknotesIcon,
    ChatBubbleLeftRightIcon, BellIcon,
    SunIcon, MoonIcon, ChevronDownIcon, Bars3Icon, XMarkIcon, SparklesIcon,
    ArrowLeftStartOnRectangleIcon, ShieldCheckIcon, ChartBarIcon,
    ClipboardDocumentListIcon, QueueListIcon, ArrowTopRightOnSquareIcon,
    BuildingOffice2Icon, PlusCircleIcon, CheckCircleIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    title: { type: String, default: '' },
    breadcrumbs: { type: Array, default: () => [] },
    fullBleed: { type: Boolean, default: false },
});

const page = usePage();
const user        = computed(() => page.props.auth?.user);
const role        = computed(() => user.value?.role);
const unreadMsgs  = computed(() => page.props.unread_messages ?? 0);
const notifications = computed(() => page.props.notifications ?? []);

const isDark = useDark({
    storageKey: 'smart-scribe-theme',
    valueDark: 'dark',
    valueLight: 'light',
});
const toggleDark = useToggle(isDark);

const sidebarOpen   = ref(false);
const profileOpen   = ref(false);
const bellOpen      = ref(false);
const bellRef       = ref(null);
onClickOutside(bellRef, () => { bellOpen.value = false; });

function markAllRead() {
    router.post(route('notifications.markAllRead'), {}, { preserveScroll: true });
    bellOpen.value = false;
}

function openNotification(url) {
    bellOpen.value = false;
    if (url) router.visit(url);
}

const navGroups = computed(() => {
    const groups = [
        {
            title: 'Main Menu',
            items: [
                { name: 'Dashboard', href: route('dashboard'), icon: HomeIcon, route: 'dashboard' },
            ],
        },
    ];

    if (role.value === 'admin' || role.value === 'doctor') {
        groups.push({
            title: 'Clinic',
            items: [
                ...(role.value === 'admin' ? [{ name: 'Doctors', href: route('doctors.index'), icon: UserGroupIcon, route: 'doctors.*' }] : []),
                { name: 'Patients', href: route('patients.index'), icon: UsersIcon, route: 'patients.*' },
                { name: 'Appointments', href: route('appointments.index'), icon: CalendarDaysIcon, route: 'appointments.*' },
                ...(role.value === 'admin' ? [
                    { name: 'Locations', href: route('locations.index'), icon: MapPinIcon, route: 'locations.*' },
                    { name: 'Services', href: route('services.index'), icon: BriefcaseIcon, route: 'services.*' },
                    { name: 'Specializations', href: route('specializations.index'), icon: AcademicCapIcon, route: 'specializations.*' },
                ] : []),
            ],
        });
    }

    if (role.value === 'patient') {
        groups.push({
            title: 'My Care',
            items: [
                { name: 'My Appointments', href: route('appointments.index'), icon: CalendarDaysIcon, route: () => { const c = route().current(); return !!c?.startsWith('appointments.') && c !== 'appointments.book'; } },
                { name: 'Book Appointment', href: route('appointments.book'), icon: PlusCircleIcon, route: 'appointments.book' },
            ],
        });
    }

    const opsItems = [
        { name: 'Billing', href: route('invoices.index'), icon: BanknotesIcon, route: 'invoices.*' },
        { name: 'Messages', href: route('messages.index'), icon: ChatBubbleLeftRightIcon, route: 'messages.*', badge: unreadMsgs.value || null },
    ];
    if (role.value === 'admin' || role.value === 'doctor') {
        opsItems.push({ name: 'Insurance Providers', href: route('insurance-providers.index'), icon: BuildingOffice2Icon, route: 'insurance-providers.*' });
        opsItems.push({ name: 'Insurance Claims', href: route('claims.index'), icon: ShieldCheckIcon, route: 'claims.*' });
        opsItems.push({ name: 'Waitlist', href: route('waitlist.index'), icon: QueueListIcon, route: 'waitlist.*' });
        opsItems.push({ name: 'Care Plans', href: route('care-plans.index'), icon: ClipboardDocumentListIcon, route: 'care-plans.*' });
        opsItems.push({ name: 'Referrals', href: route('referrals.index'), icon: ArrowTopRightOnSquareIcon, route: 'referrals.*' });
    }
    if (role.value === 'admin') {
        opsItems.push({ name: 'Analytics', href: route('analytics.index'), icon: ChartBarIcon, route: 'analytics.*' });
    }
    groups.push({ title: 'Operations', items: opsItems });

    return groups;
});

const isActive = (pattern) => {
    if (typeof pattern === 'function') return pattern();
    const current = route().current();
    if (!current) return false;
    if (pattern.endsWith('.*')) {
        return current.startsWith(pattern.slice(0, -2));
    }
    return current === pattern;
};

const logout = () => router.post(route('logout'));
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-700 dark:text-slate-200">
        <!-- Sidebar (mobile overlay) -->
        <div v-if="sidebarOpen" class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden" @click="sidebarOpen = false"></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 transform transition-transform lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex h-16 items-center justify-between px-5 border-b border-slate-200 dark:border-slate-800">
                <Link :href="route('dashboard')" class="flex items-center gap-2">
                    <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-white">
                        <SparklesIcon class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-semibold text-slate-900 dark:text-white">SmartHealth</span>
                </Link>
                <button class="lg:hidden text-slate-500" @click="sidebarOpen = false">
                    <XMarkIcon class="h-6 w-6" />
                </button>
            </div>

            <div class="px-4 py-4">
                <div class="flex items-center gap-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 px-3 py-2.5 border border-slate-200 dark:border-slate-700">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-brand-600 text-white text-sm font-semibold">T</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">Trustcare Clinic</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Las Vegas</p>
                    </div>
                </div>
            </div>

            <nav class="px-3 pb-6 space-y-6 overflow-y-auto scrollbar-thin h-[calc(100vh-9rem)]">
                <div v-for="group in navGroups" :key="group.title">
                    <p class="px-3 mb-1.5 text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                        {{ group.title }}
                    </p>
                    <ul class="space-y-0.5">
                        <li v-for="item in group.items" :key="item.name">
                            <Link
                                :href="item.href"
                                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                                :class="isActive(item.route)
                                    ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30'
                                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                            >
                                <component :is="item.icon" class="h-5 w-5 shrink-0" />
                                <span class="flex-1">{{ item.name }}</span>
                                <span
                                    v-if="item.badge"
                                    class="ml-auto min-w-[1.25rem] h-5 px-1 rounded-full text-xs font-bold flex items-center justify-center"
                                    :class="isActive(item.route) ? 'bg-white text-brand-600' : 'bg-brand-600 text-white'"
                                >{{ item.badge }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main column -->
        <div class="lg:pl-64">
            <!-- Topbar -->
            <header class="sticky top-0 z-30 bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800">
                <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8 gap-4">
                    <div class="flex items-center gap-3">
                        <button class="lg:hidden text-slate-500" @click="sidebarOpen = true">
                            <Bars3Icon class="h-6 w-6" />
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="toggleDark()">
                            <SunIcon v-if="isDark" class="h-5 w-5" />
                            <MoonIcon v-else class="h-5 w-5" />
                        </button>
                        <!-- Notifications bell -->
                        <div ref="bellRef" class="relative">
                            <button
                                @click="bellOpen = !bellOpen"
                                class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 relative"
                            >
                                <BellIcon class="h-5 w-5" />
                                <span
                                    v-if="notifications.length"
                                    class="absolute top-1 right-1 min-w-[1rem] h-4 px-0.5 rounded-full bg-danger text-white text-[10px] font-bold flex items-center justify-center"
                                >{{ notifications.length > 9 ? '9+' : notifications.length }}</span>
                            </button>

                            <Transition
                                enter-active-class="transition duration-100 ease-out"
                                enter-from-class="scale-95 opacity-0"
                                enter-to-class="scale-100 opacity-100"
                                leave-active-class="transition duration-75 ease-in"
                                leave-from-class="scale-100 opacity-100"
                                leave-to-class="scale-95 opacity-0"
                            >
                                <div
                                    v-if="bellOpen"
                                    class="absolute right-0 mt-2 w-80 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-xl z-50 overflow-hidden"
                                >
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 dark:border-slate-800">
                                        <p class="text-sm font-semibold text-slate-900 dark:text-white">Notifications</p>
                                        <button
                                            v-if="notifications.length"
                                            @click="markAllRead"
                                            class="text-xs text-brand-600 dark:text-brand-400 hover:underline flex items-center gap-1"
                                        >
                                            <CheckCircleIcon class="h-3.5 w-3.5" /> Mark all read
                                        </button>
                                    </div>

                                    <ul class="max-h-80 overflow-y-auto scrollbar-thin divide-y divide-slate-100 dark:divide-slate-800">
                                        <li v-if="!notifications.length" class="px-4 py-6 text-center text-sm text-slate-400">
                                            No new notifications
                                        </li>
                                        <li
                                            v-for="n in notifications"
                                            :key="n.id"
                                            @click="openNotification(n.url)"
                                            class="px-4 py-3 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                        >
                                            <p class="text-sm font-medium text-slate-900 dark:text-white leading-snug">{{ n.title }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-2">{{ n.body }}</p>
                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">{{ n.created_at }}</p>
                                        </li>
                                    </ul>
                                </div>
                            </Transition>
                        </div>
                        <div class="relative">
                            <button class="flex items-center gap-2 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800" @click="profileOpen = !profileOpen">
                                <span class="grid h-8 w-8 place-items-center rounded-full bg-brand-600 text-white text-sm font-semibold">
                                    {{ user?.name?.[0]?.toUpperCase() ?? '?' }}
                                </span>
                                <ChevronDownIcon class="h-4 w-4 text-slate-400 hidden sm:block" />
                            </button>
                            <div v-if="profileOpen" class="absolute right-0 mt-2 w-56 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-lg py-1 z-50">
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-800">
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ user?.name }}</p>
                                    <p class="text-xs text-slate-500 capitalize">{{ user?.role }}</p>
                                </div>
                                <button class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800" @click="logout">
                                    <ArrowLeftStartOnRectangleIcon class="h-4 w-4" /> Logout
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page header (hidden in full-bleed mode) -->
            <div v-if="!fullBleed" class="px-4 sm:px-6 lg:px-8 pt-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 v-if="title" class="text-2xl font-bold text-slate-900 dark:text-white">{{ title }}</h1>
                        <nav v-if="breadcrumbs.length" class="text-sm text-slate-500 mt-1">
                            <span v-for="(b, idx) in breadcrumbs" :key="idx">
                                <Link v-if="b.href" :href="b.href" class="hover:text-brand-600">{{ b.label }}</Link>
                                <span v-else>{{ b.label }}</span>
                                <span v-if="idx < breadcrumbs.length - 1" class="mx-2 text-slate-300">/</span>
                            </span>
                        </nav>
                    </div>
                    <div class="flex items-center gap-2">
                        <slot name="actions" />
                    </div>
                </div>
            </div>

            <main :class="fullBleed ? 'overflow-hidden' : 'px-4 sm:px-6 lg:px-8 py-6'">
                <div v-if="!fullBleed && $page.props.flash?.success" class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300">
                    {{ $page.props.flash.success }}
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
