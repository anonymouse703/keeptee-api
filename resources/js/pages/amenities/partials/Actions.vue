<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { MoreVertical, Edit, Trash2 } from 'lucide-vue-next'
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'

import Modal from '@/components/ui/modal/Modal.vue'

const props = defineProps<{ item: any }>()

const open = ref(false)
const buttonRef = ref<HTMLElement | null>(null)
const dropdownRef = ref<HTMLElement | null>(null)

const showDeleteModal = ref(false)

const deleteMessage = computed(() => `Delete "${props.item.name}"?`)

const CLOSE_DROPDOWNS_EVENT = 'close-all-dropdowns'

const openMenu = () => {
  if (!open.value) {
    window.dispatchEvent(
      new CustomEvent(CLOSE_DROPDOWNS_EVENT, {
        detail: { except: buttonRef.value },
      })
    )
  }
  open.value = !open.value
}

const closeDropdown = () => {
  open.value = false
}

const edit = () => {
  router.visit(`/amenities/${props.item.id}/edit`)
  closeDropdown()
}

const confirmDelete = () => {
  router.delete(`/amenities/${props.item.id}`)
  showDeleteModal.value = false
}

const openDeleteModal = () => {
  showDeleteModal.value = true
  closeDropdown()
}

const handleClickOutside = (e: MouseEvent) => {
  if (
    !buttonRef.value?.contains(e.target as Node) &&
    !dropdownRef.value?.contains(e.target as Node)
  ) {
    closeDropdown()
  }
}

const handleCloseOtherDropdowns = (e: CustomEvent) => {
  if (e.detail.except !== buttonRef.value) {
    closeDropdown()
  }
}

watch(open, (val) => {
  if (val) {
    document.addEventListener('click', handleClickOutside)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})

onMounted(() => {
  window.addEventListener(
    CLOSE_DROPDOWNS_EVENT as any,
    handleCloseOtherDropdowns
  )
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener(
    CLOSE_DROPDOWNS_EVENT as any,
    handleCloseOtherDropdowns
  )
})
</script>
<template>
  <div class="relative">
    <button ref="buttonRef" @click.stop="openMenu" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100
             dark:text-gray-500 dark:hover:text-gray-300 dark:hover:bg-gray-800
             transition-colors" :class="{ 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300': open }">
      <MoreVertical class="h-5 w-5" />
    </button>

    <Transition enter-active-class="transition-all duration-150 ease-out" enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100" leave-active-class="transition-all duration-100 ease-in"
      leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-95">
      <div v-if="open" ref="dropdownRef" class="absolute z-50 w-44 bg-white dark:bg-gray-900
         border border-gray-200 dark:border-gray-700
         rounded-lg shadow-lg overflow-hidden
         right-0 top-full mt-2" @click.stop>

        <button @click="edit" class="flex w-full items-center gap-2 px-4 py-2.5 text-sm
                 text-gray-700 dark:text-gray-300
                 hover:bg-gray-50 dark:hover:bg-gray-800">
          <Edit class="h-4 w-4 text-blue-600 dark:text-blue-400 shrink-0" />
          <span>Edit</span>
        </button>

        <button @click="openDeleteModal" class="flex w-full items-center gap-2 px-4 py-2.5 text-sm
                 text-red-600 dark:text-red-500
                 hover:bg-gray-50 dark:hover:bg-gray-800">
          <Trash2 class="h-4 w-4 shrink-0" />
          <span>Delete</span>
        </button>
      </div>
    </Transition>

    <Modal :show="showDeleteModal" title="Confirm Delete" :message="deleteMessage" confirmText="Delete"
      cancelText="Cancel" variant="danger" @confirm="confirmDelete" @cancel="showDeleteModal = false" />
  </div>
</template>
