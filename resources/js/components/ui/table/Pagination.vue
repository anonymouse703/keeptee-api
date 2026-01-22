<script setup lang="ts">
import { computed } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

interface Link {
  url: string | null;
  label: string;
  active: boolean;
}

interface Props {
  meta: {
    current_page: number;
    last_page: number;
    links: Link[];
    total?: number;
    from?: number;
    to?: number;
  };
  query?: Record<string, any>;
}

const props = defineProps<Props>()

const emit = defineEmits<{
  pageChange: [url: string | null];
}>();

// Filter out navigation links (Previous, Next, etc.)
const pagesList = computed(() =>
  props.meta?.links?.filter(
    link =>
      !link.label.includes('Previous') &&
      !link.label.includes('Next') &&
      !link.label.includes('«') &&
      !link.label.includes('»')
  ) ?? []
)

// Navigation links
const prevPage = computed(() =>
  props.meta?.links?.find(
    link => link.label.includes('Previous') || link.label.includes('«')
  ) ?? null
)

const nextPage = computed(() =>
  props.meta?.links?.find(
    link => link.label.includes('Next') || link.label.includes('»')
  ) ?? null
)

const currentPage = computed(() => props.meta?.current_page ?? 1)
const totalPages = computed(() => props.meta?.last_page ?? 1)

// Item counts
const startItem = computed(() => props.meta?.from ?? 0)
const endItem = computed(() => props.meta?.to ?? 0)
const totalItems = computed(() => props.meta?.total ?? 0)

const handlePageChange = (url: string | null) => {
  if (url) {
    emit('pageChange', url);
  }
}
</script>

<template>
  <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <!-- Items Info -->
    <div v-if="totalItems > 0" class="text-sm text-gray-500 dark:text-gray-400">
      <span>Showing {{ startItem }} to {{ endItem }} of {{ totalItems }}</span>
    </div>
    <div v-else class="text-sm text-gray-500 dark:text-gray-400">
      <span>No results found</span>
    </div>

    <!-- Pagination Controls -->
    <div class="flex flex-col items-center gap-3 sm:flex-row sm:gap-2">
      <!-- Page Info (Mobile) -->
      <div class="text-sm text-gray-500 dark:text-gray-400 sm:hidden">
        Page {{ currentPage }} of {{ totalPages }}
      </div>

      <!-- Pagination Buttons -->
      <div class="flex items-center space-x-1">
        <!-- Previous Button -->
        <button
          v-if="prevPage"
          @click="handlePageChange(prevPage.url)"
          :disabled="!prevPage.url"
          class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          <ChevronLeft class="h-4 w-4" />
          <span class="ml-1 hidden sm:inline">Previous</span>
        </button>
        <button
          v-else
          disabled
          class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-400 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500"
        >
          <ChevronLeft class="h-4 w-4" />
          <span class="ml-1 hidden sm:inline">Previous</span>
        </button>

        <!-- Page Numbers -->
        <div class="hidden items-center space-x-1 sm:flex">
          <button
            v-for="(page, index) in pagesList"
            :key="index"
            @click="handlePageChange(page.url)"
            :disabled="!page.url || page.active"
            class="min-w-[40px] rounded-lg border px-3 py-2 text-sm font-medium transition-all"
            :class="[
              page.active
                ? 'border-blue-600 bg-blue-600 text-white dark:border-blue-500 dark:bg-blue-600'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'
            ]"
          >
            {{ page.label }}
          </button>
        </div>

        <!-- Current Page (Mobile) -->
        <div class="sm:hidden">
          <span class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ currentPage }}/{{ totalPages }}
          </span>
        </div>

        <!-- Next Button -->
        <button
          v-if="nextPage"
          @click="handlePageChange(nextPage.url)"
          :disabled="!nextPage.url"
          class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          <span class="mr-1 hidden sm:inline">Next</span>
          <ChevronRight class="h-4 w-4" />
        </button>
        <button
          v-else
          disabled
          class="flex items-center justify-center rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-400 disabled:opacity-50 disabled:cursor-not-allowed dark:border-gray-700 dark:bg-gray-800 dark:text-gray-500"
        >
          <span class="mr-1 hidden sm:inline">Next</span>
          <ChevronRight class="h-4 w-4" />
        </button>
      </div>
    </div>
  </div>
</template>