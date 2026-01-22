<script setup lang="ts">
import { AlertTriangle, X } from 'lucide-vue-next';

const props = withDefaults(
  defineProps<{
    show: boolean;
    title?: string;
    message?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'danger' | 'warning' | 'info' | 'success';
    icon?: boolean;
    size?: 'sm' | 'md' | 'lg';
  }>(),
  {
    title: 'Confirm Action',
    message: 'Are you sure you want to proceed?',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'danger',
    icon: true,
    size: 'md',
  }
);

const emit = defineEmits<{
  confirm: [];
  cancel: [];
  close: [];
}>();

const handleConfirm = () => {
  emit('confirm');
  emit('close');
};

const handleCancel = () => {
  emit('cancel');
  emit('close');
};

const handleBackdropClick = (e: MouseEvent) => {
  if (e.target === e.currentTarget) {
    handleCancel();
  }
};

// Variant styles for icon & confirm button
const getVariantStyles = () => {
  switch (props.variant) {
    case 'danger':
      return {
        icon: 'text-red-600 bg-red-50 dark:text-red-400 dark:bg-red-900/20',
        button: 'bg-red-600 hover:bg-red-500 text-white focus:ring-red-500',
        text: 'text-red-600 dark:text-red-400',
      };
    case 'warning':
      return {
        icon: 'text-yellow-600 bg-yellow-50 dark:text-yellow-400 dark:bg-yellow-900/20',
        button: 'bg-yellow-600 hover:bg-yellow-500 text-white focus:ring-yellow-500',
        text: 'text-yellow-600 dark:text-yellow-400',
      };
    case 'info':
      return {
        icon: 'text-blue-600 bg-blue-50 dark:text-blue-400 dark:bg-blue-900/20',
        button: 'bg-blue-600 hover:bg-blue-500 text-white focus:ring-blue-500',
        text: 'text-blue-600 dark:text-blue-400',
      };
    case 'success':
      return {
        icon: 'text-green-600 bg-green-50 dark:text-green-400 dark:bg-green-900/20',
        button: 'bg-green-600 hover:bg-green-500 text-white focus:ring-green-500',
        text: 'text-green-600 dark:text-green-400',
      };
    default:
      return {
        icon: 'text-gray-600 bg-gray-50 dark:text-gray-400 dark:bg-gray-900/20',
        button: 'bg-blue-600 hover:bg-blue-500 text-white focus:ring-blue-500',
        text: 'text-gray-600 dark:text-gray-400',
      };
  }
};

// Size classes
const getSizeClass = () => {
  switch (props.size) {
    case 'sm':
      return 'max-w-sm';
    case 'lg':
      return 'max-w-lg';
    case 'md':
    default:
      return 'max-w-md';
  }
};

const variantStyles = getVariantStyles();
const sizeClass = getSizeClass();
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        @click="handleBackdropClick"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4"
      >
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="opacity-0 translate-y-4 scale-95"
          enter-to-class="opacity-100 translate-y-0 scale-100"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 translate-y-0 scale-100"
          leave-to-class="opacity-0 translate-y-4 scale-95"
        >
          <div
            v-if="show"
            :class="['bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 shadow-xl w-full', sizeClass]"
            @click.stop
          >
            <!-- Header with close button -->
            <div class="relative px-6 pt-5 pb-4 border-b border-gray-200 dark:border-gray-800">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div v-if="icon" :class="['rounded-full p-2.5', variantStyles.icon]">
                    <AlertTriangle class="h-5 w-5" />
                  </div>
                  <h3 :class="['text-lg font-semibold', variantStyles.text]">
                    {{ title }}
                  </h3>
                </div>
                <button
                  @click="handleCancel"
                  class="rounded-lg p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                  aria-label="Close"
                >
                  <X class="h-5 w-5" />
                </button>
              </div>
            </div>

            <!-- Content -->
            <div class="px-6 py-5">
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ message }}
              </p>
            </div>

            <!-- Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 flex justify-end gap-3">
              <button
                @click="handleCancel"
                class="px-4 py-2.5 text-sm font-medium border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
              >
                {{ cancelText }}
              </button>
              <button
                @click="handleConfirm"
                :class="[
                  'px-4 py-2.5 text-sm font-medium rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900',
                  variantStyles.button
                ]"
              >
                {{ confirmText }}
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* Custom backdrop blur for modern browsers */
@supports (backdrop-filter: blur(8px)) or (-webkit-backdrop-filter: blur(8px)) {
  div[class*="bg-black/"] {
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
  }
}
</style>