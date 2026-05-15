<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import { SparklesIcon } from '@heroicons/vue/24/outline';

const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });
const submit = () => form.post(route('register'));
</script>

<template>
    <Head title="Register" />
    <div class="min-h-screen flex bg-slate-50 dark:bg-slate-950">
        <div class="hidden lg:flex flex-1 bg-gradient-to-br from-brand-600 via-brand-700 to-brand-900 text-white p-12 items-center justify-center">
            <div class="max-w-md">
                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white/20 backdrop-blur mb-8">
                    <SparklesIcon class="h-7 w-7" />
                </div>
                <h2 class="text-4xl font-bold mb-4">Join SmartHealth</h2>
                <p class="text-brand-100 text-lg">Create your patient portal account to book appointments and manage your care.</p>
            </div>
        </div>
        <div class="flex-1 flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Create your account</h1>
                <p class="mt-1 text-sm text-slate-500">As a new patient, you'll complete onboarding next.</p>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Full name</span>
                        <input v-model="form.name" required class="w-full px-3 py-2.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        <span v-if="form.errors.name" class="block mt-1 text-xs text-red-600">{{ form.errors.name }}</span>
                    </label>
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email</span>
                        <input v-model="form.email" type="email" required class="w-full px-3 py-2.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        <span v-if="form.errors.email" class="block mt-1 text-xs text-red-600">{{ form.errors.email }}</span>
                    </label>
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password</span>
                        <input v-model="form.password" type="password" required class="w-full px-3 py-2.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                        <span v-if="form.errors.password" class="block mt-1 text-xs text-red-600">{{ form.errors.password }}</span>
                    </label>
                    <label class="block">
                        <span class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Confirm password</span>
                        <input v-model="form.password_confirmation" type="password" required class="w-full px-3 py-2.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500" />
                    </label>
                    <button type="submit" :disabled="form.processing" class="w-full py-2.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm shadow-sm shadow-brand-600/30 disabled:opacity-50">
                        {{ form.processing ? 'Creating…' : 'Create Account' }}
                    </button>
                </form>

                <p class="mt-6 text-sm text-slate-500 text-center">
                    Already registered? <Link :href="route('login')" class="text-brand-600 font-medium hover:underline">Sign in</Link>
                </p>
            </div>
        </div>
    </div>
</template>
