<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { useActiveUrl } from '@/composables/useActiveUrl'
import { type NavItem } from '@/types'

defineProps<{
    items: NavItem[]
    title?: string
}>()

const { urlIsActive } = useActiveUrl()
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel 
            v-if="title"
            class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider px-3 mb-2"
        >
            {{ title }}
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem 
                v-for="item in items" 
                :key="item.title"
                class="mb-0.5 last:mb-0"
            >
                <SidebarMenuButton
                    as-child
                    :is-active="urlIsActive(item.href)"
                    :tooltip="item.title"
                    class="group h-10 rounded-lg transition-all duration-200 hover:bg-linear-to-r hover:from-blue-500/10 hover:to-emerald-500/10 dark:hover:from-blue-500/5 dark:hover:to-emerald-500/5 data-[active=true]:bg-linear-to-r data-[active=true]:from-blue-500/20 data-[active=true]:to-emerald-500/20 dark:data-[active=true]:from-blue-500/10 dark:data-[active=true]:to-emerald-500/10"
                >
                    <Link 
                        :href="item.href"
                        class="flex items-center gap-3 px-3"
                    >
                        <div 
                            class="flex h-8 w-8 items-center justify-center rounded-lg transition-all duration-300 group-hover:scale-110"
                            :class="[
                                urlIsActive(item.href) 
                                    ? 'bg-linear-to-br from-blue-500 to-emerald-500 text-white shadow-md' 
                                    : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400'
                            ]"
                        >
                            <component 
                                :is="item.icon" 
                                class="size-4"
                                :class="[
                                    urlIsActive(item.href) 
                                        ? 'text-white' 
                                        : 'group-hover:text-blue-600 dark:group-hover:text-blue-400'
                                ]"
                            />
                        </div>
                        <span 
                            class="font-medium text-sm transition-colors duration-200"
                            :class="[
                                urlIsActive(item.href) 
                                    ? 'text-blue-700 dark:text-blue-300' 
                                    : 'text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white'
                            ]"
                        >
                            {{ item.title }}
                        </span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>