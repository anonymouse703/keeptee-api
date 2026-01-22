<script setup lang="ts">
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    ArcElement,
    type ChartOptions,
    type ChartData
} from 'chart.js'
import { Pie } from 'vue-chartjs'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

interface Props {
    data: ChartData<'pie'>
    options?: ChartOptions<'pie'>
}

const props = defineProps<Props>()

const defaultOptions: ChartOptions<'pie'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                padding: 20,
                color: '#6b7280',
                font: {
                    size: 12,
                },
            },
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#4b5563',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
        },
    },
    cutout: '70%',
}

const chartOptions = { ...defaultOptions, ...props.options }
</script>

<template>
    <div class="h-full w-full">
        <Pie
            :data="data"
            :options="chartOptions"
        />
    </div>
</template>