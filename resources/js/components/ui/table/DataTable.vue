<script setup lang="ts">
interface Column {
  key: string;
  label: string;
  width?: string;
}

interface Props {
  columns: Column[];
  data: any[];
  emptyMessage?: string;
  clickableRows?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  emptyMessage: 'No data found',
  clickableRows: true,
});

const emit = defineEmits<{
  rowClick: [item: any];
}>();

const handleRowClick = (item: any) => {
  if (props.clickableRows) {
    emit('rowClick', item);
  }
};
</script>

<template>
  <div class="w-full space-y-4">
    <div class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden bg-white dark:bg-gray-900 shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
            <tr>
              <th
                v-for="column in columns"
                :key="column.key"
                :style="column.width ? { width: column.width } : {}"
                class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase tracking-wider"
              >
                {{ column.label }}
              </th>

              <!-- Actions column (only show if slot is provided) -->
              <th
                v-if="$slots['row-actions']"
                class="px-6 py-3.5 text-right text-xs font-semibold text-gray-700 dark:text-gray-400 uppercase tracking-wider"
                style="width: 120px;"
              >
                Actions
              </th>
            </tr>
          </thead>

          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
            <tr
              v-for="(item, index) in props.data"
              :key="item.id || index"
              :class="[
                'transition-colors',
                clickableRows ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/50' : ''
              ]"
              @click="handleRowClick(item)"
            >
              <td
                v-for="column in columns"
                :key="column.key"
                class="px-6 py-4 text-sm"
                :class="clickableRows ? 'text-gray-900 dark:text-gray-300' : 'text-gray-700 dark:text-gray-400'"
              >
                <slot :name="`cell-${column.key}`" :item="item" :value="item[column.key]">
                  {{ item[column.key] }}
                </slot>
              </td>

              <!-- Row actions slot -->
              <td 
                v-if="$slots['row-actions']" 
                class="px-6 py-4 whitespace-nowrap text-right"
                @click.stop
              >
                <slot name="row-actions" :item="item"></slot>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="props.data.length === 0" class="text-center py-12">
        <slot name="empty-state">
          <div class="text-gray-500 dark:text-gray-400">
            <div class="mx-auto w-12 h-12 mb-4 flex items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
              <svg class="w-6 h-6 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
            </div>
            <p class="text-sm font-medium">{{ emptyMessage }}</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try adjusting your search or filter</p>
          </div>
        </slot>
      </div>
    </div>

    <!-- Footer slot -->
    <div v-if="$slots.footer" class="flex justify-between items-center pt-2">
      <slot name="footer" :total="props.data.length" />
    </div>
  </div>
</template>