<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { Users, AlertCircle, X } from 'lucide-vue-next'
import { computed, ref } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import AsyncSelect from '@/components/ui/input/AsyncSelect.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'

const props = defineProps<{
  tenant?: any
}>()

const formatDateForInput = (dateString: string | null): string | null => {
  if (!dateString) return null

  try {
    const date = new Date(dateString)

    if (isNaN(date.getTime())) return null

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
  } catch (error) {
    console.error('Error formatting date:', error)
    return null
  }
}

const form = useForm({
  name: props.tenant?.name ?? '',
  property_id: props.tenant?.proterty_id ?? null,
  email: props.tenant?.email ?? '',
  phone: props.tenant?.phone ?? '',
  lease_start: formatDateForInput(props.tenant?.lease_start) ?? null,
  lease_end: formatDateForInput(props.tenant?.lease_end) ?? null,
})

const today = computed(() => new Date().toISOString().split('T')[0])
const leaseEndMin = computed(() => form.lease_start || today.value)
const isFormValid = computed(() => form.name.trim().length > 0 && !!form.property_id)
const allErrors = computed(() => form.errors)

const selectedOption = ref<{ value: number; label: string } | null>(
  props.tenant?.property
    ? { value: props.tenant.proterty_id, label: props.tenant.property.title }
    : null
)

// Store the original property selection to allow restoration
const originalProperty = ref<{ value: number; label: string } | null>(
  selectedOption.value ? { ...selectedOption.value } : null
)

const showAsyncSelect = ref(!props.tenant?.property)

const fetchProperties = async (query: string) => {
  try {
    const res = await fetch(`/properties/search?query=${encodeURIComponent(query)}`)
    if (!res.ok) throw new Error('Failed to fetch properties')
    const data = await res.json()
    return data.map((p: any) => ({ value: p.id, label: p.title }))
  } catch (error) {
    console.error('Error fetching properties:', error)
    return []
  }
}

const handleClearProperty = () => {
  form.property_id = null
  selectedOption.value = null
  showAsyncSelect.value = true
}

const handleCancelPropertyChange = () => {
  // Restore the original property
  if (originalProperty.value) {
    form.property_id = originalProperty.value.value
    selectedOption.value = { ...originalProperty.value }
    showAsyncSelect.value = false
  }
}

const handlePropertySelect = () => {
  if (form.property_id && selectedOption.value) {
    // Update the original property when a new selection is made
    originalProperty.value = { ...selectedOption.value }
    showAsyncSelect.value = false
  }
}

const handleKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && showAsyncSelect.value && originalProperty.value) {
    event.preventDefault()
    handleCancelPropertyChange()
  }
}

const handleSubmit = () => {
  if (!isFormValid.value) return

  if (props.tenant?.id) {
    form.put(`/tenants/${props.tenant.id}`, { preserveScroll: true })
  } else {
    form.post('/tenants', { preserveScroll: true })
  }
}

const handleCancel = () => {
  form.reset()
  history.back()
}
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
          {{ props.tenant
            ? 'Update tenant information'
            : 'Add a new tenant to manage your property' }}
        </p>
      </div>
    </div>

    <div v-if="Object.keys(allErrors).length > 0"
      class="rounded-lg border border-red-200 bg-red-50 dark:border-red-800 dark:bg-red-900/20 p-4">
      <div class="flex items-start gap-3">
        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
        <div class="space-y-1">
          <h4 class="text-sm font-medium text-red-800 dark:text-red-300">
            Please fix the following errors:
          </h4>
          <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-400 space-y-1">
            <li v-for="(error, field) in allErrors" :key="field">
              {{ error }}
            </li>
          </ul>
        </div>
      </div>
    </div>

    <div class="space-y-5">

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Tenant Name <span class="text-red-500">*</span>
        </label>
        <BaseInput v-model="form.name" type="text" placeholder="Enter tenant name" :error="allErrors.name" required
          class="w-full" />
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Property <span class="text-red-500">*</span>
        </label>

        <!-- Display selected property as readonly input -->
        <div v-if="!showAsyncSelect && selectedOption" class="relative">
          <input type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 pr-10 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white"
            :value="selectedOption.label" readonly />

          <!-- X button to clear property -->
          <button type="button" @click="handleClearProperty"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
            title="Clear selection">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

        <!-- AsyncSelect for searching/selecting property -->
        <div v-else ref="asyncSelectContainer" class="relative" @keydown="handleKeyDown" tabindex="-1">
          <AsyncSelect 
            v-model="form.property_id" 
            :fetchOptions="fetchProperties" 
            :selectedOption="selectedOption"
            placeholder="Search and select a property..." 
            :error="allErrors.property_id"
            @update:modelValue="handlePropertySelect" 
          />

          <!-- Cancel button to restore original property (shows only if there was an original property) -->
          <button 
            v-if="originalProperty" 
            type="button" 
            @click="handleCancelPropertyChange"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors z-10"
            title="Cancel change (ESC)">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
        <BaseInput v-model="form.email" type="email" placeholder="Enter email" :error="allErrors.email"
          class="w-full" />
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Phone</label>
        <BaseInput v-model="form.phone" type="tel" placeholder="Enter phone number" :error="allErrors.phone"
          class="w-full" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Lease Start Date</label>
          <BaseInput v-model="form.lease_start" type="date" :min="today" :error="allErrors.lease_start"
            class="w-full" />
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Lease End Date</label>
          <BaseInput v-model="form.lease_end" type="date" :min="leaseEndMin" :error="allErrors.lease_end"
            class="w-full" />
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
      <BaseButton type="button" variant="outline" @click="handleCancel" :disabled="form.processing">
        Cancel
      </BaseButton>

      <BaseButton type="button" @click="handleSubmit" :disabled="!isFormValid || form.processing"
        class="bg-blue-600 text-white hover:bg-blue-700">
        {{ props.tenant ? 'Update Tenant' : 'Create Tenant' }}
      </BaseButton>
    </div>
  </div>
</template>

<style scoped></style>