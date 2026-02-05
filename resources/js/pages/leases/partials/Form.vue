<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Users, X, Paperclip } from 'lucide-vue-next'
import { computed, ref, onMounted } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import AsyncSelect from '@/components/ui/input/AsyncSelect.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'

// import FileUpload from './FileUpload.vue'

interface TenantFile {
  url: string
  type?: string
  name?: string
  document_type?: string
}

const props = defineProps<{
  tenant?: any
  document_types: any
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

const selectedOption = ref<{ value: number; label: string } | null>(
  props.tenant?.data?.property
    ? { value: props.tenant.data.property_id, label: props.tenant.data.property.title }
    : null
)

const originalProperty = ref<{ value: number; label: string } | null>(
  selectedOption.value ? { ...selectedOption.value } : null
)

const selectedFiles = ref<File[]>([])
const fileDocumentTypes = ref<string[]>([])
const existingFiles = ref<TenantFile[]>([])
const showAsyncSelect = ref(!props.tenant?.data?.property)

const form = useForm({
  name: props.tenant?.data?.name ?? '',
  property_id: props.tenant?.data?.property_id ?? null,
  email: props.tenant?.data?.email ?? '',
  phone: props.tenant?.data?.phone ?? '',
  lease_start: formatDateForInput(props.tenant?.data?.lease_start) ?? null,
  lease_end: formatDateForInput(props.tenant?.data?.lease_end) ?? null,
  files: [] as File[],
  file_document_types: [] as string[],
  delete_files: [] as string[]
})

const leaseEndMin = computed(() => form.lease_start || null)
const allErrors = computed(() => form.errors)
const previewUrls = computed(() => existingFiles.value.map(file => file.url))
const hasExistingFiles = computed(() => existingFiles.value.length > 0)

const documentTypesOptions = computed(() =>
  props.document_types?.map((i: { key: string; label: string }) => ({ 
    label: i.label, 
    value: i.key 
  })) ?? []
)

const initializeExistingFiles = (): TenantFile[] => {
  if (!props.tenant?.data?.files || !Array.isArray(props.tenant.data.files)) {
    return []
  }

  return props.tenant.data.files.map((file: any) => ({
    url: file.url || '',
    type: file.type || 'application/octet-stream',
    name: file.name || getFileNameFromUrl(file.url || ''),
    document_type: file.document_type || file.pivot?.document_type || 'other'
  }))
}

const initializeDocumentTypes = (): string[] => {
  if (!props.tenant?.data?.files || !Array.isArray(props.tenant.data.files)) {
    return []
  }
  
  const types = props.tenant.data.files.map((file: any) => 
    file.document_type || file.pivot?.document_type || 'other'
  )
  
  return types
}

const handleDocumentTypesChange = (types: string[]) => {

  fileDocumentTypes.value = [...types]
  form.file_document_types = [...types]
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
  
  if (!form.property_id) {
    if (selectedOption.value?.value) {
      form.property_id = selectedOption.value.value
    } else if (props.tenant?.data?.property_id) {
      form.property_id = props.tenant.data.property_id
    }
  }

  const formData = new FormData()

  formData.append('name', form.name)
  formData.append('property_id', String(form.property_id || ''))
  formData.append('email', form.email || '')
  formData.append('phone', form.phone || '')
  formData.append('lease_start', form.lease_start || '')
  formData.append('lease_end', form.lease_end || '')

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

  if (props.tenant?.data?.id) {
    formData.append('_method', 'PUT')
  }

  const url = props.tenant?.data?.id ? `/tenants/${props.tenant.data.id}` : '/tenants'

  router.post(url, formData, {
    preserveScroll: true,
    onStart: () => console.log('Submitting tenant form...'),
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
  router.visit('/tenants')
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

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
      
            <FileUpload 
              v-model="selectedFiles" 
              :preview-urls="previewUrls" 
              :document-types-options="documentTypesOptions"
              label="Tenant Documents"
              description="Upload tenant agreement and related documents (PDF, DOC, DOCX, Images)"
              accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
              :max-size="1" 
              :multiple="true" 
              :max-files="10"
              :required="!props.tenant && !hasExistingFiles" 
              :error="allErrors.files"
              @update:modelValue="handleFilesChange"
              @update:document-types="handleDocumentTypesChange"
              @remove-existing="handleRemoveExistingFile"
              @remove-all="handleRemoveAllFiles"
              @download="handleDownloadFile" 
            />

      
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


      <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
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