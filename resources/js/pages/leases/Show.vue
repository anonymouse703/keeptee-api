<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { 
  Users, 
  ArrowLeft,
  User,
  Mail,
  Phone,
  Calendar,
  Building,
  MapPin,
  FileText,
  Clock,
  BadgeCheck,
  Edit,
  Home,
  Globe,
  Key,
  CheckCircle,
  AlertCircle
} from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'

import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

const props = defineProps<{
  tenant: object
}>()

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Tenants', href: '/tenants' },
  { title: tenantData.value?.name || 'Tenant Details', href: `/tenants/${tenantData.value?.id}` },
])

// Convert Proxy to plain object for easier access
const tenantData = computed(() => {
  if (!props.tenant) return null
  
  // Handle Inertia's proxy object
  const data = JSON.parse(JSON.stringify(props.tenant))
  return data
})

const isLoaded = ref(false)

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true
  }, 100)
})

const goBack = () => history.back()

const formatDate = (dateString: string) => {
  if (!dateString) return 'Not specified'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount: number) => {
  if (!amount) return '$0'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(amount)
}

// Lease duration calculation
const leaseDuration = computed(() => {
  if (!tenantData.value?.lease_start || !tenantData.value?.lease_end) return 'Not specified'
  
  const start = new Date(tenantData.value.lease_start)
  const end = new Date(tenantData.value.lease_end)
  const diffTime = Math.abs(end.getTime() - start.getTime())
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  const diffMonths = Math.floor(diffDays / 30)
  
  if (diffMonths >= 12) {
    const years = Math.floor(diffMonths / 12)
    const remainingMonths = diffMonths % 12
    return `${years} year${years > 1 ? 's' : ''}${remainingMonths > 0 ? ` ${remainingMonths} month${remainingMonths > 1 ? 's' : ''}` : ''}`
  }
  
  return `${diffMonths} month${diffMonths > 1 ? 's' : ''}`
})

// Check if lease is active
const isLeaseActive = computed(() => {
  if (!tenantData.value?.lease_end) return false
  const endDate = new Date(tenantData.value.lease_end)
  const today = new Date()
  return endDate > today
})

// Property address formatter
const propertyAddress = computed(() => {
  if (!tenantData.value?.property) return 'Not assigned'
  
  const prop = tenantData.value.property
  const parts = []
  if (prop.address) parts.push(prop.address)
  if (prop.city) parts.push(prop.city)
  if (prop.state) parts.push(prop.state)
  if (prop.country) parts.push(prop.country)
  
  return parts.join(', ')
})

// Status badge color
const statusColor = computed(() => {
  if (isLeaseActive.value) {
    return {
      bg: 'bg-linear-to-r from-green-50 to-green-100',
      text: 'text-green-800',
      border: 'border-green-200',
      icon: CheckCircle
    }
  }
  return {
    bg: 'bg-linear-to-r from-orange-50 to-orange-100',
    text: 'text-orange-800',
    border: 'border-orange-200',
    icon: AlertCircle
  }
})
</script>

<template>
  <Head :title="tenantData?.name || 'Tenant Details'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-linear-to-br from-gray-50 via-white to-blue-50/30 p-4">
      <div class="max-w-6xl mx-auto">
        <!-- Back Button -->
        <div 
          class="mb-8 transition-all duration-700 ease-out"
          :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4'"
        >
          <button 
            @click="goBack"
            class="group flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300 transition-all duration-300 hover:-translate-x-1"
          >
            <div class="p-2 bg-white rounded-lg shadow-sm group-hover:shadow-md group-hover:bg-blue-50 transition-all duration-300">
              <ArrowLeft class="h-4 w-4" />
            </div>
            <span class="font-medium">Back to Tenants</span>
          </button>
        </div>

        <!-- Header Section -->
        <div 
          class="mb-10 transition-all duration-1000 ease-out"
          :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
        >
          <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <div class="space-y-4">
              <div class="flex items-center gap-4">
                <div class="relative">
                  <div class="absolute -inset-4 bg-linear-to-r from-blue-500 to-blue-600 rounded-full opacity-20 blur-xl"></div>
                  <div class="relative p-4 bg-linear-to-br from-blue-500 to-blue-600 rounded-2xl shadow-2xl">
                    <Users class="h-8 w-8 text-white" />
                  </div>
                </div>
                <div>
                  <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white">
                    {{ tenantData?.name || 'No Name' }}
                  </h1>
                  <div class="flex items-center gap-2 mt-2">
                    <BadgeCheck class="h-5 w-5 text-green-500" />
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-medium">
                      Tenant ID: {{ tenantData?.id || 'N/A' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex items-center gap-4">
              <div class="hidden lg:flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                <Clock class="h-4 w-4" />
                <span>Updated {{ formatDate(tenantData?.updated_at) }}</span>
              </div>
              <button 
                class="group flex items-center gap-2 px-4 py-2 bg-linear-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-300 hover:-translate-y-0.5"
              >
                <Edit class="h-4 w-4" />
                <span class="font-medium">Edit Tenant</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Left Column - Tenant Information -->
          <div 
            class="lg:col-span-2 space-y-8 transition-all duration-1000 ease-out delay-300"
            :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
          >
            <!-- Lease Status Card -->
            <div class="bg-linear-to-br from-white to-gray-50 rounded-3xl shadow-xl p-8 border border-gray-100 hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <h2 class="text-2xl font-bold mb-6 text-gray-900 flex items-center gap-3">
                <div class="p-2 bg-linear-to-br from-blue-100 to-blue-200 rounded-lg">
                  <Key class="h-5 w-5 text-blue-600" />
                </div>
                Lease Information
              </h2>
              
              <div class="space-y-6">
                <!-- Lease Status Badge -->
                <div class="flex items-center justify-between p-5 rounded-2xl" :class="[statusColor.bg, statusColor.border, statusColor.text]">
                  <div class="flex items-center gap-4">
                    <component :is="statusColor.icon" class="h-6 w-6" />
                    <div>
                      <div class="font-bold text-lg">{{ isLeaseActive ? 'Lease Active' : 'Lease Expired' }}</div>
                      <div class="text-sm opacity-90">{{ leaseDuration }}</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-lg">{{ formatDate(tenantData?.lease_start) }}</div>
                    <div class="text-sm opacity-90">to {{ formatDate(tenantData?.lease_end) }}</div>
                  </div>
                </div>

                <!-- Lease Dates Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="space-y-2 p-4 rounded-xl hover:bg-linear-to-br hover:from-white hover:to-blue-50 transition-all duration-500 hover:-translate-y-1 hover:shadow-lg border border-transparent hover:border-blue-100">
                    <div class="flex items-center gap-3 text-gray-600 text-sm font-medium">
                      <div class="p-2 bg-blue-100 rounded-lg">
                        <Calendar class="h-4 w-4 text-blue-600" />
                      </div>
                      <span>Lease Start Date</span>
                    </div>
                    <div class="font-bold text-gray-900 text-lg pl-11">{{ formatDate(tenantData?.lease_start) }}</div>
                  </div>

                  <div class="space-y-2 p-4 rounded-xl hover:bg-linear-to-br hover:from-white hover:to-blue-50 transition-all duration-500 hover:-translate-y-1 hover:shadow-lg border border-transparent hover:border-blue-100">
                    <div class="flex items-center gap-3 text-gray-600 text-sm font-medium">
                      <div class="p-2 bg-blue-100 rounded-lg">
                        <Calendar class="h-4 w-4 text-blue-600" />
                      </div>
                      <span>Lease End Date</span>
                    </div>
                    <div class="font-bold text-gray-900 text-lg pl-11">{{ formatDate(tenantData?.lease_end) }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Information Card -->
            <div class="bg-linear-to-br from-white to-gray-50 rounded-3xl shadow-xl p-8 border border-gray-100 hover:shadow-2xl transition-all duration-500 hover:-translate-y-1">
              <h2 class="text-2xl font-bold mb-6 text-gray-900 flex items-center gap-3">
                <div class="p-2 bg-linear-to-br from-green-100 to-green-200 rounded-lg">
                  <User class="h-5 w-5 text-green-600" />
                </div>
                Contact Information
              </h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Email -->
                <div class="space-y-2 p-4 rounded-xl hover:bg-linear-to-br hover:from-white hover:to-green-50 transition-all duration-500 hover:-translate-y-1 hover:shadow-lg border border-transparent hover:border-green-100">
                  <div class="flex items-center gap-3 text-gray-600 text-sm font-medium">
                    <div class="p-2 bg-green-100 rounded-lg">
                      <Mail class="h-4 w-4 text-green-600" />
                    </div>
                    <span>Email Address</span>
                  </div>
                  <div class="font-bold text-gray-900 text-lg pl-11">{{ tenantData?.email || 'Not provided' }}</div>
                </div>

                <!-- Phone -->
                <div class="space-y-2 p-4 rounded-xl hover:bg-linear-to-br hover:from-white hover:to-green-50 transition-all duration-500 hover:-translate-y-1 hover:shadow-lg border border-transparent hover:border-green-100">
                  <div class="flex items-center gap-3 text-gray-600 text-sm font-medium">
                    <div class="p-2 bg-green-100 rounded-lg">
                      <Phone class="h-4 w-4 text-green-600" />
                    </div>
                    <span>Phone Number</span>
                  </div>
                  <div class="font-bold text-gray-900 text-lg pl-11">{{ tenantData?.phone || 'Not provided' }}</div>
                </div>

                <!-- Created Date -->
                <div class="space-y-2 p-4 rounded-xl hover:bg-linear-to-br hover:from-white hover:to-green-50 transition-all duration-500 hover:-translate-y-1 hover:shadow-lg border border-transparent hover:border-green-100">
                  <div class="flex items-center gap-3 text-gray-600 text-sm font-medium">
                    <div class="p-2 bg-green-100 rounded-lg">
                      <Calendar class="h-4 w-4 text-green-600" />
                    </div>
                    <span>Member Since</span>
                  </div>
                  <div class="font-bold text-gray-900 text-lg pl-11">{{ formatDate(tenantData?.created_at) }}</div>
                </div>

                <!-- Last Updated -->
                <div class="space-y-2 p-4 rounded-xl hover:bg-linear-to-br hover:from-white hover:to-green-50 transition-all duration-500 hover:-translate-y-1 hover:shadow-lg border border-transparent hover:border-green-100">
                  <div class="flex items-center gap-3 text-gray-600 text-sm font-medium">
                    <div class="p-2 bg-green-100 rounded-lg">
                      <Clock class="h-4 w-4 text-green-600" />
                    </div>
                    <span>Last Updated</span>
                  </div>
                  <div class="font-bold text-gray-900 text-lg pl-11">{{ formatDate(tenantData?.updated_at) }}</div>
                </div>
              </div>
            </div>

            <!-- Property Information Card -->
            <div 
              v-if="tenantData?.property"
              class="bg-linear-to-br from-white to-gray-50 rounded-3xl shadow-xl p-8 border border-gray-100 hover:shadow-2xl transition-all duration-500 hover:-translate-y-1"
            >
              <h2 class="text-2xl font-bold mb-6 text-gray-900 flex items-center gap-3">
                <div class="p-2 bg-linear-to-br from-purple-100 to-purple-200 rounded-lg">
                  <Home class="h-5 w-5 text-purple-600" />
                </div>
                Assigned Property
              </h2>
              
              <div class="space-y-6">
                <div class="flex items-start gap-4 p-6 rounded-2xl bg-linear-to-r from-purple-50 to-white border border-purple-100">
                  <div class="p-3 bg-linear-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg">
                    <Building class="h-6 w-6 text-white" />
                  </div>
                  <div class="flex-1">
                    <div class="flex justify-between items-start">
                      <div>
                        <div class="text-xl font-bold text-gray-900 mb-1">{{ tenantData.property.title }}</div>
                        <div class="flex items-center gap-2 text-gray-600">
                          <MapPin class="h-4 w-4" />
                          <span>{{ propertyAddress }}</span>
                        </div>
                      </div>
                      <span class="px-3 py-1 rounded-full text-sm font-semibold" :class="{
                        'bg-green-100 text-green-800': tenantData.property.status === 'rented',
                        'bg-blue-100 text-blue-800': tenantData.property.status === 'for_rent',
                        'bg-gray-100 text-gray-800': tenantData.property.status === 'sold'
                      }">
                        {{ tenantData.property.status === 'rented' ? 'Rented' : tenantData.property.status }}
                      </span>
                    </div>
                    <p class="text-gray-700 mt-3">{{ tenantData.property.description }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div class="text-center p-4 rounded-xl bg-linear-to-br from-white to-purple-50 border border-purple-100">
                    <div class="font-bold text-2xl text-gray-900 mb-1">{{ tenantData.property.bedrooms || '-' }}</div>
                    <div class="text-xs text-gray-600 font-medium">Bedrooms</div>
                  </div>
                  <div class="text-center p-4 rounded-xl bg-linear-to-br from-white to-purple-50 border border-purple-100">
                    <div class="font-bold text-2xl text-gray-900 mb-1">{{ tenantData.property.bathrooms || '-' }}</div>
                    <div class="text-xs text-gray-600 font-medium">Bathrooms</div>
                  </div>
                  <div class="text-center p-4 rounded-xl bg-linear-to-br from-white to-purple-50 border border-purple-100">
                    <div class="font-bold text-2xl text-gray-900 mb-1">{{ tenantData.property.floor_area || '-' }}</div>
                    <div class="text-xs text-gray-600 font-medium">SQM</div>
                  </div>
                  <div class="text-center p-4 rounded-xl bg-linear-to-br from-white to-purple-50 border border-purple-100">
                    <div class="font-bold text-xl text-gray-900 mb-1">{{ formatCurrency(tenantData.property.price) }}</div>
                    <div class="text-xs text-gray-600 font-medium">Monthly Rent</div>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-4 rounded-xl bg-linear-to-br from-white to-purple-50 border border-purple-100">
                    <div class="font-bold text-xl text-gray-900 mb-1 capitalize">{{ tenantData.property.property_type }}</div>
                    <div class="text-xs text-gray-600 font-medium">Property Type</div>
                  </div>
                  <div class="text-center p-4 rounded-xl bg-linear-to-br from-white to-purple-50 border border-purple-100">
                    <div class="font-bold text-xl text-gray-900 mb-1">
                      {{ tenantData.property.is_active ? 'Active' : 'Inactive' }}
                    </div>
                    <div class="text-xs text-gray-600 font-medium">Status</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Stats & Actions -->
          <div 
            class="space-y-8 transition-all duration-1000 ease-out delay-500"
            :class="isLoaded ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-8'"
          >
            <!-- Lease Status Card -->
            <div class="bg-linear-to-br from-white to-blue-50 rounded-3xl shadow-xl p-8 border border-blue-100">
              <h2 class="text-xl font-bold mb-6 text-gray-900">Lease Status</h2>
              
              <div class="space-y-6">
                <div class="flex items-center justify-between p-5 rounded-2xl" :class="[statusColor.bg, statusColor.border]">
                  <div class="flex items-center gap-4">
                    <component :is="statusColor.icon" class="h-6 w-6" :class="statusColor.text" />
                    <div>
                      <div class="font-bold text-lg" :class="statusColor.text">{{ isLeaseActive ? 'Lease Active' : 'Lease Expired' }}</div>
                      <div class="text-sm" :class="statusColor.text + '/90'">{{ leaseDuration }}</div>
                    </div>
                  </div>
                  <div class="h-3 w-3 rounded-full" :class="isLeaseActive ? 'bg-green-500 animate-pulse' : 'bg-orange-500'"></div>
                </div>

                <!-- Days Remaining -->
                <div class="text-center p-6 rounded-2xl bg-linear-to-br from-blue-50 to-white border border-blue-200">
                  <div 
                    v-if="tenantData?.lease_end"
                    class="text-4xl font-bold mb-2"
                    :class="isLeaseActive ? 'text-green-600' : 'text-orange-600'"
                  >
                    {{
                      (() => {
                        const endDate = new Date(tenantData.lease_end)
                        const today = new Date()
                        const diffTime = endDate.getTime() - today.getTime()
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
                        return diffDays > 0 ? diffDays : Math.abs(diffDays)
                      })()
                    }}
                  </div>
                  <div 
                    class="text-sm"
                    :class="isLeaseActive ? 'text-green-600' : 'text-orange-600'"
                  >
                    {{ isLeaseActive ? 'Days Remaining' : 'Days Since Expiry' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="bg-linear-to-br from-white to-gray-50 rounded-3xl shadow-xl p-8 border border-gray-100">
              <h2 class="text-xl font-bold mb-6 text-gray-900">Quick Actions</h2>
              
              <div class="space-y-3">
                <button 
                  v-if="tenantData?.email"
                  class="w-full flex items-center gap-3 p-4 rounded-xl bg-linear-to-r from-blue-50 to-white hover:from-blue-100 hover:to-white border border-blue-200 hover:border-blue-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                  <Mail class="h-5 w-5 text-blue-600" />
                  <span class="font-medium text-gray-900">Send Email</span>
                </button>
                
                <button 
                  v-if="tenantData?.phone"
                  class="w-full flex items-center gap-3 p-4 rounded-xl bg-linear-to-r from-green-50 to-white hover:from-green-100 hover:to-white border border-green-200 hover:border-green-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                  <Phone class="h-5 w-5 text-green-600" />
                  <span class="font-medium text-gray-900">Call Tenant</span>
                </button>
                
                <button 
                  class="w-full flex items-center gap-3 p-4 rounded-xl bg-linear-to-r from-purple-50 to-white hover:from-purple-100 hover:to-white border border-purple-200 hover:border-purple-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                  <FileText class="h-5 w-5 text-purple-600" />
                  <span class="font-medium text-gray-900">View Documents</span>
                </button>

                <button 
                  class="w-full flex items-center gap-3 p-4 rounded-xl bg-linear-to-r from-orange-50 to-white hover:from-orange-100 hover:to-white border border-orange-200 hover:border-orange-300 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                  <Edit class="h-5 w-5 text-orange-600" />
                  <span class="font-medium text-gray-900">Renew Lease</span>
                </button>
              </div>
            </div>

            <!-- System Information Card -->
            <div class="bg-linear-to-br from-white to-gray-50 rounded-3xl shadow-xl p-8 border border-gray-100">
              <h2 class="text-xl font-bold mb-6 text-gray-900 flex items-center gap-3">
                <Globe class="h-5 w-5 text-gray-600" />
                System Information
              </h2>
              
              <div class="space-y-4">
                <div class="flex justify-between items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                  <span class="text-sm text-gray-600">Tenant ID</span>
                  <span class="font-mono font-bold text-gray-900">{{ tenantData?.id || 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                  <span class="text-sm text-gray-600">Property ID</span>
                  <span class="font-mono font-bold text-gray-900">{{ tenantData?.property_id || 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                  <span class="text-sm text-gray-600">Created</span>
                  <span class="font-medium text-gray-900">{{ formatDate(tenantData?.created_at) }}</span>
                </div>
                <div class="flex justify-between items-center p-3 rounded-lg hover:bg-gray-50 transition-colors">
                  <span class="text-sm text-gray-600">Last Updated</span>
                  <span class="font-medium text-gray-900">{{ formatDate(tenantData?.updated_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Floating Decorative Elements -->
        <div class="fixed -z-10 top-0 right-0 w-96 h-96 bg-linear-to-br from-blue-300/10 to-blue-400/5 rounded-full blur-3xl"></div>
        <div class="fixed -z-10 bottom-0 left-0 w-72 h-72 bg-linear-to-br from-purple-200/10 to-purple-300/5 rounded-full blur-3xl"></div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for any scrollable content */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(243, 244, 246, 0.8);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-linear(180deg, var(--color-blue-500), var(--color-blue-600));
  border-radius: 10px;
  transition: all 0.3s ease;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-linear(180deg, var(--color-blue-600), var(--color-blue-700));
}

/* Smooth transitions for all interactive elements */
button, .hover-lift {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), 
              box-shadow 0.3s ease,
              background-color 0.3s ease,
              border-color 0.3s ease;
}

/* Card hover effect */
.hover-lift:hover {
  transform: translateY(-4px);
}

/* linear text animation */
@keyframes linear-shift {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.bg-linear-animate {
  background-size: 200% 200%;
  animation: linear-shift 3s ease infinite;
}

/* Pulse animation for status indicators */
@keyframes pulse-glow {
  0%, 100% {
    opacity: 1;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  }
  50% {
    opacity: 0.8;
    box-shadow: 0 0 0 10px rgba(34, 197, 94, 0);
  }
}

.animate-pulse {
  animation: pulse-glow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Enhanced backdrop blur */
.backdrop-blur-lg {
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
</style>