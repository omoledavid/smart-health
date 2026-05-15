<script setup>
defineProps({
    label: String,
    value: [String, Number],
    delta: String,
    deltaPositive: { type: Boolean, default: true },
    icon: Object,
    iconBg: { type: String, default: 'bg-brand-600' },
    spark: { type: Array, default: () => [] },
    sparkColor: { type: String, default: '#2e37a4' },
});
</script>

<template>
    <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 relative overflow-hidden">
        <div class="flex items-start justify-between">
            <div :class="['grid h-12 w-12 place-items-center rounded-xl text-white', iconBg]">
                <component :is="icon" v-if="icon" class="h-6 w-6" />
            </div>
            <span
                v-if="delta"
                :class="[
                    'px-2 py-0.5 rounded-md text-xs font-semibold',
                    deltaPositive ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                ]"
            >{{ delta }}</span>
        </div>
        <div class="mt-3 flex items-end justify-between">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ label }}</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ value }}</p>
            </div>
            <div v-if="spark.length" class="w-24 h-12">
                <apexchart
                    height="48"
                    width="96"
                    type="area"
                    :options="{
                        chart: { sparkline: { enabled: true }, toolbar: { show: false } },
                        stroke: { curve: 'smooth', width: 2 },
                        colors: [sparkColor],
                        fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0 } },
                        tooltip: { enabled: false },
                    }"
                    :series="[{ data: spark }]"
                />
            </div>
        </div>
    </div>
</template>
