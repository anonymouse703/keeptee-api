<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { Home } from 'lucide-vue-next'
import { computed } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'

const props = defineProps<{
  amenity?: any
}>()


const form = useForm({
  name: props.amenity?.name ?? '',
})

const isFormValid = computed(() => (form.name ?? '').trim().length > 0)

const allErrors = computed(() => form.errors)

const handleSubmit = () => {
  if (!isFormValid.value) return

  if (props.amenity?.id) {
    form.put(`/amenities/${props.amenity.id}`, { preserveScroll: true })
  } else {
    form.post('/amenities', { preserveScroll: true })
  }
}

const handleCancel = () => {
  form.reset()
  router.visit('/amenities')
}
</script>

<template>
  <div class="space-y-6">
    <!-- Form Header -->
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-800">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <Home class="h-5 w-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ amenity?.id ? 'Edit Amenity' : 'Create New Amenity' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ amenity?.id ? 'Update your amenity details' : 'Add a new amenity to organize your properties' }}
        </p>
      </div>
    </div>
    <div class="space-y-5">

      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Amenity Name <span class="text-red-500">*</span>
        </label>
        <BaseInput v-model="form.name" type="text" placeholder="Enter amenity name" :error="allErrors.name" required
          class="w-full" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
          A descriptive name for your amenity (e.g., "pool", "Security", "Townhouse")
        </p>
      </div>
    </div>

    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
      <BaseButton type="button" variant="outline" @click="handleCancel" :disabled="form.processing"
        class="border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
        Cancel
      </BaseButton>
      <BaseButton type="button" @click="handleSubmit" :disabled="!isFormValid || form.processing"
        :loading="form.processing"
        class="bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
        {{ amenity?.id ? 'Update Amenity' : 'Create Amenity' }}
      </BaseButton>
    </div>
  </div>
</template>
