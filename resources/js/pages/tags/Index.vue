<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Tag as TagIcon } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import { Badge } from '@/components/ui/badge'
import BaseButton from '@/components/ui/button/BaseButton.vue'
import DataTable from '@/components/ui/table/DataTable.vue'
import Pagination from '@/components/ui/table/Pagination.vue'
import AppLayout from '@/layouts/AppLayout.vue'

import Actions from './partials/Actions.vue'
import SearchFilter from './partials/SearchFilter.vue'

const props = defineProps({
    tags: Object,
})

const tagsData = computed(() => props.tags?.data ?? [])

const paginationLinks = computed(() => props.tags?.meta ?? {})

const searchFilterRef = ref<InstanceType<typeof SearchFilter> | null>(null)

const filteredTags = computed(() => {
    if (!searchFilterRef.value) return tagsData.value

    const filters = searchFilterRef.value.filters
    let result = [...tagsData.value]

    if (filters.search) {
        const q = filters.search.toLowerCase()
        result = result.filter(tag =>
            tag.name.toLowerCase().includes(q)
        )
    }

    if (filters.sort) {
        switch (filters.sort) {
            case 'name_asc':
                result.sort((a, b) => a.name.localeCompare(b.name))
                break
            case 'name_desc':
                result.sort((a, b) => b.name.localeCompare(a.name))
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
    { key: 'name', label: 'Tag Name' },
    { key: 'color', label: 'Color' },
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
    { label: 'Most Tags', value: 'count_desc' },
    { label: 'Least Tags', value: 'count_asc' },
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

            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-emerald-500">
                            <TagIcon class="size-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                                Property Tags
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Organize and categorize your properties with tags
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="text-xs">
                            {{paginationLinks.total }} tags total
                        </Badge>
                        <Badge
                            variant="outline"
                            class="text-xs bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                        >
                            {{ filteredTags.length }} displayed
                        </Badge>
                    </div>
                </div>

                <div class="shrink-0">
                    <BaseButton @click="router.visit('/tags/create')">
                        <TagIcon class="size-4" />
                        Add New Tag
                    </BaseButton>
                </div>
            </div>

            <SearchFilter
                ref="searchFilterRef"
                search-placeholder="Search tags..."
                :status-options="statusOptions"
                :sort-options="sortOptions"
                :show-sort-dropdown="true"
                @search="handleSearch"
                @filter="handleFilter"
                @sort="handleSort"
                @reset="handleReset"
            />

            <DataTable :columns="columns" :data="tagsData">
                <template #row-actions="{ item }">
                    <Actions :item="item" />
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

            <Pagination
                :meta="paginationLinks"
                @page-change="handlePageChange"
            />
        </div>
    </AppLayout>
</template>