<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { FileText, X, Paperclip, ClipboardList } from 'lucide-vue-next'
import { computed, ref, onMounted } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import AsyncSelect from '@/components/ui/input/AsyncSelect.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'
import BaseSelect from '@/components/ui/input/Select.vue'

import FileUpload from './FileUpload.vue'

interface TenantFile {
  url: string
  type?: string
  name?: string
  document_type?: string
}

const props = defineProps<{
  lease?: any
  late_fee_types: any
  statuses: any
  document_types: any
}>()

console.log(props.late_fee_types)

const formatDateForInput = (dateString: string | null): string | null => {
  if (!dateString) return null

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return null

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')

    return `${year}-${month}-${day}`
  } catch {
    return null
  }
}

const getFileNameFromUrl = (url: string): string => {
  try {
    return new URL(url).pathname.split('/').pop() || 'Document'
  } catch {
    return url.split('/').pop() || 'Document'
  }
}

const selectedTenantOption = ref<{ value: number; label: string } | null>(
  props.lease?.data?.lease
    ? { value: props.lease.data.lease_id, label: props.lease.data.tenant.name }
    : null
)

const originalTenant = ref<{ value: number; label: string } | null>(
  selectedTenantOption.value ? { ...selectedTenantOption.value } : null
)

const selectedPropertyOption = ref<{ value: number; label: string } | null>(
  props.lease?.data?.property
    ? { value: props.lease.data.property_id, label: props.lease.data.property.title }
    : null
)

const originalProperty = ref<{ value: number; label: string } | null>(
  selectedPropertyOption.value ? { ...selectedPropertyOption.value } : null
)

const selectedFiles = ref<File[]>([])
const fileDocumentTypes = ref<string[]>([])
const existingFiles = ref<TenantFile[]>([])
const showAsyncTenantSelect = ref(!props.lease?.data?.tenant)
const showAsyncPropertySelect = ref(!props.lease?.data?.property)

const form = useForm({
  tenant_id: props.lease?.data?.tenant_id ?? null,
  file_id: props.lease?.data?.file_id ?? null,
  property_id: props.lease?.data?.property_id ?? null,
  monthly_rent: props.lease?.data?.monthly_rent ?? '',
  start_date: formatDateForInput(props.lease?.data?.start_date) ?? null,
  end_date: formatDateForInput(props.lease?.data?.end_date) ?? null,
  status: props.lease?.data?.status ?? '',
  terms: props.lease?.data?.terms ?? '',
  notes: props.lease?.data?.notes ?? '',
  rent_due_day: props.lease?.data?.rent_due_day ?? '',
  grace_period_days: props.lease?.data?.grace_period_days ?? '',
  late_fee_type: props.lease?.data?.late_fee_type ?? '',
  late_fee_value: props.lease?.data?.late_fee_value ?? '',
  late_fee_cap: props.lease?.data?.late_fee_cap ?? '',
  files: [] as File[],
  file_document_types: [] as string[],
  delete_files: [] as string[]
})

const leaseEndMin = computed(() => form.start_date || null)
const allErrors = computed(() => form.errors)
const previewUrls = computed(() => existingFiles.value.map(file => file.url))
const hasExistingFiles = computed(() => existingFiles.value.length > 0)

const documentTypesOptions = computed(() =>
  props.document_types?.map((i: { key: string; label: string }) => ({ 
    label: i.label, 
    value: i.key 
  })) ?? []
)

const statusOptions = computed(() =>
  props.statuses?.map((i: { key: string; label: string }) => ({ label: i.label, value: i.key })) ?? []
)

const lateFeeTypeOptions = computed(() =>
  props.late_fee_types?.map((i: { key: string; label: string }) => ({ label: i.label, value: i.key })) ?? []
)

const initializeExistingFiles = (): TenantFile[] => {
  if (!props.lease?.data?.files || !Array.isArray(props.lease.data.files)) {
    return []
  }

  return props.lease.data.files.map((file: any) => ({
    url: file.url || '',
    type: file.type || 'application/octet-stream',
    name: file.name || getFileNameFromUrl(file.url || ''),
    document_type: file.document_type || file.pivot?.document_type || 'other'
  }))
}

const initializeDocumentTypes = (): string[] => {
  if (!props.lease?.data?.files || !Array.isArray(props.lease.data.files)) {
    return []
  }
  
  const types = props.lease.data.files.map((file: any) => 
    file.document_type || file.pivot?.document_type || 'other'
  )
  
  return types
}

const handleDocumentTypesChange = (types: string[]) => {

  fileDocumentTypes.value = [...types]
  form.file_document_types = [...types]
}

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

const handleClearTenant = () => {
  form.property_id = null
  selectedPropertyOption.value = null
  showAsyncPropertySelect.value = true
}

const handleCancelTenantChange = () => {
  if (originalProperty.value) {
    form.property_id = originalProperty.value.value
    selectedPropertyOption.value = { ...originalProperty.value }
    showAsyncPropertySelect.value = false
  }
}

const handleTenantSelect = () => {
  if (form.property_id && selectedPropertyOption.value) {
    originalProperty.value = { ...selectedPropertyOption.value }
    showAsyncPropertySelect.value = false
  }
}

const handleTenantKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && showAsyncPropertySelect.value && originalProperty.value) {
    event.preventDefault()
    handleCancelPropertyChange()
  }
}

const handleClearProperty = () => {
  form.property_id = null
  selectedPropertyOption.value = null
  showAsyncPropertySelect.value = true
}

const handleCancelPropertyChange = () => {
  if (originalProperty.value) {
    form.property_id = originalProperty.value.value
    selectedPropertyOption.value = { ...originalProperty.value }
    showAsyncPropertySelect.value = false
  }
}

const handlePropertySelect = () => {
  if (form.property_id && selectedPropertyOption.value) {
    originalProperty.value = { ...selectedPropertyOption.value }
    showAsyncPropertySelect.value = false
  }
}

const handlePropertyKeyDown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && showAsyncPropertySelect.value && originalProperty.value) {
    event.preventDefault()
    handleCancelPropertyChange()
  }
}

const handleFilesChange = (files: File[]) => {
  
  selectedFiles.value = files
  form.files = files
  
}

const handleRemoveExistingFile = (url: string) => {
  
  if (!form.delete_files.includes(url)) {
    form.delete_files.push(url)
  }
  
  const index = existingFiles.value.findIndex(f => f.url === url)
  if (index !== -1) {
    existingFiles.value.splice(index, 1)
  }
}

const handleRemoveAllFiles = () => {
  
  existingFiles.value.forEach(file => {
    if (!form.delete_files.includes(file.url)) {
      form.delete_files.push(file.url)
    }
  })
  
  existingFiles.value = []
  selectedFiles.value = []
  fileDocumentTypes.value = []
  form.files = []
  form.file_document_types = []
  
}

const handleDownloadFile = (index: number) => {
  if (index < existingFiles.value.length) {
    const file = existingFiles.value[index]
    window.open(file.url, '_blank')
  }
}

const handleSubmit = () => {
  
  if (!form.tenant_id) {
    if (selectedTenantOption.value?.value) {
      form.tenant_id = selectedTenantOption.value.value
    } else if (props.lease?.data?.tenant_id) {
      form.tenant_id = props.lease.data.tenant_id
    }
  }

   if (!form.property_id) {
    if (selectedPropertyOption.value?.value) {
      form.property_id = selectedPropertyOption.value.value
    } else if (props.lease?.data?.property_id) {
      form.property_id = props.lease.data.property_id
    }
  }

  const formData = new FormData()

  formData.append('tenant_id', String(form.tenant_id || ''))
  formData.append('property_id', String(form.property_id || ''))
  formData.append('monthly_rent', form.monthly_rent || '')
  formData.append('start_date', form.start_date || '')
  formData.append('end_date', form.end_date || '')
  formData.append('status', form.status || '')
  formData.append('terms', form.terms || '')
  formData.append('notes', form.notes || '')
  formData.append('rent_due_day', form.rent_due_day || '')
  formData.append('grace_period_days', form.grace_period_days || '')
  formData.append('late_fee_type', form.late_fee_type || '')
  formData.append('late_fee_value', form.late_fee_value || '')
  formData.append('late_fee_cap', form.late_fee_cap || '')

  const newFilesDocTypes = fileDocumentTypes.value.slice(existingFiles.value.length)

  newFilesDocTypes.forEach((docType, index) => {
    console.log(`Appending file_document_types[${index}] = ${docType}`)
    formData.append(`file_document_types[${index}]`, docType)
  })

  form.files.forEach((file, index) => {
    console.log(`Appending files[${index}] = ${file.name}`)
    formData.append(`files[${index}]`, file)
  })

  form.delete_files.forEach((url, index) => {
    formData.append(`delete_files[${index}]`, url)
  })

  if (props.lease?.data?.id) {
    formData.append('_method', 'PUT')
  }

  const url = props.lease?.data?.id ? `/leases/${props.lease.data.id}` : '/leases'

  router.post(url, formData, {
    preserveScroll: true,
    onStart: () => console.log('Submitting lease form...'),
    onSuccess: () => {
      form.reset()
      selectedFiles.value = []
      fileDocumentTypes.value = []
      existingFiles.value = []
      form.delete_files = []
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors)
    }
  })
}

const handleCancel = () => {
  form.reset()
  selectedFiles.value = []
  fileDocumentTypes.value = []
  existingFiles.value = initializeExistingFiles()
  form.delete_files = []
  router.visit('/leases')
}

onMounted(() => {
  
  existingFiles.value = initializeExistingFiles()
  
  const initialTypes = initializeDocumentTypes()
  fileDocumentTypes.value = initialTypes
  form.file_document_types = initialTypes

})
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
            ? 'Update lease information and documents'
            : 'Add a new lease agreement' }}
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Left Column: Lease Details -->
      <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
          <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30">
              <FileText class="h-5 w-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                Lease Information
              </h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Enter lease details and terms
              </p>
            </div>
          </div>

          <div class="space-y-5">
            <!-- Tenant Selection -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tenant <span class="text-red-500">*</span>
              </label>

              <div v-if="!showAsyncTenantSelect && selectedTenantOption" class="relative">
                <input 
                  type="text"
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 pr-10 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white"
                  :value="selectedTenantOption.label" 
                  readonly 
                />

                <button 
                  type="button" 
                  @click="handleClearTenant"
                  class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                  title="Clear selection"
                >
                  <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </button>
              </div>

              <div v-else ref="asyncSelectContainer" class="relative" @keydown="handleTenantKeyDown" tabindex="-1">
                <AsyncSelect 
                  v-model="form.tenant_id" 
                  :fetchOptions="fetchTenants" 
                  :selectedTenantOption="selectedTenantOption"
                  placeholder="Search and select a tenant..." 
                  :error="allErrors.tenant_id"
                  @update:modelValue="handleTenantSelect" 
                />

                <button 
                  v-if="originalTenant" 
                  type="button" 
                  @click="handleCancelTenantChange"
                  class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors z-10"
                  title="Cancel change (ESC)"
                >
                  <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </button>
              </div>
            </div>

            <!-- Property Selection -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Property <span class="text-red-500">*</span>
              </label>

              <div v-if="!showAsyncPropertySelect && selectedPropertyOption" class="relative">
                <input 
                  type="text"
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 pr-10 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white"
                  :value="selectedPropertyOption.label" 
                  readonly 
                />

                <button 
                  type="button" 
                  @click="handleClearProperty"
                  class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                  title="Clear selection"
                >
                  <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </button>
              </div>

              <div v-else ref="asyncSelectContainer" class="relative" @keydown="handlePropertyKeyDown" tabindex="-1">
                <AsyncSelect 
                  v-model="form.property_id" 
                  :fetchOptions="fetchProperties" 
                  :selectedPropertyOption="selectedPropertyOption"
                  placeholder="Search and select a property..." 
                  :error="allErrors.property_id"
                  @update:modelValue="handlePropertySelect" 
                />

                <button 
                  v-if="originalProperty" 
                  type="button" 
                  @click="handleCancelPropertyChange"
                  class="absolute right-2 top-1/2 -translate-y-1/2 p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors z-10"
                  title="Cancel change (ESC)"
                >
                  <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </button>
              </div>
            </div>

            <!-- Monthly Rate -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Monthly Rate <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                <BaseInput 
                  v-model="form.monthly_rent" 
                  type="number" 
                  placeholder="0.00" 
                  :error="allErrors.monthly_rent"
                  class="w-full pl-8" 
                  min="0"
                  step="0.01"
                />
              </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Start Date <span class="text-red-500">*</span>
                </label>
                <BaseInput 
                  v-model="form.start_date" 
                  type="date" 
                  :error="allErrors.start_date"
                  class="w-full" 
                />
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  End Date
                </label>
                <BaseInput 
                  v-model="form.end_date" 
                  type="date" 
                  :min="leaseEndMin" 
                  :error="allErrors.end_date"
                  class="w-full" 
                />
              </div>
            </div>

            <!-- Rent Due Day -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Rent Due Day <span class="text-red-500">*</span>
              </label>
              <BaseInput 
                v-model="form.rent_due_day" 
                type="number" 
                placeholder="e.g., 1 (for 1st of the month)" 
                :error="allErrors.rent_due_day"
                class="w-full" 
                min="1"
                max="31"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Day of the month when rent is due (1-31)
              </p>
            </div>

            <!-- Grace Period -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Grace Period (Days)
              </label>
              <BaseInput 
                v-model="form.grace_period_days" 
                type="number" 
                placeholder="e.g., 5" 
                :error="allErrors.grace_period_days"
                class="w-full" 
                min="0"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Number of days after due date before late fees apply
              </p>
            </div>

            <!-- Late Fee Configuration -->
            <div class="space-y-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
              <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                Late Fee Configuration
              </h5>
              
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Late Fee Type
                </label>
                <BaseSelect
                  v-model="form.late_fee_type"
                  :options="lateFeeTypeOptions"
                  :error="allErrors.late_fee_type"
                  placeholder="Select Late Fee Type"
                  class="w-full"
                />
              </div>

              <div v-if="form.late_fee_type">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Late Fee Value
                </label>
                <div class="relative">
                  <BaseInput 
                    v-model="form.late_fee_value" 
                    type="number" 
                    :placeholder="form.late_fee_type === 'percentage' ? 'e.g., 5' : 'e.g., 50'" 
                    :error="allErrors.late_fee_value"
                    class="w-full" 
                    min="0"
                    :step="form.late_fee_type === 'percentage' ? '0.1' : '0.01'"
                  />
                  <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">
                    {{ form.late_fee_type === 'percentage' ? '%' : '$' }}
                  </span>
                </div>
              </div>

              <div v-if="form.late_fee_type">
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Late Fee Cap
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">$</span>
                  <BaseInput 
                    v-model="form.late_fee_cap" 
                    type="number" 
                    placeholder="0.00" 
                    :error="allErrors.late_fee_cap"
                    class="w-full pl-8" 
                    min="0"
                    step="0.01"
                  />
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  Maximum late fee amount (optional)
                </p>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Status <span class="text-red-500">*</span>
              </label>
              <BaseSelect
                  v-model="form.status"
                  :options="statusOptions"
                  :error="allErrors.status"
                  placeholder="Select status"
                  class="w-full"
                />
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Documents and Additional Info -->
      <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
          <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30">
              <Paperclip class="h-5 w-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                Lease Documents
              </h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Upload required lease documents
              </p>
            </div>
          </div>

          <div class="space-y-6">
            <FileUpload 
              v-model="selectedFiles" 
              :preview-urls="previewUrls" 
              :document-types-options="documentTypesOptions"
              label="Lease Documents"
              description="Upload lease agreement and related documents (PDF, DOC, DOCX, Images)"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
              :max-size="10" 
              :multiple="true" 
              :max-files="10"
              :required="!props.lease && !hasExistingFiles" 
              :error="allErrors.files"
              @update:modelValue="handleFilesChange"
              @update:document-types="handleDocumentTypesChange"
              @remove-existing="handleRemoveExistingFile"
              @remove-all="handleRemoveAllFiles"
              @download="handleDownloadFile" 
            />

            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800">
              <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">
                Recommended Documents
              </h5>
              <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-2">
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Signed Lease Agreement</span>
                </li>
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Tenant Application Form</span>
                </li>
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>ID Verification</span>
                </li>
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Proof of Income</span>
                </li>
              </ul>
            </div>
            
            <div v-if="hasExistingFiles" class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
              <div class="flex items-center justify-between mb-2">
                <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Existing Documents
                </h5>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  {{ existingFiles.length }} file{{ existingFiles.length !== 1 ? 's' : '' }}
                </span>
              </div>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                Click the download icon to view existing files. Remove to delete them.
              </p>
            </div>
          </div>
        </div>

        <!-- Additional Information -->
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
          <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30">
              <ClipboardList class="h-5 w-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                Additional Information
              </h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Terms, conditions, and notes
              </p>
            </div>
          </div>

          <div class="space-y-5">
            <!-- Terms -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Terms & Conditions
              </label>
              <textarea 
                v-model="form.terms" 
                rows="4"
                placeholder="Enter lease terms and conditions..."
                :error="allErrors.terms"
                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-white resize-none"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Optional: Special terms or conditions for this lease
              </p>
            </div>

            <!-- Notes -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Notes
              </label>
              <textarea 
                v-model="form.notes" 
                rows="3"
                placeholder="Enter any additional notes..."
                :error="allErrors.notes"
                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-3 bg-white dark:bg-gray-800 text-gray-900 dark:text-white resize-none"
              ></textarea>
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Internal notes about this lease (not shown to tenant)
              </p>
            </div>
          </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
          <BaseButton 
            type="button" 
            variant="outline" 
            @click="handleCancel" 
            :disabled="form.processing"
          >
            Cancel
          </BaseButton>

          <BaseButton 
            type="button" 
            @click="handleSubmit" 
            :disabled="form.processing"
            class="bg-blue-600 text-white hover:bg-blue-700"
          >
            {{ props.lease ? 'Update Lease' : 'Create Lease' }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>