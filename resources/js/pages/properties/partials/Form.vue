<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { Building2, DollarSign, Ruler, Bed, Bath, MapPin, Star, Image as ImageIcon } from 'lucide-vue-next'
import { computed, ref } from 'vue'

import BaseButton from '@/components/ui/button/BaseButton.vue'
import ImageUpload from '@/components/ui/image-upload/ImageUpload.vue'
import BaseInput from '@/components/ui/input/BaseInput.vue'
import BaseSelect from '@/components/ui/input/Select.vue'

const props = defineProps<{
  property?: any
  property_types?: { key: string; label: string }[]
  statuses?: { key: string; label: string }[]
  image_types?: { key: string; label: string }[]
  amenities : object 
}>()

console.log('amenities', props.amenities)

const toBoolean = (value: any): boolean => {
  if (typeof value === 'boolean') return value
  if (typeof value === 'number') return value === 1
  if (typeof value === 'string') return value === '1' || value.toLowerCase() === 'true'
  return false
}

const images = ref<File[]>([])
const imageTypes = ref<Array<{id: string, type: string}>>([])
const primaryImageIndex = ref(0)

const form = useForm({
  title: props.property?.title ?? '',
  description: props.property?.description ?? '',
  owner_id: props.property?.owner_id ?? null,
  price: props.property?.price != null ? String(props.property.price) : '',
  property_type: props.property?.property_type ?? 'house',
  status: props.property?.status ?? 'for_sale',
  bedrooms: props.property?.bedrooms != null ? String(props.property.bedrooms) : '0',
  bathrooms: props.property?.bathrooms != null ? String(props.property.bathrooms) : '0',
  floor_area: props.property?.floor_area != null ? String(props.property.floor_area) : '',
  address: props.property?.address ?? '',
  city: props.property?.city ?? '',
  state: props.property?.state ?? '',
  country: props.property?.country ?? 'USA',
  latitude: props.property?.latitude != null ? String(props.property.latitude) : '',
  longitude: props.property?.longitude != null ? String(props.property.longitude) : '',
  is_featured: props.property?.is_featured != null
    ? toBoolean(props.property.is_featured)
    : false,
  is_active: props.property?.is_active != null
    ? toBoolean(props.property.is_active)
    : true,
  images: [] as File[],
  image_types: [] as string[],
  primary_image_index: 0,
  delete_images: [] as string[] 
})

const allErrors = computed(() => form.errors)

const isFormValid = computed(() => {
  return form.title && 
         form.property_type && 
         form.status && 
         form.price && 
         form.bedrooms && 
         form.bathrooms && 
         form.address && 
         form.city && 
         form.country
})

const imageTypeOptions = computed(() => {
  if (!props.image_types) return []
  return props.image_types.map(type => ({
    value: type.key,
    label: type.label
  }))
})

const propertyTypeOptions = computed(() =>
  props.property_types?.map(i => ({ label: i.label, value: i.key })) ?? []
)

const statusOptions = computed(() =>
  props.statuses?.map(i => ({ label: i.label, value: i.key })) ?? []
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

const parseNumberOrNull = (value: any): number | null => {
  if (value === null || value === undefined || value === '') return null
  const num = Number(value)
  return isNaN(num) ? null : num
}

const handleImagesChange = (files: File[]) => {
  console.log('Images changed:', files.length)
  images.value = files
  form.images = files
}

const handleImageTypes = (types: Array<{id: string, type: string}>) => {
  console.log('Image types updated:', types)
  imageTypes.value = types
  form.image_types = types.map(t => t.type)
}

const handleRemoveExistingImage = (url: string) => {
  console.log('Removing existing image:', url)
  form.delete_images.push(url)
}

const handlePrimaryImageChange = (index: number) => {
  console.log('Primary image changed to index:', index)
  primaryImageIndex.value = index
  form.primary_image_index = index
}

const handleImageRemove = (index: number) => {
  console.log('Removing image at index:', index)
  if (props.property?.images && index < props.property.images.length) {
    const imageToDelete = props.property.images[index]
    form.delete_images.push(imageToDelete.url || imageToDelete.id)
  } else {
    const newIndex = index - (props.property?.images?.length || 0)
    images.value.splice(newIndex, 1)
    form.images = [...images.value]
    
    if (imageTypes.value[newIndex]) {
      imageTypes.value.splice(newIndex, 1)
      form.image_types = imageTypes.value.map(t => t.type)
    }
  }
}

const handleSubmit = () => {
  if (!isFormValid.value) {
    console.error('Form validation failed')
    return
  }

  const formData = new FormData()

  // Regular fields
  const fields = {
    title: form.title,
    description: form.description,
    owner_id: form.owner_id || '',
    property_type: form.property_type,
    status: form.status,
    price: parseNumberOrNull(form.price)?.toString() || '',
    bedrooms: parseNumberOrNull(form.bedrooms)?.toString() || '',
    bathrooms: parseNumberOrNull(form.bathrooms)?.toString() || '',
    floor_area: parseNumberOrNull(form.floor_area)?.toString() || '',
    address: form.address,
    city: form.city,
    state: form.state || '',
    country: form.country,
    latitude: parseNumberOrNull(form.latitude)?.toString() || '',
    longitude: parseNumberOrNull(form.longitude)?.toString() || '',
    primary_image_index: form.primary_image_index.toString(),
  }

  Object.entries(fields).forEach(([key, value]) => {
    if (value !== null && value !== undefined) formData.append(key, value)
  })

  // Booleans
  formData.append('is_featured', form.is_featured ? '1' : '0')
  formData.append('is_active', form.is_active ? '1' : '0')

  // Images + image types must have same numeric indices
  form.images.forEach((file, index) => {
    formData.append(`images.${index}`, file)
    formData.append(`image_types.${index}`, form.image_types[index] || 'other')
  })

  // Delete existing images
  form.delete_images.forEach((url, index) => {
    formData.append(`delete_images.${index}`, url)
  })

  // Determine create vs update
  const url = props.property?.id
    ? `/properties/${props.property.id}`
    : '/properties'

  const method = props.property?.id ? 'put' : 'post'

  if (props.property?.id) formData.append('_method', 'put')

  router.post(url, formData, {
    preserveScroll: true,
    onStart: () => console.log(`${method.toUpperCase()} started`),
    onSuccess: () => {
      console.log(`${method.toUpperCase()} successful`)
      router.visit('/properties')
    },
    onError: (errors) => {
      console.error(`${method.toUpperCase()} errors:`, errors)
      form.errors = errors
    },
  })
}


const handleCancel = () => {
  router.visit('/properties')
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-3 pb-4 border-b border-gray-200 dark:border-gray-800">
      <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
        <Building2 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
          {{ props.property?.id ? 'Edit Property' : 'Create Property' }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Enter all the details about your property
        </p>
      </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Images -->
      <div class="lg:col-span-1 space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
          <div class="flex items-center gap-3 pb-4 mb-4 border-b border-gray-200 dark:border-gray-800">
            <ImageIcon class="h-5 w-5 text-blue-600 dark:text-blue-400" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
              Property Images
            </h3>
          </div>

          <ImageUpload
            v-model="images"
            :existing-images="props.property?.images || []"
            :image-types="imageTypeOptions"
            :max-files="15"
            :max-size-mb="10"
            @update:model-value="handleImagesChange"
            @update:image-types="handleImageTypes" 
            @update:primary="handlePrimaryImageChange"
            @remove="handleImageRemove"
            @remove-existing="handleRemoveExistingImage"
          />

          <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            <p>• Upload high-quality images (max 10MB each)</p>
            <p>• Click star icon to set as primary</p>
            <p>• Select image type for each photo</p>
          </div>
        </div>
      </div>

      <!-- Right Column: Form Fields -->
      <div class="lg:col-span-2 space-y-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
          <div class="space-y-6">
            <!-- Title -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                <Building2 class="inline h-4 w-4 mr-1" /> Property Title <span class="text-red-500">*</span>
              </label>
              <BaseInput v-model="form.title" type="text" placeholder="e.g., Beautiful 3-Bedroom House"
                :error="allErrors.title" required class="w-full" />
            </div>

            <!-- Description -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description
              </label>
              <textarea v-model="form.description" rows="4" placeholder="Describe the property features..."
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"></textarea>
            </div>

            <!-- Property Type & Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Property Type <span class="text-red-500">*</span>
                </label>
                <BaseSelect v-model="form.property_type" :options="propertyTypeOptions" placeholder="Select Property Type"
                  :error="allErrors.property_type" required class="w-full" />
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Status <span class="text-red-500">*</span>
                </label>
                <BaseSelect v-model="form.status" :options="statusOptions" placeholder="Select Status"
                  :error="allErrors.status" required class="w-full" />
              </div>
            </div>

            <!-- Price -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                <DollarSign class="inline h-4 w-4 mr-1" /> Price <span class="text-red-500">*</span>
              </label>
              <BaseInput v-model="form.price" type="number" min="0.01" step="0.01" placeholder="0.00" :error="allErrors.price"
                required class="w-full" />
            </div>

            <!-- Bedrooms, Bathrooms, Floor Area -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  <Bed class="inline h-4 w-4 mr-1" /> Bedrooms <span class="text-red-500">*</span>
                </label>
                <BaseSelect v-model="form.bedrooms" :options="bedroomOptions" placeholder="Select bedrooms"
                  :error="allErrors.bedrooms" required class="w-full" />
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  <Bath class="inline h-4 w-4 mr-1" /> Bathrooms <span class="text-red-500">*</span>
                </label>
                <BaseSelect v-model="form.bathrooms" :options="bathroomOptions" placeholder="Select bathrooms"
                  :error="allErrors.bathrooms" required class="w-full" />
              </div>

              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  <Ruler class="inline h-4 w-4 mr-1" /> Floor Area (sq ft)
                </label>
                <BaseInput v-model="form.floor_area" type="number" min="0" placeholder="e.g., 1500"
                  :error="allErrors.floor_area" class="w-full" />
              </div>
            </div>

            <!-- Address -->
            <div>
              <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                <MapPin class="inline h-4 w-4 mr-1" /> Full Address <span class="text-red-500">*</span>
              </label>
              <BaseInput v-model="form.address" type="text" placeholder="Enter complete property address..."
                :error="allErrors.address" required class="w-full" />
            </div>

            <!-- City / State / Country -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  City <span class="text-red-500">*</span>
                </label>
                <BaseInput v-model="form.city" type="text" placeholder="Enter city" :error="allErrors.city" required
                  class="w-full" />
              </div>
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  State
                </label>
                <BaseInput v-model="form.state" type="text" placeholder="Enter state" :error="allErrors.state"
                  class="w-full" />
              </div>
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Country <span class="text-red-500">*</span>
                </label>
                <BaseInput v-model="form.country" type="text" placeholder="Enter country" :error="allErrors.country" required
                  class="w-full" />
              </div>
            </div>

            <!-- Latitude / Longitude -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Latitude</label>
                <BaseInput v-model="form.latitude" type="number" step="any" placeholder="e.g., 37.7749"
                  :error="allErrors.latitude" class="w-full" />
              </div>
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Longitude</label>
                <BaseInput v-model="form.longitude" type="number" step="any" placeholder="e.g., -122.4194"
                  :error="allErrors.longitude" class="w-full" />
              </div>
            </div>

            <!-- Featured & Active -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  <Star class="inline h-4 w-4 mr-1" /> Featured Property
                </label>
                <BaseSelect v-model="form.is_featured" :options="featuredOptions" :error="allErrors.is_featured"
                  placeholder="Select" class="w-full" />
              </div>
              <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Property Status
                </label>
                <BaseSelect v-model="form.is_active" :options="activeOptions" :error="allErrors.is_active"
                  placeholder="Select" class="w-full" />
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-800">
          <BaseButton type="button" variant="outline" @click="handleCancel" :disabled="form.processing">
            Cancel
          </BaseButton>
          <BaseButton type="submit" @click="handleSubmit" :disabled="form.processing || !isFormValid">
            <span v-if="form.processing">Processing...</span>
            <span v-else>{{ props.property?.id ? 'Update Property' : 'Create Property' }}</span>
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>