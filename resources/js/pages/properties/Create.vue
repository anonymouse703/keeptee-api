<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { Home, ArrowLeft, Image as ImageIcon } from 'lucide-vue-next'
import { ref } from 'vue'

import ImageUpload from '@/components/ui/image-upload/ImageUpload.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

import { type Property } from '../../../types/type'

import Form from './partials/Form.vue'

interface Props {
    property_types?: Array<{ label: string; key: string }>
    statuses?: Array<{ label: string; key: string }>
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Properties', href: '/properties' },
    { title: 'Create Property', href: '/properties/create' }
]

const isLoading = ref(false)
const images = ref<File[]>([])
const primaryImageIndex = ref(0)

const formErrors = ref<Record<string, string[] | string>>({})

const handlePrimaryImageChange = (index: number) => {
    primaryImageIndex.value = index
}

const handleImageRemove = (index: number) => {
    images.value.splice(index, 1)
}

const handleSubmit = async (formData: Property) => {
    isLoading.value = true
    formErrors.value = {}

    const formDataToSend = new FormData()

    Object.keys(formData).forEach(key => {
        const value = formData[key as keyof Property]
        if (value !== null && value !== undefined && value !== '') {
            formDataToSend.append(key, String(value))
        }
    })
   
    images.value.forEach((file, index) => {
        formDataToSend.append(`images[${index}]`, file)
    })
   
    formDataToSend.append('primary_image_index', String(primaryImageIndex.value))

    router.post('/properties', formDataToSend, {
        onSuccess: () => {
            isLoading.value = false
            formErrors.value = {}
        },
        onError: (errors) => {
            isLoading.value = false
            formErrors.value = errors
            console.error('Error creating property:', errors)
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
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create New Property</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Add a new property listing with details and images
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
                                    {{ images.length }} of 15 photos uploaded
                                </p>
                            </div>
                        </div>
                        
                        <ImageUpload
                            v-model="images"
                            :max-files="15"
                            :max-size-mb="10"
                            @update:primary="handlePrimaryImageChange"
                            @remove="handleImageRemove"
                        />
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
                        <Form
                            :is-loading="isLoading"
                            :property-types="props.property_types"
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
