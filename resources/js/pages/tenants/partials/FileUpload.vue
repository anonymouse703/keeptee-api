<script setup lang="ts">
import { Upload, File, X, Check, AlertCircle, FileText, FileImage, FileArchive, Download } from 'lucide-vue-next'
import { ref, computed, watch } from 'vue'

import BaseSelect from '@/components/ui/input/Select.vue'

interface DocumentType {
  value: string
  label: string
}

interface UploadedFile {
  file: File | null
  preview: string
  type: string
  name: string
  size?: number
  existingUrl?: string
  isExisting: boolean
}

const props = withDefaults(
  defineProps<{
    maxFiles?: number
    maxSize?: number
    accept?: string
    label?: string
    description?: string
    multiple?: boolean
    required?: boolean
    error?: string | null
    modelValue?: File[]
    previewUrls?: string[]
    documentTypesOptions?: DocumentType[]
  }>(),
  {
    maxFiles: 10,
    maxSize: 10,
    accept: '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt',
    label: 'Upload Files',
    description: 'Upload files',
    multiple: false,
    required: false,
    error: null,
    modelValue: () => [],
    previewUrls: () => [],
    documentTypesOptions: () => []
  }
)

const emit = defineEmits<{
  'update:modelValue': [files: File[]]
  'update:document-types': [types: string[]]
  'remove': [index: number]
  'remove-existing': [url: string]
  'remove-all': []
  'download': [index: number]
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const dragOver = ref(false)
const fileError = ref<string | null>(null)
const uploadedFiles = ref<UploadedFile[]>([])
const existingFiles = ref<UploadedFile[]>([])
const documentTypes = ref<string[]>([])

/**
 * Computed properties
 */
const fileCount = computed(() => (props.modelValue?.length || 0) + existingFiles.value.length)
const hasFiles = computed(() => fileCount.value > 0)
const canAddMore = computed(() => props.multiple ? (props.modelValue?.length || 0) < props.maxFiles : false)
const allFiles = computed(() => [...existingFiles.value, ...uploadedFiles.value])

/**
 * Format helpers, icons, colors...
 */
const getFileIcon = (file: UploadedFile) => {
  const type = file.type?.toLowerCase() || ''
  const name = file.name?.toLowerCase() || ''

  if (type.includes('pdf') || name.endsWith('.pdf')) return FileText
  if (type.includes('image') || name.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/)) return FileImage
  if (type.includes('zip|rar|tar|gz|7z')) return FileArchive
  if (type.includes('word') || name.match(/\.(doc|docx)$/)) return FileText
  if (type.includes('excel') || name.match(/\.(xls|xlsx)$/)) return FileText
  if (type.includes('text') || name.endsWith('.txt')) return FileText
  return File
}

const getFileCategoryColor = (file: UploadedFile): string => {
  const type = file.type?.toLowerCase() || ''
  const name = file.name?.toLowerCase() || ''

  if (type.includes('image') || name.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/)) return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
  if (type.includes('pdf') || name.endsWith('.pdf')) return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
  if (type.includes('word') || name.match(/\.(doc|docx)$/)) return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
  if (type.includes('excel') || name.match(/\.(xls|xlsx)$/)) return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
  if (type.includes('zip') || type.includes('rar') || type.includes('tar') || name.match(/\.(zip|rar|tar|gz|7z)$/)) return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
  return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
}

/**
 * File name & size formatting
 */
const truncateFileName = (name: string, maxLength: number = 24): string => {
  if (name.length <= maxLength) return name
  const extension = name.split('.').pop() || ''
  const nameWithoutExt = name.slice(0, name.length - extension.length - 1)
  const maxNameLength = maxLength - extension.length - 4
  return `${nameWithoutExt.substring(0, maxNameLength)}...${extension}`
}

const formatFileSize = (bytes?: number): string => {
  if (!bytes || bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

const formatFileType = (file: UploadedFile): string => {
  if (file.type) {
    const type = file.type.split('/')[1]?.toUpperCase() || file.type.toUpperCase()
    return type
  }

  const ext = file.name?.split('.').pop()?.toUpperCase() || 'FILE'
  return ext
}

const formatFileSizeCompact = (bytes?: number): string => {
  if (!bytes || bytes === 0) return '0B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  const size = parseFloat((bytes / Math.pow(k, i)).toFixed(1))
  return `${size}${sizes[i]}`
}

const formatFileTypeShort = (file: UploadedFile): string => {
  if (file.type) {
    const type = file.type.split('/')[1]?.toUpperCase() || file.type.toUpperCase()
    return type.length > 4 ? type.substring(0, 4) : type
  }
  const ext = file.name?.split('.').pop()?.toUpperCase() || 'FILE'
  return ext.length > 4 ? ext.substring(0, 4) : ext
}

/**
 * Validation & Preview
 */
const validateFile = (file: File): boolean => {
  if (file.size > props.maxSize * 1024 * 1024) {
    fileError.value = `"${file.name}" exceeds ${props.maxSize}MB limit`
    return false
  }
  if (props.multiple && props.modelValue && props.modelValue.length >= props.maxFiles) {
    fileError.value = `Maximum ${props.maxFiles} files allowed`
    return false
  }
  if (props.accept && props.accept !== '*') {
    const acceptedTypes = props.accept.split(',').map(t => t.trim().toLowerCase())
    const fileName = file.name.toLowerCase()
    const fileType = file.type.toLowerCase()
    const isValid = acceptedTypes.some(accepted => {
      if (accepted.startsWith('.') && fileName.endsWith(accepted)) return true
      if (accepted.includes('*') && fileType.includes(accepted.replace('*', ''))) return true
      return fileType === accepted
    })
    if (!isValid) {
      fileError.value = `"${file.name}" type not allowed. Accepted: ${props.accept}`
      return false
    }
  }
  return true
}

const generateImagePreview = (file: File): Promise<string> => {
  return new Promise(resolve => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => resolve(e.target?.result as string)
      reader.onerror = () => resolve('')
      reader.readAsDataURL(file)
    } else {
      resolve('')
    }
  })
}

/**
 * Determine default document type
 */
const getDefaultDocumentType = (file: File): string => {
  const name = file.name.toLowerCase()
  if (name.includes('lease') || name.includes('contract')) return 'lease_contract'
  if (name.includes('id') || name.includes('passport') || name.includes('drivers')) return 'government_id'
  if (name.includes('income') || name.includes('salary') || name.includes('pay')) return 'proof_of_income'
  if (name.includes('rental') || name.includes('reference') || name.includes('previous')) return 'rental_reference'
  if (name.includes('bank') || name.includes('statement')) return 'bank_statement'
  if (name.includes('employment') || name.includes('letter') || name.includes('job')) return 'employment_letter'
  return 'other'
}

/**
 * Handle document type change
 */
const handleDocumentTypeChange = (index: number, selected: any) => {
  let type = 'other'
  if (selected) {
    if (typeof selected === 'string') type = selected
    else if (typeof selected === 'object' && 'value' in selected) type = selected.value
  }

  while (documentTypes.value.length <= index) {
    documentTypes.value.push('other')
  }
  
  documentTypes.value[index] = type

  emit('update:document-types', [...documentTypes.value])
}

/**
 * Add files (from input or drop)
 */
const addFiles = async (newFiles: File[]) => {
  if (!newFiles.length) return

  if (!props.multiple) {
    newFiles = [newFiles[0]]
    uploadedFiles.value = []
    documentTypes.value = documentTypes.value.slice(0, existingFiles.value.length)
  }
  
  const validFiles: File[] = []
  
  for (const file of newFiles) {
    if (!validateFile(file)) {
      continue
    }
    
    validFiles.push(file)
    const preview = await generateImagePreview(file)
    const uploadedFile: UploadedFile = {
      file, 
      preview, 
      type: file.type, 
      name: file.name, 
      size: file.size, 
      isExisting: false
    }
    uploadedFiles.value.push(uploadedFile)
  }
  
  validFiles.forEach((file) => {
    const defaultType = getDefaultDocumentType(file)
    documentTypes.value.push(defaultType)
  })
  
  const currentFiles = props.modelValue || []
  const allNewFiles = props.multiple ? [...currentFiles, ...validFiles] : validFiles
  
  emit('update:modelValue', allNewFiles)
  emit('update:document-types', [...documentTypes.value])
}

const handleFileChange = async (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files?.length) return
  await addFiles(Array.from(input.files))
  input.value = ''
}

const handleDrop = async (event: DragEvent) => {
  event.preventDefault()
  dragOver.value = false
  if (!event.dataTransfer?.files.length) return
  await addFiles(Array.from(event.dataTransfer.files))
}

const handleDragOver = (event: DragEvent) => { 
  event.preventDefault()
  dragOver.value = true 
}

const handleDragLeave = () => { 
  dragOver.value = false 
}

const removeFile = (index: number) => {
  const actualIndex = existingFiles.value.length + index
  
  uploadedFiles.value.splice(index, 1)
  documentTypes.value.splice(actualIndex, 1)
  
  emit('update:modelValue', uploadedFiles.value.map(f => f.file!).filter(Boolean))
  emit('update:document-types', [...documentTypes.value])
}

const removeExistingFile = (index: number) => {
  if (index < 0 || index >= existingFiles.value.length) {
    return
  }
  
  const url = existingFiles.value[index].existingUrl
  if (url) {
    emit('remove-existing', url)
  }
  
  existingFiles.value.splice(index, 1)
  documentTypes.value.splice(index, 1)
  
  emit('update:document-types', [...documentTypes.value])
}

const removeAllFiles = () => {
  
  uploadedFiles.value = []
  existingFiles.value = []
  documentTypes.value = []
  fileError.value = null
  
  if (fileInput.value) fileInput.value.value = ''
  
  emit('update:modelValue', [])
  emit('update:document-types', [])
  emit('remove-all')
}

const downloadFile = (index: number) => {
  emit('download', index)
}

watch(
  () => props.previewUrls,
  (urls) => {
    
    existingFiles.value = (urls || []).map((url, idx) => {
      const filename = url.split('/').pop() || `File ${idx + 1}`
      const ext = filename.split('.').pop()?.toLowerCase() || ''
      let type = 'application/octet-stream'
      
      if (ext === 'pdf') type = 'application/pdf'
      else if (['jpg','jpeg'].includes(ext)) type = 'image/jpeg'
      else if (ext === 'png') type = 'image/png'
      else if (['doc','docx'].includes(ext)) type = 'application/msword'
      else if (['xls','xlsx'].includes(ext)) type = 'application/vnd.ms-excel'
      
      const file: UploadedFile = { 
        file: null, 
        preview: ext.match(/jpg|jpeg|png/) ? url : '', 
        type, 
        name: filename, 
        existingUrl: url, 
        isExisting: true 
      }
      return file
    })
    
    if (documentTypes.value.length === 0) {
      documentTypes.value = existingFiles.value.map(() => 'other')
      emit('update:document-types', [...documentTypes.value])
    }
  },
  { immediate: true }
)
</script>

<template>
  <div class="space-y-4">
    <div>
      <label
        v-if="label"
        class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1"
      >
        {{ label }} 
        <span v-if="required && !hasFiles" class="text-red-500">*</span>
        <span v-if="hasFiles" class="text-sm text-gray-500 ml-2">
          ({{ fileCount }} file{{ fileCount !== 1 ? 's' : '' }})
        </span>
      </label>

      <!-- Upload Area -->
      <div
        @click="fileInput?.click()"
        @dragover="handleDragOver"
        @dragleave="handleDragLeave"
        @drop="handleDrop"
        :class="[
          'border-2 border-dashed rounded-lg p-3 text-center cursor-pointer transition-all duration-200 mb-3',
          dragOver
            ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20'
            : 'border-gray-300 dark:border-gray-600 hover:border-primary-400 hover:bg-gray-50 dark:hover:bg-gray-800/50',
          fileError || error ? 'border-red-300 dark:border-red-500' : '',
          !canAddMore && props.multiple ? 'opacity-50 cursor-not-allowed' : ''
        ]"
        :title="!canAddMore && props.multiple ? `Maximum ${maxFiles} files reached` : ''"
      >
        <input
          ref="fileInput"
          type="file"
          :multiple="multiple"
          :accept="accept"
          @change="handleFileChange"
          class="hidden"
          :disabled="!canAddMore && props.multiple"
        />

        <!-- EMPTY STATE -->
        <div v-if="!hasFiles" class="space-y-2">
          <div class="flex justify-center">
            <div class="p-2 rounded-full bg-primary-100 dark:bg-primary-900/30">
              <Upload class="h-4 w-4 text-primary-600 dark:text-primary-400" />
            </div>
          </div>
          <div>
            <p class="text-xs font-medium text-gray-900 dark:text-white">
              {{ multiple ? 'Tap to upload' : 'Tap to upload file' }}
            </p>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
              Max {{ maxSize }}MB{{ multiple ? ` • Up to ${maxFiles} files` : '' }}
            </p>
          </div>
        </div>

        <!-- UPLOADED STATE -->
        <div v-else class="text-left">
          <div class="flex items-center justify-between mb-1.5">
            <div class="flex items-center gap-1.5">
              <div class="p-1 rounded bg-green-100 dark:bg-green-900/30">
                <Check class="h-3 w-3 text-green-600 dark:text-green-400" />
              </div>
              <span class="text-xs font-medium text-gray-900 dark:text-white">
                {{ fileCount }} file{{ fileCount !== 1 ? 's' : '' }}
              </span>
            </div>
            <button
              type="button"
              @click.stop="removeAllFiles"
              class="text-[11px] text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            >
              Clear all
            </button>
          </div>
          <p class="text-[11px] text-gray-500 dark:text-gray-400">
            {{ multiple && canAddMore ? 'Tap to add more' : 'Ready' }}
          </p>
        </div>
      </div>

      <!-- File List -->
      <div v-if="hasFiles" class="space-y-2">
        <div
          v-for="(file, index) in allFiles"
          :key="index"
          :class="[
            'p-2.5 rounded border',
            file.isExisting 
              ? 'border-blue-200 dark:border-blue-800 bg-blue-50/30 dark:bg-blue-900/10' 
              : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'
          ]"
        >
          <!-- Mobile Layout (Ultra Compact) -->
          <div class="sm:hidden">
            <!-- File Header - Single Compact Row -->
            <div class="flex items-center justify-between gap-1.5">
              <!-- Left side: Icon and filename -->
              <div class="flex items-center gap-1.5 flex-1 min-w-0">
                <div :class="['p-1 rounded', getFileCategoryColor(file)]">
                  <component 
                    :is="getFileIcon(file)" 
                    class="h-3.5 w-3.5" 
                    :class="file.isExisting ? 'text-blue-600 dark:text-blue-400' : getFileCategoryColor(file).split(' ')[2]"
                  />
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-xs text-gray-900 dark:text-white truncate">
                    {{ truncateFileName(file.name, 24) }}
                  </p>
                  <div class="flex items-center gap-1 mt-0.5">
                    <span class="text-[10px] text-gray-500 dark:text-gray-400">
                      {{ formatFileSizeCompact(file.size) }}
                    </span>
                    <span class="text-[10px] px-1 py-0.5 rounded-full" :class="getFileCategoryColor(file)">
                      {{ formatFileTypeShort(file) }}
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Right side: Action buttons -->
              <div class="flex items-center gap-0.5">
                <!-- Download button for existing files -->
                <button
                  v-if="file.isExisting"
                  type="button"
                  @click="downloadFile(index)"
                  class="p-1 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded transition-colors"
                  title="Download"
                >
                  <Download class="h-3 w-3 text-blue-600 dark:text-blue-400" />
                </button>
                
                <!-- Remove button -->
                <button
                  type="button"
                  @click="file.isExisting ? removeExistingFile(index) : removeFile(index - existingFiles.length)"
                  class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                  :title="file.isExisting ? 'Remove' : 'Remove'"
                >
                  <X class="h-3 w-3 text-gray-500 dark:text-gray-400" />
                </button>
              </div>
            </div>
            
            <!-- Document Type Selector (Compact) -->
            <div v-if="props.documentTypesOptions && props.documentTypesOptions.length > 0" class="mt-1.5">
              <BaseSelect
                :model-value="documentTypes[index] || 'other'"
                @update:model-value="(val) => handleDocumentTypeChange(index, val)"
                :options="props.documentTypesOptions"
                placeholder="Select document type"
                class="w-full max-w-xs text-sm"
              />
            </div>
          </div>

          <!-- Desktop Layout -->
          <div class="hidden sm:flex sm:items-start gap-2">
            <!-- File Icon -->
            <div class="shrink-0">
              <div :class="['p-2 rounded', getFileCategoryColor(file)]">
                <component 
                  :is="getFileIcon(file)" 
                  class="h-4 w-4" 
                  :class="file.isExisting ? 'text-blue-600 dark:text-blue-400' : getFileCategoryColor(file).split(' ')[2]"
                />
              </div>
            </div>
            
            <!-- File Info -->
            <div class="flex-1 min-w-0 space-y-1.5">
              <div class="flex items-start justify-between gap-2">
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ file.name }}
                    <span v-if="file.isExisting" class="text-xs text-blue-600 dark:text-blue-400 ml-1">
                      (Existing)
                    </span>
                  </p>
                  <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                      {{ formatFileSize(file.size) }}
                    </span>
                    <span class="text-xs px-2 py-0.5 rounded-full" :class="getFileCategoryColor(file)">
                      {{ formatFileType(file) }}
                    </span>
                  </div>
                </div>
                <div class="flex items-center gap-0.5 shrink-0">
                  <!-- Download button for existing files -->
                  <button
                    v-if="file.isExisting"
                    type="button"
                    @click="downloadFile(index)"
                    class="p-1 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded transition-colors"
                    title="Download"
                  >
                    <Download class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                  </button>
                  
                  <!-- Remove button -->
                  <button
                    type="button"
                    @click="file.isExisting ? removeExistingFile(index) : removeFile(index - existingFiles.length)"
                    class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                    :title="file.isExisting ? 'Remove' : 'Remove'"
                  >
                    <X class="h-3.5 w-3.5 text-gray-500 dark:text-gray-400" />
                  </button>
                </div>
              </div>
              
              <!-- Document Type Selector -->
              <div v-if="props.documentTypesOptions && props.documentTypesOptions.length > 0" class="pt-0.5">
                <BaseSelect
                  :model-value="documentTypes[index] || 'other'"
                  @update:model-value="(val) => handleDocumentTypeChange(index, val)"
                  :options="props.documentTypesOptions"
                  placeholder="Select document type"
                  class="w-full max-w-xs text-sm"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Messages -->
      <div class="space-y-1 mt-2">
        <!-- Error Message -->
        <div
          v-if="fileError || error"
          class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400"
        >
          <AlertCircle class="h-3 w-3" />
          <span>{{ fileError || error }}</span>
        </div>

        <!-- Success Message -->
        <div
          v-if="!fileError && !error && hasFiles"
          class="flex items-center gap-1 text-xs text-green-600 dark:text-green-400"
        >
          <Check class="h-3 w-3" />
          <span>
            {{ fileCount }} ready
            <span v-if="multiple && canAddMore"> • +{{ maxFiles - (modelValue?.length || 0) }}</span>
          </span>
        </div>
      </div>
    </div>

    <!-- File Requirements (Always visible but compact) -->
    <div class="bg-gray-50 dark:bg-gray-800/30 rounded p-2">
      <div class="space-y-1.5">
        <div class="flex items-start gap-1.5">
          <Check class="h-2.5 w-2.5 text-green-500 dark:text-green-400 mt-0.5 shrink-0" />
          <span class="text-[11px] text-gray-600 dark:text-gray-400">
            Max: {{ maxSize }}MB per file
          </span>
        </div>
        <div v-if="multiple" class="flex items-start gap-1.5">
          <Check class="h-2.5 w-2.5 text-green-500 dark:text-green-400 mt-0.5 shrink-0" />
          <span class="text-[11px] text-gray-600 dark:text-gray-400">
            Up to {{ maxFiles }} files
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pr-2::-webkit-scrollbar {
  width: 6px;
}

.pr-2::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

.pr-2::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

.pr-2::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.dark .pr-2::-webkit-scrollbar-track {
  background: #374151;
}

.dark .pr-2::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark .pr-2::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>