<script setup>
import { computed, ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useDark, useToggle } from '@vueuse/core';
import {
    HomeIcon, UserGroupIcon, UsersIcon, CalendarDaysIcon, MapPinIcon,
    BriefcaseIcon, AcademicCapIcon, DocumentTextIcon, BanknotesIcon,
    ChatBubbleLeftRightIcon, BellIcon, MagnifyingGlassIcon,
    SunIcon, MoonIcon, ChevronDownIcon, Bars3Icon, XMarkIcon, SparklesIcon,
    ArrowLeftStartOnRectangleIcon, ShieldCheckIcon, ChartBarIcon,
    ClipboardDocumentListIcon, QueueListIcon, ArrowTopRightOnSquareIcon,
} from '@heroicons/vue/24/outline';

defineProps({
    title: { type: String, default: '' },
    breadcrumbs: { type: Array, default: () => [] },
    fullBleed: { type: Boolean, default: false },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role);

const isDark = useDark({
    storageKey: 'smart-scribe-theme',
    valueDark: 'dark',
    valueLight: 'light',
});
const toggleDark = useToggle(isDark);

const sidebarOpen = ref(false);
const profileOpen = ref(false);

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
                { name: 'Consultations', href: route('consultations.index'), icon: DocumentTextIcon, route: 'consultations.*' },
                ...(role.value === 'admin' ? [
                    { name: 'Locations', href: route('locations.index'), icon: MapPinIcon, route: 'locations.*' },
                    { name: 'Services', href: route('services.index'), icon: BriefcaseIcon, route: 'services.*' },
                    { name: 'Specializations', href: route('specializations.index'), icon: AcademicCapIcon, route: 'specializations.*' },
                ] : []),
            ],
        });
    }

    const opsItems = [
        { name: 'Billing', href: route('invoices.index'), icon: BanknotesIcon, route: 'invoices.*' },
        { name: 'Messages', href: route('messages.index'), icon: ChatBubbleLeftRightIcon, route: 'messages.*' },
    ];
    if (role.value === 'admin' || role.value === 'doctor') {
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
                    <span class="text-lg font-semibold text-slate-900 dark:text-white">Preclinic</span>
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
                                <component :is="item.icon" class="h-5 w-5" />
                                <span>{{ item.name }}</span>
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
                    <div class="flex items-center gap-3 flex-1">
                        <button class="lg:hidden text-slate-500" @click="sidebarOpen = true">
                            <Bars3Icon class="h-6 w-6" />
                        </button>
                        <div class="relative max-w-md flex-1 hidden sm:block">
                            <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                            <input
                                type="text"
                                placeholder="Search..."
                                class="w-full pl-10 pr-12 py-2 rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500"
                            />
                            <kbd class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-slate-400 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded px-1.5 py-0.5">⌘</kbd>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-medium shadow">
                            <SparklesIcon class="h-4 w-4" /> AI Assistance
                        </button>
                        <button class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="toggleDark()">
                            <SunIcon v-if="isDark" class="h-5 w-5" />
                            <MoonIcon v-else class="h-5 w-5" />
                        </button>
                        <button class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 relative">
                            <BellIcon class="h-5 w-5" />
                            <span class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-danger"></span>
                        </button>
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
