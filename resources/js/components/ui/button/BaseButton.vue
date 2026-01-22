<script setup lang="ts">

const props = defineProps<{
  type?: 'button' | 'submit' | 'reset'
  disabled?: boolean
}>()
</script>

<template>
  <button
    :type="props.type || 'button'"
    :disabled="props.disabled"
    class="relative w-full overflow-hidden rounded-xl bg-linear-to-r from-blue-600 to-emerald-600
           px-6 py-4 text-white font-semibold shadow-lg
           hover:shadow-xl hover:scale-[1.02] transform transition-all duration-300
           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
           disabled:opacity-50 disabled:cursor-not-allowed
           flex items-center justify-center gap-2"
  >
    <span class="relative z-10 flex items-center gap-2">
      <slot />
    </span>
    <div class="absolute inset-0 bg-linear-to-r from-emerald-600 to-blue-600 opacity-0 hover:opacity-100 transition-opacity duration-300" />
  </button>
</template>