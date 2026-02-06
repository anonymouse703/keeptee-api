<script setup lang="ts">
import {
  MapPin,
  Bed,
  Square,
  ChevronRight,
  Building,
  BathIcon,
  Calendar,
  Tag,
  Home,
  DollarSign
} from 'lucide-vue-next'
import { ref, computed, onMounted } from 'vue'

const props = defineProps<{
  property: any
}>()

console.log(props.property)

const property = computed(() => props.property.data)
const activeImageIndex = ref(0)
const isLoaded = ref(false)

onMounted(() => {
  setTimeout(() => {
    isLoaded.value = true
  }, 100)
})

const propertyImages = computed(() => {
  if (!property.value.images?.length) return []
  return [...(property.value.images )]
    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
})

const activeImage = computed(() => {
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

const statusLabels = {
  for_sale: 'For Sale',
  for_rent: 'For Rent',
  sold: 'Sold',
  rented: 'Rented',
} as const;

const statusColors = {
  for_sale: 'bg-emerald-100 text-emerald-800',
  for_rent: 'bg-blue-100 text-blue-800',
  sold: 'bg-gray-100 text-gray-800',
  rented: 'bg-purple-100 text-purple-800',
} as const;

const statusLabel = computed(
  () =>
    statusLabels[property.value.status as keyof typeof statusLabels] ??
    property.value.status
);

const statusColor = computed(
  () =>
    statusColors[property.value.status as keyof typeof statusColors] ??
    'bg-gray-100 text-gray-800'
);

const fullAddress = computed(
  () =>
    `${property.value.address}, ${property.value.city}, ${property.value.state}, ${property.value.country}`
)

</script>

<template>
  <div class="min-h-screen bg-linear-to-b from-gray-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Animated Breadcrumb -->
      <div 
        class="flex items-center text-sm mb-6 transition-all duration-500"
        :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-2'"
      >
        <span class="text-gray-500 hover:text-primary transition-colors cursor-pointer">Properties</span>
        <ChevronRight class="w-4 h-4 mx-2 text-gray-400" />
        <span class="text-gray-800 font-medium truncate">{{ property.title }}</span>
      </div>

      <!-- Header with Animation -->
      <div 
        class="flex flex-col lg:flex-row justify-between gap-6 mb-10 transition-all duration-700"
        :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
      >
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <span 
              class="px-3 py-1 rounded-full text-xs font-semibold tracking-wide"
              :class="statusColor"
            >
              {{ statusLabel }}
            </span>
            <span class="text-sm text-gray-500">• Listed {{ formattedDate }}</span>
          </div>
          
          <h1 class="text-4xl font-bold bg-linear-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
            {{ property.title }}
          </h1>
          
          <div class="flex items-center text-gray-600 group">
            <div class="p-2 bg-primary/10 rounded-lg mr-3 group-hover:bg-primary/20 transition-colors">
              <MapPin class="w-5 h-5 text-primary" />
            </div>
            <span class="text-lg">{{ fullAddress }}</span>
          </div>
        </div>

        <div class="text-right space-y-2">
          <div class="inline-flex items-center justify-end gap-2">
            <div class="p-2 bg-primary/10 rounded-lg">
              <DollarSign class="w-5 h-5 text-primary" />
            </div>
            <div class="text-4xl font-bold text-gray-900 tracking-tight">
              {{ formattedPrice }}
            </div>
          </div>
          <div class="text-sm text-gray-500 font-medium">Asking Price</div>
        </div>
      </div>

      <!-- Gallery with Modern Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <!-- Main Gallery with Animation -->
        <div class="lg:col-span-2 space-y-4">
          <div 
            class="relative overflow-hidden rounded-2xl shadow-2xl transition-all duration-700"
            :class="isLoaded ? 'opacity-100 scale-100' : 'opacity-0 scale-95'"
          >
            <div class="aspect-video overflow-hidden">
              <img
                v-if="activeImage"
                :src="activeImage.url"
                :alt="property.title"
                class="w-full h-full object-cover transition-transform duration-700 hover:scale-105"
              />
              <div v-else class="w-full h-full bg-linear-to-br from-gray-200 to-gray-300 animate-pulse"></div>
            </div>
            
            <!-- Image Counter -->
            <div class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
              {{ activeImageIndex + 1 }} / {{ propertyImages.length }}
            </div>
          </div>

          <!-- Thumbnail Gallery -->
          <div 
            class="flex gap-3 overflow-x-auto pb-2 transition-all duration-700"
            :class="isLoaded ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-4'"
          >
            <div
              v-for="(img, index) in propertyImages"
              :key="img.id"
              class="shrink-0 relative group cursor-pointer"
              @click="activeImageIndex = index"
            >
              <div class="relative overflow-hidden rounded-xl">
                <img
                  :src="img.url"
                  :alt="`${property.title} - Image ${index + 1}`"
                  class="w-24 h-24 object-cover transition-all duration-300 group-hover:scale-110"
                  :class="index === activeImageIndex
                    ? 'ring-3 ring-primary ring-offset-2'
                    : 'opacity-80 group-hover:opacity-100'"
                />
                <div 
                  v-if="index === activeImageIndex"
                  class="absolute inset-0 bg-primary/20"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Property Details Card with Animation -->
        <div 
          class="space-y-6 transition-all duration-700 delay-300"
          :class="isLoaded ? 'opacity-100 translate-x-0' : 'opacity-0 translate-x-4'"
        >
          <div class="bg-white rounded-2xl shadow-xl p-6 border border-gray-100">
            <h2 class="text-xl font-bold mb-6 pb-3 border-b border-gray-100 text-gray-900">
              Property Details
            </h2>
            
            <!-- Detail Grid -->
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                  <Home class="w-4 h-4" />
                  <span>Type</span>
                </div>
                <div class="font-semibold text-gray-900 capitalize">{{ property.property_type }}</div>
              </div>
              
              <div class="space-y-1 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                  <Tag class="w-4 h-4" />
                  <span>Status</span>
                </div>
                <div class="font-semibold text-gray-900">{{ statusLabel }}</div>
              </div>
              
              <div class="space-y-1 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                  <Calendar class="w-4 h-4" />
                  <span>Listed</span>
                </div>
                <div class="font-semibold text-gray-900">{{ formattedDate }}</div>
              </div>
              
              <div class="space-y-1 p-3 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                  <DollarSign class="w-4 h-4" />
                  <span>Price</span>
                </div>
                <div class="font-semibold text-gray-900">{{ formattedPrice }}</div>
              </div>
            </div>

            <!-- Features Highlight -->
            <div class="mt-8 pt-6 border-t border-gray-100">
              <h3 class="text-lg font-semibold mb-4 text-gray-900">Key Features</h3>
              <div class="grid grid-cols-3 gap-6 text-center">
                <div class="group">
                  <div class="p-4 bg-primary/5 rounded-2xl mb-2 group-hover:bg-primary/10 transition-all duration-300 group-hover:-translate-y-1">
                    <Bed class="mx-auto w-6 h-6 text-primary" />
                  </div>
                  <div class="font-bold text-2xl text-gray-900">{{ property.bedrooms ?? '-' }}</div>
                  <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Bedrooms</div>
                </div>
                <div class="group">
                  <div class="p-4 bg-primary/5 rounded-2xl mb-2 group-hover:bg-primary/10 transition-all duration-300 group-hover:-translate-y-1">
                    <BathIcon class="mx-auto w-6 h-6 text-primary" />
                  </div>
                  <div class="font-bold text-2xl text-gray-900">{{ property.bathrooms ?? '-' }}</div>
                  <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">Bathrooms</div>
                </div>
                <div class="group">
                  <div class="p-4 bg-primary/5 rounded-2xl mb-2 group-hover:bg-primary/10 transition-all duration-300 group-hover:-translate-y-1">
                    <Square class="mx-auto w-6 h-6 text-primary" />
                  </div>
                  <div class="font-bold text-2xl text-gray-900">{{ property.floor_area ?? '-' }}</div>
                  <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">SQM</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Description with Animation -->
      <div 
        class="mb-12 transition-all duration-700 delay-500"
        :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
      >
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
          <h2 class="text-2xl font-bold mb-6 text-gray-900">
            <span class="bg-linear-to-r from-primary to-primary/60 bg-clip-text text-transparent">
              Property Description
            </span>
          </h2>
          <div class="prose prose-lg max-w-none">
            <p class="text-gray-700 leading-relaxed text-lg">
              {{ property.description }}
            </p>
          </div>
        </div>
      </div>

      <!-- Owner Information with Animation -->
      <div 
        v-if="property.owner"
        class="transition-all duration-700 delay-700"
        :class="isLoaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
      >
        <div class="bg-linear-to-r from-primary/5 to-primary/10 rounded-2xl p-8 border border-primary/20">
          <h2 class="text-2xl font-bold mb-6 text-gray-900">
            Property Owner
          </h2>
          <div class="flex items-center gap-6">
            <div class="p-4 bg-white rounded-2xl shadow-lg">
              <Building class="w-10 h-10 text-primary" />
            </div>
            <div class="space-y-3">
              <div>
                <div class="font-bold text-xl text-gray-900">{{ property.owner.name }}</div>
                <div class="text-sm text-gray-600 font-medium">{{ property.owner.email }}</div>
              </div>
              <div v-if="property.owner.phone" class="text-gray-700">
                📞 {{ property.owner.phone }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for thumbnail gallery */
::-webkit-scrollbar {
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: linear-linear(to right, #3b82f6, #8b5cf6);
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-linear(to right, #2563eb, #7c3aed);
}

/* Smooth transitions for images */
img {
  transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* linear text animation */
@keyframes linear {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.bg-linear-animate {
  background-size: 200% 200%;
  animation: linear 3s ease infinite;
}

/* Card hover effects */
.group:hover .group-hover\:scale-110 {
  transform: scale(1.1);
}

.group:hover .group-hover\:-translate-y-1 {
  transform: translateY(-4px);
}

/* Glass effect for image counter */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
}
</style>