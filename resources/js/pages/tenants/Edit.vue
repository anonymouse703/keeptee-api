<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { Users, ArrowLeft } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

import { type TenantForm } from '../../../types/type'

import Form from './partials/Form.vue'

interface Props {
    tenant: { data: TenantForm } 
}

const props = defineProps<Props>()

const tenantData = computed(() => props.tenant.data)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tenants', href: '/tenants' },
  { title: 'Edit Tenant', href: `/tenants/${tenantData.value.id}/edit` }
]

const formErrors = ref<Record<string, string[] | string>>({})
const isLoading = ref(false)

const formData = ref<TenantForm>({
  name: tenantData.value.name ?? '',
  email: tenantData.value.email ?? '',
  phone: tenantData.value.phone ?? '',
  property_id: tenantData.value.property_id ?? null,
  lease_start: tenantData.value.lease_start ?? null,
  lease_end: tenantData.value.lease_end ?? null,
})

const handleSubmit = async (formData: TenantForm) => {
    isLoading.value = true
    formErrors.value = {}

    const formDataToSend = new FormData()

    formDataToSend.append('_method', 'PUT')

    Object.keys(formData).forEach(key => {
        const value = formData[key as keyof TenantForm]
        if (value !== null && value !== undefined && value !== '') {
            formDataToSend.append(key, String(value))
        }
    })

    router.post(`/properties/${tenantData.value.id}`, formDataToSend, {
        onSuccess: () => {
            isLoading.value = false
            formErrors.value = {} 
        },
        onError: (errors) => {
            isLoading.value = false
            formErrors.value = errors 
            console.error('Error updating property:', errors)
        },
        preserveScroll: true,
        forceFormData: true
    })
}

// Cancel handler
const handleCancel = () => router.visit('/tenants')
</script>

<template>
  <Head :title="`Edit Tenant: ${tenantData.name}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button
            @click="handleCancel"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
          >
            <ArrowLeft class="h-4 w-4" /> Back to Tenants
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Tenant</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Update tenant details as needed
            </p>
          </div>
          <div class="flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
              <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Form Container -->
      <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <Form
            :isLoading="isLoading"
            :tenant="tenantData"
            :errors="formErrors"
            :modelValue="formData"
            @submit="handleSubmit"
            @cancel="handleCancel"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>
