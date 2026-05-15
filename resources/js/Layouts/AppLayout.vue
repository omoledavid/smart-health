<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: '' },
});
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <header class="bg-white ring-1 ring-slate-200">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <Link :href="route('consultations.index')" class="flex items-center gap-2 text-slate-900">
                    <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-600 text-white font-semibold">S</span>
                    <span class="text-lg font-semibold tracking-tight">Smart Scribe</span>
                </Link>
                <nav class="text-sm text-slate-500">
                    <Link :href="route('consultations.index')" class="hover:text-indigo-600">Consultations</Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <h1 v-if="title" class="mb-6 text-2xl font-semibold text-slate-900">{{ title }}</h1>
            <slot />
        </main>
    </div>
</template>
