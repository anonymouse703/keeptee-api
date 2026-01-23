<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'


type FlashType = 'success' | 'error' | 'warning' | 'info'

interface Flash {
  type: FlashType
  message: string
}

const page = usePage()
const flash = computed<Flash | null>(() => page.props.flash as Flash | null)


const visible = ref(false)

watch(
  flash,
  (value) => {
    if (value) {
      visible.value = true
      setTimeout(() => (visible.value = false), 4000)
    }
  },
  { immediate: true }
)

const classes = computed(() => ({
  success: 'bg-green-100 text-green-700 border-green-300',
  error: 'bg-red-100 text-red-700 border-red-300',
  warning: 'bg-yellow-100 text-yellow-700 border-yellow-300',
  info: 'bg-blue-100 text-blue-700 border-blue-300',
  danger: 'bg-red-100 text-red-700 border-red-300',
}))
</script>

<template>
  <transition name="fade">
    <div
      v-if="flash && visible"
      class="mb-4 rounded border px-4 py-3 text-sm"
      :class="classes[flash.type]"
    >
      {{ flash.message }}
    </div>
  </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
