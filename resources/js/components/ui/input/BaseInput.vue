<script setup lang="ts">
import { computed, watch } from 'vue'

/**
 * Fully generic input that supports any HTML input type
 * and forwards all attributes. Supports null values for number inputs.
 */
const props = defineProps<{
  modelValue?: string | number | null
  type?: string
  error?: string
}>()

console.log('BaseInput errors props:', props.error);

// Emit for v-model
const emit = defineEmits<{
  'update:modelValue': [value: string | number | null]
}>()

// Computed value to handle null properly
const inputValue = computed({
  get() {
    // For number inputs, return empty string if null/undefined
    if (props.type === 'number' && (props.modelValue === null || props.modelValue === undefined)) {
      return ''
    }
    return props.modelValue ?? ''
  },
  set(val: string | number) {
    // Handle number type specially
    if (props.type === 'number') {
      if (val === '' || val === null) {
        emit('update:modelValue', null)
      } else {
        const numVal = Number(val)
        emit('update:modelValue', isNaN(numVal) ? null : numVal)
      }
    } else {
      emit('update:modelValue', val)
    }
  }
})

// Update handler
const updateValue = (event: Event) => {
  const target = event.target as HTMLInputElement
  
  if (props.type === 'number') {
    if (target.value === '') {
      emit('update:modelValue', null)
    } else {
      const numVal = Number(target.value)
      emit('update:modelValue', isNaN(numVal) ? null : numVal)
    }
  } else {
    emit('update:modelValue', target.value)
  }
}

watch(() => props.error, (newVal) => {
  if (newVal) {
    console.log('BaseInput received error:', newVal)
  }
})
</script>

<template>
  <div>
    <input
      v-bind="$attrs"
      :type="props.type || 'text'"
      :value="inputValue"
      @input="updateValue"
      :class="[
        'block w-full rounded-lg border bg-white/50 pl-4 pr-4 py-3 text-gray-900 placeholder-gray-500',
        'focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white',
        'dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400',
        'transition-all duration-200',
        error ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-300'
      ]"
    />
    
    <!-- Error message -->
    <p v-if="error" class="mt-1 text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </p>
  </div>
</template>