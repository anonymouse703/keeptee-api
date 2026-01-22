<script setup lang="ts">
import { 
    Search, 
    Filter, 
    MoreVertical,
    Eye,
    Edit,
    Trash2,
    Building2,
    MapPin,
    DollarSign,
    Users,
    Calendar,
    Plus,
    Download,
    Printer,
    Mail
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import CustomSelect from '@/components/ui/input/Select.vue'
import AppLayout from '@/layouts/AppLayout.vue'

interface Property {
    id: number
    name: string
    address: string
    type: 'Residential' | 'Commercial' | 'Industrial'
    status: 'Occupied' | 'Vacant' | 'Maintenance'
    monthlyRent: number
    tenants: number
    nextPayment: string
    lastUpdated: string
}

const showFilterDropdown = ref(false);

// Mock data - replace with actual API data
const properties = ref<Property[]>([
    {
        id: 1,
        name: 'Sky Garden Apartments',
        address: '123 Main St, Makati',
        type: 'Residential',
        status: 'Occupied',
        monthlyRent: 45000,
        tenants: 12,
        nextPayment: '2024-01-15',
        lastUpdated: '2024-01-01',
    },
    {
        id: 2,
        name: 'Tech Hub Offices',
        address: '456 BGC, Taguig',
        type: 'Commercial',
        status: 'Occupied',
        monthlyRent: 120000,
        tenants: 8,
        nextPayment: '2024-01-10',
        lastUpdated: '2024-01-02',
    },
    {
        id: 3,
        name: 'Green Valley Condo',
        address: '789 QC Circle',
        type: 'Residential',
        status: 'Vacant',
        monthlyRent: 35000,
        tenants: 0,
        nextPayment: '-',
        lastUpdated: '2023-12-28',
    },
    {
        id: 4,
        name: 'Industrial Warehouse',
        address: '101 Industrial Park',
        type: 'Industrial',
        status: 'Maintenance',
        monthlyRent: 80000,
        tenants: 2,
        nextPayment: '2024-01-20',
        lastUpdated: '2023-12-30',
    },
    {
        id: 5,
        name: 'Seaside Villas',
        address: '345 Coastal Road',
        type: 'Residential',
        status: 'Occupied',
        monthlyRent: 75000,
        tenants: 6,
        nextPayment: '2024-01-05',
        lastUpdated: '2024-01-03',
    },
    {
        id: 6,
        name: 'Business Center',
        address: '678 Ayala Ave',
        type: 'Commercial',
        status: 'Occupied',
        monthlyRent: 95000,
        tenants: 15,
        nextPayment: '2024-01-12',
        lastUpdated: '2024-01-02',
    },
    {
        id: 7,
        name: 'Factory Complex',
        address: '999 Industrial Zone',
        type: 'Industrial',
        status: 'Vacant',
        monthlyRent: 150000,
        tenants: 0,
        nextPayment: '-',
        lastUpdated: '2023-12-25',
    },
    {
        id: 8,
        name: 'Urban Lofts',
        address: '234 Pioneer St',
        type: 'Residential',
        status: 'Occupied',
        monthlyRent: 55000,
        tenants: 10,
        nextPayment: '2024-01-18',
        lastUpdated: '2024-01-04',
    },
])

// Search and Filter States
const searchQuery = ref('')
const selectedType = ref<string>('all')
const selectedStatus = ref<string>('all')
const selectedSort = ref<string | number>('name_asc')
const currentPage = ref(1)
const itemsPerPage = ref<number>(10)


const itemsPerPageOptions: { label: string; value: string | number }[] = [
    { label: '5 per page', value: 5 },
    { label: '10 per page', value: 10 },
    { label: '25 per page', value: 25 },
    { label: '50 per page', value: 50 },
]

// Status colors
const statusColors = {
    Occupied: { bg: 'bg-emerald-500', text: 'text-white' },
    Vacant: { bg: 'bg-amber-500', text: 'text-white' },
    Maintenance: { bg: 'bg-rose-500', text: 'text-white' },
}

// Type colors
const typeColors = {
    Residential: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    Commercial: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
    Industrial: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
}

// Filtered properties
const filteredProperties = computed(() => {
    let filtered = [...properties.value]
    
    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        filtered = filtered.filter(property => 
            property.name.toLowerCase().includes(query) ||
            property.address.toLowerCase().includes(query)
        )
    }
    
    // Type filter
    if (selectedType.value !== 'all') {
        filtered = filtered.filter(property => property.type === selectedType.value)
    }
    
    // Status filter
    if (selectedStatus.value !== 'all') {
        filtered = filtered.filter(property => property.status === selectedStatus.value)
    }
    
    // Sorting
    filtered.sort((a, b) => {
        switch (selectedSort.value) {
            case 'name_asc':
                return a.name.localeCompare(b.name)
            case 'name_desc':
                return b.name.localeCompare(a.name)
            case 'rent_asc':
                return a.monthlyRent - b.monthlyRent
            case 'rent_desc':
                return b.monthlyRent - a.monthlyRent
            case 'date_desc':
                return new Date(b.lastUpdated).getTime() - new Date(a.lastUpdated).getTime()
            default:
                return 0
        }
    })
    
    return filtered
})

// Pagination
const totalPages = computed(() => Math.ceil(filteredProperties.value.length / itemsPerPage.value))
const paginatedProperties = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredProperties.value.slice(start, end)
})

// Actions
const handleView = (property: Property) => {
    console.log('View property:', property)
    // Navigate to property view
}

const handleEdit = (property: Property) => {
    console.log('Edit property:', property)
    // Navigate to property edit
}

const handleDelete = (property: Property) => {
    if (confirm(`Are you sure you want to delete ${property.name}?`)) {
        console.log('Delete property:', property)
        // Delete property
    }
}

const handleAddProperty = () => {
    console.log('Add new property')
    // Navigate to add property form
}

const handleExport = () => {
    console.log('Export properties')
    // Export functionality
}

const handlePrint = () => {
    window.print()
}

const sendReminders = () => {
    console.log('Send payment reminders')
    // Send reminders functionality
}

// Reset filters
const resetFilters = () => {
    searchQuery.value = ''
    selectedType.value = 'all'
    selectedStatus.value = 'all'
    selectedSort.value = 'name_asc'
    currentPage.value = 1
}
</script>

<template>
    <AppLayout>
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Properties
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Manage your real estate properties, view details, and track payments
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" @click="handleExport">
                        <Download class="mr-2 size-4" />
                        Export
                    </Button>
                    <Button variant="outline" @click="handlePrint">
                        <Printer class="mr-2 size-4" />
                        Print
                    </Button>
                    <Button @click="handleAddProperty" class="bg-linear-to-r from-blue-600 to-emerald-600 hover:from-blue-700 hover:to-emerald-700">
                        <Plus class="mr-2 size-4" />
                        Add Property
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Properties</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ properties.length }}</p>
                        </div>
                        <div class="rounded-lg bg-blue-100 dark:bg-blue-900/30 p-3">
                            <Building2 class="size-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Occupied</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ properties.filter(p => p.status === 'Occupied').length }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-emerald-100 dark:bg-emerald-900/30 p-3">
                            <Users class="size-6 text-emerald-600 dark:text-emerald-400" />
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Monthly Revenue</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                ₱{{ properties.reduce((sum, p) => sum + p.monthlyRent, 0).toLocaleString() }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-amber-100 dark:bg-amber-900/30 p-3">
                            <DollarSign class="size-6 text-amber-600 dark:text-amber-400" />
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Tenants</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ properties.reduce((sum, p) => sum + p.tenants, 0) }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-indigo-100 dark:bg-indigo-900/30 p-3">
                            <Users class="size-6 text-indigo-600 dark:text-indigo-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filters -->
            <div class="flex justify-between items-center gap-3">

                <!-- Search -->
                <div class="relative max-w-sm w-full">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                    v-model="searchQuery"
                    placeholder="Search categories..."
                    class="w-full pl-10 pr-4 py-2 border rounded-lg"
                />
                </div>

                <!-- Filter -->
                <div class="relative">
                <Button
                    @click="showFilterDropdown = !showFilterDropdown"
                    class="flex items-center gap-2 px-4 py-2 border rounded-lg"
                >
                    <Filter class="h-4 w-4" />
                    Filter
                </Button>

                <div
                    class="absolute right-0 mt-2 w-72 bg-gray-100 border-gray-500 rounded-lg shadow-lg p-4 z-50"
                >
                    <label class="text-sm text-gray-500">Start Date</label>
                    <input type="date" class="w-full border rounded-lg px-2 py-1 mb-2 text-gray-500" />

                    <label class="text-sm text-gray-500">End Date</label>
                    <input type="date"  class="w-full border rounded-lg px-2 py-1 mb-3 text-gray-500" />

                    <div class="flex gap-2">
                    <Button
                        class="flex-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg py-2"
                    >
                        Apply
                    </Button>

                    <Button
                        class="p-2 border rounded-lg"
                    >
                        <RefreshCcw class="h-4 w-4" />
                    </Button>
                    </div>
                </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm overflow-hidden">
                <!-- Table Header -->
                <div class="border-b border-gray-200 dark:border-gray-800 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Property List
                        </h3>
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            @click="sendReminders"
                            class="text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        >
                            <Mail class="mr-2 size-4" />
                            Send Payment Reminders
                        </Button>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Property</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Type</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Status</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Monthly Rent</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Tenants</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Next Payment</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900 dark:text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            <tr 
                                v-for="property in paginatedProperties" 
                                :key="property.id"
                                class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors duration-150"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-linear-to-br from-blue-500 to-emerald-500">
                                            <Building2 class="size-5 text-white" />
                                        </div>
                                        <div>
                                            <div class="font-medium text-gray-900 dark:text-white">{{ property.name }}</div>
                                            <div class="flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
                                                <MapPin class="size-3.5" />
                                                <span class="truncate max-w-50">{{ property.address }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :class="typeColors[property.type]">
                                        {{ property.type }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge :class="[statusColors[property.status].bg, statusColors[property.status].text]">
                                        {{ property.status }}
                                    </Badge>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <DollarSign class="size-4 text-gray-400" />
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            ₱{{ property.monthlyRent.toLocaleString() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-1">
                                        <Users class="size-4 text-gray-400" />
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ property.tenants }}
                                        </span>
                                        <span class="text-sm text-gray-500">tenants</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="property.nextPayment !== '-'" class="flex items-center gap-1">
                                        <Calendar class="size-4 text-gray-400" />
                                        <span class="text-gray-900 dark:text-white">
                                            {{ property.nextPayment }}
                                        </span>
                                    </div>
                                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            @click="handleView(property)"
                                            class="h-8 w-8 p-0 hover:bg-blue-100 dark:hover:bg-blue-900/30"
                                        >
                                            <Eye class="size-4 text-blue-600 dark:text-blue-400" />
                                        </Button>
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            @click="handleEdit(property)"
                                            class="h-8 w-8 p-0 hover:bg-emerald-100 dark:hover:bg-emerald-900/30"
                                        >
                                            <Edit class="size-4 text-emerald-600 dark:text-emerald-400" />
                                        </Button>
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button 
                                                    variant="ghost" 
                                                    size="sm" 
                                                    class="h-8 w-8 p-0 hover:bg-gray-100 dark:hover:bg-gray-800"
                                                >
                                                    <MoreVertical class="size-4 text-gray-600 dark:text-gray-400" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end">
                                                <DropdownMenuLabel>Actions</DropdownMenuLabel>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem class="cursor-pointer" @click="handleView(property)">
                                                    <Eye class="mr-2 size-4" />
                                                    View Details
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="cursor-pointer">
                                                    <Calendar class="mr-2 size-4" />
                                                    View Schedule
                                                </DropdownMenuItem>
                                                <DropdownMenuItem class="cursor-pointer">
                                                    <Mail class="mr-2 size-4" />
                                                    Send Notice
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem 
                                                    class="cursor-pointer text-red-600 dark:text-red-400"
                                                    @click="handleDelete(property)"
                                                >
                                                    <Trash2 class="mr-2 size-4" />
                                                    Delete Property
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="filteredProperties.length === 0" class="px-6 py-12 text-center">
                    <Building2 class="mx-auto size-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No properties found</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Try adjusting your search or filter to find what you're looking for.
                    </p>
                    <Button @click="resetFilters" class="mt-4">
                        Clear all filters
                    </Button>
                </div>

                <!-- Pagination -->
                <div v-if="filteredProperties.length > 0" class="border-t border-gray-200 dark:border-gray-800 px-6 py-4">
                    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Showing 
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ Math.min((currentPage - 1) * itemsPerPage + 1, filteredProperties.length) }}
                            </span>
                            to 
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ Math.min(currentPage * itemsPerPage, filteredProperties.length) }}
                            </span>
                            of 
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ filteredProperties.length }}
                            </span>
                            results
                        </div>
                        <div class="flex items-center gap-2">
                            <CustomSelect
                                v-model="itemsPerPage"
                                :options="itemsPerPageOptions"
                                :placeholder="`${itemsPerPage} per page`"
                                class="w-25"
                            />
                            <div class="flex items-center gap-1">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="currentPage === 1"
                                    @click="currentPage--"
                                >
                                    Previous
                                </Button>
                                <div class="flex items-center gap-1">
                                    <Button
                                        v-for="page in totalPages"
                                        :key="page"
                                        variant="outline"
                                        size="sm"
                                        :class="[
                                            currentPage === page 
                                                ? 'bg-blue-600 text-white hover:bg-blue-700' 
                                                : ''
                                        ]"
                                        @click="currentPage = page"
                                    >
                                        {{ page }}
                                    </Button>
                                </div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="currentPage === totalPages"
                                    @click="currentPage++"
                                >
                                    Next
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>