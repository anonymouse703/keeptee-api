<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Search, Filter, X, Calendar } from 'lucide-vue-next'
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'

interface FilterOption {
  label: string
  value: string | null
}

const props = defineProps<{
  searchPlaceholder?: string
  statusOptions?: FilterOption[]
  sortOptions?: FilterOption[]
  showSortDropdown?: boolean
}>()

const emit = defineEmits<{
  search: []
  filter: []
  sort: []
  reset: []
}>()

const searchQuery = ref('')
const selectedSort = ref<string | null>(null)
const filterStatus = ref<string | null>(null)
const dateRange = ref({ start: '', end: '' })
const showFilterDropdown = ref(false)

const filterButtonRef = ref<HTMLElement | null>(null)
const dropdownTop = ref(0)
const dropdownLeft = ref(0)

const openDropdown = () => {
  if (!filterButtonRef.value) return
  const rect = filterButtonRef.value.getBoundingClientRect()
  dropdownTop.value = rect.bottom + 6
  dropdownLeft.value = rect.left
  showFilterDropdown.value = true
}

const closeOnOutsideClick = (e: MouseEvent) => {
  const dropdown = document.getElementById('filter-dropdown-teleport')
  if (
    showFilterDropdown.value &&
    dropdown &&
    !dropdown.contains(e.target as Node) &&
    !filterButtonRef.value?.contains(e.target as Node)
  ) {
    showFilterDropdown.value = false
  }
}

onMounted(() => document.addEventListener('click', closeOnOutsideClick))
onBeforeUnmount(() => document.removeEventListener('click', closeOnOutsideClick))

const filters = computed(() => ({
  search: searchQuery.value,
  status: filterStatus.value,
  sort: selectedSort.value,
  dateRange: dateRange.value
}))

const fetchTags = () => {
  router.get(
    '/tags',
    {
      search: searchQuery.value,
      status: filterStatus.value,
      sort: selectedSort.value,
      start_date: dateRange.value.start,
      end_date: dateRange.value.end,
    },
    { preserveState: true, replace: true }
  )
}

const handleSearch = () => {
  emit('search')
  fetchTags()
}

const handleSort = () => {
  emit('sort')
  fetchTags()
}

const handleFilter = () => {
  emit('filter')
  fetchTags()
}

const handleReset = () => {
  emit('reset')
  resetFilters()
}

const applyFilters = () => {
  showFilterDropdown.value = false
  handleFilter()
}

const resetFilters = () => {
  searchQuery.value = ''
  filterStatus.value = null
  selectedSort.value = null
  dateRange.value = { start: '', end: '' }
  handleReset()
}

defineExpose({
  filters,
  resetFilters,
  applyFilters
})
</script>

<template>
  <div class="flex justify-between items-center gap-3">

    <div class="relative max-w-sm w-full">
      <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
      <input v-model="searchQuery" @keyup.enter="handleSearch" :placeholder="props.searchPlaceholder ?? 'Search...'"
        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
    </div>


    <div class="flex items-center gap-2">

      <div class="relative">
        <button ref="filterButtonRef" @click="openDropdown"
          class="flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 dark:hover:bg-gray-800 dark:text-white">
          <Filter class="h-4 w-4" />
          Filter
        </button>
      </div>


      <div class="relative" v-if="props.showSortDropdown">
        <select v-model="selectedSort" @change="handleSort"
          class="px-4 py-2 border border-gray-300 rounded-lg bg-white dark:bg-gray-900 dark:border-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-40">
          <option :value="null">Sort by...</option>
          <option v-for="option in props.sortOptions ?? []" :key="option.label" :value="option.value">
            {{ option.label }}
          </option>
        </select>
      </div>
    </div>


    <teleport to="body">
      <div v-if="showFilterDropdown" id="filter-dropdown-teleport"
        class="fixed z-9999 w-80 rounded-lg border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
        :style="{ top: dropdownTop + 'px', left: dropdownLeft + 'px' }">
        <div class="p-4">
          <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
            <button @click="showFilterDropdown = false"
              class="rounded p-1 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
              <X class="size-4" />
            </button>
          </div>


          <div class="mb-4">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <div class="space-y-2">
              <label v-for="option in props.statusOptions ?? []" :key="option.label"
                class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                <input type="radio" v-model="filterStatus" :value="option.value" class="cursor-pointer" />
                <span class="text-sm text-gray-700 dark:text-gray-300">{{ option.label }}</span>
              </label>
            </div>
          </div>


          <div class="mb-6">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
              <Calendar class="inline size-4 mr-1" /> Date Range
            </label>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">From</label>
                <input v-model="dateRange.start" type="date"
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
              <div>
                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">To</label>
                <input v-model="dateRange.end" type="date"
                  class="w-full rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <button @click="handleReset"
              class="flex-1 border border-gray-300 dark:border-gray-700 rounded-lg py-2 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300">
              Clear All
            </button>
            <button @click="applyFilters" class="flex-1 bg-blue-600 text-white rounded-lg py-2 hover:bg-blue-700">
              Apply Filters
            </button>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>
