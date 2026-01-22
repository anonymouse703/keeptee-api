<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { 
    // BookOpen, 
    LayoutGrid, 
    Building2, 
    Users, 
    FileText,
    Settings,
    Home,
    BarChart3,
    BellRing,
    Calendar,
    MessageSquare,
    CreditCard,
    Tags
} from 'lucide-vue-next'

import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { dashboard } from '@/routes'
import { type NavItem } from '@/types'


// Real Estate Theme Navigation Items
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        // href: dashboard(),
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Properties',
        href: '/properties.index',
        icon: Building2,
    },
    {
        title: 'Listings',
        href: '/listings.index',
        icon: Home,
    },
    {
        title: 'Tenants',
        href: '/tenants.index',
        icon: Users,
    },
    {
        title: 'Payments',
        href: '/payments.index',
        icon: CreditCard,
    },
    {
        title: 'Invoices',
        href: '/invoices.index',
        icon: FileText,
    },
    {
        title: 'Leases',
        href: '/leases.index',
        icon: FileText,
    },
    {
        title: 'Maintenance',
        href: '/maintenance.index',
        icon: Settings,
    },
    {
        title: 'Tags',
        href: '/tags',
        icon: Tags,
    },
]

const reportsNavItems: NavItem[] = [
    {
        title: 'Analytics',
        href: '/analytics.index',
        icon: BarChart3,
    },
    {
        title: 'Reports',
        href: '/reports.index',
        icon: FileText,
    },
    {
        title: 'Calendar',
        href: '/calendar.index',
        icon: Calendar,
    },
]

const supportNavItems: NavItem[] = [
    {
        title: 'Notifications',
        href: '/notifications.index',
        icon: BellRing,
    },
    {
        title: 'Messages',
        href: '/messages.index',
        icon: MessageSquare,
    },
    {
        title: 'Settings',
        href: '/settings.index',
        icon: Settings,
    },
]

</script>

<template>
    <Sidebar 
        collapsible="icon" 
        variant="inset"
        class="border-r border-gray-200/50 dark:border-gray-800/50"
    >
        <SidebarHeader class="px-4 pt-4">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton 
                        size="lg" 
                        as-child
                        class="group h-12 rounded-xl transition-all duration-300 hover:bg-linear-to-r hover:from-blue-500/10 hover:to-emerald-500/10 dark:hover:from-blue-500/5 dark:hover:to-emerald-500/5"
                    >
                        <Link :href="dashboard()" class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-emerald-500 shadow-lg group-hover:scale-105 transition-transform duration-300">
                                <img src="/logo1.png" alt="Logo" class="h-12 w-12 rounded-lg p-2" />
                            </div>
                            <div class="flex flex-col items-start">
                                <span class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">
                                    {{ $page.props.name }}
                                </span>
                                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                    Property Management
                                </span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="px-2 pt-6">
            <!-- Quick Stats -->
            <div class="mb-6 px-3">
                <div class="rounded-xl bg-linear-to-r from-blue-50 to-emerald-50 dark:from-blue-950/30 dark:to-emerald-950/30 p-3">
                    <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">TODAY'S STATS</div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900 dark:text-white">12</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">New Leads</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900 dark:text-white">₱45K</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Revenue</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Navigation -->
            <NavMain 
                :items="mainNavItems" 
                title="Management"
            />
            
            <div class="h-px bg-linear-to-r from-transparent via-gray-200 to-transparent dark:via-gray-800 mx-3 my-4" />

            <!-- Reports -->
            <NavMain 
                :items="reportsNavItems" 
                title="Reports"
            />

            <div class="h-px bg-linear-to-r from-transparent via-gray-200 to-transparent dark:via-gray-800 mx-3 my-4" />

            <!-- Support -->
            <NavMain 
                :items="supportNavItems" 
                title="Support"
            />
        </SidebarContent>

        <SidebarFooter class="px-2 pb-4">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>