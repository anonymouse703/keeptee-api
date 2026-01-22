<script setup lang="ts">
import { Building2, MapPin, DollarSign, Calendar } from 'lucide-vue-next'

interface Property {
    id: number
    name: string
    address: string
    type: 'Residential' | 'Commercial' | 'Industrial'
    status: 'Occupied' | 'Vacant' | 'Maintenance'
    monthlyRent: number
    nextPayment: string
    image?: string
}

const properties: Property[] = [
    {
        id: 1,
        name: 'Sky Garden Apartments',
        address: '123 Main St, Makati',
        type: 'Residential',
        status: 'Occupied',
        monthlyRent: 45000,
        nextPayment: '2024-01-15',
    },
    {
        id: 2,
        name: 'Tech Hub Offices',
        address: '456 BGC, Taguig',
        type: 'Commercial',
        status: 'Occupied',
        monthlyRent: 120000,
        nextPayment: '2024-01-10',
    },
    {
        id: 3,
        name: 'Green Valley Condo',
        address: '789 QC Circle',
        type: 'Residential',
        status: 'Vacant',
        monthlyRent: 35000,
        nextPayment: '-',
    },
    {
        id: 4,
        name: 'Industrial Warehouse',
        address: '101 Industrial Park',
        type: 'Industrial',
        status: 'Maintenance',
        monthlyRent: 80000,
        nextPayment: '2024-01-20',
    },
]

const statusColors = {
    Occupied: 'bg-emerald-500',
    Vacant: 'bg-amber-500',
    Maintenance: 'bg-rose-500',
}

const typeColors = {
    Residential: 'bg-blue-500',
    Commercial: 'bg-purple-500',
    Industrial: 'bg-gray-500',
}
</script>

<template>
    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm">
        <div class="border-b border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Properties Overview
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Total {{ properties.length }} properties
                    </p>
                </div>
                <button class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                    View All →
                </button>
            </div>
        </div>
        
        <div class="divide-y divide-gray-200 dark:divide-gray-800">
            <div 
                v-for="property in properties" 
                :key="property.id"
                class="p-6 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-200"
            >
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-emerald-500">
                        <Building2 class="size-6 text-white" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900 dark:text-white truncate">
                                {{ property.name }}
                            </h4>
                            <div class="flex items-center gap-2">
                                <span 
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                    :class="[
                                        statusColors[property.status],
                                        'text-white'
                                    ]"
                                >
                                    {{ property.status }}
                                </span>
                                <span 
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium text-white"
                                    :class="typeColors[property.type]"
                                >
                                    {{ property.type }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <MapPin class="size-4" />
                                <span class="truncate">{{ property.address }}</span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-1">
                                        <DollarSign class="size-4 text-gray-400" />
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                                            ₱{{ property.monthlyRent.toLocaleString() }}
                                        </span>
                                        <span class="text-xs text-gray-500">/month</span>
                                    </div>
                                    
                                    <div v-if="property.nextPayment !== '-'" class="flex items-center gap-1">
                                        <Calendar class="size-4 text-gray-400" />
                                        <span class="text-sm text-gray-600 dark:text-gray-400">
                                            Due {{ property.nextPayment }}
                                        </span>
                                    </div>
                                </div>
                                
                                <button class="text-sm font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>