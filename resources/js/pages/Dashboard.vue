<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { 
    Building2, 
    Users, 
    DollarSign, 
    Home,
    Calendar,
    AlertCircle,
    CreditCard
} from 'lucide-vue-next'

import PropertyList from '@/components/dashboard/PropertyList.vue'
import RevenueChart from '@/components/dashboard/RevenueChart.vue'
import StatsCard from '@/components/dashboard/StatsCard.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
]

// Mock data - replace with your actual data
const stats = {
    totalProperties: 24,
    occupiedRate: 87,
    monthlyRevenue: 752000,
    pendingPayments: 3,
    activeTenants: 42,
    maintenanceRequests: 5,
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Welcome Header -->
            <div class="rounded-2xl bg-linear-to-r from-blue-500 to-emerald-500 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Welcome back, {{ $page.props.auth.user.name }}! 👋</h1>
                        <p class="text-blue-100 mt-2">
                            Here's what's happening with your properties today.
                        </p>
                    </div>
                    <div class="hidden md:block">
                        <div class="rounded-lg bg-white/20 p-3 backdrop-blur-sm">
                            <div class="text-sm">Today's Date</div>
                            <div class="text-xl font-bold">{{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <StatsCard
                    title="Total Properties"
                    :value="stats.totalProperties"
                    :icon="Building2"
                    :trend="8"
                    trendLabel="↑ 2 new this month"
                    color="blue"
                />
                <StatsCard
                    title="Occupancy Rate"
                    :value="`${stats.occupiedRate}%`"
                    :icon="Home"
                    :trend="3.5"
                    trendLabel="↑ from last month"
                    color="emerald"
                />
                <StatsCard
                    title="Monthly Revenue"
                    :value="`₱${stats.monthlyRevenue.toLocaleString()}`"
                    :icon="DollarSign"
                    :trend="12.5"
                    trendLabel="↑ from last month"
                    color="amber"
                />
                <StatsCard
                    title="Pending Payments"
                    :value="stats.pendingPayments"
                    :icon="AlertCircle"
                    :trend="-20"
                    trendLabel="↓ 1 resolved today"
                    color="rose"
                />
            </div>

            <!-- Second Row Stats -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <StatsCard
                    title="Active Tenants"
                    :value="stats.activeTenants"
                    :icon="Users"
                    color="indigo"
                />
                <StatsCard
                    title="Maintenance Requests"
                    :value="stats.maintenanceRequests"
                    :icon="AlertCircle"
                    color="rose"
                />
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-linear-to-br from-blue-50 to-emerald-50 dark:from-blue-950/30 dark:to-emerald-950/30 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">
                                Upcoming Events
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white dark:bg-gray-800">
                                        <Calendar class="size-5 text-blue-500" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">Property Inspection</div>
                                        <div class="text-xs text-gray-500">Tomorrow, 10:00 AM</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-white dark:bg-gray-800">
                                        <CreditCard class="size-5 text-emerald-500" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">Rent Collection</div>
                                        <div class="text-xs text-gray-500">Jan 15, 2024</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Property List -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <RevenueChart />
                <PropertyList />
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Recent Activities -->
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Recent Activities
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900">
                                <DollarSign class="size-4 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    Payment received from <span class="font-medium">John Doe</span>
                                </p>
                                <p class="text-xs text-gray-500">₱25,000 • 2 hours ago</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                                <Users class="size-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    New tenant <span class="font-medium">Sarah Smith</span> registered
                                </p>
                                <p class="text-xs text-gray-500">Yesterday, 3:45 PM</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900">
                                <AlertCircle class="size-4 text-amber-600 dark:text-amber-400" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    Maintenance request for <span class="font-medium">Unit 304</span>
                                </p>
                                <p class="text-xs text-gray-500">Plumbing issue • 1 day ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Types Distribution -->
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Property Types
                    </h3>
                    <div class="space-y-4">
                        <div v-for="type in [
                            { name: 'Residential', count: 15, color: 'bg-blue-500', percentage: 62 },
                            { name: 'Commercial', count: 6, color: 'bg-purple-500', percentage: 25 },
                            { name: 'Industrial', count: 3, color: 'bg-gray-500', percentage: 13 }
                        ]" :key="type.name" class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 w-3 rounded-full" :class="type.color"></div>
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ type.name }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ type.count }} units</span>
                            </div>
                            <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                                <div 
                                    class="h-full rounded-full"
                                    :class="type.color"
                                    :style="{ width: `${type.percentage}%` }"
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-linear-to-br from-blue-50 to-emerald-50 dark:from-blue-950/30 dark:to-emerald-950/30 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <button class="flex flex-col items-center justify-center rounded-xl bg-white dark:bg-gray-800 p-4 hover:shadow-lg transition-shadow duration-200">
                            <Building2 class="size-6 text-blue-500 mb-2" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Add Property</span>
                        </button>
                        <button class="flex flex-col items-center justify-center rounded-xl bg-white dark:bg-gray-800 p-4 hover:shadow-lg transition-shadow duration-200">
                            <Users class="size-6 text-emerald-500 mb-2" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Add Tenant</span>
                        </button>
                        <button class="flex flex-col items-center justify-center rounded-xl bg-white dark:bg-gray-800 p-4 hover:shadow-lg transition-shadow duration-200">
                            <CreditCard class="size-6 text-amber-500 mb-2" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white">Record Payment</span>
                        </button>
                        <button class="flex flex-col items-center justify-center rounded-xl bg-white dark:bg-gray-800 p-4 hover:shadow-lg transition-shadow duration-200">
                            <AlertCircle class="size-6 text-rose-500 mb-2" />
                            <span class="text-sm font-medium text-gray-900 dark:text-white">New Ticket</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>