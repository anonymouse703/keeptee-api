<script setup lang="ts">
    
import { ref } from 'vue'

import LineChart from './LineChart.vue'


const chartData = ref({
    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    datasets: [
        {
            label: 'Revenue',
            data: [45000, 52000, 48000, 61000, 59000, 72000, 68000, 75000, 82000, 78000, 85000, 90000],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4,
        },
        {
            label: 'Expenses',
            data: [22000, 25000, 23000, 28000, 27000, 31000, 29000, 32000, 34000, 33000, 35000, 36000],
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            fill: true,
            tension: 0.4,
        },
    ],
})

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top' as const,
            align: 'end' as const,
            labels: {
                padding: 20,
                usePointStyle: true,
                pointStyle: 'circle' as const,
                color: '#6b7280',
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
            beginAtZero: true,
            grid: {
                color: 'rgba(229, 231, 235, 0.3)',
            },
            ticks: {
                color: '#9ca3af',
                callback: function(value: string | number) {
                    return '₱' + Number(value).toLocaleString()
                },
            },
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
</script>

<template>
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Revenue Overview
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Monthly revenue vs expenses
                </p>
            </div>
            <select class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-1.5 text-sm">
                <option>2024</option>
                <option>2023</option>
                <option>2022</option>
            </select>
        </div>
        
        <div class="h-80">
            <LineChart :data="chartData" :options="options" />
        </div>
        
        <div class="mt-6 grid grid-cols-2 gap-4">
            <div class="rounded-xl bg-blue-50/50 dark:bg-blue-950/20 p-4">
                <div class="text-sm font-medium text-blue-600 dark:text-blue-400 mb-1">
                    Total Revenue
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    ₱752,000
                </div>
                <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                    ↑ 12.5% from last month
                </div>
            </div>
            <div class="rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 p-4">
                <div class="text-sm font-medium text-emerald-600 dark:text-emerald-400 mb-1">
                    Net Profit
                </div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    ₱396,000
                </div>
                <div class="text-xs text-green-600 dark:text-green-400 mt-1">
                    ↑ 8.3% from last month
                </div>
            </div>
        </div>
    </div>
</template>