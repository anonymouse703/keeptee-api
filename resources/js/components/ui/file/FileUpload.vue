<!-- components/ui/file/FileUpload.vue -->
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Upload, File, X, Check, AlertCircle, FileText, FileImage, FileArchive, Download } from 'lucide-vue-next'

interface Props {
  modelValue?: File[] | null
  accept?: string
  label?: string
  description?: string
  maxSize?: number // in MB
  multiple?: boolean
  required?: boolean
  error?: string
  previewUrls?: (string | null)[] // For existing files
  maxFiles?: number // Maximum number of files allowed
}

const props = withDefaults(defineProps<Props>(), {
  accept: '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.txt',
  label: 'Upload Files',
  description: 'Upload files',
  maxSize: 10,
  multiple: false,
  required: false,
  previewUrls: () => [],
  maxFiles: 10
})

const emit = defineEmits<{
  'update:modelValue': [files: File[]]
  'remove': [index: number]
  'remove-all': []
  'download': [index: number]
}>()

const fileInput = ref<HTMLInputElement | null>(null)
const dragOver = ref(false)
const fileError = ref<string | null>(null)
const filePreviews = ref<Map<number, string>>(new Map()) // Store previews for image files only
const existingFiles = ref<Array<{ name: string; size?: number; type: string; url?: string }>>([]) // Track existing files separately

/**
 * Computed properties
 */
const fileCount = computed(() => {
  const newFiles = props.modelValue?.length || 0
  const existingCount = existingFiles.value.length
  return newFiles + existingCount
})

const hasFiles = computed(() => fileCount.value > 0)
const canAddMore = computed(() => {
  if (!props.multiple) return false
  return (props.modelValue?.length || 0) < props.maxFiles
})

/**
 * Get appropriate file icon based on file type
 */
const getFileIcon = (file: File | { type?: string; name?: string }) => {
  const type = file.type?.toLowerCase() || ''
  const name = file.name?.toLowerCase() || ''
  
  if (type.includes('pdf') || name.endsWith('.pdf')) return FileText
  if (type.includes('image') || name.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/)) return FileImage
  if (type.includes('zip') || type.includes('rar') || type.includes('tar') || 
      name.match(/\.(zip|rar|tar|gz|7z)$/)) return FileArchive
  if (type.includes('word') || name.match(/\.(doc|docx)$/)) return FileText
  if (type.includes('excel') || name.match(/\.(xls|xlsx)$/)) return FileText
  if (type.includes('text') || name.endsWith('.txt')) return FileText
  
  return File
}

/**
 * Get file category color
 */
const getFileCategoryColor = (file: File | { type?: string; name?: string }): string => {
  const type = file.type?.toLowerCase() || ''
  const name = file.name?.toLowerCase() || ''
  
  if (type.includes('image') || name.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/)) {
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
  }
  if (type.includes('pdf') || name.endsWith('.pdf')) {
    return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'
  }
  if (type.includes('word') || name.match(/\.(doc|docx)$/)) {
    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'
  }
  if (type.includes('excel') || name.match(/\.(xls|xlsx)$/)) {
    return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
  }
  if (type.includes('zip') || type.includes('rar') || type.includes('tar') || 
      name.match(/\.(zip|rar|tar|gz|7z)$/)) {
    return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
  }
  
  return 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-300'
}

/**
 * Validation
 */
const validateFile = (file: File): boolean => {
  // Check file size
  if (file.size > props.maxSize * 1024 * 1024) {
    fileError.value = `"${file.name}" exceeds ${props.maxSize}MB limit`
    return false
  }

  // Check max files if multiple is enabled
  if (props.multiple && props.modelValue && props.modelValue.length >= props.maxFiles) {
    fileError.value = `Maximum ${props.maxFiles} files allowed`
    return false
  }

  // Check file type if accept is specified
  if (props.accept && props.accept !== '*') {
    const acceptedTypes = props.accept.split(',').map(t => t.trim().toLowerCase())
    const fileName = file.name.toLowerCase()
    const fileType = file.type.toLowerCase()
    
    // Check by extension or mime type
    const isValid = acceptedTypes.some(accepted => {
      // Check by file extension
      if (accepted.startsWith('.') && fileName.endsWith(accepted)) {
        return true
      }
      
      // Check by mime type (for generic types like "image/*")
      if (accepted.includes('*')) {
        const mimePattern = accepted.replace('*', '')
        return fileType.includes(mimePattern)
      }
      
      // Check by exact mime type
      return fileType === accepted
    })
    
    if (!isValid) {
      fileError.value = `"${file.name}" type not allowed. Accepted: ${props.accept}`
      return false
    }
  }

  return true
}

/**
 * Generate preview URL for image files only
 */
const generateImagePreview = (file: File): Promise<string> => {
  return new Promise((resolve) => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader()
      reader.onload = (e) => {
        resolve(e.target?.result as string)
      }
      reader.onerror = () => {
        resolve('')
      }
      reader.readAsDataURL(file)
    } else {
      resolve('')
    }
  })
}

/**
 * Input change - handles both single and multiple files
 */
const handleFileChange = async (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files?.length) return

  fileError.value = null

  // Get valid files
  const newFiles = Array.from(input.files).filter(validateFile)
  
  if (newFiles.length === 0) {
    input.value = ''
    return
  }

  // If not multiple, replace existing files
  let filesToAdd = newFiles
  if (!props.multiple) {
    filesToAdd = [newFiles[0]] // Take only the first file
    filePreviews.value.clear() // Clear all previews
  }

  // Generate previews for image files only
  const existingFileCount = existingFiles.value.length
  for (let i = 0; i < filesToAdd.length; i++) {
    if (filesToAdd[i].type.startsWith('image/')) {
      const preview = await generateImagePreview(filesToAdd[i])
      if (preview) {
        const currentIndex = existingFileCount + (props.modelValue?.length || 0) + i
        filePreviews.value.set(currentIndex, preview)
      }
    }
  }

  // Combine with existing files
  const currentFiles = props.modelValue || []
  const allFiles = props.multiple ? [...currentFiles, ...filesToAdd] : filesToAdd

  emit('update:modelValue', allFiles)
  input.value = ''
}

/**
 * Drag & drop
 */
const handleDrop = async (event: DragEvent) => {
  event.preventDefault()
  dragOver.value = false

  if (!event.dataTransfer?.files.length) return

  fileError.value = null

  // Get valid files
  const newFiles = Array.from(event.dataTransfer.files).filter(validateFile)
  
  if (newFiles.length === 0) return

  // If not multiple, replace existing files
  let filesToAdd = newFiles
  if (!props.multiple) {
    filesToAdd = [newFiles[0]] // Take only the first file
    filePreviews.value.clear() // Clear all previews
  }

  // Generate previews for image files only
  const existingFileCount = existingFiles.value.length
  for (let i = 0; i < filesToAdd.length; i++) {
    if (filesToAdd[i].type.startsWith('image/')) {
      const preview = await generateImagePreview(filesToAdd[i])
      if (preview) {
        const currentIndex = existingFileCount + (props.modelValue?.length || 0) + i
        filePreviews.value.set(currentIndex, preview)
      }
    }
  }

  // Combine with existing files
  const currentFiles = props.modelValue || []
  const allFiles = props.multiple ? [...currentFiles, ...filesToAdd] : filesToAdd

  emit('update:modelValue', allFiles)
}

const handleDragOver = (event: DragEvent) => {
  event.preventDefault()
  dragOver.value = true
}

const handleDragLeave = () => {
  dragOver.value = false
}

/**
 * Remove single file
 */
const removeFile = (index: number) => {
  const files = [...(props.modelValue || [])]
  files.splice(index, 1)
  
  // Remove preview if exists
  const actualIndex = existingFiles.value.length + index
  filePreviews.value.delete(actualIndex)
  
  // Update other preview indices
  const newPreviews = new Map<number, string>()
  filePreviews.value.forEach((value, key) => {
    if (key > actualIndex) {
      newPreviews.set(key - 1, value)
    } else if (key < actualIndex) {
      newPreviews.set(key, value)
    }
  })
  filePreviews.value = newPreviews
  
  emit('update:modelValue', files)
  emit('remove', index)
}

/**
 * Remove all files
 */
const removeAllFiles = () => {
  emit('update:modelValue', [])
  emit('remove-all')
  filePreviews.value.clear()
  existingFiles.value = []
  fileError.value = null
  if (fileInput.value) fileInput.value.value = ''
}

/**
 * Download existing file
 */
const downloadFile = (index: number) => {
  emit('download', index)
}

/**
 * Format file size
 */
const formatFileSize = (bytes?: number): string => {
  if (!bytes || bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

/**
 * Format file type
 */
const formatFileType = (file: File | { type?: string; name?: string }): string => {
  if (file.type) {
    const type = file.type.split('/')[1]?.toUpperCase() || file.type.toUpperCase()
    return type
  }
  
  // Fallback to file extension
  const ext = file.name?.split('.').pop()?.toUpperCase() || 'FILE'
  return ext
}

/**
 * Get all files including existing ones for display
 */
const allFiles = computed(() => {
  const newFiles = props.modelValue || []
  const existing = existingFiles.value.map(f => ({ ...f, isExisting: true }))
  const fresh = newFiles.map(f => ({ 
    name: f.name, 
    size: f.size, 
    type: f.type,
    isExisting: false 
  }))
  return [...existing, ...fresh]
})

/**
 * Watch for external preview URL changes (for existing files)
 */
watch(
  () => props.previewUrls,
  (newUrls) => {
    if (newUrls?.length) {
      // Convert preview URLs to existing files
      existingFiles.value = newUrls.map((url, index) => {
        // Extract filename from URL
        const filename = url ? url.split('/').pop() || `File ${index + 1}` : 'Unknown file'
        
        // Try to determine type from extension
        let type = 'application/octet-stream'
        if (url) {
          const ext = url.split('.').pop()?.toLowerCase()
          if (ext === 'pdf') type = 'application/pdf'
          else if (['jpg', 'jpeg'].includes(ext || '')) type = 'image/jpeg'
          else if (ext === 'png') type = 'image/png'
          else if (ext === 'gif') type = 'image/gif'
          else if (ext === 'webp') type = 'image/webp'
          else if (ext === 'bmp') type = 'image/bmp'
          else if (['doc', 'docx'].includes(ext || '')) type = 'application/msword'
          else if (['xls', 'xlsx'].includes(ext || '')) type = 'application/vnd.ms-excel'
          else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext || '')) type = 'application/zip'
          else if (ext === 'txt') type = 'text/plain'
        }
        
        return {
          name: filename,
          url: url || undefined,
          type: type
        }
      })
      
      // Store image previews
      newUrls.forEach((url, index) => {
        if (url && url.match(/\.(jpg|jpeg|png|gif|bmp|webp)$/i)) {
          filePreviews.value.set(index, url)
        }
      })
    } else {
      existingFiles.value = []
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
          'border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all duration-200 mb-4',
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
        <div v-if="!hasFiles" class="space-y-3">
          <div class="flex justify-center">
            <div class="p-3 rounded-full bg-primary-100 dark:bg-primary-900/30">
              <Upload class="h-6 w-6 text-primary-600 dark:text-primary-400" />
            </div>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">
              {{ multiple ? 'Click to upload or drag and drop' : 'Click to upload a file' }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ description }} • Max {{ maxSize }}MB per file
              <span v-if="multiple"> • Up to {{ maxFiles }} files</span>
            </p>
          </div>
        </div>

        <!-- UPLOADED STATE -->
        <div v-else class="text-left">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30">
                <Check class="h-4 w-4 text-green-600 dark:text-green-400" />
              </div>
              <span class="text-sm font-medium text-gray-900 dark:text-white">
                {{ fileCount }} file{{ fileCount !== 1 ? 's' : '' }} selected
              </span>
            </div>
            <button
              type="button"
              @click.stop="removeAllFiles"
              class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
            >
              Clear all
            </button>
          </div>
          <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            {{ multiple && canAddMore ? 'Click to add more files or drag and drop' : 'Upload completed' }}
          </p>
        </div>
      </div>

      <!-- File List -->
      <div v-if="hasFiles" class="space-y-3 max-h-96 overflow-y-auto pr-2">
        <div
          v-for="(file, index) in allFiles"
          :key="index"
          :class="[
            'flex items-start gap-3 p-3 rounded-lg border',
            file.isExisting 
              ? 'border-blue-200 dark:border-blue-800 bg-blue-50/50 dark:bg-blue-900/20' 
              : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'
          ]"
        >
          <!-- File Icon -->
          <div class="shrink-0">
            <div :class="['p-2 rounded-lg', getFileCategoryColor(file)]">
              <component 
                :is="getFileIcon(file)" 
                class="h-5 w-5" 
                :class="file.isExisting ? 'text-blue-600 dark:text-blue-400' : getFileCategoryColor(file).split(' ')[2]"
              />
            </div>
          </div>
          
          <!-- File Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ file.name }}
                  <span v-if="file.isExisting" class="text-xs text-blue-600 dark:text-blue-400 ml-2">
                    (Existing)
                  </span>
                </p>
                <div class="flex items-center gap-3 mt-1">
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatFileSize(file.size) }}
                  </span>
                  <span class="text-xs px-2 py-0.5 rounded-full" :class="getFileCategoryColor(file)">
                    {{ formatFileType(file) }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-1">
                <!-- Download button for existing files -->
                <button
                  v-if="file.isExisting "
                  type="button"
                  @click="downloadFile(index)"
                  class="p-1 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded transition-colors"
                  title="Download file"
                >
                  <Download class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                </button>
                
                <!-- Remove button for non-existing files -->
                <button
                  v-if="!file.isExisting"
                  type="button"
                  @click="removeFile(index - existingFiles.length)"
                  class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded transition-colors"
                  title="Remove file"
                >
                  <X class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                </button>
              </div>
            </div>
            
            <!-- Image Preview (only for image files) -->
            <div 
              v-if="filePreviews.get(index)" 
              class="mt-3"
            >
              <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Preview:</p>
              <div class="relative h-32 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                <img
                  :src="filePreviews.get(index)"
                  :alt="file.name"
                  class="w-full h-full object-contain bg-gray-50 dark:bg-gray-900"
                />
              </div>
            </div>
            
            <!-- No Preview Message for non-image files -->
            <div 
              v-else-if="!file.type?.startsWith('image/')" 
              class="mt-2"
            >
              <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                File preview not available for {{ formatFileType(file) }} files
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Message -->
      <div
        v-if="fileError || error"
        class="flex items-center gap-2 mt-2 text-sm text-red-600 dark:text-red-400"
      >
        <AlertCircle class="h-4 w-4" />
        <span>{{ fileError || error }}</span>
      </div>

      <!-- Success Message -->
      <div
        v-if="!fileError && !error && hasFiles"
        class="flex items-center gap-2 mt-2 text-sm text-green-600 dark:text-green-400"
      >
        <Check class="h-4 w-4" />
        <span>
          {{ fileCount }} file{{ fileCount !== 1 ? 's' : '' }} ready for upload
          <span v-if="multiple && canAddMore"> • You can add {{ maxFiles - (modelValue?.length || 0) }} more</span>
        </span>
      </div>
    </div>

    <!-- File Requirements -->
    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4">
      <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
        File Requirements
      </h4>
      <div class="space-y-3">
        <div class="flex items-start gap-2">
          <Check class="h-3 w-3 text-green-500 dark:text-green-400 mt-0.5 shrink-0" />
          <span class="text-xs text-gray-600 dark:text-gray-400">
            Maximum file size: <strong>{{ maxSize }}MB</strong> per file
          </span>
        </div>
        <div class="flex items-start gap-2">
          <Check class="h-3 w-3 text-green-500 dark:text-green-400 mt-0.5 shrink-0" />
          <span class="text-xs text-gray-600 dark:text-gray-400">
            Accepted formats: <strong>{{ accept }}</strong>
          </span>
        </div>
        <div v-if="multiple" class="flex items-start gap-2">
          <Check class="h-3 w-3 text-green-500 dark:text-green-400 mt-0.5 shrink-0" />
          <span class="text-xs text-gray-600 dark:text-gray-400">
            Multiple files: <strong>Up to {{ maxFiles }} files</strong>
          </span>
        </div>
        <div v-else class="flex items-start gap-2">
          <Check class="h-3 w-3 text-green-500 dark:text-green-400 mt-0.5 shrink-0" />
          <span class="text-xs text-gray-600 dark:text-gray-400">
            Single file only
          </span>
        </div>
        <div class="flex items-start gap-2">
          <Check class="h-3 w-3 text-green-500 dark:text-green-400 mt-0.5 shrink-0" />
          <span class="text-xs text-gray-600 dark:text-gray-400">
            Image files will display previews
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar styling */
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

/* Dark mode scrollbar */
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