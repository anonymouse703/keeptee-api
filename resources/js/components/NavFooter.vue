<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
  Settings,
  User,
  Shield,
  LogOut,
} from 'lucide-vue-next'

import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
} from '@/components/ui/sidebar'
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

const sidebarNavItems: NavItem[] = [
  {
    title: 'Profile',
    href: editProfile(), 
    icon: User,
  },
  {
    title: 'Password',
    href: editPassword(),
    icon: Shield,
  },
  {
    title: 'Two-Factor Auth',
    href: showTwoFactor(),
    icon: Shield,
  },
  {
    title: 'Appearance',
    href: editAppearance(),
    icon: Settings,
  },
]

</script>

<template>
  <div class="space-y-2">
    <!-- Sidebar Navigation -->
    <SidebarMenu class="px-2">
      <SidebarMenuItem
        v-for="item in sidebarNavItems"
        :key="item.title"
        class="mb-1 last:mb-0"
      >
        <SidebarMenuButton
          as-child
          :tooltip="item.title"
          class="group h-9 rounded-lg transition-all duration-200
                 hover:bg-linear-to-r hover:from-blue-500/10 hover:to-emerald-500/10
                 dark:hover:from-blue-500/5 dark:hover:to-emerald-500/5"
        >
          <Link
            :href="item.href"
            class="flex items-center gap-3 px-3"
          >
            <div
              class="flex h-7 w-7 items-center justify-center rounded-lg
                     bg-gray-100 dark:bg-gray-800
                     text-gray-600 dark:text-gray-400
                     transition-all duration-300
                     group-hover:scale-110 group-hover:text-blue-600
                     dark:group-hover:text-blue-400"
            >
              <component :is="item.icon" class="size-3.5" />
            </div>
            <span
              class="text-sm font-medium text-gray-700 dark:text-gray-300
                     group-hover:text-gray-900 dark:group-hover:text-white
                     transition-colors duration-200"
            >
              {{ item.title }}
            </span>
          </Link>
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>

    <!-- Logout -->
    <div class="px-3">
      <Link
        href="/logout"
        method="post"
        as="button"
        class="group flex w-full items-center gap-2 rounded-lg p-2
               text-sm font-medium text-red-600
               hover:bg-red-50 dark:text-red-400
               dark:hover:bg-red-950/20
               transition-colors duration-200"
      >
        <LogOut class="size-4 transition-transform duration-300 group-hover:scale-110" />
        <span>Log Out</span>
      </Link>
    </div>
  </div>
</template>
