<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import {
  MoreVertical,
  Edit,
  Trash2,
  CheckCircle,
  XCircle,
  RefreshCw,
  BookDashed
} from 'lucide-vue-next'
import { ref, nextTick, onMounted, onBeforeUnmount, computed } from 'vue'

import Modal from '@/components/ui/modal/Modal.vue'

interface StatusOption {
  label: string
  key: string
}

interface Item {
  id: number
  is_active: boolean
  name: string
}

const props = withDefaults(defineProps<{
  item: Item
  statuses?: StatusOption[]
}>(), {
  statuses: () => [],
})


const open = ref(false)
const buttonRef = ref<HTMLElement | null>(null)
const position = ref({ top: 0, left: 0 })

const showDeleteModal = ref(false)
const showToggleModal = ref(false)
const showStatusModal = ref(false)

const toggleAction = ref<'activate' | 'deactivate' | null>(null)
const selectedStatus = ref<StatusOption | null>(null)

const deleteMessage = computed(() => `Delete "${props.item.name}"?`)

const toggleMessage = computed(() => {
  const action = toggleAction.value === 'activate' ? 'activate' : 'deactivate'
  return `Are you sure you want to ${action} "${props.item.name}"?`
})

const statusMessage = computed(() => {
  if (!selectedStatus.value) return ''
  return `Set property status to "${selectedStatus.value.label}"?`
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

    const dropdownHeight = 200
    const viewportHeight = window.innerHeight
    if (top + dropdownHeight > viewportHeight) {
      top = rect.top - dropdownHeight - 6
    }

    position.value = {
      top: top + window.scrollY,
      left: left + window.scrollX,
    }
  }
}

const show = () => {
  router.visit(`/properties/${props.item.id}`)
  open.value = false
}

const edit = () => {
  router.visit(`/properties/${props.item.id}/edit`)
  open.value = false
}

const openToggleModal = () => {
  toggleAction.value = props.item.is_active ? 'deactivate' : 'activate'
  showToggleModal.value = true
  open.value = false
}

const confirmToggleStatus = () => {
  router.put(
    `/properties/${props.item.id}/toggle-active-status`,
    {},
    { onSuccess: () => (showToggleModal.value = false) }
  )
}

const openStatusModal = (status: StatusOption) => {
  selectedStatus.value = status
  showStatusModal.value = true
  open.value = false
}

const confirmStatusChange = () => {
  if (!selectedStatus.value) return

  router.put(
    `/properties/${props.item.id}/status`,
    { status: selectedStatus.value.key },
    {
      onSuccess: () => {
        showStatusModal.value = false
        selectedStatus.value = null
      },
    }
  )
}

const confirmDelete = () => {
  router.delete(`/properties/${props.item.id}`)
  showDeleteModal.value = false
}

const openDeleteModal = () => {
  showDeleteModal.value = true
  open.value = false
}

const handleClickOutside = (e: MouseEvent) => {
  if (!buttonRef.value?.contains(e.target as Node)) open.value = false
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
  <!-- Trigger -->
  <button
    ref="buttonRef"
    @click.stop="openMenu"
    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800"
  >
    <MoreVertical class="h-5 w-5" />
  </button>

  <!-- Dropdown -->
  <Teleport to="body">
    <div
      v-if="open"
      class="fixed z-9999 w-44 bg-white dark:bg-gray-900 border rounded-lg shadow-lg"
      :style="{ top: `${position.top}px`, left: `${position.left}px` }"
    >
      <button 
        @click="edit" 
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <Edit class="h-4 w-4 text-blue-500" /> 
        Edit
      </button>

       <button 
        @click="show" 
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <BookDashed class="h-4 w-4 text-green-500" /> 
        Show
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

      <!-- Dynamic status buttons -->
      <button
        v-for="status in statuses"
        :key="status.key"
        @click="openStatusModal(status)"
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <RefreshCw class="h-4 w-4 text-indigo-500" />
        {{ status.label }}
      </button>

      <button 
        @click="openDeleteModal" 
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <Trash2 class="h-4 w-4" /> 
        Delete
      </button>
    </div>
  </Teleport>

  <!-- Delete Modal -->
  <Modal
    :show="showDeleteModal"
    title="Confirm Delete"
    :message="deleteMessage"
    confirmText="Delete"
    variant="danger"
    @confirm="confirmDelete"
    @cancel="showDeleteModal = false"
  />

  <!-- Toggle Modal -->
  <Modal
    :show="showToggleModal"
    title="Confirm Action"
    :message="toggleMessage"
    confirmText="Yes"
    @confirm="confirmToggleStatus"
    @cancel="showToggleModal = false"
  />

  <!-- Status Modal -->
  <Modal
    :show="showStatusModal"
    title="Change Status"
    :message="statusMessage"
    confirmText="Confirm"
    @confirm="confirmStatusChange"
    @cancel="showStatusModal = false"
  />
</template>