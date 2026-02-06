<script setup lang="ts">
import { computed, PropType } from 'vue'

interface Option {
  label: string
  value: string | boolean | number
}

const props = defineProps({
  modelValue: {
    type: [String, Number, Boolean] as PropType<string | number | boolean | null>,
    default: null
  },
  options: {
    type: Array as PropType<Option[]>,
    required: true
  },
  placeholder: {
    type: String,
    default: 'Select option'
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number | boolean | null): void
}>()

// Internal value directly uses modelValue
const internalValue = computed({
  get() {
    return props.modelValue
  },
  set(val: string | number | boolean | null) {
    emit('update:modelValue', val)
  }
})
</script>

<template>
  <div class="w-full">
    <select v-model="internalValue"
      class="w-full min-w-0 rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white appearance-none"
      :class="props.error
        ? 'border-red-500 focus:ring-red-500/20'
        : ''">
      <option :value="null" disabled>{{ props.placeholder }}</option>
      <option v-for="option in props.options" :key="String(option.value)" :value="option.value">
        {{ option.label }}
      </option>
    </select>


    <!-- Error display -->
    <p v-if="props.error" class="mt-1 text-xs text-red-500">{{ props.error }}</p>
  </div>
</template>
