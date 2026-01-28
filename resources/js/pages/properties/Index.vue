<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { HomeIcon } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import { Badge } from '@/components/ui/badge'
import BaseButton from '@/components/ui/button/BaseButton.vue'
import DataTable from '@/components/ui/table/DataTable.vue'
import Pagination from '@/components/ui/table/Pagination.vue'
import AppLayout from '@/layouts/AppLayout.vue'

import { type PropertyListItem, type PaginationMeta } from '../../../types/type'

import Actions from './partials/Actions.vue'
import SearchFilter from './partials/SearchFilter.vue'



const props = defineProps<{
    properties: {
        data: PropertyListItem[]
        meta: PaginationMeta
    }
    statuses?: Array<{ label: string; key: string }>
}>()

const searchFilterRef = ref<InstanceType<typeof SearchFilter> | null>(null)

const properties = computed(() => props.properties.data)

const filteredproperties = computed<PropertyListItem[]>(() => {
    if (!searchFilterRef.value) return properties.value

    const filters = searchFilterRef.value.filters
    let result = [...properties.value]

    // Search
    if (filters.search) {
        const q = filters.search.toLowerCase()
        result = result.filter(tag =>
            tag.title.toLowerCase().includes(q)
        )
    }

    // Sorting
    if (filters.sort) {
        switch (filters.sort) {
            case 'title_asc':
                result.sort((a, b) => a.title.localeCompare(b.title))
                break
            case 'title_desc':
                result.sort((a, b) => b.title.localeCompare(a.title))
                break
            case 'count_desc':
                result.sort((a, b) => b.properties_count - a.properties_count)
                break
            case 'count_asc':
                result.sort((a, b) => a.properties_count - b.properties_count)
                break
            case 'created_desc':
                result.sort(
                    (a, b) =>
                        new Date(b.created_at).getTime() -
                        new Date(a.created_at).getTime()
                )
                break
            case 'created_asc':
                result.sort(
                    (a, b) =>
                        new Date(a.created_at).getTime() -
                        new Date(b.created_at).getTime()
                )
                break
        }
    }

    return result
})

const columns = [
    { key: 'title', label: 'Property Title' },
    { key: 'price', label: 'Price' },
    { key: 'is_featured', label: 'Featured' },
    { key: 'is_active', label: 'Status' },
]

const statusOptions = [
    { label: 'All Status', value: null },
    { label: 'Active', value: 'active' },
    { label: 'Archived', value: 'archived' },
    { label: 'Draft', value: 'draft' },
]

const sortOptions = [
    { label: 'Name (A–Z)', value: 'name_asc' },
    { label: 'Name (Z–A)', value: 'name_desc' },
    { label: 'Most Properties', value: 'count_desc' },
    { label: 'Least Properties', value: 'count_asc' },
    { label: 'Newest First', value: 'created_desc' },
    { label: 'Oldest First', value: 'created_asc' },
]

const handlePageChange = (url: string | null) => {
    if (!url) return

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    })
}

const handleSearch = () => {}
const handleFilter = () => {}
const handleSort = () => {}
const handleReset = () => {}
</script>

<template>
    <AppLayout>
        <div class="relative space-y-6 p-4 md:p-6 lg:p-8">
            <!-- Header -->
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-emerald-500">
                            <HomeIcon class="size-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                                Property management
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                               A centralized system to organize and manage your properties.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="text-xs">
                            {{ props.properties.meta.total }} properties total
                        </Badge>
                        <Badge
                            variant="outline"
                            class="text-xs bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                        >
                            {{ filteredproperties.length }} displayed
                        </Badge>
                    </div>
                </div>

                <div class="shrink-0">
                    <BaseButton @click="router.visit('/properties/create')">
                        <HomeIcon class="size-4" />
                        Add New Properties
                    </BaseButton>
                </div>
            </div>

            <!-- Search -->
            <SearchFilter
                ref="searchFilterRef"
                search-placeholder="Search properties..."
                :status-options="statusOptions"
                :sort-options="sortOptions"
                :show-sort-dropdown="true"
                @search="handleSearch"
                @filter="handleFilter"
                @sort="handleSort"
                @reset="handleReset"
            />

            <!-- Data Table -->
            <DataTable :columns="columns" :data="props.properties.data">
                <template #row-actions="{ item }">
                    <Actions :item="item" :statuses="props.statuses ?? []" />
                </template>
                 
                <template #cell-color="{ item }">
                    <div class="flex items-center gap-2">
                        <span
                            class="w-4 h-4 rounded-full border"
                            :style="{ backgroundColor: item.color }"
                        ></span>
                        <span class="text-sm">{{ item.color }}</span>
                    </div>
                </template>

                <template #cell-is_featured="{ item }">
                    <span
                        class="px-2 py-1 rounded text-xs"
                        :class="item.is_featured ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                    >
                        {{ item.is_featured ? 'Featured' : 'Not Featured' }}
                    </span>
                </template>

                <template #cell-is_active="{ item }">
                    <span
                        class="px-2 py-1 rounded text-xs"
                        :class="item.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                    >
                        {{ item.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </template>
                
                <template #cell-created_at="{ item }">
                    {{ new Date(item.created_at).toLocaleDateString() }}
                </template>
            </DataTable>

            <!-- Pagination -->
            <Pagination
                :meta="props.properties.meta"
                @page-change="handlePageChange"
            />
        </div>
    </AppLayout>
</template>