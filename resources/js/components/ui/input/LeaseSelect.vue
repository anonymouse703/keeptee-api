<script setup lang="ts">
import { ref, watch } from 'vue'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption } from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid'

const props = defineProps<{
  modelValue: number | null
  fetchLeases?: (query: string) => Promise<any[]>
  fetchOptions?: (query: string) => Promise<any[]>
  selectedOption?: any
  placeholder?: string
  error?: string
}>()

const emit = defineEmits<{
  'update:modelValue': (val: number | null) => void
}>()

// Use whichever fetch function is provided
const fetchFunction = props.fetchLeases || props.fetchOptions

const query = ref('')
const options = ref<any[]>([])
const selected = ref<any>(props.selectedOption || null)
const loading = ref(false)

// Watch for selectedOption prop changes
watch(
  () => props.selectedOption,
  (newVal) => {
    if (newVal) {
      selected.value = newVal
    }
  },
  { immediate: true }
)

// Initialize selected if modelValue exists
watch(
  () => props.modelValue,
  async (val) => {
    if (val != null && !selected.value) {
      const exist = options.value.find(o => o.value === val)
      if (exist) {
        selected.value = exist
      } else if (fetchFunction) {
        loading.value = true
        options.value = await fetchFunction('')
        selected.value = options.value.find(o => o.value === val) ?? null
        loading.value = false
      }
    }
  },
  { immediate: true }
)

watch(query, async val => {
  if (!fetchFunction) return
  
  loading.value = true
  try {
    options.value = await fetchFunction(val || '')
  } catch {
    options.value = []
  } finally {
    loading.value = false
  }
})

const displayValue = (option: any) => option?.label ?? ''
</script>

<template>
  <div class="w-full">
    <Combobox v-model="selected">
      <div class="relative">
        <ComboboxInput
          :displayValue="displayValue"
          @change="query = $event.target.value"
          :placeholder="placeholder"
          class="w-full rounded-lg border border-gray-300 dark:border-gray-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition dark:bg-gray-800 dark:text-white"
          :class="{ 'border-red-500 focus:ring-red-500': error }"
        />
        <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
          <ChevronUpDownIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
        </ComboboxButton>

        <Transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="opacity-0 scale-95"
          enter-to-class="opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="opacity-100 scale-100"
          leave-to-class="opacity-0 scale-95"
        >
          <ComboboxOptions class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white dark:bg-gray-800 py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
            <div v-if="loading" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">Loading...</div>
            <template v-else-if="options.length === 0">
              <div class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                {{ query ? 'No results found' : 'Start typing to search...' }}
              </div>
            </template>
            <ComboboxOption v-else v-for="option in options" :key="option.value" :value="option" v-slot="{ active, selected: isSelected }">
              <li
                :class="['relative cursor-pointer select-none py-2 pl-3 pr-9', active ? 'bg-blue-100 dark:bg-gray-700 text-blue-900 dark:text-white' : 'text-gray-900 dark:text-gray-200']"
              >
                <span :class="['block truncate', isSelected ? 'font-semibold' : 'font-normal']">{{ option.label }}</span>
                <span v-if="isSelected" class="absolute inset-y-0 right-0 flex items-center pr-4 text-blue-600 dark:text-blue-400">
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </Transition>
      </div>
    </Combobox>
    <p v-if="error" class="mt-1 text-xs text-red-500">{{ error }}</p>
  </div>
</template>