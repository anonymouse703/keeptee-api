<script setup lang="ts">
import { ref, computed, watch, onUnmounted } from 'vue'
import { Upload, Trash2, AlertCircle, ImageIcon, Tag, Star } from 'lucide-vue-next'
import BaseButton from '@/components/ui/button/BaseButton.vue'
import BaseSelect from '@/components/ui/input/Select.vue'

interface ExistingImage {
  id: string
  url: string
  is_primary?: boolean
  image_type?: string
  sort_order?: number
  file_id?: string
}

interface ImageFile {
  id: string
  file: File
  preview: string
  name: string
  size: string
  isPrimary?: boolean
  imageType?: string
}

interface Props {
  maxFiles?: number
  maxSizeMB?: number
  accept?: string
  label?: string
  errors?: string[]
  modelValue?: File[]
  existingImages?: ExistingImage[]
  imageTypes?: Array<{value: string, label: string}> 
}

const props = withDefaults(defineProps<Props>(), {
  maxFiles: 15,
  maxSizeMB: 10,
  accept: 'image/*',
  label: 'Upload Images',
  errors: () => [],
  modelValue: () => [],
  existingImages: () => [],
  imageTypes: () => [] 
})

interface ImageTypeUpdate {
  id: string
  type: string
}

const emit = defineEmits<{
  'update:modelValue': [files: File[]]
  'update:primary': [primaryIndex: number]
  'update:image-types': [types: ImageTypeUpdate[]]
  'remove': [index: number]
  'remove-existing': [url: string]
}>()

const images = ref<ImageFile[]>([])
const isDragging = ref(false)
const inputRef = ref<HTMLInputElement | null>(null)

// Get default image type (first one from props or 'other')
const getDefaultImageType = (): string => {
  if (props.imageTypes && props.imageTypes.length > 0) {
    return props.imageTypes[0].value
  }
  return 'other'
}

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
    
    // Check maximum files (including existing images)
    const totalImages = props.existingImages.length + images.value.length + validFiles.length
    if (totalImages >= props.maxFiles) {
      emitError(`Maximum ${props.maxFiles} images allowed`)
      return
    }
    
    const imageFile: ImageFile = {
      id: Math.random().toString(36).substr(2, 9),
      file,
      preview: URL.createObjectURL(file),
      name: file.name,
      size: formatFileSize(file.size),
      isPrimary: props.existingImages.length === 0 && images.value.length === 0 && validFiles.length === 0,
      imageType: getDefaultImageType()
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

// Remove new image
const removeImage = (index: number) => {
  URL.revokeObjectURL(images.value[index].preview)
  
  const wasPrimary = images.value[index].isPrimary
  images.value.splice(index, 1)
  
  // If we removed a primary image and there are no existing primary images, set first image as primary
  if (wasPrimary && !props.existingImages.some(img => img.is_primary) && images.value.length > 0) {
    images.value[0].isPrimary = true
    emit('update:primary', 0)
  }
  
  emitFiles(images.value)
  emit('remove', index + props.existingImages.length)
}

// Remove existing image
const removeExistingImage = (index: number) => {
  const image = props.existingImages[index]
  if (image) {
    emit('remove-existing', image.url || image.id)
  }
}

// Remove all new images
const removeAllImages = () => {
  images.value.forEach(img => {
    URL.revokeObjectURL(img.preview)
  })
  images.value = []
  emitFiles([])
}

// Set primary image
const setPrimaryImage = (index: number) => {
  // For new images
  if (index >= props.existingImages.length) {
    const newImageIndex = index - props.existingImages.length
    images.value.forEach((img, i) => {
      img.isPrimary = i === newImageIndex
    })
  }
  
  emit('update:primary', index)
}

// Update image type for new images
const updateImageType = (index: number, type: string | number | boolean | null) => {
  const newImageIndex = index - props.existingImages.length
  if (newImageIndex >= 0 && newImageIndex < images.value.length) {
    const typeString = type !== null && type !== undefined 
      ? String(type) 
      : getDefaultImageType()
    
    images.value[newImageIndex].imageType = typeString
    emitImageTypes()
  }
}

// Emit files to parent
const emitFiles = (files: ImageFile[]) => {
  const fileArray = files.map(img => img.file)
  emit('update:modelValue', fileArray)
  emitImageTypes()
}

// Emit image types to parent
const emitImageTypes = () => {
  emit(
    'update:image-types',
    images.value.map(img => ({
      id: img.id,
      type: img.imageType || getDefaultImageType()
    }))
  )
}

// Emit error
const emitError = (message: string) => {
  console.error(message)
}

// Cleanup on unmount
onUnmounted(() => {
  images.value.forEach(img => {
    URL.revokeObjectURL(img.preview)
  })
})

// Watch for value changes from parent
watch(() => props.modelValue, (newFiles) => {
  // Handle initial value if needed
  // This allows parent to reset the component
  if (newFiles && newFiles.length === 0 && images.value.length > 0) {
    removeAllImages()
  }
}, { immediate: true })

// Open file dialog
const openFileDialog = () => {
  inputRef.value?.click()
}

// Computed properties
const totalImages = computed(() => props.existingImages.length + images.value.length)
const remainingSlots = computed(() => props.maxFiles - totalImages.value)
const uploadLabel = computed(() => {
  if (totalImages.value === 0) {
    return 'Upload Images'
  }
  return `Add more images (${remainingSlots.value} remaining)`
})

// Image type summary
const imageTypeSummary = computed(() => {
  const summary: Record<string, { name: string; count: number }> = {}
  
  // Count existing images types
  props.existingImages.forEach(img => {
    const type = img.image_type || getDefaultImageType()
    const typeName = props.imageTypes?.find(t => t.value === type)?.label || type
    
    if (!summary[type]) {
      summary[type] = { name: typeName, count: 0 }
    }
    summary[type].count++
  })
  
  // Count new images types
  images.value.forEach(img => {
    const type = img.imageType || getDefaultImageType()
    const typeName = props.imageTypes?.find(t => t.value === type)?.label || type
    
    if (!summary[type]) {
      summary[type] = { name: typeName, count: 0 }
    }
    summary[type].count++
  })
  
  return Object.values(summary)
})

// Get human readable type name
const getTypeName = (type: string | undefined): string => {
  if (!type) return 'No type'
  const found = props.imageTypes?.find(t => t.value === type)
  return found?.label || type
}
</script>

<template>
  <div class="space-y-4">
    <!-- Upload Area (only show if slots available) -->
    <div v-if="remainingSlots > 0">
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
    </div>

    <!-- Max files reached message -->
    <div v-else class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/20">
      <div class="flex items-start gap-2">
        <AlertCircle class="h-5 w-5 text-yellow-600 dark:text-yellow-400 mt-0.5 shrink-0" />
        <div>
          <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
            Maximum images reached
          </p>
          <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-400">
            You've reached the maximum of {{ maxFiles }} images. Remove some images to add new ones.
          </p>
        </div>
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

    <!-- All Images Grid -->
    <div v-if="totalImages > 0" class="space-y-4">
      <div class="flex items-center justify-between">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
          All Images ({{ totalImages }})
        </h4>
        <span class="text-xs text-gray-500 dark:text-gray-400">
          Click star to set as primary
        </span>
      </div>
      
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <!-- Existing Images -->
        <div
          v-for="(image, index) in existingImages"
          :key="image.id || image.url"
          class="group overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
          <!-- Image Container -->
          <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img
              :src="image.url"
              :alt="`Existing image ${index + 1}`"
              class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            
            <!-- Overlay Actions -->
            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
              <!-- Primary button -->
              <button
                type="button"
                @click.stop="setPrimaryImage(index)"
                class="rounded-full bg-white/90 p-2 text-yellow-600 hover:bg-white"
                :title="image.is_primary ? 'Primary Image' : 'Set as Primary'"
              >
                <Star class="h-4 w-4" :class="{ 'fill-yellow-600': image.is_primary }" />
              </button>
              <!-- Remove button -->
              <button
                type="button"
                @click.stop="removeExistingImage(index)"
                class="rounded-full bg-white/90 p-2 text-red-600 hover:bg-white"
                title="Remove Image"
              >
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
            
            <!-- Primary Badge -->
            <div v-if="image.is_primary" class="absolute top-2 left-2">
              <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                <Star class="h-3 w-3" />
                Primary
              </span>
            </div>
            
            <!-- Image Type Badge -->
            <div v-if="image.image_type" class="absolute top-2 right-2">
              <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                <Tag class="h-3 w-3" />
                {{ getTypeName(image.image_type) }}
              </span>
            </div>
          </div>
          
          <!-- Image Info -->
          <div class="p-3 space-y-2">
            <p class="truncate text-xs font-medium text-gray-900 dark:text-white">
              Existing Image {{ index + 1 }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ image.image_type ? getTypeName(image.image_type) : 'No type set' }}
            </p>
          </div>
        </div>

        <!-- New Images -->
        <div
          v-for="(image, newIndex) in images"
          :key="image.id"
          class="group overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900"
        >
          <!-- Image Container -->
          <div class="relative aspect-square overflow-hidden bg-gray-100 dark:bg-gray-800">
            <img
              :src="image.preview"
              :alt="image.name"
              class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            
            <!-- Overlay Actions -->
            <div class="absolute inset-0 flex items-center justify-center gap-2 bg-black/60 opacity-0 transition-opacity group-hover:opacity-100">
              <!-- Primary button -->
              <button
                type="button"
                @click.stop="setPrimaryImage(existingImages.length + newIndex)"
                class="rounded-full bg-white/90 p-2 text-yellow-600 hover:bg-white"
                :title="image.isPrimary ? 'Primary Image' : 'Set as Primary'"
              >
                <Star class="h-4 w-4" :class="{ 'fill-yellow-600': image.isPrimary }" />
              </button>
              <!-- Remove button -->
              <button
                type="button"
                @click.stop="removeImage(newIndex)"
                class="rounded-full bg-white/90 p-2 text-red-600 hover:bg-white"
                title="Remove Image"
              >
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
            
            <!-- Primary Badge -->
            <div v-if="image.isPrimary" class="absolute top-2 left-2">
              <span class="inline-flex items-center gap-1 rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                <Star class="h-3 w-3" />
                Primary
              </span>
            </div>
          </div>
          
          <!-- Image Info -->
          <div class="p-3 space-y-2">
            <!-- Filename -->
            <p class="truncate text-xs font-medium text-gray-900 dark:text-white">
              {{ image.name }}
            </p>
            
            <!-- File size -->
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ image.size }}
            </p>
            
            <!-- Image Type Selector using BaseSelect -->
            <div class="pt-2">
              <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                <Tag class="inline-block h-3 w-3 mr-1" />
                Image Type
              </label>
              <BaseSelect
                :model-value="image.imageType || getDefaultImageType()"
                @update:model-value="(value) => updateImageType(existingImages.length + newIndex, value)"
                :options="imageTypes || []"
                placeholder="Select image type"
                size="sm"
                class="w-full"
              />
            </div>
          </div>
        </div>
      </div>
      
      <!-- Image Summary -->
      <div class="rounded-lg border border-gray-200 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-900/50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <ImageIcon class="h-4 w-4 text-gray-400" />
            <span class="text-sm text-gray-600 dark:text-gray-400">
              {{ totalImages }} image{{ totalImages !== 1 ? 's' : '' }} total
              <span v-if="existingImages.length > 0" class="text-gray-400">
                ({{ existingImages.length }} existing, {{ images.length }} new)
              </span>
            </span>
          </div>
          <button
            v-if="images.length > 0"
            type="button"
            @click="removeAllImages"
            class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
          >
            Remove All New
          </button>
        </div>
        
        <!-- Image Types Summary -->
        <div v-if="imageTypeSummary.length > 0" class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
          <div class="flex flex-wrap gap-2">
            <span v-for="type in imageTypeSummary" :key="type.name" 
                  class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
              <Tag class="h-3 w-3" />
              {{ type.count }} {{ type.name }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>