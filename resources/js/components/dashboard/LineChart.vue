<script setup lang="ts">
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    CategoryScale,
    type ChartOptions,
    type ChartData,
    Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    LinearScale,
    PointElement,
    CategoryScale,
    Filler
)

interface Props {
    data: ChartData<'line'>
    options?: ChartOptions<'line'>
}

const props = defineProps<Props>()

const defaultOptions: ChartOptions<'line'> = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: 'rgba(255, 255, 255, 0.95)',
            titleColor: '#1f2937',
            bodyColor: '#4b5563',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
            displayColors: false,
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
            ticks: {
                color: '#9ca3af',
            },
        },
        y: {
            grid: {
                color: 'rgba(229, 231, 235, 0.3)',
            },
            ticks: {
                color: '#9ca3af',
            },
            beginAtZero: true,
        },
    },
    elements: {
        line: {
            tension: 0.4,
        },
        point: {
            radius: 4,
            hoverRadius: 6,
        },
    },
}

const chartOptions = { ...defaultOptions, ...props.options }
</script>

<template>
    <div class="h-full w-full">
        <Line
            :data="data"
            :options="chartOptions"
        />
    </div>
</template>