<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Tag as TagIcon } from 'lucide-vue-next'
import { computed } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'
import BaseSelect from '@/components/ui/input/Select.vue'

const props = defineProps<{
  tag?: any
}>()

const t = props.tag ?? { name: '', color: '#3b82f6', description: '', is_active: true }

const form = useForm({
  name: t.name ?? '',
  color: t.color ?? '#3b82f6',
  description: t.description ?? '',
  is_active: t.is_active ?? true
})

const statusOptions = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

const colorOptions = [
  { label: 'Blue', value: '#3b82f6', class: 'bg-blue-500' },
  { label: 'Green', value: '#10b981', class: 'bg-green-500' },
  { label: 'Red', value: '#ef4444', class: 'bg-red-500' },
  { label: 'Yellow', value: '#f59e0b', class: 'bg-yellow-500' },
  { label: 'Purple', value: '#8b5cf6', class: 'bg-purple-500' },
  { label: 'Pink', value: '#ec4899', class: 'bg-pink-500' },
  { label: 'Indigo', value: '#6366f1', class: 'bg-indigo-500' },
  { label: 'Teal', value: '#14b8a6', class: 'bg-teal-500' }
]

const isFormValid = computed(() => (form.name ?? '').trim().length > 0)

const allErrors = computed(() => form.errors)

const handleSubmit = () => {
  if (!isFormValid.value) return

  if (props.tag?.id) {
    form.put(`/tags/${props.tag.id}`, { preserveScroll: true })
  } else {
    form.post('/tags', { preserveScroll: true })
  }
}

const handleCancel = () => {
  form.reset()
  router.visit('/tags')
}
</script>

<template>
  <div class="space-y-6">
    <!-- Form Header -->
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-800">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <TagIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ tag?.id ? 'Edit Tag' : 'Create New Tag' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ tag?.id ? 'Update your tag details' : 'Add a new tag to organize your properties' }}
        </p>
      </div>
    </div>
    <div class="space-y-5">
      
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Tag Name <span class="text-red-500">*</span>
        </label>
        <BaseInput
          v-model="form.name"
          type="text"
          placeholder="Enter tag name"
          :error="allErrors.name"
          required
          class="w-full"
        />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          A descriptive name for your tag (e.g., "Luxury", "Waterfront", "Renovated")
        </p>
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Tag Color
        </label>
        <div class="grid grid-cols-4 gap-2 sm:grid-cols-8">
          <button
            v-for="color in colorOptions"
            :key="color.value"
            type="button"
            @click="form.color = color.value"
            class="relative flex flex-col items-center gap-1.5 rounded-lg p-2 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            :class="{ 'bg-gray-100 dark:bg-gray-800': form.color === color.value }"
          >
            <div class="h-8 w-8 rounded-full" :class="color.class"></div>
            <span class="text-xs text-gray-600 dark:text-gray-400">{{ color.label }}</span>
            <div
              v-if="form.color === color.value"
              class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-blue-600"
            >
              <svg class="h-2.5 w-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </button>
        </div>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
          Color helps visually distinguish this tag from others
        </p>
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <BaseSelect
          v-model="form.is_active"
          :options="statusOptions"
          placeholder="Select status"
          class="w-full max-w-xs"
        />
      </div>

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea
          v-model="form.description"
          rows="3"
          placeholder="Optional description for this tag"
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-500 dark:focus:border-blue-400"
        ></textarea>
      </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
      <BaseButton
        type="button"
        variant="outline"
        @click="handleCancel"
        :disabled="form.processing"
        class="border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800"
      >
        Cancel
      </BaseButton>
      <BaseButton
        type="button"
        @click="handleSubmit"
        :disabled="!isFormValid || form.processing"
        :loading="form.processing"
        class="bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        {{ tag?.id ? 'Update Tag' : 'Create Tag' }}
      </BaseButton>
    </div>
  </div>
</template>
