<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3'
import { Home, ArrowLeft, Image as ImageIcon } from 'lucide-vue-next'
import { ref } from 'vue'

import ImageUpload from '@/components/ui/image-upload/ImageUpload.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

import PropertyForm from './partials/Form.vue'

const props = defineProps<{
  property: any
  property_types?: any
  statuses?: any
}>()

/* ✅ Extract once */
const propertyData = props.property.data

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Properties', href: '/properties' },
  { title: 'Edit Property', href: `/properties/${propertyData.id}/edit` }
]

const isLoading = ref(false)

/* Images */
const images = ref<File[]>([])
const existingImages = ref([...propertyData.images])
const primaryImageIndex = ref(0)

/* Validation errors */
const formErrors = ref<Record<string, string>>({})

const handlePrimaryImageChange = (index: number) => {
  primaryImageIndex.value = index
}

const handleImageRemove = (index: number) => {
  images.value.splice(index, 1)
}

/* Existing image delete */
const handleExistingImageRemove = (imageId: number) => {
  existingImages.value = existingImages.value.filter(img => img.id !== imageId)

  router.delete(`/property-images/${imageId}`, {
    preserveScroll: true,
    onError: err => console.error(err)
  })
}

const handleSubmit = (formData: Record<string, any>) => {
  isLoading.value = true
  formErrors.value = {}

  const payload = new FormData()
  payload.append('_method', 'PUT')

  Object.entries(formData).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      payload.append(key, value)
    }
  })

  /* New images */
  images.value.forEach((file, index) => {
    payload.append(`images[${index}]`, file)
  })

  /* Primary image only if new images exist */
  if (images.value.length > 0) {
    payload.append('primary_image_index', String(primaryImageIndex.value))
  }

  /* Keep existing images */
  existingImages.value.forEach((img, index) => {
    payload.append(`existing_images[${index}]`, String(img.id))
  })

  router.put(`/properties/${propertyData.id}`, payload, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      isLoading.value = false
      formErrors.value = {}
    },
    onError: errors => {
      isLoading.value = false
      formErrors.value = errors as Record<string, string>
    }
  })
}

const handleCancel = () => {
  router.visit('/properties')
}
</script>

<template>
  <Head :title="`Edit ${propertyData.title}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6">
        <button
          @click="handleCancel"
          class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900"
        >
          <ArrowLeft class="h-4 w-4" /> Back to Properties
        </button>

        <div class="mt-4 flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold">Edit Property</h1>
            <p class="text-sm text-gray-500">
              Update property details and images for "{{ propertyData.title }}"
            </p>
          </div>
          <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-blue-100">
            <Home class="h-5 w-5 text-blue-600" />
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Images -->
        <div class="lg:col-span-1 space-y-6">
          <div class="rounded-lg border bg-white p-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 mb-4 border-b">
              <ImageIcon class="h-5 w-5 text-blue-600" />
              <h3 class="text-lg font-semibold">Property Images</h3>
            </div>

            <!-- Existing Images -->
            <div v-if="existingImages.length" class="mb-4">
              <h4 class="text-sm font-medium mb-3">Current Images</h4>
              <div class="grid grid-cols-2 gap-3">
                <div
                  v-for="img in existingImages"
                  :key="img.id"
                  class="relative group"
                >
                  <img
                    :src="img.image_url"
                    class="w-full h-24 object-cover rounded-lg border"
                  />

                  <button
                    type="button"
                    @click="handleExistingImageRemove(img.id)"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100"
                  >
                    ✕
                  </button>

                  <span
                    v-if="img.is_primary"
                    class="absolute bottom-1 left-1 text-xs bg-blue-500 text-white px-2 rounded"
                  >
                    Primary
                  </span>
                </div>
              </div>
            </div>

            <!-- Upload new -->
            <ImageUpload
              v-model="images"
              :max-files="15"
              :max-size-mb="10"
              :existing-images-count="existingImages.length"
              @update:primary="handlePrimaryImageChange"
              @remove="handleImageRemove"
            />
          </div>
        </div>

        <!-- Form -->
        <div class="lg:col-span-2">
          <div class="rounded-lg border bg-white p-6 shadow-sm">
            <PropertyForm
              :property="propertyData"
              :property_types="property_types"
              :statuses="statuses"
              :errors="formErrors"
              @submit="handleSubmit"
              @cancel="handleCancel"
            />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
