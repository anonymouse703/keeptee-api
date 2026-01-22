<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { User, Settings, Shield, LogOut } from 'lucide-vue-next'

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar'
import { logout } from '@/routes'
import {
    edit as editAppearance
} from '@/routes/appearance'
import {
    edit as editProfile
} from '@/routes/profile'
import {
    show as showTwoFactor
} from '@/routes/two-factor'
import {
    edit as editPassword
} from '@/routes/user-password'
import type { NavItem } from '@/types'

// Sidebar navigation links
const sidebarNavItems: NavItem[] = [
    { title: 'Profile', href: editProfile(), icon: User },
    { title: 'Password', href: editPassword(), icon: Shield },
    { title: 'Two-Factor Auth', href: showTwoFactor(), icon: Shield },
    { title: 'Appearance', href: editAppearance(), icon: Settings },
]

// Logout handler
const handleLogout = () => {
    router.flushAll()
}
</script>

<template>
    <SidebarMenu class="px-2">
        <SidebarMenuItem>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <SidebarMenuButton
                        class="group h-11 rounded-xl transition-all duration-300 hover:bg-linear-to-r hover:from-blue-500/10 hover:to-emerald-500/10 dark:hover:from-blue-500/5 dark:hover:to-emerald-500/5"
                    >
                        <div class="flex size-8 items-center justify-center rounded-lg bg-linear-to-br from-blue-500 to-emerald-500 shadow-md">
                            <User class="size-4 text-white" />
                        </div>
                        <div class="grid flex-1 text-left text-sm leading-tight">
                            <span class="truncate font-semibold text-gray-900 dark:text-white">
                                {{ $page.props.auth.user.name }}
                            </span>
                            <span class="truncate text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                {{ $page.props.auth.user.role }}
                            </span>
                        </div>
                    </SidebarMenuButton>
                </DropdownMenuTrigger>

                <DropdownMenuContent
                    class="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-xl border border-gray-200 bg-white/95 backdrop-blur-sm dark:border-gray-800 dark:bg-gray-900/95"
                    side="right"
                    :side-offset="4"
                >
                    <DropdownMenuLabel class="p-0 font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <div class="flex size-9 items-center justify-center rounded-lg bg-linear-to-br from-blue-500 to-emerald-500">
                                <User class="size-4 text-white" />
                            </div>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold text-gray-900 dark:text-white">
                                    {{ $page.props.auth.user.name }}
                                </span>
                                <span class="truncate text-xs text-gray-500 dark:text-gray-400">
                                    {{ $page.props.auth.user.email }}
                                </span>
                            </div>
                        </div>
                    </DropdownMenuLabel>

                    <DropdownMenuSeparator />

                    <!-- Dynamic Links -->
                    <DropdownMenuItem
                        v-for="item in sidebarNavItems"
                        :key="item.title"
                        as-child
                        class="gap-2 p-2.5 cursor-pointer hover:bg-linear-to-r hover:from-blue-500/10 hover:to-emerald-500/10 dark:hover:from-blue-500/5 dark:hover:to-emerald-500/5"
                    >
                        <Link :href="item.href" class="flex items-center gap-2">
                            <component :is="item.icon" class="size-4 text-gray-600 dark:text-gray-400" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </DropdownMenuItem>

                    <DropdownMenuSeparator />

                    <!-- Updated Logout -->
                    <DropdownMenuItem
                        as="div"
                        class="w-full"
                        >
                        <Link
                            class="flex items-center gap-2 w-full text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20 p-2.5 rounded"
                            :href="logout()"
                            @click="handleLogout"
                            as="button"
                            data-test="logout-button"
                        >
                            <LogOut class="size-4" />
                            <span>Log Out</span>
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </SidebarMenuItem>
    </SidebarMenu>
</template>
