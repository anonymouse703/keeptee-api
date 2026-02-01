<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { Building2, ArrowLeft } from 'lucide-vue-next'

import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

import Form from './partials/Form.vue'

const props = defineProps<{
  property: any
  property_types?: any
  statuses?: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Properties', href: '/properties' },
  { title: props.property.title, href: `/properties/${props.property.id}/edit` }
]

const goBack = () => history.back()
</script>

<template>

  <Head :title="`Edit ${property.title}`" />
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
              Edit {{ property.title }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Update property details and images
            </p>
          </div>

          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
            <Building2 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <Form :property="property" :property_types="property_types" :statuses="statuses" />
      </div>
    </div>
  </AppLayout>
</template>