<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { Tag as TagIcon, ArrowLeft } from 'lucide-vue-next'

import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

import Form from './partials/Form.vue'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tags', href: '/tags' },
  { title: 'Create Tag', href: '/tags/create' }
]

const handleCancel = () => {
  router.visit('/tags')
}
</script>

<template>
  <Head title="Create Tag" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">

      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button
            @click="handleCancel"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
          >
            <ArrowLeft class="h-4 w-4" />
            Back to Tags
          </button>
        </div>
        
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Tag</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Add a new tag to organize and categorize your properties
            </p>
          </div>
          <div class="flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
              <TagIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>
      </div>
      
      <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <Form/>
        </div>
      </div>
    </div>
  </AppLayout>
</template>