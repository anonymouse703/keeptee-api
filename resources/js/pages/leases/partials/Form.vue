<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { FileText, X } from 'lucide-vue-next'
import { computed, ref } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import TenantSelect from '@/components/ui/input/AsyncSelect.vue'
import PropertySelect from '@/components/ui/input/AsyncSelect.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'

const props = defineProps<{
  lease?: any
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

const selectedTenantOption = ref<{ value: number; label: string } | null>(
  props.lease?.tenant
    ? { value: props.lease.tenant.id, label: props.lease.tenant.name }
    : null
)

const originalTenant = ref<{ value: number; label: string } | null>(
  selectedTenantOption.value ? { ...selectedTenantOption.value } : null
)

const selectedPropertyOption = ref<{ value: number; label: string } | null>(
  props.lease?.property
    ? { value: props.lease.property.id, label: props.lease.property.title }
    : null
)

const originalProperty = ref<{ value: number; label: string } | null>(
  selectedPropertyOption.value ? { ...selectedPropertyOption.value } : null
)

const form = useForm({
  tenant_id: props.lease?.tenant_id ?? null,
  property_id: props.lease?.property_id ?? null,
  monthly_rent: props.lease?.monthly_rent ?? '',
  start_date: formatDateForInput(props.lease?.start_date) ?? null,
  end_date: formatDateForInput(props.lease?.end_date) ?? null,
})

const enddMin = computed(() => form.start_date || null)

const allErrors = computed(() => form.errors)

const showTenantSelect = ref(!props.lease?.tenant)
const showPropertySelect = ref(!props.lease?.property)

const fetchTenants = async (query: string) => {
  try {
    const res = await fetch(`/tenants/search-tenant?query=${encodeURIComponent(query)}`)
    if (!res.ok) throw new Error('Failed to fetch tenants')
    const data = await res.json()
    return data.map((p: any) => ({ value: p.id, label: p.name }))
  } catch (error) {
    console.error('Error fetching tenants:', error)
    return []
  }
}


const handleClearTenant = () => {
  form.tenant_id = null
  selectedTenantOption.value = null
  showTenantSelect.value = true
}

const handleCancelTenantChange = () => {
  if (originalTenant.value) {
    form.tenant_id = originalTenant.value.value
    selectedTenantOption.value = { ...originalTenant.value }
    showTenantSelect.value = false
  }
}

const handleTenantSelect = () => {
  if (form.tenant_id && selectedTenantOption.value) {
    originalTenant.value = { ...selectedTenantOption.value }
    showTenantSelect.value = false
  }
}

const handleTenantKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && showTenantSelect.value && originalTenant.value) {
    event.preventDefault()
    handleCancelTenantChange()
  }
}

const fetchProperties = async (query: string) => {
  try {
    const res = await fetch(`/properties/search-property?query=${encodeURIComponent(query)}`)
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
  selectedPropertyOption.value = null
  showPropertySelect.value = true
}

const handleCancelPropertyChange = () => {
  if (originalProperty.value) {
    form.property_id = originalProperty.value.value
    selectedPropertyOption.value = { ...originalProperty.value }
    showPropertySelect.value = false
  }
}

const handlePropertySelect = () => {
  if (form.property_id && selectedPropertyOption.value) {
    originalProperty.value = { ...selectedPropertyOption.value }
    showPropertySelect.value = false
  }
}

const handlePropertyKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && showPropertySelect.value && originalProperty.value) {
    event.preventDefault()
    handleCancelPropertyChange()
  }
}

const handleSubmit = () => {
  // Set property_id if not already set
  if (!form.property_id) {
    if (selectedPropertyOption.value?.value) {
      form.property_id = selectedPropertyOption.value.value
    } else if (props.lease?.property_id) {
      form.property_id = props.lease.property_id
    }
  }

  // Set tenant_id if not already set
  if (!form.tenant_id) {
    if (selectedTenantOption.value?.value) {
      form.tenant_id = selectedTenantOption.value.value
    } else if (props.lease?.tenant_id) {
      form.tenant_id = props.lease.tenant_id
    }
  }

  // Submit form
  if (props.lease?.id) {
    form.put(`/leases/${props.lease.id}`, { preserveScroll: true })
  } else {
    form.post('/leases', { preserveScroll: true })
  }
}


const handleCancel = () => {
  form.reset()
  router.visit('/leases')
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-800">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <FileText class="h-5 w-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ props.lease ? 'Edit Lease' : 'Create New Lease' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ props.lease
            ? 'Update lease information'
            : 'Add a new lease to manage your property' }}
        </p>
      </div>
    </div>

    <div class="space-y-5">

       <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Tenant Name <span class="text-red-500">*</span>
        </label>

        <div v-if="!showTenantSelect && selectedTenantOption" class="relative">
          <input type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 pr-10 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white"
            :value="selectedTenantOption.label" readonly />

          <button type="button" @click="handleClearTenant"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
            title="Clear selection">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

        <div v-else ref="asyncSelectContainer" class="relative" @keydown="handleTenantKeyDown" tabindex="-1">
          <TenantSelect v-model="form.tenant_id" :fetchOptions="fetchTenants" :selectedTenantOption="selectedTenantOption"
            placeholder="Search and select a tenant..." :error="allErrors.tenant_id"
            @update:modelValue="handleTenantSelect" />

          <button v-if="originalTenant" type="button" @click="handleCancelTenantChange"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors z-10"
            title="Cancel change (ESC)">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Property <span class="text-red-500">*</span>
        </label>

        <div v-if="!showPropertySelect && selectedPropertyOption" class="relative">
          <input type="text"
            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 pr-10 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white"
            :value="selectedPropertyOption.label" readonly />

          <button type="button" @click="handleClearProperty"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
            title="Clear selection">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

        <div v-else ref="asyncSelectContainer" class="relative" @keydown="handlePropertyKeyDown" tabindex="-1">
          <PropertySelect v-model="form.property_id" :fetchOptions="fetchProperties" :selectedPropertyOption="selectedPropertyOption"
            placeholder="Search and select a property..." :error="allErrors.property_id"
            @update:modelValue="handlePropertySelect" />

          <button v-if="originalProperty" type="button" @click="handleCancelPropertyChange"
            class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors z-10"
            title="Cancel change (ESC)">
            <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          </button>
        </div>

      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Monthly Rent</label>
        <BaseInput v-model="form.monthly_rent" type="number" placeholder="Enter monthly rent amount" :error="allErrors.monthly_rent"
          class="w-full" />
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> Start Date</label>
          <BaseInput v-model="form.start_date" type="date" :error="allErrors.start_date"
            class="w-full" />
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300"> End Date</label>
          <BaseInput v-model="form.end_date" type="date" :min="enddMin" :error="allErrors.end_date"
            class="w-full" />
        </div>
      </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
      <BaseButton type="button" variant="outline" @click="handleCancel" :disabled="form.processing">
        Cancel
      </BaseButton>

      <BaseButton type="button" @click="handleSubmit" :disabled="form.processing"
        class="bg-blue-600 text-white hover:bg-blue-700">
        {{ props.lease ? 'Update Lease' : 'Create Lease' }}
      </BaseButton>
    </div>
  </div>
</template>

<style scoped></style>