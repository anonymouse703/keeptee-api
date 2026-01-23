<script setup lang="ts">
import { computed } from 'vue'

type OptionValue = string | number | boolean

const props = defineProps<{
  modelValue?: OptionValue | null
  options: { label: string; value: OptionValue }[]
  id?: string
  placeholder?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: OptionValue | null]
}>()

const value = computed<OptionValue | ''>({
  get() {
    return props.modelValue ?? ''
  },
  set(val) {
    // Handle string conversion back to proper types
    if (val === 'true') {
      emit('update:modelValue', true)
    } else if (val === 'false') {
      emit('update:modelValue', false)
    } else if (val === '') {
      emit('update:modelValue', null)
    } else {
      emit('update:modelValue', val)
    }
  }
})

// Convert value to string for select element
const selectValue = computed({
  get() {
    if (props.modelValue === null || props.modelValue === undefined) {
      return ''
    }
    return String(props.modelValue)
  },
  set(val: string) {
    // Find the original option to get the correct type
    const option = props.options.find(opt => String(opt.value) === val)
    if (option) {
      emit('update:modelValue', option.value)
    } else if (val === '') {
      emit('update:modelValue', null)
    }
  }
})
</script>

<template>
  <select
    :id="props.id"
    v-model="selectValue"
    class="block w-full rounded-lg border border-gray-300 bg-white/50 px-4 py-3
           text-gray-900 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20
           dark:border-gray-700 dark:bg-gray-800/50 dark:text-white"
  >
    <!-- Placeholder -->
    <option v-if="props.placeholder" value="" disabled>
      {{ props.placeholder }}
    </option>

    <!-- Options -->
    <option
      v-for="option in props.options"
      :key="String(option.value)"
      :value="String(option.value)"
    >
      {{ option.label }}
    </option>
  </select>
</template>