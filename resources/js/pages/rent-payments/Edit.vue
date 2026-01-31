<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { CreditCard, ArrowLeft } from 'lucide-vue-next'
import { computed } from 'vue'

import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

import Form from './partials/Form.vue'

const props = defineProps<{
  rentPayment?: any,
  status?: any,
  payment_method?: any
}>()


const rentPaymentData = computed(() => props.rentPayment?.tenant ?? [])

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Rent Payments', href: '/rent-payments' },
  { title: 'Edit Rent Payment', href: `/rent-payments/${rentPaymentData.value.id}/edit` },
]

const goBack = () => history.back()
</script>

<template>

  <Head :title="`Edit Rent Payment: ${rentPaymentData.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button @click="goBack"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300">
            <ArrowLeft class="h-4 w-4" /> Back to Rent Payments
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
              Edit Rent Payment
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Update rent payment details as needed
            </p>
          </div>

          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
            <CreditCard class="h-5 w-5 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <Form :tenant="rentPaymentData" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
