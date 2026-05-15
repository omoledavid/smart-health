<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PreclinicLayout from '@/Layouts/PreclinicLayout.vue';
import Card from '@/Components/Card.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { PlusIcon, CheckIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ plan: Object });
const page = usePage();
const flash = computed(() => page.props.flash || {});

const showTaskForm = ref(false);
const taskForm = useForm({
    title: '',
    description: '',
    priority: 'normal',
    assigned_to: '',
    due_date: '',
});
const submitTask = () => taskForm.post(route('care-plans.tasks.store', props.plan.id), {
    onSuccess: () => { taskForm.reset(); showTaskForm.value = false; },
});

const statusForm = useForm({ status: props.plan.status });
const completeTask = (taskId) => useForm({}).patch(route('tasks.complete', taskId));

const priorityColors = {
    urgent: 'text-danger font-semibold',
    high: 'text-orange-600 dark:text-orange-400',
    normal: 'text-slate-600 dark:text-slate-400',
    low: 'text-slate-400',
};

const openTasks = computed(() => props.plan.tasks?.filter(t => t.status !== 'completed') ?? []);
const doneTasks = computed(() => props.plan.tasks?.filter(t => t.status === 'completed') ?? []);
</script>

<template>
    <Head :title="plan.title" />
    <PreclinicLayout :title="plan.title" :breadcrumbs="[{ label: 'Care Plans', href: route('care-plans.index') }, { label: plan.title }]">
        <template #actions>
            <select v-model="statusForm.status" @change="statusForm.patch(route('care-plans.status', plan.id))"
                class="px-3 py-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                <option value="active">Active</option>
                <option value="on_hold">On Hold</option>
                <option value="completed">Completed</option>
            </select>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Plan details -->
            <Card class="lg:col-span-1" title="Plan Overview">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500 mb-0.5">Patient</dt>
                        <dd class="font-medium text-slate-900 dark:text-white">{{ plan.patient?.first_name }} {{ plan.patient?.last_name }}</dd>
                    </div>
                    <div v-if="plan.doctor">
                        <dt class="text-slate-500 mb-0.5">Doctor</dt>
                        <dd>{{ plan.doctor?.user?.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 mb-0.5">Status</dt>
                        <dd><StatusPill :status="plan.status" /></dd>
                    </div>
                    <div v-if="plan.start_date">
                        <dt class="text-slate-500 mb-0.5">Start Date</dt>
                        <dd>{{ plan.start_date }}</dd>
                    </div>
                    <div v-if="plan.review_date">
                        <dt class="text-slate-500 mb-0.5">Review Date</dt>
                        <dd :class="new Date(plan.review_date) < new Date() ? 'text-danger font-medium' : ''">{{ plan.review_date }}</dd>
                    </div>
                    <div v-if="plan.goals">
                        <dt class="text-slate-500 mb-0.5">Goals</dt>
                        <dd class="whitespace-pre-line">{{ plan.goals }}</dd>
                    </div>
                    <div v-if="plan.interventions">
                        <dt class="text-slate-500 mb-0.5">Interventions</dt>
                        <dd class="whitespace-pre-line">{{ plan.interventions }}</dd>
                    </div>
                </dl>
            </Card>

            <!-- Tasks -->
            <div class="lg:col-span-2 space-y-4">
                <Card title="Tasks">
                    <template #title>
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold">Tasks</h3>
                            <button @click="showTaskForm = !showTaskForm"
                                class="inline-flex items-center gap-1 text-sm text-brand-600 hover:text-brand-700">
                                <PlusIcon class="h-4 w-4" /> Add Task
                            </button>
                        </div>
                    </template>

                    <form v-if="showTaskForm" @submit.prevent="submitTask" class="mb-4 p-4 rounded-lg bg-slate-50 dark:bg-slate-800/50 space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <label class="block col-span-2">
                                <span class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Title *</span>
                                <input v-model="taskForm.title" required class="w-full px-2 py-1.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                            </label>
                            <label class="block">
                                <span class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Priority</span>
                                <select v-model="taskForm.priority" class="w-full px-2 py-1.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm">
                                    <option value="urgent">Urgent</option>
                                    <option value="high">High</option>
                                    <option value="normal">Normal</option>
                                    <option value="low">Low</option>
                                </select>
                            </label>
                            <label class="block">
                                <span class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Due Date</span>
                                <input v-model="taskForm.due_date" type="date" class="w-full px-2 py-1.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm" />
                            </label>
                            <label class="block col-span-2">
                                <span class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Description</span>
                                <textarea v-model="taskForm.description" rows="2" class="w-full px-2 py-1.5 rounded bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-sm"></textarea>
                            </label>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <button type="button" @click="showTaskForm = false" class="px-3 py-1.5 text-sm text-slate-600 dark:text-slate-400 border border-slate-300 dark:border-slate-700 rounded-lg">Cancel</button>
                            <button type="submit" :disabled="taskForm.processing" class="px-3 py-1.5 text-sm bg-brand-600 text-white rounded-lg disabled:opacity-50">Add</button>
                        </div>
                    </form>

                    <!-- Open tasks -->
                    <ul v-if="openTasks.length" class="space-y-2 mb-4">
                        <li v-for="t in openTasks" :key="t.id"
                            class="flex items-start gap-3 p-3 rounded-lg border border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/30">
                            <button @click="completeTask(t.id)" class="mt-0.5 flex-shrink-0 h-5 w-5 rounded border-2 border-slate-300 dark:border-slate-600 hover:border-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors"></button>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 dark:text-white">{{ t.title }}</p>
                                <p v-if="t.description" class="text-xs text-slate-500 mt-0.5">{{ t.description }}</p>
                                <div class="flex items-center gap-3 mt-1">
                                    <span class="text-xs" :class="priorityColors[t.priority]">{{ t.priority }}</span>
                                    <span v-if="t.due_date" class="text-xs text-slate-400">Due {{ t.due_date }}</span>
                                    <span v-if="t.assigned_to" class="text-xs text-slate-400">→ {{ t.assigned_to?.name }}</span>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- Completed tasks -->
                    <div v-if="doneTasks.length" class="border-t border-slate-100 dark:border-slate-800 pt-3">
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Completed ({{ doneTasks.length }})</p>
                        <ul class="space-y-1.5">
                            <li v-for="t in doneTasks" :key="t.id" class="flex items-center gap-3 text-sm text-slate-400 line-through">
                                <CheckIcon class="h-4 w-4 text-emerald-500 flex-shrink-0 no-underline" style="text-decoration: none;" />
                                {{ t.title }}
                            </li>
                        </ul>
                    </div>

                    <p v-if="!openTasks.length && !doneTasks.length" class="text-sm text-slate-500 text-center py-4">No tasks yet. Add one to get started.</p>
                </Card>
            </div>
        </div>
    </PreclinicLayout>
</template>
