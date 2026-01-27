<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Upload, X, Image as ImageIcon, Eye, Trash2, AlertCircle } from 'lucide-vue-next'
import BaseButton from '@/components/ui/button/BaseButton.vue'

interface ImageFile {
  id: string
  file: File
  preview: string
  name: string
  size: string
  isPrimary?: boolean
}

interface Props {
  maxFiles?: number
  maxSizeMB?: number
  accept?: string
  label?: string
  errors?: string[]
  value?: File[]
}

const props = withDefaults(defineProps<Props>(), {
  maxFiles: 10,
  maxSizeMB: 5,
  accept: 'image/*',
  label: 'Upload Images',
  errors: () => [],
  value: () => []
})

const emit = defineEmits<{
  'update:modelValue': [files: File[]]
  'update:primary': [primaryIndex: number]
  'remove': [index: number]
}>()

const images = ref<ImageFile[]>([])
const isDragging = ref(false)
const inputRef = ref<HTMLInputElement | null>(null)

// Format file size
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Handle file selection
const handleFileSelect = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files?.length) return
  
  const files = Array.from(input.files)
  processFiles(files)
  input.value = ''
}

// Process selected files
const processFiles = (files: File[]) => {
  const validFiles: ImageFile[] = []
  
  files.forEach((file) => {
    // Check file type
    if (!file.type.startsWith('image/')) {
      emitError(`"${file.name}" is not an image file`)
      return
    }
    
    // Check file size
    const maxSize = props.maxSizeMB * 1024 * 1024
    if (file.size > maxSize) {
      emitError(`"${file.name}" exceeds ${props.maxSizeMB}MB limit`)
      return
    }
    
    // Check maximum files
    if (images.value.length + validFiles.length >= props.maxFiles) {
      emitError(`Maximum ${props.maxFiles} images allowed`)
      return
    }
    
    const imageFile: ImageFile = {
      id: Math.random().toString(36).substr(2, 9),
      file,
      preview: URL.createObjectURL(file),
      name: file.name,
      size: formatFileSize(file.size),
      isPrimary: images.value.length === 0 && validFiles.length === 0 // First image is primary
    }
    
    validFiles.push(imageFile)
  })
  
  if (validFiles.length > 0) {
    const newImages = [...images.value, ...validFiles]
    images.value = newImages
    emitFiles(newImages)
  }
}

// Handle drag and drop
const handleDragOver = (event: DragEvent) => {
  event.preventDefault()
  isDragging.value = true
}

const handleDragLeave = () => {
  isDragging.value = false
}

const handleDrop = (event: DragEvent) => {
  event.preventDefault()
  isDragging.value = false
  
  const files = Array.from(event.dataTransfer?.files || [])
  processFiles(files)
}

// Remove image
const removeImage = (index: number) => {
  // Revoke object URL to prevent memory leaks
  URL.revokeObjectURL(images.value[index].preview)
  
  const wasPrimary = images.value[index].isPrimary
  images.value.splice(index, 1)
  
  // If primary image was removed, set first image as primary
  if (wasPrimary && images.value.length > 0) {
    images.value[0].isPrimary = true
  }
  
  emitFiles(images.value)
  emit('remove', index)
}

// Set primary image
const setPrimaryImage = (index: number) => {
  images.value.forEach((img, i) => {
    img.isPrimary = i === index
  })
  emit('update:primary', index)
}

// Emit files to parent
const emitFiles = (files: ImageFile[]) => {
  const fileArray = files.map(img => img.file)
  emit('update:modelValue', fileArray)
}

// Emit error
const emitError = (message: string) => {
  // You could emit errors to parent or show inline
  console.error(message)
}

// Cleanup on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
  images.value.forEach(img => {
    URL.revokeObjectURL(img.preview)
  })
})

// Watch for value changes from parent
watch(() => props.value, (newFiles) => {
  // Handle initial value if needed
}, { immediate: true })

// Open file dialog
const openFileDialog = () => {
  inputRef.value?.click()
}

// Computed
const remainingSlots = computed(() => props.maxFiles - images.value.length)
const uploadLabel = computed(() => {
  if (images.value.length === 0) {
    return 'Upload Images'
  }
  return `Add more images (${remainingSlots.value} remaining)`
})
</script>

<template>
  <div class="space-y-4">
    <!-- Label -->
    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }} <span class="text-red-500">*</span>
    </label>
    
    <!-- Upload Area -->
    <div
      @dragover="handleDragOver"
      @dragleave="handleDragLeave"
      @drop="handleDrop"
      @click="openFileDialog"
      :class="[
        'relative border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-all',
        isDragging 
          ? 'border-blue-500 bg-blue-50 dark:border-blue-400 dark:bg-blue-900/20' 
          : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50 dark:border-gray-700 dark:hover:border-blue-500 dark:hover:bg-gray-800'
      ]"
    >
      <input
        ref="inputRef"
        type="file"
        :accept="accept"
        :multiple="maxFiles > 1"
        @change="handleFileSelect"
        class="hidden"
      />
      
      <div class="space-y-3">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
          <Upload class="h-6 w-6 text-blue-600 dark:text-blue-400" />
        </div>
        
        <div>
          <p class="text-sm font-medium text-gray-900 dark:text-white">
            {{ uploadLabel }}
          </p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Drag & drop images or click to browse
          </p>
          <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
            Maximum {{ maxFiles }} images • {{ maxSizeMB }}MB each • PNG, JPG, GIF, WebP
          </p>
        </div>
        
        <BaseButton
          type="button"
          variant="outline"
          size="sm"
          class="border-blue-600 text-blue-600 hover:bg-blue-50 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-900/30"
          @click.stop="openFileDialog"
        >
          <Upload class="mr-2 h-4 w-4" />
          Browse Files
        </BaseButton>
      </div>
    </div>

    <!-- Errors -->
    <div v-if="errors.length > 0" class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
      <div class="flex items-start gap-2">
        <AlertCircle class="h-4 w-4 text-red-600 dark:text-red-400 mt-0.5 shrink-0" />
        <div class="space-y-1">
          <p v-for="(error, index) in errors" :key="index" class="text-xs text-red-700 dark:text-red-400">
            {{ error }}
          </p>
        </div>
      </div>
    </div>

    <!-- Image Grid -->
    <div v-if="images.length > 0" class="space-y-4">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
          Selected Images ({{ images.length }}/{{ maxFiles }})
        </h4>
        <span class="text-xs text-gray-500 dark:text-gray-400">
          Click star to set as primary
        </span>
      </div>
      
      <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
        <div
          v-for="(image, index) in images"
          :key="image.id"
          class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
          <!-- Image -->
          <div class="aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img
              :src="image.preview"
              :alt="image.name"
              class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
          </div>
          
          <!-- Overlay Actions -->
          <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
            <button
              type="button"
              @click.stop="setPrimaryImage(index)"
              class="rounded-full bg-white/90 p-2 text-yellow-600 hover:bg-white"
              :title="image.isPrimary ? 'Primary Image' : 'Set as Primary'"
            >
              <svg class="h-4 w-4" :class="{ 'fill-current': image.isPrimary }" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </button>
            <button
              type="button"
              @click.stop="removeImage(index)"
              class="rounded-full bg-white/90 p-2 text-red-600 hover:bg-white"
              title="Remove Image"
            >
              <Trash2 class="h-4 w-4" />
            </button>
          </div>
          
          <!-- Primary Badge -->
          <div v-if="image.isPrimary" class="absolute top-2 left-2">
            <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
              <svg class="h-3 w-3 fill-current" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
              Primary
            </span>
          </div>
          
          <!-- Image Info -->
          <div class="p-3">
            <p class="truncate text-xs font-medium text-gray-900 dark:text-white">
              {{ image.name }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ image.size }}
            </p>
          </div>
        </div>
      </div>
      
      <!-- Image Summary -->
      <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <ImageIcon class="h-4 w-4 text-gray-400" />
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ images.length }} image{{ images.length !== 1 ? 's' : '' }} selected
            </span>
          </div>
          <button
            type="button"
            @click="images = []"
            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
          >
            Remove All
          </button>
        </div>
      </div>
    </div>
  </div>
</template>