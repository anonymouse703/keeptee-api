<!-- components/common/ImageSlider.vue -->
<script setup lang="ts">
import { ChevronLeft, ChevronRight, Download } from 'lucide-vue-next'
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Carousel, Navigation, Slide, Pagination } from 'vue3-carousel'
import 'vue3-carousel/dist/carousel.css'

interface Image {
  id: number
  url: string
  alt: string
  is_primary?: boolean
  order?: number
}

interface Props {
  images: Image[]
  autoPlay?: boolean
  autoPlayInterval?: number
  showThumbnails?: boolean
  showDownload?: boolean
  showCounter?: boolean
  aspectRatio?: string
}

const props = withDefaults(defineProps<Props>(), {
  images: () => [],
  autoPlay: true,
  autoPlayInterval: 5000,
  showThumbnails: true,
  showDownload: true,
  showCounter: true,
  aspectRatio: 'aspect-video'
})

const emit = defineEmits<{
  'slide-change': [index: number]
  'download-all': []
}>()

const currentSlide = ref(0)
const autoplayInterval = ref<NodeJS.Timeout | null>(null)
const carouselRef = ref<InstanceType<typeof Carousel> | null>(null)

// Carousel settings
const carouselSettings = {
  itemsToShow: 1,
  itemsToScroll: 1,
  snapAlign: 'center',
  wrapAround: true,
  pauseAutoplayOnHover: true,
  autoplay: props.autoPlay ? props.autoPlayInterval : 0,
  transition: 500
}

// Handle slide change
const handleSlideChange = (index: number) => {
  currentSlide.value = index
  emit('slide-change', index)
}

// Navigate to specific slide
const goToSlide = (index: number) => {
  if (carouselRef.value) {
    carouselRef.value.slideTo(index)
  }
  currentSlide.value = index
}

// Navigation functions
const nextSlide = () => {
  if (carouselRef.value) {
    carouselRef.value.next()
  }
}

const prevSlide = () => {
  if (carouselRef.value) {
    carouselRef.value.prev()
  }
}

// Auto-play controls
const startAutoplay = () => {
  if (!props.autoPlay || autoplayInterval.value) return
  
  autoplayInterval.value = setInterval(() => {
    nextSlide()
  }, props.autoPlayInterval)
}

const stopAutoplay = () => {
  if (autoplayInterval.value) {
    clearInterval(autoplayInterval.value)
    autoplayInterval.value = null
  }
}

// Keyboard navigation
const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'ArrowLeft') {
    prevSlide()
    event.preventDefault()
  } else if (event.key === 'ArrowRight') {
    nextSlide()
    event.preventDefault()
  }
}

// Lifecycle
onMounted(() => {
  if (props.autoPlay) {
    startAutoplay()
  }
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  stopAutoplay()
  window.removeEventListener('keydown', handleKeydown)
})

// Watch for prop changes
watch(() => props.autoPlay, (newValue) => {
  if (newValue) {
    startAutoplay()
  } else {
    stopAutoplay()
  }
})

// Thumbnails computed
const thumbnailImages = computed(() => {
  return props.images.slice(0, 8) // Limit to 8 thumbnails
})

// Event handlers
const handleDownloadAll = () => {
  emit('download-all')
}
</script>

<template>
  <div class="image-slider">
    <!-- Main Carousel -->
    <div class="relative rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-800">
      <Carousel
        ref="carouselRef"
        v-bind="carouselSettings"
        v-model="currentSlide"
        @update:model-value="handleSlideChange"
        class="carousel-container"
        :class="aspectRatio"
      >
        <Slide v-for="image in images" :key="image.id" class="h-full">
          <div class="carousel__item w-full h-full">
            <img
              :src="image.url"
              :alt="image.alt"
              class="w-full h-full object-cover"
              loading="lazy"
            />
            <div class="absolute inset-0 bg-linear-to-t from-black/30 to-transparent opacity-0 hover:opacity-100 transition-opacity duration-300"></div>
          </div>
        </Slide>

        <!-- Navigation -->
        <template #addons>
          <Navigation>
            <template #next>
              <button 
                class="carousel__next absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm flex items-center justify-center text-gray-800 dark:text-white shadow-lg hover:bg-white dark:hover:bg-gray-700 hover:shadow-xl transition-all duration-200 z-10 border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @mouseenter="stopAutoplay"
                @mouseleave="startAutoplay"
                aria-label="Next slide"
              >
                <ChevronRight class="h-5 w-5 md:h-6 md:w-6" />
              </button>
            </template>
            <template #prev>
              <button 
                class="carousel__prev absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 md:w-12 md:h-12 rounded-full bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm flex items-center justify-center text-gray-800 dark:text-white shadow-lg hover:bg-white dark:hover:bg-gray-700 hover:shadow-xl transition-all duration-200 z-10 border-0 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @mouseenter="stopAutoplay"
                @mouseleave="startAutoplay"
                aria-label="Previous slide"
              >
                <ChevronLeft class="h-5 w-5 md:h-6 md:w-6" />
              </button>
            </template>
          </Navigation>
          
          <Pagination class="absolute bottom-4 left-0 right-0 flex justify-center items-center gap-2 z-10" />
        </template>
      </Carousel>

      <!-- Image Counter -->
      <div v-if="showCounter && images.length > 0" 
        class="absolute top-4 right-4 px-3 py-1 rounded-full bg-black/60 text-white text-sm font-medium z-10">
        {{ currentSlide + 1 }} / {{ images.length }}
      </div>

      <!-- Download Button -->
      <button
        v-if="showDownload"
        @click="handleDownloadAll"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
        class="absolute top-4 left-4 px-3 py-2 rounded-lg bg-black/60 hover:bg-black/80 text-white text-sm font-medium flex items-center gap-2 z-10 transition-colors focus:outline-none focus:ring-2 focus:ring-white/50"
        aria-label="Download all images"
      >
        <Download class="h-4 w-4" />
        <span class="hidden sm:inline">Download All</span>
        <span class="sm:hidden">Download</span>
      </button>
    </div>

    <!-- Thumbnails -->
    <div v-if="showThumbnails && thumbnailImages.length > 1" class="mt-4">
      <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-2">
        <button
          v-for="(image, index) in thumbnailImages"
          :key="image.id"
          @click="goToSlide(index)"
          @mouseenter="stopAutoplay"
          @mouseleave="startAutoplay"
          :class="[
            'relative rounded-lg overflow-hidden aspect-square transition-all duration-200',
            'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2',
            currentSlide === index 
              ? 'ring-2 ring-blue-500 dark:ring-blue-400 scale-[1.02] shadow-md' 
              : 'ring-1 ring-gray-300 dark:ring-gray-700 hover:ring-2 hover:ring-gray-400 dark:hover:ring-gray-500'
          ]"
          :aria-label="`Go to slide ${index + 1}`"
          :aria-current="currentSlide === index ? 'true' : 'false'"
        >
          <img
            :src="image.url"
            :alt="image.alt"
            class="w-full h-full object-cover"
            loading="lazy"
          />
          <div 
            v-if="currentSlide === index"
            class="absolute inset-0 bg-blue-500/20 pointer-events-none"
            aria-hidden="true"
          />
          <div 
            v-if="image.is_primary"
            class="absolute top-1 left-1 px-2 py-0.5 rounded bg-blue-500 text-white text-xs font-medium"
          >
            Main
          </div>
        </button>
      </div>
    </div>
  </div>
</template>

<style>
/* Only keep essential carousel styles from the package */
:deep(.carousel__viewport) {
  border-radius: 0.75rem;
  overflow: hidden;
}

:deep(.carousel__track) {
  height: 100%;
}

:deep(.carousel__slide) {
  height: 100%;
}

:deep(.carousel__item) {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Style pagination dots with Tailwind */
:deep(.carousel__pagination-button) {
  width: 10px;
  height: 10px;
  border-radius: 9999px;
  margin: 0 4px;
  background-color: rgba(255, 255, 255, 0.5);
  border: none;
  padding: 0;
  cursor: pointer;
  transition: all 300ms ease;
}

:deep(.carousel__pagination-button:hover) {
  background-color: rgba(255, 255, 255, 0.8);
}

:deep(.carousel__pagination-button--active) {
  background-color: white;
  width: 24px;
}

/* Dark mode adjustments */
@media (prefers-color-scheme: dark) {
  :deep(.carousel__pagination-button) {
    background-color: rgba(255, 255, 255, 0.3);
  }
  
  :deep(.carousel__pagination-button:hover) {
    background-color: rgba(255, 255, 255, 0.5);
  }
  
  :deep(.carousel__pagination-button--active) {
    background-color: white;
  }
}

/* Mobile responsive adjustments */
@media (max-width: 640px) {
  :deep(.carousel__pagination-button) {
    width: 8px;
    height: 8px;
    margin: 0 3px;
  }
  
  :deep(.carousel__pagination-button--active) {
    width: 20px;
  }
}
</style>