<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-600 rounded-full mb-4">
          <AcademicCapIcon class="w-8 h-8 text-white" />
        </div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">QR Attendance System</h1>
        <p class="text-gray-600">Daftar sebagai pengguna baru</p>
      </div>

      <!-- Register Form -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <form @submit.prevent="handleRegister" class="space-y-6">
          <!-- Name Field -->
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
              Nama Lengkap
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <UserIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                placeholder="Masukkan nama lengkap"
                :class="{ 'border-red-500': errors.name }"
              />
            </div>
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
          </div>

          <!-- Email Field -->
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
              Email
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <AtSymbolIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                placeholder="nama@email.com"
                :class="{ 'border-red-500': errors.email }"
              />
            </div>
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
          </div>

          <!-- Role Selection -->
          <div>
            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
              Role
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <IdentificationIcon class="h-5 w-5 text-gray-400" />
              </div>
              <select
                id="role"
                v-model="form.role"
                required
                class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors appearance-none bg-white"
                :class="{ 'border-red-500': errors.role }"
              >
                <option value="">Pilih role</option>
                <option value="admin">Administrator</option>
                <option value="satpam">Satpam</option>
              </select>
              <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <ChevronDownIcon class="h-5 w-5 text-gray-400" />
              </div>
            </div>
            <p v-if="errors.role" class="mt-1 text-sm text-red-600">{{ errors.role }}</p>
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
                placeholder="Minimal 8 karakter"
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

          <!-- Confirm Password Field -->
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
              Konfirmasi Password
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <LockClosedIcon class="h-5 w-5 text-gray-400" />
              </div>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                required
                class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-colors"
                placeholder="Ulangi password"
                :class="{ 'border-red-500': errors.password_confirmation }"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
              >
                <EyeIcon v-if="!showConfirmPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" />
                <EyeSlashIcon v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" />
              </button>
            </div>
            <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
          </div>

          <!-- Terms and Conditions -->
          <div class="flex items-start">
            <div class="flex items-center h-5">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                :class="{ 'border-red-500': errors.terms }"
              />
            </div>
            <div class="ml-3">
              <label for="terms" class="text-sm text-gray-700">
                Saya menyetujui
                <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">
                  Syarat dan Ketentuan
                </a>
                serta
                <a href="#" class="text-indigo-600 hover:text-indigo-500 font-medium">
                  Kebijakan Privasi
                </a>
              </label>
            </div>
          </div>
          <p v-if="errors.terms" class="text-sm text-red-600">{{ errors.terms }}</p>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading"
            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            <UserPlusIcon v-if="!loading" class="w-5 h-5 mr-2" />
            <div v-else class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
            {{ loading ? 'Mendaftar...' : 'Daftar' }}
          </button>

          <!-- Success Message -->
          <div v-if="successMessage" class="bg-green-50 border border-green-200 rounded-lg p-3">
            <div class="flex">
              <CheckCircleIcon class="h-5 w-5 text-green-400 mr-2 flex-shrink-0" />
              <p class="text-sm text-green-800">{{ successMessage }}</p>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-3">
            <div class="flex">
              <ExclamationTriangleIcon class="h-5 w-5 text-red-400 mr-2 flex-shrink-0" />
              <p class="text-sm text-red-800">{{ errorMessage }}</p>
            </div>
          </div>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center">
          <p class="text-sm text-gray-600">
            Sudah punya akun?
            <router-link
              to="/login"
              class="font-medium text-indigo-600 hover:text-indigo-500 ml-1"
            >
              Masuk sekarang
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
  AtSymbolIcon,
  IdentificationIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  UserPlusIcon,
  ChevronDownIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const loading = ref(false)
const showPassword = ref(false)
const showConfirmPassword = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const errors = reactive({})

const form = reactive({
  name: '',
  email: '',
  role: '',
  password: '',
  password_confirmation: '',
  terms: false
})

// Methods
const validateForm = () => {
  Object.keys(errors).forEach(key => delete errors[key])
  let isValid = true

  if (!form.name.trim()) {
    errors.name = 'Nama wajib diisi'
    isValid = false
  }

  if (!form.email.trim()) {
    errors.email = 'Email wajib diisi'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Format email tidak valid'
    isValid = false
  }

  if (!form.role) {
    errors.role = 'Role wajib dipilih'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Password wajib diisi'
    isValid = false
  } else if (form.password.length < 8) {
    errors.password = 'Password minimal 8 karakter'
    isValid = false
  }

  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Konfirmasi password tidak sesuai'
    isValid = false
  }

  if (!form.terms) {
    errors.terms = 'Anda harus menyetujui syarat dan ketentuan'
    isValid = false
  }

  return isValid
}

const handleRegister = async () => {
  if (!validateForm()) return

  loading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    // Call register API through store
    await authStore.register(form)

    successMessage.value = 'Pendaftaran berhasil! Anda akan diarahkan ke halaman login.'

    // Redirect to login after success
    setTimeout(() => {
      router.push('/login')
    }, 2000)
  } catch (error) {
    // Handle validation errors from API
    if (error.errors) {
      Object.keys(error.errors).forEach(key => {
        errors[key] = error.errors[key][0]
      })
    } else {
      errorMessage.value = error.message || 'Terjadi kesalahan saat mendaftar'
    }
  } finally {
    loading.value = false
  }
}
</script>
