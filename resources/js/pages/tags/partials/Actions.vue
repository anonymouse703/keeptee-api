<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { MoreVertical, Edit, Trash2, CheckCircle, XCircle } from 'lucide-vue-next'
import { ref, nextTick, onMounted, onBeforeUnmount, computed } from 'vue'

import Modal from '@/components/ui/modal/Modal.vue'

const props = defineProps<{ item: any }>()

const open = ref(false)
const buttonRef = ref<HTMLElement | null>(null)
const position = ref({ top: 0, left: 0 })

const showDeleteModal = ref(false)
const showToggleModal = ref(false)
const toggleAction = ref<'activate' | 'deactivate' | null>(null)

const deleteMessage = computed(() => `Delete "${props.item.name}"?`)
const toggleMessage = computed(() => {
  const action = toggleAction.value === 'activate' ? 'activate' : 'deactivate'
  return `Are you sure you want to ${action} "${props.item.name}"?`
})

const openMenu = async () => {
  open.value = !open.value
  if (open.value) {
    await nextTick()
    const rect = buttonRef.value!.getBoundingClientRect()
    const viewportWidth = window.innerWidth
    
  
    let left = rect.right - 180
    let top = rect.bottom + 6
    
  
    const dropdownWidth = 176
    if (left + dropdownWidth > viewportWidth) {
      left = viewportWidth - dropdownWidth - 16
    }
  
    if (left < 0) {
      left = 16
    }
  
    const dropdownHeight = 132
    const viewportHeight = window.innerHeight
    if (top + dropdownHeight > viewportHeight) {
      top = rect.top - dropdownHeight - 6
    }
    
    position.value = { 
      top: top + window.scrollY, 
      left: left + window.scrollX 
    }
  }
}

const edit = () => {
  router.visit(`/tags/${props.item.id}/edit`)
  open.value = false
}

const openToggleModal = () => {
  toggleAction.value = props.item.is_active ? 'deactivate' : 'activate'
  showToggleModal.value = true
  open.value = false
}

const confirmToggleStatus = () => {
  router.put(`/tags/${props.item.id}/toggle-status`, {}, { 
    onSuccess: () => (showToggleModal.value = false) 
  })
}

const confirmDelete = () => {
  router.delete(`/tags/${props.item.id}`)
  showDeleteModal.value = false
}

const openDeleteModal = () => {
  showDeleteModal.value = true
  open.value = false
}

const handleClickOutside = (e: MouseEvent) => {
  if (!buttonRef.value?.contains(e.target as Node)) open.value = false
}

const repositionDropdown = () => {
  if (open.value && buttonRef.value) {
    const rect = buttonRef.value!.getBoundingClientRect()
    const viewportWidth = window.innerWidth
    
    let left = rect.right - 180
    let top = rect.bottom + 6
    
    const dropdownWidth = 176
    if (left + dropdownWidth > viewportWidth) {
      left = viewportWidth - dropdownWidth - 16
    }
    
    if (left < 0) {
      left = 16
    }
    
    const dropdownHeight = 132
    const viewportHeight = window.innerHeight
    if (top + dropdownHeight > viewportHeight) {
      top = rect.top - dropdownHeight - 6
    }
    
    position.value = { 
      top: top + window.scrollY, 
      left: left + window.scrollX 
    }
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('scroll', repositionDropdown)
  window.addEventListener('resize', repositionDropdown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('scroll', repositionDropdown)
  window.removeEventListener('resize', repositionDropdown)
})
</script>

<template>

  <button 
    ref="buttonRef" 
    @click.stop="openMenu" 
    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:text-gray-500 dark:hover:text-gray-300 dark:hover:bg-gray-800 transition-colors"
  >
    <MoreVertical class="h-5 w-5" />
  </button>

  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-150 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition-all duration-100 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="open"
        class="fixed z-9999 w-44 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg"
        :style="{ top: `${position.top}px`, left: `${position.left}px` }"
      >
        <button 
          @click="edit" 
          class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 first:rounded-t-lg last:rounded-b-lg transition-colors"
        >
          <Edit class="h-4 w-4 text-blue-600 dark:text-blue-400" /> 
          <span>Edit</span>
        </button>

        <button
          @click="openToggleModal"
          class="flex w-full items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
          :class="item.is_active ? 'text-yellow-600 dark:text-yellow-500' : 'text-green-600 dark:text-green-500'"
        >
          <component 
            :is="item.is_active ? XCircle : CheckCircle" 
            class="h-4 w-4" 
          />
          <span>{{ item.is_active ? 'Deactivate' : 'Activate' }}</span>
        </button>

        <button 
          @click="openDeleteModal" 
          class="flex w-full items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-500 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
        >
          <Trash2 class="h-4 w-4" /> 
          <span>Delete</span>
        </button>
      </div>
    </Transition>
  </Teleport>

  <Modal 
    :show="showDeleteModal" 
    title="Confirm Delete" 
    :message="deleteMessage"
    confirmText="Delete" 
    cancelText="Cancel" 
    variant="danger"
    @confirm="confirmDelete" 
    @cancel="showDeleteModal = false" 
  />

  <Modal 
    :show="showToggleModal" 
    :title="toggleAction === 'activate' ? 'Activate Tag' : 'Deactivate Tag'" 
    :message="toggleMessage"
    confirmText="Yes" 
    cancelText="Cancel" 
    :variant="toggleAction === 'activate' ? 'success' : 'warning'"
    @confirm="confirmToggleStatus" 
    @cancel="showToggleModal = false" 
  />
</template>