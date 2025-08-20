<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/satpam/dashboard" class="flex items-center">
              <QrCodeIcon class="h-8 w-8 text-blue-600 mr-3" />
              <div>
                <h1 class="text-xl font-semibold text-gray-900">Presensi</h1>
                <p class="text-sm text-gray-500">Scan QR Code untuk presensi</p>
              </div>
            </router-link>
          </div>
          <router-link
            to="/satpam/dashboard"
            class="text-gray-600 hover:text-gray-900"
          >
            Kembali ke Dashboard
          </router-link>
        </div>
      </div>
    </nav>

    <!-- Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <QrCodeIcon class="h-24 w-24 text-gray-300 mx-auto mb-4" />
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Presensi QR Code</h2>
        <p class="text-gray-600 mb-6">Halaman ini akan menyediakan fitur scan QR Code</p>
        <div class="space-y-2 text-sm text-gray-500">
          <p>✓ Camera scanner QR Code</p>
          <p>✓ Validasi lokasi GPS</p>
          <p>✓ Capture foto saat presensi</p>
          <p>✓ Check in dan check out</p>
          <p>✓ Status presensi real-time</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { QrCodeIcon } from '@heroicons/vue/24/outline'
</script>
