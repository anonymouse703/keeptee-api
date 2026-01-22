<script setup lang="ts">
import { ArrowUp, ArrowDown } from 'lucide-vue-next'

interface Props {
    title: string
    value: string | number
    icon: any
    trend?: number
    trendLabel?: string
    color: 'blue' | 'emerald' | 'amber' | 'rose' | 'indigo'
}

const { title, value, icon, trend, trendLabel, color } = defineProps<Props>()

const colorClasses = {
    blue: {
        bg: 'from-blue-500 to-blue-600',
        icon: 'bg-blue-500',
        text: 'text-blue-600 dark:text-blue-400',
    },
    emerald: {
        bg: 'from-emerald-500 to-emerald-600',
        icon: 'bg-emerald-500',
        text: 'text-emerald-600 dark:text-emerald-400',
    },
    amber: {
        bg: 'from-amber-500 to-amber-600',
        icon: 'bg-amber-500',
        text: 'text-amber-600 dark:text-amber-400',
    },
    rose: {
        bg: 'from-rose-500 to-rose-600',
        icon: 'bg-rose-500',
        text: 'text-rose-600 dark:text-rose-400',
    },
    indigo: {
        bg: 'from-indigo-500 to-indigo-600',
        icon: 'bg-indigo-500',
        text: 'text-indigo-600 dark:text-indigo-400',
    },
}
</script>

<template>
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm p-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                    {{ title }}
                </p>
                <div class="flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ value }}
                    </h3>
                    <div v-if="trend !== undefined" class="flex items-center gap-1">
                        <ArrowUp 
                            v-if="trend > 0" 
                            class="size-4 text-emerald-500" 
                        />
                        <ArrowDown 
                            v-else 
                            class="size-4 text-rose-500" 
                        />
                        <span 
                            class="text-sm font-medium"
                            :class="trend > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'"
                        >
                            {{ Math.abs(trend) }}%
                        </span>
                    </div>
                </div>
                <p v-if="trendLabel" class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ trendLabel }}
                </p>
            </div>
            <div 
                class="flex h-12 w-12 items-center justify-center rounded-xl"
                :class="colorClasses[color].icon + ' bg-opacity-10 dark:bg-opacity-20'"
            >
                <component 
                    :is="icon" 
                    class="size-6"
                    :class="colorClasses[color].text"
                />
            </div>
        </div>
        <div class="mt-4 h-1 w-full rounded-full bg-gray-200 dark:bg-gray-800">
            <div 
                class="h-full rounded-full bg-linear-to-r"
                :class="colorClasses[color].bg"
                :style="{ width: '70%' }"
            ></div>
        </div>
    </div>
</template>