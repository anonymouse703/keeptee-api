<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { FileText } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import { Badge } from '@/components/ui/badge'
import BaseButton from '@/components/ui/button/BaseButton.vue'
import DataTable from '@/components/ui/table/DataTable.vue'
import Pagination from '@/components/ui/table/Pagination.vue'
import AppLayout from '@/layouts/AppLayout.vue'

import { type TagListItem } from '../../../types/type'

import Actions from './partials/Actions.vue'
import SearchFilter from './partials/SearchFilter.vue'

const props = defineProps({
    leases: Object,
})

const leasesData = computed(() => props.leases?.data ?? [])

const paginationLinks = computed(() => props.leases?.meta ?? {})

const searchFilterRef = ref<InstanceType<typeof SearchFilter> | null>(null)

const filteredLeases = computed<TagListItem[]>(() => {
    if (!searchFilterRef.value) return leasesData.value

    const filters = searchFilterRef.value.filters
    let result = [...leasesData.value]

    // Search
    if (filters.search) {
        const q = filters.search.toLowerCase()
        result = result.filter(lease =>
            lease.name.toLowerCase().includes(q)
        )
    }

    // Sorting
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
    { key: 'property', label: 'Property Name' },
    { key: 'tenant', label: 'Tenant Name' },
    { key: 'monthly_rate', label: 'Monthly Rate' },
    { key: 'start_date', label: 'Start Date' },
    { key: 'end_date', label: 'End Date' },
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
            <!-- Header -->
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-linear-to-br from-blue-500 to-emerald-500">
                            <FileText class="size-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                                Property Leases
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Manage and organize your property leases
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="text-xs">
                            {{paginationLinks.total }} leases total
                        </Badge>
                        <Badge
                            variant="outline"
                            class="text-xs bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                        >
                            {{ filteredLeases.length }} displayed
                        </Badge>
                    </div>
                </div>

                <div class="shrink-0">
                    <BaseButton @click="router.visit('/leases/create')">
                        <FileText class="size-4" />
                        Add New Lease
                    </BaseButton>
                </div>
            </div>

            <!-- Search -->
            <SearchFilter
                ref="searchFilterRef"
                search-placeholder="Search leases..."
                :status-options="statusOptions"
                :sort-options="sortOptions"
                :show-sort-dropdown="true"
                @search="handleSearch"
                @filter="handleFilter"
                @sort="handleSort"
                @reset="handleReset"
            />

            <!-- Data Table -->
            <DataTable :columns="columns" :data="filteredLeases">
                <template #row-actions="{ item }">
                    <Actions :item="item" />
                </template>
                 
                <template #cell-property="{ item }">
                    {{ item.property?.title ?? '—' }}
                </template>

                <template #cell-tenant="{ item }">
                    {{ item.tenant?.name ?? '—' }}
                </template>

                <template #cell-status="{ item }">
                    <span
                        class="px-2 py-1 rounded text-xs font-semibold"
                        :class="{
                        'bg-green-100 text-green-800': item.status === 'Active',
                        'bg-gray-100 text-gray-800': item.status === 'Ended',
                        'bg-red-100 text-red-800': item.status === 'Terminated'
                        }"
                    >
                        {{ item.status }}
                    </span>
                </template>

            </DataTable>

            <!-- Pagination -->
            <Pagination
                :meta="paginationLinks"
                @page-change="handlePageChange"
            />
        </div>
    </AppLayout>
</template>