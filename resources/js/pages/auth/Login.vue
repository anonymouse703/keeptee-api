<script setup lang="ts">
import { EnvelopeIcon, LockClosedIcon } from '@heroicons/vue/24/solid';
import { useForm, Link } from '@inertiajs/vue3';
import { GoogleIcon, FacebookIcon } from 'vue3-simple-icons';



import AuthLayout from '@/layouts/AuthLayout.vue';

// Inertia form
const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <AuthLayout
    title="Welcome Back"
    description="Sign in to access your property dashboard"
  >
    <form @submit.prevent="submit" class="space-y-6">

      <!-- Email Field -->
      <div class="space-y-2">
        <label for="email" class="text-sm font-medium text-gray-700 dark:text-gray-300">
          Email Address
        </label>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <EnvelopeIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            id="email"
            type="email"
            v-model="form.email"
            required
            autofocus
            autocomplete="email"
            placeholder="you@example.com"
            class="block w-full rounded-lg border border-gray-300 bg-white/50 pl-10 pr-4 py-3 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 transition-all duration-200"
          />
        </div>
        <p v-if="form.errors.email" class="text-sm text-red-600">{{ form.errors.email }}</p>
      </div>

      <!-- Password Field -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label for="password" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Password
          </label>
          <Link
            href="/forgot-password"
            class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400"
          >
            Forgot password?
          </Link>
        </div>
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <LockClosedIcon class="h-5 w-5 text-gray-400" />
          </div>
          <input
            id="password"
            type="password"
            v-model="form.password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
            class="block w-full rounded-lg border border-gray-300 bg-white/50 pl-10 pr-4 py-3 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-white dark:border-gray-700 dark:bg-gray-800/50 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-400 transition-all duration-200"
          />
        </div>
        <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
      </div>

      <!-- Remember Me -->
      <div class="flex items-center">
        <input
          id="remember"
          type="checkbox"
          v-model="form.remember"
          class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
        />
        <label for="remember" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
          Remember me
        </label>
      </div>

      <!-- Submit Button -->
      <button
        type="submit"
        :disabled="form.processing"
        class="relative w-full overflow-hidden rounded-xl bg-linear-to-r from-blue-600 to-emerald-600 px-6 py-4 text-white font-semibold shadow-lg hover:shadow-xl hover:scale-[1.02] transform transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span class="relative z-10">Sign In</span>
        <div class="absolute inset-0 bg-linear-to-r from-emerald-600 to-blue-600 opacity-0 hover:opacity-100 transition-opacity duration-300" />
      </button>

      <!-- Divider -->
      <div class="relative pt-4">
        <div class="absolute inset-0 flex items-center pt-4">
          <div class="w-full border-t border-gray-300 dark:border-gray-700" />
        </div>
        <div class="relative flex justify-center text-sm">
          <span class="bg-white px-2 text-gray-500 dark:bg-gray-900 dark:text-gray-400">
            Or continue with
          </span>
        </div>
      </div>

      <!-- Social Buttons -->
      <div class="grid grid-cols-2 gap-3">
        <!-- Google -->
        <button
          type="button"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200"
        >
          <GoogleIcon class="h-5 w-5 mr-2 text-red-500" />
          Google
        </button>

        <!-- Facebook -->
        <button
          type="button"
          class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200"
        >
          <FacebookIcon class="h-5 w-5 mr-2 text-blue-600" />
          Facebook
        </button>
      </div>

    </form>
  </AuthLayout>
</template>
