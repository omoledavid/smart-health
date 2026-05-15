<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import Input from '@/Components/Input.vue';
import { PlusIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ specializations: Object });
const showAdd = ref(false);
const form = useForm({ name: '', description: '' });
const submit = () => form.post(route('specializations.store'), { onSuccess: () => { form.reset(); showAdd.value = false; } });
</script>

<template>
    <Head title="Specializations" />
    <PreclinicLayout title="Specializations">
        <template #actions>
            <button @click="showAdd = !showAdd" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium"><PlusIcon class="h-4 w-4" /> Add</button>
        </template>
        <Card v-if="showAdd" class="mb-5" title="New Specialization">
            <form @submit.prevent="submit" class="space-y-3">
                <Input v-model="form.name" label="Name" />
                <label class="block"><span class="block text-sm font-medium mb-1">Description</span>
                    <textarea v-model="form.description" rows="2" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                </label>
                <div class="flex justify-end"><button type="submit" class="px-4 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium">Save</button></div>
            </form>
        </Card>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <Card v-for="s in specializations.data" :key="s.id">
                <h3 class="font-semibold text-slate-900 dark:text-white">{{ s.name }}</h3>
                <p class="text-sm text-slate-500 mt-1">{{ s.description || '—' }}</p>
            </Card>
        </div>
    </PreclinicLayout>
</template>
