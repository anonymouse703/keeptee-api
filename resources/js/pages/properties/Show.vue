<script setup lang="ts">
import {
  MapPin,
  Bed,
  Square,
  ChevronRight,
  Building,
  BathIcon
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

import { Property, PropertyImage } from '../../../types/type'

const props = defineProps<{
  property: { data: Property } // note the 'data' wrapper
}>()

// Destructure the 'data' for easier access
const property = computed(() => props.property.data)

console.log(property.value)

const activeImageIndex = ref(0)

const propertyImages = computed<PropertyImage[]>(() => {
  if (!property.value.images?.length) return []

  return [...(property.value.images as PropertyImage[])]
    .sort((a, b) => a.sort_order - b.sort_order)
})

const activeImage = computed<PropertyImage | null>(() => {
  return propertyImages.value[activeImageIndex.value] ?? null
})

const formattedPrice = computed(() =>
  property.value.price
    ? new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
      }).format(property.value.price)
    : '-'
)

const formattedDate = computed(() =>
  new Date(property.value.created_at ?? Date.now()).toLocaleDateString(
    'en-US',
    { year: 'numeric', month: 'long', day: 'numeric' }
  )
)

const statusLabel = computed(
  () =>
    ({
      for_sale: 'For Sale',
      for_rent: 'For Rent',
      sold: 'Sold',
      rented: 'Rented',
    }[property.value.status] ?? property.value.status)
)

const fullAddress = computed(
  () =>
    `${property.value.address}, ${property.value.city}, ${property.value.state}, ${property.value.country}`
)
</script>

<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <div class="flex items-center text-sm text-gray-500 mb-4">
      <span>Properties</span>
      <ChevronRight class="w-4 h-4 mx-2" />
      <span class="text-gray-800 font-medium">{{ property.title }}</span>
    </div>

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between gap-6 mb-8">
      <div>
        <h1 class="text-3xl font-bold mb-2">{{ property.title }}</h1>
        <div class="flex items-center text-gray-600">
          <MapPin class="w-4 h-4 mr-1" />
          {{ fullAddress }}
        </div>
      </div>

      <div class="text-right">
        <div class="text-3xl font-bold text-primary">
          {{ formattedPrice }}
        </div>
        <div class="text-sm text-gray-500">
          {{ statusLabel }} • Listed {{ formattedDate }}
        </div>
      </div>
    </div>

    <!-- Gallery and Property Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
      <!-- Main Gallery -->
      <div class="lg:col-span-2">
        <img
          v-if="activeImage"
          :src="activeImage.image_url"
          :alt="property.title"
          class="w-full h-105 object-cover rounded-xl"
        />

        <div class="flex gap-3 mt-4 overflow-x-auto">
          <img
            v-for="(img, index) in propertyImages"
            :key="img.id"
            :src="img.thumbnail_url ?? img.image_url"
            :alt="`${property.title} - Image ${index + 1}`"
            class="w-20 h-20 object-cover rounded-lg cursor-pointer border"
            :class="index === activeImageIndex ? 'border-primary' : 'border-transparent'"
            @click="activeImageIndex = index"
          />
        </div>
      </div>

      <!-- Property Summary -->
      <div class="border rounded-xl p-6 space-y-6">
        <h2 class="text-xl font-semibold">Property Details</h2>
        
        <div class="grid grid-cols-2 gap-6">
          <div class="space-y-1">
            <div class="text-sm text-gray-500">Property Type</div>
            <div class="font-semibold capitalize">{{ property.property_type }}</div>
          </div>
          
          <div class="space-y-1">
            <div class="text-sm text-gray-500">Status</div>
            <div class="font-semibold">{{ statusLabel }}</div>
          </div>
          
          <div class="space-y-1">
            <div class="text-sm text-gray-500">Listed Date</div>
            <div class="font-semibold">{{ formattedDate }}</div>
          </div>
          
          <div class="space-y-1">
            <div class="text-sm text-gray-500">Price</div>
            <div class="font-semibold">{{ formattedPrice }}</div>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-4 text-center pt-4 border-t">
          <div>
            <Bed class="mx-auto mb-1 text-gray-600" />
            <div class="font-semibold text-lg">{{ property.bedrooms ?? '-' }}</div>
            <div class="text-xs text-gray-500">Bedrooms</div>
          </div>
          <div>
            <BathIcon class="mx-auto mb-1 text-gray-600" />
            <div class="font-semibold text-lg">{{ property.bathrooms ?? '-' }}</div>
            <div class="text-xs text-gray-500">Bathrooms</div>
          </div>
          <div>
            <Square class="mx-auto mb-1 text-gray-600" />
            <div class="font-semibold text-lg">{{ property.floor_area ?? '-' }}</div>
            <div class="text-xs text-gray-500">sqm</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Description -->
    <div class="mb-10">
      <h2 class="text-xl font-semibold mb-3">Description</h2>
      <p class="text-gray-700 leading-relaxed">
        {{ property.description }}
      </p>
    </div>

    <!-- Owner Information -->
    <div v-if="property.owner" class="border rounded-xl p-6 mb-6">
      <h2 class="text-xl font-semibold mb-4">Property Owner</h2>
      <div class="flex items-center gap-4">
        <Building class="w-10 h-10 text-primary" />
        <div>
          <div class="font-semibold">{{ property.owner.name }}</div>
          <div class="text-sm text-gray-500">{{ property.owner.email }}</div>
          <div v-if="property.owner.phone" class="text-sm text-gray-500">
            {{ property.owner.phone }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
