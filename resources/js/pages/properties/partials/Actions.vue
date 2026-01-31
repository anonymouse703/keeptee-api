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
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'

import Modal from '@/components/ui/modal/Modal.vue'

const props = defineProps<{
  item: any
  statuses: any[]
}>()

const open = ref(false)
const buttonRef = ref<HTMLElement | null>(null)
const dropdownRef = ref<HTMLElement | null>(null)

const showDeleteModal = ref(false)
const showToggleModal = ref(false)
const showStatusModal = ref(false)

const toggleAction = ref<'activate' | 'deactivate' | null>(null)
const selectedStatus = ref<any>(null)

const deleteMessage = computed(() => `Delete "${props.item.title}"?`)

const toggleMessage = computed(() => {
  const action = toggleAction.value === 'activate' ? 'activate' : 'deactivate'
  return `Are you sure you want to ${action} "${props.item.title}"?`
})

const statusMessage = computed(() => {
  if (!selectedStatus.value) return ''
  return `Set property status to "${selectedStatus.value.label}"?`
})

const openMenu = () => {
  open.value = !open.value
}

const closeDropdown = () => {
  open.value = false
}

const show = () => {
  router.visit(`/properties/${props.item.id}`)
  closeDropdown()
}

const edit = () => {
  router.visit(`/properties/${props.item.id}/edit`)
  closeDropdown()
}

const openToggleModal = () => {
  toggleAction.value = props.item.is_active ? 'deactivate' : 'activate'
  showToggleModal.value = true
  closeDropdown()
}

const confirmToggleStatus = () => {
  router.put(
    `/properties/${props.item.id}/toggle-active-status`,
    {},
    { onSuccess: () => (showToggleModal.value = false) }
  )
}

const openStatusModal = (status: any) => {
  selectedStatus.value = status
  showStatusModal.value = true
  closeDropdown()
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

watch(open, (val) => {
  if (val) {
    document.addEventListener('click', handleClickOutside)
  } else {
    document.removeEventListener('click', handleClickOutside)
  }
})

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
<template>
  <div class="relative">
    <!-- Trigger -->
    <button
      ref="buttonRef"
      @click.stop="openMenu"
      class="p-2 rounded-lg text-gray-400 hover:text-gray-600
             hover:bg-gray-100 dark:hover:bg-gray-800"
    >
      <MoreVertical class="h-5 w-5" />
    </button>

    <!-- Dropdown -->
    <div
      v-if="open"
      ref="dropdownRef"
      class="absolute z-50 w-44 bg-white dark:bg-gray-900
             border border-gray-200 dark:border-gray-700
             rounded-lg shadow-lg
             right-0 top-full mt-2"
      @click.stop
    >
      <button
        @click="edit"
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm
               hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <Edit class="h-4 w-4 text-blue-500" />
        Edit
      </button>

      <button
        @click="show"
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm
               hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <BookDashed class="h-4 w-4 text-green-500" />
        Show
      </button>

      <button
        @click="openToggleModal"
        class="flex w-full items-center gap-2 px-4 py-2.5 text-sm
               hover:bg-gray-50 dark:hover:bg-gray-800"
        :class="item.is_active
          ? 'text-yellow-600 dark:text-yellow-500'
          : 'text-green-600 dark:text-green-500'"
      >
        <component
          :is="item.is_active ? XCircle : CheckCircle"
          class="h-4 w-4"
        />
        <span>{{ item.is_active ? 'Deactivate' : 'Activate' }}</span>
      </button>

      <!-- Status buttons -->
      <button
        v-for="status in statuses"
        :key="status.key"
        @click="openStatusModal(status)"
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm
               hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <RefreshCw class="h-4 w-4 text-indigo-500" />
        {{ status.label }}
      </button>

      <button
        @click="openDeleteModal"
        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm
               text-red-600 hover:bg-gray-50 dark:hover:bg-gray-800"
      >
        <Trash2 class="h-4 w-4" />
        Delete
      </button>
    </div>

    <!-- Modals -->
    <Modal
      :show="showDeleteModal"
      title="Confirm Delete"
      :message="deleteMessage"
      confirmText="Delete"
      variant="danger"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />

    <Modal
      :show="showToggleModal"
      title="Confirm Action"
      :message="toggleMessage"
      confirmText="Yes"
      @confirm="confirmToggleStatus"
      @cancel="showToggleModal = false"
    />

    <Modal
      :show="showStatusModal"
      title="Change Status"
      :message="statusMessage"
      confirmText="Confirm"
      @confirm="confirmStatusChange"
      @cancel="showStatusModal = false"
    />
  </div>
</template>
