<script setup lang="ts">

/**
 * Fully generic input that supports any HTML input type
 * and forwards all attributes.
 */
const props = defineProps<{
  modelValue?: string | number
  type?: string
}>()

// Emit for v-model
const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
}>()

// Update handler
const updateValue = (event: Event) => {
  const target = event.target as HTMLInputElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <input
    v-bind="$attrs"
    :type="props.type || 'text'"
    :value="props.modelValue"
    @input="updateValue"
    class="block w-full rounded-lg border border-gray-300 bg-white/50 pl-4 pr-4 py-3 text-gray-900 placeholder-gray-500
           focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white
           dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400
           transition-all duration-200"
  />
</template>
