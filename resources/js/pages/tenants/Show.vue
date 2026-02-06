<script setup lang="ts">
import { router, Head } from '@inertiajs/vue3'
import {
  ArrowLeft,
  FileText,
  Mail,
  Phone,
  MapPin,
  Download,
  ExternalLink,
  ChevronRight,
} from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BreadcrumbItem } from '@/types'

const props = defineProps<{
  tenant: any
}>()

const tenantData = computed(() => props.tenant.data ?? {})

const isLoaded = ref(false)

onMounted(() => {
  setTimeout(() => (isLoaded.value = true), 100)
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tenants', href: '/tenants' },
  { title: tenantData.value.name || 'Tenant Details', href: `/tenants/${tenantData.value.id}` },
]

const handleBack = () => router.visit('/tenants')

/* Dates */
const formattedDate = computed(() =>
  tenantData.value.created_at
    ? new Date(tenantData.value.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      })
    : '-'
)

/* Address */
const fullAddress = computed(() => tenantData.value.address || '')

/* Files (normalized) */
const tenantFiles = computed(() => {
  if (!Array.isArray(tenantData.value.files)) return []

  return tenantData.value.files.map((file: any, index: number) => {
    const ext = file.type?.split('/').pop() || 'file'
    return {
      id: index,
      name: `Document ${index + 1}.${ext}`,
      url: file.url,
      type: file.type,
      created_at: tenantData.value.created_at,
    }
  })
})

/* File helpers */
const previewFile = (file: any) => {
  window.open(file.url, '_blank')
}

const downloadFile = (file: any) => {
  const link = document.createElement('a')
  link.href = file.url
  link.download = file.name
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}
</script>
<template>
  <Head :title="`${tenantData.name || 'Tenant'} Details`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-linear-to-b from-gray-50 to-white">
      <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- Breadcrumb -->
        <div class="flex items-center text-sm mb-6">
          <button @click="handleBack" class="flex items-center gap-2 text-gray-500 hover:text-primary">
            <ArrowLeft class="w-4 h-4" /> Tenants
          </button>
          <ChevronRight class="w-4 h-4 mx-2 text-gray-400" />
          <span class="font-medium text-gray-800">{{ tenantData.name }}</span>
        </div>

        <!-- Header -->
        <div class="flex flex-col lg:flex-row justify-between gap-6 mb-10">
          <div>
            <h1 class="text-4xl font-bold text-gray-900">
              {{ tenantData.name || 'Unnamed Tenant' }}
            </h1>

            <div class="mt-3 flex items-center gap-3 text-gray-600">
              <Mail class="w-5 h-5 text-primary" />
              {{ tenantData.email || 'No email provided' }}
            </div>
          </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

          <!-- Left -->
          <div class="lg:col-span-2 space-y-8">

            <!-- Personal Info -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border">
              <h2 class="text-2xl font-bold mb-6">Personal Information</h2>

              <div class="grid md:grid-cols-2 gap-6">
                <div>
                  <div class="flex items-center gap-2 text-gray-500 text-sm">
                    <Mail class="w-4 h-4" /> Email
                  </div>
                  <p class="font-medium text-gray-900">{{ tenantData.email || '—' }}</p>
                </div>

                <div>
                  <div class="flex items-center gap-2 text-gray-500 text-sm">
                    <Phone class="w-4 h-4" /> Phone
                  </div>
                  <p class="font-medium text-gray-900">{{ tenantData.phone || '—' }}</p>
                </div>
              </div>
            </div>

            <!-- Address -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border">
              <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                <MapPin class="w-5 h-5 text-primary" /> Address
              </h2>

              <p v-if="fullAddress" class="text-gray-900">{{ fullAddress }}</p>
              <p v-else class="italic text-gray-500">No address provided</p>
            </div>

            <!-- Documents -->
            <div class="bg-white rounded-2xl shadow-xl p-8 border">
              <h2 class="text-2xl font-bold mb-6">Attached Documents</h2>

              <div v-if="tenantFiles.length" class="grid md:grid-cols-2 gap-6">
                <div
                  v-for="file in tenantFiles"
                  :key="file.id"
                  class="rounded-xl border p-6 hover:shadow-lg transition"
                >
                  <div class="flex items-center gap-3 mb-4">
                    <FileText class="w-6 h-6 text-primary" />
                    <div>
                      <p class="font-semibold text-gray-900">{{ file.name }}</p>
                      <p class="text-xs text-gray-500">{{ file.type }}</p>
                    </div>
                  </div>

                  <div class="flex gap-3">
                    <BaseButton
                      type="button"
                      @click="previewFile(file)"
                      class="flex-1 flex items-center justify-center gap-2 rounded-lg"
                    >
                      <ExternalLink class="w-4 h-4" />
                      Preview
                    </BaseButton>

                    <button
                      @click="downloadFile(file)"
                      class="flex-1 flex items-center justify-center gap-2 rounded-lg border py-2"
                    >
                      <Download class="w-4 h-4" />
                      Download
                    </button>
                  </div>
                </div>
              </div>

              <div v-else class="text-center py-12 text-gray-500">
                No documents uploaded
              </div>
            </div>
          </div>

          <!-- Right -->
          <div>
            <div class="bg-white rounded-2xl shadow-xl p-6 border">
              <h2 class="text-xl font-bold mb-4">Meta</h2>

              <div class="text-sm text-gray-500">Created On</div>
              <div class="font-semibold text-gray-900">{{ formattedDate }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
