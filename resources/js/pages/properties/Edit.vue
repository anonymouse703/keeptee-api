<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { Home, ArrowLeft, Image as ImageIcon } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import ImageUpload from '@/components/ui/image-upload/ImageUpload.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

import { type Property } from '../../../types/type'

import PropertyForm from './partials/Form.vue'

interface PropertyImage {
    id: number
    image_url: string
    is_primary: boolean
    sort_order: number
}

interface Props {
    property: { data: Property & { images?: PropertyImage[] } } // Notice the data wrapper
    property_types?: Array<{ label: string; key: string }>
    statuses?: Array<{ label: string; key: string }>
}

const props = defineProps<Props>()

// Extract the actual property data
const propertyData = computed(() => props.property.data)

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Properties', href: '/properties' },
    { title: 'Edit Property', href: `/properties/${propertyData.value.id}/edit` }
]

const isLoading = ref(false)
const images = ref<File[]>([])
const existingImages = ref<PropertyImage[]>(propertyData.value.images || [])
const primaryImageIndex = ref(0)

// NEW: reactive object to hold validation errors
const formErrors = ref<Record<string, string[] | string>>({})

const handlePrimaryImageChange = (index: number) => {
    primaryImageIndex.value = index
}

const handleImageRemove = (index: number) => {
    images.value.splice(index, 1)
}

const handleExistingImageRemove = (imageId: number) => {
    // Optimistic UI update
    existingImages.value = existingImages.value.filter(img => img.id !== imageId)
    
    // Send delete request to backend
    router.delete(`/property-images/${imageId}`, {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Error deleting image:', errors)
        }
    })
}

const handleSubmit = async (formData: Property) => {
    isLoading.value = true
    formErrors.value = {} // reset errors

    const formDataToSend = new FormData()

    // Append _method for Laravel PUT request
    formDataToSend.append('_method', 'PUT')

    // Append all form fields
    Object.keys(formData).forEach(key => {
        const value = formData[key as keyof Property]
        if (value !== null && value !== undefined && value !== '') {
            formDataToSend.append(key, String(value))
        }
    })

    // Append new images
    images.value.forEach((file, index) => {
        formDataToSend.append(`images[${index}]`, file)
    })

    // Append primary image index (if new images uploaded)
    if (images.value.length > 0) {
        formDataToSend.append('primary_image_index', String(primaryImageIndex.value))
    }

    // Append existing image IDs to keep
    existingImages.value.forEach((img, index) => {
        formDataToSend.append(`existing_images[${index}]`, String(img.id))
    })

    router.post(`/properties/${propertyData.value.id}`, formDataToSend, {
        onSuccess: () => {
            isLoading.value = false
            formErrors.value = {} // clear errors on success
        },
        onError: (errors) => {
            isLoading.value = false
            formErrors.value = errors // pass API errors to Form.vue
            console.error('Error updating property:', errors)
        },
        preserveScroll: true,
        forceFormData: true
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
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Property</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Update property details and images for "{{ propertyData.title }}"
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <Home class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left Column - Image Upload -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-200 dark:border-gray-800">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <ImageIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Property Images</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ existingImages.length + images.length }} of 15 photos
                                </p>
                            </div>
                        </div>

                        <!-- Existing Images -->
                        <div v-if="existingImages.length > 0" class="mb-4">
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Current Images</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div 
                                    v-for="(image, index) in existingImages" 
                                    :key="image.id"
                                    class="relative group"
                                >
                                    <img 
                                        :src="image.image_url" 
                                        :alt="`Property image ${index + 1}`"
                                        class="w-full h-24 object-cover rounded-lg border-2 border-gray-200 dark:border-gray-700"
                                    />
                                    <button
                                        @click="handleExistingImageRemove(image.id)"
                                        type="button"
                                        class="absolute top-1 right-1 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                                        title="Delete image"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                    <div 
                                        v-if="image.is_primary"
                                        class="absolute bottom-1 left-1 bg-blue-500 text-white text-xs px-2 py-0.5 rounded shadow"
                                    >
                                        Primary
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- New Images Upload -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                {{ existingImages.length > 0 ? 'Add New Images' : 'Upload Images' }}
                            </h4>
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

                    <div class="rounded-lg border border-gray-200 bg-blue-50 p-4 dark:border-gray-800 dark:bg-blue-900/20">
                        <div class="flex items-start gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-800/30">
                                <ImageIcon class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-blue-800 dark:text-blue-300">Image Guidelines</h4>
                                <ul class="mt-2 space-y-2 text-sm text-blue-700 dark:text-blue-400">
                                    <li class="flex items-start gap-2"><div class="h-1.5 w-1.5 rounded-full bg-blue-500 mt-1.5"></div> Upload high-quality, well-lit photos</li>
                                    <li class="flex items-start gap-2"><div class="h-1.5 w-1.5 rounded-full bg-blue-500 mt-1.5"></div> Set the best photo as primary (appears first)</li>
                                    <li class="flex items-start gap-2"><div class="h-1.5 w-1.5 rounded-full bg-blue-500 mt-1.5"></div> Include photos of all rooms and exterior</li>
                                    <li class="flex items-start gap-2"><div class="h-1.5 w-1.5 rounded-full bg-blue-500 mt-1.5"></div> Maximum 15 images, 10MB each</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Property Form -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <PropertyForm
                            :property="propertyData"
                            :is-loading="isLoading"
                            :property-types="property_types"
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