<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Users, Paperclip } from 'lucide-vue-next'
import { computed, ref, onMounted } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'
import Textarea from '@/components/ui/input/TextArea.vue'

import FileUpload from './FileUpload.vue'

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

const getFileNameFromUrl = (url: string): string => {
  try {
    return new URL(url).pathname.split('/').pop() || 'Document'
  } catch {
    return url.split('/').pop() || 'Document'
  }
}


const selectedFiles = ref<File[]>([])
const fileDocumentTypes = ref<string[]>([])
const existingFiles = ref<TenantFile[]>([])

const form = useForm({
  name: props.tenant?.data?.name ?? '',
  email: props.tenant?.data?.email ?? '',
  phone: props.tenant?.data?.phone ?? '',
  address: props.tenant?.data?.address ?? '',
  files: [] as File[],
  file_document_types: [] as string[],
  delete_files: [] as string[]
})

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

  const formData = new FormData()

  formData.append('name', form.name)
  formData.append('email', form.email || '')
  formData.append('phone', form.phone || '')
  formData.append('address', form.address || '') 

  const newFilesDocTypes = fileDocumentTypes.value.slice(existingFiles.value.length)

  newFilesDocTypes.forEach((docType, index) => {
    formData.append(`file_document_types[${index}]`, docType)
  })

  form.files.forEach((file, index) => {
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
              :max-size="2" 
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
                <li
                  v-for="doc in props.document_types"
                  :key="doc.value"
                  class="flex items-center gap-2"
                >
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span>{{ doc.label }}</span>
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

            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Address
              </label>
              <Textarea
                  v-model="form.address"
                  placeholder="Enter tenant address"
                  :rows="3"
                />
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