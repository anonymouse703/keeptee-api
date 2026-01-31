<!-- tenants/partials/Form.vue -->
<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Users, X, Paperclip } from 'lucide-vue-next'
import { computed, ref, onMounted } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import FileUpload from '@/components/ui/file/FileUpload.vue'
import AsyncSelect from '@/components/ui/input/AsyncSelect.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'

interface TenantFile {
  url: string
  type?: string
  name?: string
}

const props = defineProps<{
  tenant?: any
}>()

// Helper functions
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

// State
const selectedOption = ref<{ value: number; label: string } | null>(
  props.tenant?.property
    ? { value: props.tenant.property_id, label: props.tenant.property.title }
    : null
)

const originalProperty = ref<{ value: number; label: string } | null>(
  selectedOption.value ? { ...selectedOption.value } : null
)

const selectedFiles = ref<File[]>([])
const existingFiles = ref<TenantFile[]>([])
const showAsyncSelect = ref(!props.tenant?.property)

// Form
const form = useForm({
  name: props.tenant?.name ?? '',
  property_id: props.tenant?.property_id ?? null,
  email: props.tenant?.email ?? '',
  phone: props.tenant?.phone ?? '',
  lease_start: formatDateForInput(props.tenant?.lease_start) ?? null,
  lease_end: formatDateForInput(props.tenant?.lease_end) ?? null,
  files: [] as File[],
  delete_existing_files: [] as string[]
})

// Computed
const leaseEndMin = computed(() => form.lease_start || null)
const allErrors = computed(() => form.errors)
const previewUrls = computed(() => existingFiles.value.map(file => file.url))
const hasExistingFiles = computed(() => existingFiles.value.length > 0)

// Methods
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
  selectedOption.value = null
  showAsyncSelect.value = true
}

const handleCancelPropertyChange = () => {
  if (originalProperty.value) {
    form.property_id = originalProperty.value.value
    selectedOption.value = { ...originalProperty.value }
    showAsyncSelect.value = false
  }
}

const handlePropertySelect = () => {
  if (form.property_id && selectedOption.value) {
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

const handleFilesChange = (files: File[]) => {
  selectedFiles.value = files
  form.files = files
}

const handleRemoveFile = (index: number) => {
  if (index < existingFiles.value.length) {
    const fileToDelete = existingFiles.value[index]
    form.delete_existing_files.push(fileToDelete.url)
    existingFiles.value.splice(index, 1)
  } else {
    const newFileIndex = index - existingFiles.value.length
    selectedFiles.value.splice(newFileIndex, 1)
    form.files = [...selectedFiles.value]
  }
}

const handleRemoveAllFiles = () => {
  existingFiles.value.forEach(file => {
    form.delete_existing_files.push(file.url)
  })
  existingFiles.value = []
  selectedFiles.value = []
  form.files = []
}

const handleDownloadFile = (index: number) => {
  if (index < existingFiles.value.length) {
    const file = existingFiles.value[index]
    window.open(file.url, '_blank')
  }
}

const handleSubmit = () => {
  // Ensure property_id is set
  if (!form.property_id) {
    if (selectedOption.value?.value) {
      form.property_id = selectedOption.value.value
    } else if (props.tenant?.property_id) {
      form.property_id = props.tenant.property_id
    }
  }

  const formData = new FormData()

  // Add form fields
  Object.entries(form.data()).forEach(([key, value]) => {
    if (key === 'files' && Array.isArray(value)) {
      value.forEach(file => {
        if (file instanceof File) {
          formData.append('files[]', file)
        }
      })
    } else if (key === 'delete_existing_files' && Array.isArray(value)) {
      value.forEach(identifier => {
        formData.append('delete_existing_files[]', identifier)
      })
    } else if (value !== null && value !== undefined) {
      formData.append(key, String(value))
    }
  })

  // Add method spoofing for PUT requests
  if (props.tenant?.id) {
    formData.append('_method', 'PUT')
  }

  // Submit
  const url = props.tenant?.id ? `/tenants/${props.tenant.id}` : '/tenants'
  
  router.post(url, formData, {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      selectedFiles.value = []
      existingFiles.value = []
      form.delete_existing_files = []
    }
  })
}

const handleCancel = () => {
  form.reset()
  selectedFiles.value = []
  existingFiles.value = initializeExistingFiles()
  form.delete_existing_files = []
  router.visit('/tenants')
}

// Initialize existing files from tenant data
const initializeExistingFiles = (): TenantFile[] => {
  if (!props.tenant?.file || !Array.isArray(props.tenant.file)) {
    return []
  }

  return props.tenant.file.map((file: any) => ({
    url: file.url || '',
    type: file.type || 'application/octet-stream',
    name: file.name || getFileNameFromUrl(file.url || '')
  }))
}

// Lifecycle
onMounted(() => {
  existingFiles.value = initializeExistingFiles()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
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
            ? 'Update tenant information and documents'
            : 'Add a new tenant with required documents' }}
        </p>
      </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Left Column: File Upload -->
      <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
          <div class="flex items-center gap-3 mb-6">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900/30">
              <Paperclip class="h-5 w-5 text-primary-600 dark:text-primary-400" />
            </div>
            <div>
              <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                Tenant Documents
              </h4>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Upload required tenant documents
              </p>
            </div>
          </div>

          <div class="space-y-6">
            <!-- File Upload Component -->
            <FileUpload 
              v-model="selectedFiles" 
              :preview-urls="previewUrls" 
              label="Tenant Documents"
              description="Upload tenant agreement and related documents (PDF, DOC, DOCX, Images)"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
              :max-size="10" 
              :multiple="true" 
              :max-files="10"
              :required="!props.tenant && !hasExistingFiles" 
              :error="allErrors.files"
              @update:modelValue="handleFilesChange" 
              @remove="handleRemoveFile" 
              @remove-all="handleRemoveAllFiles"
              @download="handleDownloadFile" 
            />

            <!-- Documents Checklist -->
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800">
              <h5 class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">
                Required Documents Checklist
              </h5>
              <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-2">
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Valid ID (Passport, Driver's License)</span>
                </li>
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Signed Rental Agreement</span>
                </li>
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Proof of Income (Last 3 months)</span>
                </li>
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Security Deposit Receipt</span>
                </li>
                <li class="flex items-center gap-2">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>Emergency Contact Information</span>
                </li>
              </ul>
            </div>

            <!-- Existing Files Info -->
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
      </div>

      <!-- Right Column: Tenant Information -->
      <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
          <div class="space-y-5">
            <!-- Tenant Name -->
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

            <!-- Property Selection -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Property <span class="text-red-500">*</span>
              </label>

              <div v-if="!showAsyncSelect && selectedOption" class="relative">
                <input 
                  type="text"
                  class="w-full border border-gray-300 dark:border-gray-600 rounded-lg p-2 pr-10 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white"
                  :value="selectedOption.label" 
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

              <div v-else ref="asyncSelectContainer" class="relative" @keydown="handleKeyDown" tabindex="-1">
                <AsyncSelect 
                  v-model="form.property_id" 
                  :fetchOptions="fetchProperties" 
                  :selectedOption="selectedOption"
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

            <!-- Email -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Email Address
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
                Phone Number
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
        </div>

        <!-- Form Actions -->
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
            {{ props.tenant ? 'Update Tenant' : 'Create Tenant' }}
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Responsive adjustments */
@media (max-width: 1024px) {
  .grid {
    grid-template-columns: 1fr;
  }
}
</style>