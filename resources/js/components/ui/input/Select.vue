<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue?: string | number | null
  options: { label: string; value: string | number }[]
  id?: string
  placeholder?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

const value = computed({
  get() {
    return props.modelValue ?? ''
  },
  set(value: string | number) {
    // Convert empty string to null or keep the value
    const newValue = value === '' ? null : value
    emit('update:modelValue', newValue)
  }
})

// Helper to check if value is number
const isNumber = (val: any): boolean => {
  return typeof val === 'number' || (!isNaN(Number(val)) && val !== '')
}
</script>

<template>
  <select
    :id="props.id"
    v-model="value"
    class="block w-full rounded-lg border border-gray-300 bg-white/50 px-4 py-3 text-gray-900 placeholder-gray-500 
           focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white
           dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400
           transition-all duration-200"
  >
    <option v-if="props.placeholder" :value="null" disabled>
      {{ props.placeholder }}
    </option>
    <option 
      v-for="option in props.options" 
      :key="option.value" 
      :value="option.value"
      :selected="option.value === props.modelValue"
    >
      {{ option.label }}
    </option>
  </select>
</template>