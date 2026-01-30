<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3'
import { Building2, ArrowLeft, Image as ImageIcon } from 'lucide-vue-next'
import { ref } from 'vue'

import ImageUpload from '@/components/ui/image-upload/ImageUpload.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

import Form from './partials/Form.vue'

const props = defineProps<{
  property_types?: any
  statuses?: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Properties', href: '/properties' },
  { title: 'Create Property', href: '/properties/create' }
]

const isLoading = ref(false)
const images = ref<File[]>([])
const primaryImageIndex = ref(0)
const formErrors = ref<Record<string, string>>({})

const handlePrimaryImageChange = (index: number) => {
  primaryImageIndex.value = index
}

const handleImageRemove = (index: number) => {
  images.value.splice(index, 1)
}

const handleSubmit = (formData: Record<string, any>) => {
  isLoading.value = true
  formErrors.value = {}

  const payload = new FormData()

  Object.entries(formData).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') {
      payload.append(key, value as any)
    }
  })

  images.value.forEach((file, index) => {
    payload.append(`images[${index}]`, file)
  })

  payload.append('primary_image_index', String(primaryImageIndex.value))

  router.post('/properties', payload, {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      isLoading.value = false
      formErrors.value = {}
    },
    onError: errors => {
      isLoading.value = false
      formErrors.value = errors
    }
  })
}

const handleCancel = () => {
  router.visit('/properties')
}
</script>

<template>
  <Head title="Create Property" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Header -->
      <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
          <button
            @click="handleCancel"
            class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300"
          >
            <ArrowLeft class="h-4 w-4" />
            Back to Properties
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
              Create New Property
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Add a new property listing with details and images
            </p>
          </div>

          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
            <Building2 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Images -->
        <div class="lg:col-span-1 space-y-6">
          <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3 pb-4 mb-4 border-b">
              <ImageIcon class="h-5 w-5 text-blue-600" />
              <h3 class="text-lg font-semibold">Property Images</h3>
            </div>

            <ImageUpload
              v-model="images"
              :max-files="15"
              :max-size-mb="10"
              @update:primary="handlePrimaryImageChange"
              @remove="handleImageRemove"
            />
          </div>
        </div>

        <!-- Form -->
        <div class="lg:col-span-2">
          <div class="rounded-lg border bg-white p-6 shadow-sm dark:bg-gray-900">
            <Form
              :property_types="props.property_types"
              :statuses="props.statuses"
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
