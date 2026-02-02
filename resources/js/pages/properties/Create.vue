<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Building2, ArrowLeft } from 'lucide-vue-next'

import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

import Form from './partials/Form.vue'

const props = defineProps<{
  property_types?: any
  statuses?: any
  image_types?: { key: string; label: string }[]
  amenities : object 
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Properties', href: '/properties' },
  { title: 'Create Property', href: '/properties/create' }
]

const goBack = () => history.back()
</script>

<template>

  <Head title="Create Property" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button @click="goBack"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
            <ArrowLeft class="h-4 w-4" />
            Back to Properties
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
              Create New Property
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Add a new property listing with details and images
            </p>
          </div>

          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
            <Building2 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>
      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
         <Form :image_types="props.image_types" :property_types="props.property_types" :statuses="props.statuses" :amenities="props.amenities" />
      </div>
    </div>
  </AppLayout>
</template>