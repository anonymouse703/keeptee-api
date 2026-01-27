<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import { Tag as TagIcon, ArrowLeft } from 'lucide-vue-next'
import { ref } from 'vue'

import DeleteModal from '@/components/ui/modal/Modal.vue' 
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

import { type Tag, type TagWithId } from '../../../types/type'

import TagForm from './partials/Form.vue'

const props = defineProps<{
  tag: TagWithId | { data: TagWithId } 
  errors?: Record<string, string>
}>()

const tagData: TagWithId = 'data' in props.tag ? props.tag.data : props.tag

console.log(tagData) 

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tags', href: '/tags' },
  { title: 'Edit Tag', href: `/tags/${tagData.id}/edit` }
]

const isLoading = ref(false)

const handleSubmit = async (formData: Tag) => {
  isLoading.value = true

  const payload = {
    name: formData.name,
    color: formData.color ?? null,
    description: formData.description ?? null,
    is_active: formData.is_active ?? true
  }

  router.put(`/tags/${tagData.id}`, payload, {
    onSuccess: () => { isLoading.value = false },
    onError: (errors) => {
      isLoading.value = false
      console.error('Error updating tag:', errors)
    },
    preserveScroll: true
  })
}

const showDeleteModal = ref(false)

const confirmDelete = () => {
  router.delete(`/tags/${tagData.id}`, {
    onSuccess: () => router.visit('/tags')
  })
  showDeleteModal.value = false
}

const handleCancel = () => {
  router.visit('/tags')
}

const handleDelete = () => {
  showDeleteModal.value = true
}
</script>

<template>
  <Head :title="`Edit Tag: ${tagData.name}`" />
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
            Back to Tags
          </button>
        </div>

        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Tag</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              Update the tag information
            </p>
          </div>
          <div class="flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
              <TagIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
            </div>
          </div>
        </div>
      </div>

      <!-- Form Container -->
      <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
          <TagForm
            :tag="tagData"
            :isLoading="isLoading"
            :errors="errors"
            @submit="handleSubmit"
            @cancel="handleCancel"
          />
        </div>

        <!-- Danger Zone -->
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
          <div class="flex items-start justify-between gap-3">
            <div>
              <h4 class="text-sm font-medium text-red-800 dark:text-red-300">Danger Zone</h4>
              <p class="mt-1 text-sm text-red-700 dark:text-red-400">
                Deleting this tag will remove it from all associated properties. This action cannot be undone.
              </p>
            </div>
            <button
              @click="handleDelete"
              class="shrink-0 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:border-red-700 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50"
            >
              Delete Tag
            </button>
          </div>
        </div>
      </div>
    </div>
    <DeleteModal
      :show="showDeleteModal"
      title="Delete Tag"
      :message="`Are you sure you want to delete the tag ${tagData.name}? This action cannot be undone.`"
      confirmText="Delete"
      cancelText="Cancel"
      variant="danger"
      @confirm="confirmDelete"
      @cancel="() => (showDeleteModal = false)"
      @close="() => (showDeleteModal = false)"
    />


  </AppLayout>
</template>
