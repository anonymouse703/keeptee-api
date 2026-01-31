<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { FileText, ArrowLeft } from 'lucide-vue-next'
import { computed } from 'vue'

import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

import Form from './partials/Form.vue'

const props = defineProps({
  tenant: Object,
});

const tenantData = computed(() => props.tenant?.data ?? [])

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tenants', href: '/tenants' },
  { title: 'Edit Tenant', href: `/tenants/${tenantData.value.id}/edit` },
]

const goBack = () => history.back()
</script>

<template>

  <Head :title="`Edit Tenant: ${tenantData.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button @click="goBack"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
            <ArrowLeft class="h-4 w-4" /> Back to Tenants
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
              Edit Tenant
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Update tenant details as needed
            </p>
          </div>

          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
            <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <Form :tenant="tenantData" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
