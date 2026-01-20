<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

import { home } from '@/routes';

const page = usePage()
const name = page.props.name

const particles = ref<number[]>([])
onMounted(() => {
    particles.value = Array.from({ length: 20 }, (_, i) => i)
})

defineProps<{
    title?: string
    description?: string
}>()
</script>



<template>
        <div
            class="relative min-h-dvh overflow-hidden bg-linear-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950">
            <div class="absolute inset-0 overflow-hidden">
                <div v-for="i in particles" :key="i"
                    class="absolute rounded-full bg-linear-to-r from-blue-100/30 to-emerald-100/30 dark:from-blue-900/20 dark:to-emerald-900/20"
                    :class="[
                        'animate-float',
                        i % 4 === 0 ? 'w-32 h-32' :
                            i % 3 === 0 ? 'w-24 h-24' :
                                i % 2 === 0 ? 'w-16 h-16' : 'w-12 h-12'
                    ]" :style="{
                    left: `${(i * 7) % 100}%`,
                    top: `${(i * 5) % 100}%`,
                    animationDelay: `${(i * 0.5)}s`,
                    animationDuration: `${15 + (i % 10)}s`
                }" />
            </div>

            <div class="absolute inset-0 bg-grid-white/[0.02] bg-grid bg-size[20px_20px]" />

            <div class="relative grid h-dvh lg:grid-cols-2">
                <!-- Left Panel -->
                <div class="relative hidden h-full flex-col justify-between p-10 lg:flex">
                    <div class="absolute inset-0 bg-linear-to-br from-blue-900 via-emerald-900/90 to-teal-900">
                        <div
                            class="absolute inset-0 bg-linear-to-r from-blue-500/10 via-transparent to-emerald-500/10 animate-linear-x" />
                        <div
                            class="absolute inset-0 opacity-[0.03] bg-[radial-linear(circle_at_1px_1px,#ffffff_1px,transparent_0)] bg-size[40px_40px]" />
                    </div>

                    <div class="relative z-20">
                        <Link :href="home()" class="group flex items-center text-lg font-semibold text-white">
                            <div
                                class="mr-3 rounded-lg bg-white/10 p-2 backdrop-blur-sm transition-all duration-300 group-hover:bg-white/20 group-hover:scale-105">
                                <!-- <AppLogoIcon class="size-7 fill-current text-white" /> -->
                                <img src="/logo1.png" alt="Logo" class="h-12 w-12 rounded-lg" />
                            </div>
                            <span class="text-xl tracking-tight">{{ name }}</span>
                        </Link>
                    </div>

                    <div class="relative z-20 space-y-8">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <div class="h-1 w-8 rounded-full bg-emerald-400" />
                                <span class="text-sm font-medium text-emerald-200">Property Management</span>
                            </div>
                            <h2 class="text-4xl font-bold tracking-tight text-white">
                                Welcome to Your
                                <span class="block text-emerald-300">Digital Real Estate Hub</span>
                            </h2>
                            <p class="max-w-md text-lg text-gray-200">
                                Manage properties, tenants, and transactions with our powerful platform designed for
                                modern real estate professionals.
                            </p>
                        </div>

                        <div class="grid grid-cols-3 gap-4 rounded-2xl bg-white/10 p-6 backdrop-blur-sm">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">10K+</div>
                                <div class="text-sm text-gray-300">Properties</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">98%</div>
                                <div class="text-sm text-gray-300">Satisfaction</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-white">24/7</div>
                                <div class="text-sm text-gray-300">Support</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative z-20">
                        <p class="text-sm text-gray-300">
                            © {{ new Date().getFullYear() }} {{ name }}. All rights reserved.
                        </p>
                    </div>
                </div>

                <!-- Right Panel - Auth Card -->
                <div class="flex h-full items-center justify-center px-8 py-12 sm:px-12 lg:px-16">
                    <div class="relative w-full max-w-md">
                        <div
                            class="relative overflow-hidden rounded-2xl bg-white/80 backdrop-blur-xl dark:bg-gray-900/80 shadow-2xl shadow-black/5 dark:shadow-black/30">
                            <div
                                class="absolute -top-24 -right-24 h-48 w-48 rounded-full bg-linear-to-r from-blue-500/20 to-emerald-500/20 blur-3xl" />
                            <div
                                class="absolute -bottom-24 -left-24 h-48 w-48 rounded-full bg-linear-to-r from-emerald-500/20 to-teal-500/20 blur-3xl" />

                            <div class="relative p-8">
                                <div class="mb-10 text-center">
                                    <div
                                        class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-linear-to-br from-blue-500 to-emerald-500 shadow-lg">
                                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                                        v-if="title">
                                        {{ title }}
                                    </h1>
                                    <p class="mt-2 text-gray-600 dark:text-gray-400" v-if="description">
                                        {{ description }}
                                    </p>
                                </div>

                                <!-- Auth Card Content -->
                                <slot />
                            </div>

                            <div class="h-1 bg-linear-to-r from-blue-500 via-emerald-500 to-teal-500" />
                        </div>

                        <!-- Mobile logo -->
                        <div class="mt-8 flex justify-center lg:hidden">
                            <Link :href="home()" class="flex items-center space-x-3 text-gray-700 dark:text-gray-300">
                                <img src="/logo1.png" alt="Logo" class="h-8 w-8" />
                                <span class="text-lg font-semibold">{{ name }}</span>
                            </Link>
                        </div>

                        <!-- Quick links -->
                        <div class="mt-8 text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Need help?
                                <a href="#" class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400">
                                    Contact support
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<style>
@keyframes float {

    0%,
    100% {
        transform: translateY(0) translateX(0) rotate(0deg);
    }

    33% {
        transform: translateY(-20px) translateX(10px) rotate(120deg);
    }

    66% {
        transform: translateY(10px) translateX(-20px) rotate(240deg);
    }
}

@keyframes linear-x {

    0%,
    100% {
        background-position: 0% 50%;
    }

    50% {
        background-position: 100% 50%;
    }
}

.animate-float {
    animation: float linear infinite;
}

.animate-linear-x {
    background-size: 200% 200%;
    animation: linear-x 15s ease infinite;
}

.bg-grid {
    background-image: linear-linear(to right, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
        linear-linear(to bottom, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
}
</style>