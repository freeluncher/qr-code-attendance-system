<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
          <AcademicCapIcon class="w-8 h-8 text-white" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">QR Attendance System</h1>
        <p class="text-gray-600">Masuk ke sistem presensi digital</p>
      </div>

      <!-- Login Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email/Username Field -->
          <div>
            <label for="identifier" class="block text-sm font-medium text-gray-700 mb-2">
              Email atau Username
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <UserIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                id="identifier"
                v-model="form.identifier"
                type="text"
                required
                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                placeholder="Masukkan email atau username"
                :class="{ 'border-red-500': errors.identifier }"
              />
            </div>
            <p v-if="errors.identifier" class="mt-1 text-sm text-red-600">{{ errors.identifier }}</p>
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
              Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockClosedIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                placeholder="Masukkan password"
                :class="{ 'border-red-500': errors.password }"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <EyeIcon v-if="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                <EyeSlashIcon v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" />
              </button>
            </div>
            <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
          </div>

          <!-- Remember Me -->
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              />
              <label for="remember" class="ml-2 block text-sm text-gray-700">
                Ingat saya
              </label>
            </div>
            <router-link
              to="/forgot-password"
              class="text-sm text-indigo-600 hover:text-indigo-500 font-medium"
            >
              Lupa password?
            </router-link>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <ArrowRightOnRectangleIcon v-if="!loading" class="w-5 h-5 mr-2" />
            <div v-else class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            {{ loading ? 'Masuk...' : 'Masuk' }}
          </button>

          <!-- Error Message -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex">
              <ExclamationTriangleIcon class="h-5 w-5 text-red-400 mr-2 flex-shrink-0" />
              <p class="text-sm text-red-800">{{ errorMessage }}</p>
            </div>
          </div>
        </form>

        <!-- Register Link -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Belum punya akun?
            <router-link
              to="/register"
              class="font-medium text-indigo-600 hover:text-indigo-500 ml-1"
            >
              Daftar sekarang
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import {
  AcademicCapIcon,
  UserIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  ArrowRightOnRectangleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const showPassword = ref(false)
const errorMessage = ref('')
const errors = reactive({})

const form = reactive({
  identifier: '',
  password: '',
  remember: false
})

// Methods
const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    // Basic validation
    if (!form.identifier.trim()) {
      errors.identifier = 'Email atau username wajib diisi'
      return
    }
    if (!form.password) {
      errors.password = 'Password wajib diisi'
      return
    }

    // Prepare credentials for API
    const credentials = {
      email: form.identifier, // Backend expects 'email' field
      password: form.password,
      remember: form.remember
    }

    // Call login API through store
    await authStore.login(credentials)

    // Success - redirect based on user role
    if (authStore.isAdmin) {
      router.push('/admin/dashboard')
    } else if (authStore.isSatpam) {
      router.push('/satpam/dashboard')
    } else {
      router.push('/dashboard')
    }
  } catch (error) {
    // Handle validation errors from API
    if (error.errors) {
      Object.keys(error.errors).forEach(key => {
        errors[key] = error.errors[key][0]
      })
    } else {
      errorMessage.value = error.message || 'Terjadi kesalahan saat masuk'
    }
  } finally {
    loading.value = false
  }
}
</script>
