<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { useDark, useToggle } from '@vueuse/core';
import { SparklesIcon, SunIcon, MoonIcon } from '@heroicons/vue/24/outline';

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post(route('login'));

const isDark = useDark({ storageKey: 'smart-scribe-theme', valueDark: 'dark', valueLight: 'light' });
const toggleDark = useToggle(isDark);
</script>

<template>
    <Head title="Login" />
    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950">
        <div class="hidden lg:flex flex-1 bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 text-white p-12 items-center justify-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 30%, white 1px, transparent 1px), radial-gradient(circle at 80% 70%, white 1px, transparent 1px); background-size: 60px 60px;"></div>
            <div class="relative max-w-md">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white/20 backdrop-blur mb-8">
                    <SparklesIcon class="h-7 w-7" />
                </div>
                <h2 class="text-4xl font-bold mb-4">Welcome back to Preclinic</h2>
                <p class="text-brand-100 text-lg">Streamlined clinical operations — patients, appointments, billing, and messaging in one place.</p>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="flex justify-end mb-6">
                    <button class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="toggleDark()">
                        <SunIcon v-if="isDark" class="h-5 w-5" />
                        <MoonIcon v-else class="h-5 w-5" />
                    </button>
                </div>
                <div class="flex items-center gap-2 mb-8 lg:hidden">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 text-white">
                        <SparklesIcon class="h-5 w-5" />
                    </span>
                    <span class="text-xl font-semibold text-slate-900 dark:text-white">Preclinic</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Sign in to your account</h1>
                <p class="mt-1 text-sm text-slate-500">Enter your credentials to access the dashboard.</p>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</span>
                        <input v-model="form.email" type="email" required class="w-full px-3 py-2.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" placeholder="you@example.com" />
                        <span v-if="form.errors.email" class="block mt-1 text-xs text-red-600">{{ form.errors.email }}</span>
                    </label>
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password</span>
                        <input v-model="form.password" type="password" required class="w-full px-3 py-2.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        <span v-if="form.errors.password" class="block mt-1 text-xs text-red-600">{{ form.errors.password }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input v-model="form.remember" type="checkbox" class="rounded" /> Remember me
                    </label>
                    <button type="submit" :disabled="form.processing" class="w-full py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm shadow-sm shadow-brand-600/30 disabled:opacity-50">
                        {{ form.processing ? 'Signing in…' : 'Sign In' }}
                    </button>
                </form>

                <p class="mt-6 text-sm text-slate-500 text-center">
                    No account? <Link :href="route('register')" class="text-brand-600 font-medium hover:underline">Register</Link>
                </p>

                <div class="mt-8 rounded-lg bg-slate-100 dark:bg-slate-800 p-4 text-xs text-slate-600 dark:text-slate-400 space-y-1">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Demo accounts (password: <code>password</code>)</p>
                    <p>Admin: admin@example.com</p>
                    <p>Doctor: sarah.johnson@clinic.test</p>
                    <p>Patient: patient@example.com</p>
                </div>
            </div>
        </div>
    </div>
</template>
