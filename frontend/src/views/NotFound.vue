<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center items-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
      <!-- Error Illustration -->
      <div class="mx-auto h-32 w-32 text-gray-300">
        <ExclamationTriangleIcon class="h-full w-full" />
      </div>

      <!-- Error Content -->
      <div class="space-y-4">
        <h1 class="text-6xl font-bold text-gray-900">404</h1>
        <h2 class="text-2xl font-semibold text-gray-700">Halaman Tidak Ditemukan</h2>
        <p class="text-gray-500 max-w-sm mx-auto">
          Maaf, halaman yang Anda cari tidak dapat ditemukan. Halaman mungkin telah dipindahkan atau tidak ada.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="space-y-4">
        <button
          @click="goBack"
          class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <ArrowLeftIcon class="h-5 w-5 mr-2" />
          Kembali
        </button>

        <router-link
          to="/"
          class="w-full flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <HomeIcon class="h-5 w-5 mr-2" />
          Kembali ke Dashboard
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import {
  ExclamationTriangleIcon,
  ArrowLeftIcon,
  HomeIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()

const goBack = () => {
  if (window.history.length > 1) {
    router.go(-1)
  } else {
    router.push('/')
  }
}
</script>
