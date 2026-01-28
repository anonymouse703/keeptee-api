<script setup lang="ts">
import { Home, DollarSign, Ruler, Bed, Bath, MapPin, Star } from 'lucide-vue-next'
import { ref, computed } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'
import BaseSelect from '@/components/ui/input/Select.vue'

import { type Property } from '../../../../types/type'

interface Props {
  property?: Property
  isLoading?: boolean
  errors?: Record<string, string[] | string>
  propertyTypes?: Array<{ label: string; key: string }>
  statuses?: Array<{ label: string; key: string }>
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  errors: () => ({}),
  propertyTypes: () => [],
  statuses: () => []
})

const emit = defineEmits<{
  submit: [formData: Property]
  cancel: []
}>()

// Helper to convert to boolean
const toBoolean = (value: any): boolean => {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') return value === '1' || value.toLowerCase() === 'true'
  return false
}

// Form state
const form = ref({
  title: props.property?.title || '',
  description: props.property?.description || '',
  owner_id: props.property?.owner_id || null,
  price: props.property?.price != null ? String(props.property.price) : '',
  property_type: props.property?.property_type || 'house', 
  status: props.property?.status || 'for_sale',
  bedrooms: props.property?.bedrooms != null ? String(props.property.bedrooms) : '0',
  bathrooms: props.property?.bathrooms != null ? String(props.property.bathrooms) : '0',
  floor_area: props.property?.floor_area != null ? String(props.property.floor_area) : '',
  address: props.property?.address || '',
  city: props.property?.city || '',
  state: props.property?.state || '',
  country: props.property?.country || 'USA',
  latitude: props.property?.latitude != null ? String(props.property.latitude) : '',
  longitude: props.property?.longitude != null ? String(props.property.longitude) : '',
  is_featured: props.property?.is_featured != null ? toBoolean(props.property.is_featured) : false,
  is_active: props.property?.is_active != null ? toBoolean(props.property.is_active) : true
})

// Options for selects
const propertyTypeOptions = computed(() =>
  props.propertyTypes.map(item => ({ label: item.label, value: item.key }))
)
const statusOptions = computed(() =>
  props.statuses.map(item => ({ label: item.label, value: item.key }))
)
const bedroomOptions = [
  { label: 'Studio', value: '0' },
  { label: '1 Bedroom', value: '1' },
  { label: '2 Bedrooms', value: '2' },
  { label: '3 Bedrooms', value: '3' },
  { label: '4 Bedrooms', value: '4' },
  { label: '5+ Bedrooms', value: '5' }
]
const bathroomOptions = [
  { label: '1 Bathroom', value: '1' },
  { label: '2 Bathrooms', value: '2' },
  { label: '3 Bathrooms', value: '3' },
  { label: '4+ Bathrooms', value: '4' }
]
const featuredOptions = [
  { label: 'Yes', value: true },
  { label: 'No', value: false }
]
const activeOptions = [
  { label: 'Active', value: true },
  { label: 'Inactive', value: false }
]

// Map API errors to first string
const allErrors = computed(() => {
  const result: Record<string, string> = {}
  if (!props.errors) return result
  for (const key in props.errors) {
    const value = props.errors[key]
    if (Array.isArray(value)) result[key] = value[0]
    else result[key] = value
  }
  return result
})

// Submit handler
const handleSubmit = () => {
  const parseNumberOrNull = (value: any): number | null => {
    if (value === null || value === undefined || value === '') return null
    const num = parseFloat(String(value))
    return isNaN(num) ? null : num
  }

  const payload: Property = {
    title: form.value.title.trim(),
    owner_id: props.property?.owner_id || null,
    description: form.value.description.trim(),
    price: parseNumberOrNull(form.value.price),
    property_type: form.value.property_type || 'house', 
    status: form.value.status || 'for_sale',
    bedrooms: parseNumberOrNull(form.value.bedrooms),
    bathrooms: parseNumberOrNull(form.value.bathrooms),
    floor_area: parseNumberOrNull(form.value.floor_area),
    address: form.value.address.trim(),
    city: form.value.city.trim(),
    state: form.value.state.trim(),
    country: form.value.country.trim() || 'USA',
    latitude: parseNumberOrNull(form.value.latitude),
    longitude: parseNumberOrNull(form.value.longitude),
    is_featured: form.value.is_featured,
    is_active: form.value.is_active
  }

  emit('submit', payload)
}

const handleCancel = () => emit('cancel')
</script>

<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-800">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <Home class="h-5 w-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ props.property?.title ? 'Edit Property' : 'Property Details' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Enter all the details about your property
        </p>
      </div>
    </div>

    <!-- Form Fields -->
    <div class="space-y-6">
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          <Home class="inline h-4 w-4 mr-1"/> Property Title <span class="text-red-500">*</span>
        </label>
        <BaseInput
          v-model="form.title"
          type="text"
          placeholder="e.g., Beautiful 3-Bedroom House"
          :error="allErrors.title"
          required
          class="w-full"
        />
      </div>

      <!-- Description -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          Description
        </label>
        <textarea
          v-model="form.description"
          rows="4"
          placeholder="Describe the property features..."
          class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
        ></textarea>
      </div>

      <!-- Property Type & Status -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Property Type <span class="text-red-500">*</span>
          </label>
          <BaseSelect
            v-model="form.property_type"
            :options="propertyTypeOptions"
            placeholder="Select Property Type"
            :error="allErrors.property_type"
            required
            class="w-full"
          />
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-red-500">*</span>
          </label>
          <BaseSelect
            v-model="form.status"
            :options="statusOptions"
            placeholder="Select Status"
            :error="allErrors.status"
            required
            class="w-full"
          />
        </div>
      </div>

      <!-- Price -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          <DollarSign class="inline h-4 w-4 mr-1"/> Price <span class="text-red-500">*</span>
        </label>
        <BaseInput
          v-model="form.price"
          type="number"
          min="0.01"
          step="0.01"
          placeholder="0.00"
          :error="allErrors.price"
          required
          class="w-full"
        />
      </div>

      <!-- Bedrooms, Bathrooms, Floor Area -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <Bed class="inline h-4 w-4 mr-1"/> Bedrooms <span class="text-red-500">*</span>
          </label>
          <BaseSelect
            v-model="form.bedrooms"
            :options="bedroomOptions"
            placeholder="Select bedrooms"
            :error="allErrors.bedrooms"
            required
            class="w-full"
          />
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <Bath class="inline h-4 w-4 mr-1"/> Bathrooms <span class="text-red-500">*</span>
          </label>
          <BaseSelect
            v-model="form.bathrooms"
            :options="bathroomOptions"
            placeholder="Select bathrooms"
            :error="allErrors.bathrooms"
            required
            class="w-full"
          />
        </div>

        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <Ruler class="inline h-4 w-4 mr-1"/> Floor Area (sq ft)
          </label>
          <BaseInput
            v-model="form.floor_area"
            type="number"
            min="0"
            placeholder="e.g., 1500"
            :error="allErrors.floor_area"
            class="w-full"
          />
        </div>
      </div>

      <!-- Address -->
      <div>
        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
          <MapPin class="inline h-4 w-4 mr-1"/> Full Address <span class="text-red-500">*</span>
        </label>
        <BaseInput
          v-model="form.address"
          type="text"
          placeholder="Enter complete property address..."
          :error="allErrors.address"
          required
          class="w-full"
        />
      </div>

      <!-- City / State / Country -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            City <span class="text-red-500">*</span>
          </label>
          <BaseInput
            v-model="form.city"
            type="text"
            placeholder="Enter city"
            :error="allErrors.city"
            required
            class="w-full"
          />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            State
          </label>
          <BaseInput
            v-model="form.state"
            type="text"
            placeholder="Enter state"
            :error="allErrors.state"
            class="w-full"
          />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Country <span class="text-red-500">*</span>
          </label>
          <BaseInput
            v-model="form.country"
            type="text"
            placeholder="Enter country"
            :error="allErrors.country"
            required
            class="w-full"
          />
        </div>
      </div>

      <!-- Latitude / Longitude -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
          <BaseInput
            v-model="form.latitude"
            type="number"
            step="any"
            placeholder="e.g., 37.7749"
            :error="allErrors.latitude"
            class="w-full"
          />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
          <BaseInput
            v-model="form.longitude"
            type="number"
            step="any"
            placeholder="e.g., -122.4194"
            :error="allErrors.longitude"
            class="w-full"
          />
        </div>
      </div>

      <!-- Featured & Active -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            <Star class="inline h-4 w-4 mr-1"/> Featured Property
          </label>
          <BaseSelect
            v-model="form.is_featured"
            :options="featuredOptions"
            :error="allErrors.is_featured"
            placeholder="Select"
            class="w-full"
          />
        </div>
        <div>
          <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Property Status
          </label>
          <BaseSelect
            v-model="form.is_active"
            :options="activeOptions"
            :error="allErrors.is_active"
            placeholder="Select"
            class="w-full"
          />
        </div>
      </div>

    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
      <BaseButton type="button" variant="outline" @click="handleCancel" :disabled="props.isLoading">
        Cancel
      </BaseButton>
      <BaseButton type="button" @click="handleSubmit" :disabled="props.isLoading" :loading="props.isLoading">
        {{ props.property?.title ? 'Update Property' : 'Create Property' }}
      </BaseButton>
    </div>

  </div>
</template>