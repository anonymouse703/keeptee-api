<script setup lang="ts">
import { Users, AlertCircle } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import AsyncSelect from '@/components/ui/input/AsyncSelect.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'

import { type TenantForm } from '../../../../types/type'

interface Props {
  tenant?: TenantForm
  isLoading?: boolean
  errors?: Record<string, string[] | string>
}


const today = computed(() =>
  new Date().toISOString().split('T')[0]
)

const leaseEndMin = computed(() => {
  return form.value.lease_start || today.value
})

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  errors: () => ({})
})

const emit = defineEmits<{
  submit: [formData: TenantForm]
  cancel: []
}>()

const form = ref<TenantForm>({
  name: props.tenant?.name ?? '',
  property_id: props.tenant?.property_id ?? null,
  email: props.tenant?.email ?? '',
  phone: props.tenant?.phone ?? '',
  lease_start: props.tenant?.lease_start ?? null,
  lease_end: props.tenant?.lease_end ?? null
})

const isFormValid = computed(() =>
  (form.value.name?.trim().length ?? 0) > 0 &&
  !!form.value.property_id
)

const fetchProperties = async (query: string) => {
  try {
  
    const url = `/properties/search?query=${encodeURIComponent(query)}`
    const response = await fetch(url)
    if (!response.ok) throw new Error('Failed to fetch properties')
    const data = await response.json()
  
    return data.map((p: any) => ({ value: p.id, label: p.title }))
  } catch (error) {
    console.error('Error fetching properties:', error)
    return []
  }
}

const allErrors = computed(() => {
  const result: Record<string, string> = {}
  if (!props.errors) return result
  for (const key in props.errors) {
    const value = props.errors[key]
    if (Array.isArray(value)) result[key] = value[0]
    else result[key] = value
  }
  return result
})


const handleSubmit = () => {
  if (isFormValid.value) {
    emit('submit', form.value)
  }
}

const handleCancel = () => emit('cancel')
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-800">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <Users class="h-5 w-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ props.tenant ? 'Edit Tenant' : 'Create New Tenant' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ props.tenant ? 'Update tenant information' : 'Add a new tenant to manage your property' }}
        </p>
      </div>
    </div>

    <div v-if="Object.keys(allErrors).length > 0" class="rounded-lg border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20 p-4">
      <div class="flex items-start gap-3">
        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
        <div class="space-y-1">
          <h4 class="text-sm font-medium text-red-800 dark:text-red-300">Please fix the following errors:</h4>
          <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
            <li v-for="(error, field) in allErrors" :key="field">{{ error }}</li>
          </ul>
        </div>
      </div>
    </div>

    <div class="space-y-5">
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Tenant Name <span class="text-red-500">*</span>
        </label>
        <BaseInput
          v-model="form.name"
          type="text"
          placeholder="Enter tenant name"
          :error="allErrors.name"
          required
          class="w-full"
        />
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Property <span class="text-red-500">*</span>
        </label>
        <AsyncSelect
          v-model="form.property_id"
          :fetchOptions="fetchProperties"
          placeholder="Search and select a property..."
          :error="allErrors.property_id"
        />
      </div>

      <!-- Email -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Email
        </label>
        <BaseInput
          v-model="form.email"
          type="email"
          placeholder="Enter email"
          :error="allErrors.email"
          class="w-full"
        />
      </div>

      <!-- Phone -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Phone
        </label>
        <BaseInput
          v-model="form.phone"
          type="tel"
          placeholder="Enter phone number"
          :error="allErrors.phone"
          class="w-full"
        />
      </div>

      <!-- Lease Dates -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Lease Start Date
          </label>
          <BaseInput
            v-model="form.lease_start"
            type="date"
            :min="today"
            :error="allErrors.lease_start"
            class="w-full"
          />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Lease End Date
          </label>
          <BaseInput
            v-model="form.lease_end"
            type="date"
            :min="leaseEndMin"
            :error="allErrors.lease_end"
            class="w-full"
          />
        </div>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
      <BaseButton
        type="button"
        variant="outline"
        @click="handleCancel"
        :disabled="props.isLoading"
        class="border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
      >
        Cancel
      </BaseButton>
      <BaseButton
        type="button"
        @click="handleSubmit"
        :disabled="!isFormValid || props.isLoading"
        :loading="props.isLoading"
        class="bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ props.tenant ? 'Update Tenant' : 'Create Tenant' }}
      </BaseButton>
    </div>
  </div>
</template>
<style scoped></style>