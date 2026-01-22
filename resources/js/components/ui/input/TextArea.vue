<script setup lang="ts">

const props = defineProps<{
  modelValue?: string
  placeholder?: string
  rows?: number
  id?: string
}>()
</script>

<template>
  <textarea
    :id="props.id"
    :rows="props.rows || 4"
    :placeholder="props.placeholder"
    v-model="props.modelValue"
    class="block w-full rounded-lg border border-gray-300 bg-white/50 px-4 py-3 text-gray-900 placeholder-gray-500 
           focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white
           dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400
           transition-all duration-200 resize-none"
  />
</template>
