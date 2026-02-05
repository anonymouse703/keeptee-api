<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { Users } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import { Badge } from '@/components/ui/badge'
import BaseButton from '@/components/ui/button/BaseButton.vue'
import DataTable from '@/components/ui/table/DataTable.vue'
import Pagination from '@/components/ui/table/Pagination.vue'
import AppLayout from '@/layouts/AppLayout.vue'

import Actions from './partials/Actions.vue'
import SearchFilter from './partials/SearchFilter.vue'


const props = defineProps({
    tenants: Object,
})

const tenantsData = computed(() => props.tenants?.data ?? [])

const paginationLinks = computed(() => props.tenants?.meta ?? {})

const searchFilterRef = ref<InstanceType<typeof SearchFilter> | null>(null)

const filteredTenants = computed(() => {
    if (!searchFilterRef.value) return tenantsData.value
    const filters = searchFilterRef.value.filters
    let result = [...tenantsData.value]

    if (filters.search) {
        const q = filters.search.toLowerCase()
        result = result.filter(tenant =>
            tenant.name.toLowerCase().includes(q)
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
                result.sort((a, b) => b.tenant_counts - a.tenant_counts)
                break
            case 'count_asc':
                result.sort((a, b) => a.tenant_counts - b.tenant_counts)
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
    { key: 'name', label: 'Tenant Name' },
    { key: 'email', label: 'Email' },
    { key: 'phone', label: 'Phone' },
    { key: 'address', label: 'Address' },
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
    { label: 'Most Tenants', value: 'count_desc' },
    { label: 'Least Tenants', value: 'count_asc' },
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
                            <Users class="size-5 text-white" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">
                                Tenant
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                Manage your tenants here.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <Badge variant="outline" class="text-xs">
                            {{ paginationLinks.total }} tenants total
                        </Badge>
                        <Badge
                            variant="outline"
                            class="text-xs bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300"
                        >
                            {{ filteredTenants.length }} displayed
                        </Badge>
                    </div>
                </div>

                <div class="shrink-0">
                    <BaseButton @click="router.visit('/tenants/create')">
                        <Users class="size-4" />
                        Add New Tenant
                    </BaseButton>
                </div>
            </div>

            <SearchFilter
                ref="searchFilterRef"
                search-placeholder="Search tenants..."
                :status-options="statusOptions"
                :sort-options="sortOptions"
                :show-sort-dropdown="true"
                @search="handleSearch"
                @filter="handleFilter"
                @sort="handleSort"
                @reset="handleReset"
            />
           
            <DataTable :columns="columns" :data="filteredTenants">
                <template #row-actions="{ item }">
                    <Actions :item="item" />
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