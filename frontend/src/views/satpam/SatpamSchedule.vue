<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/satpam/dashboard" class="flex items-center">
              <CalendarIcon class="h-8 w-8 text-green-600 mr-3" />
              <div>
                <h1 class="text-xl font-semibold text-gray-900">Jadwal Shift</h1>
                <p class="text-sm text-gray-500">Lihat jadwal kerja Anda</p>
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
        <CalendarIcon class="h-24 w-24 text-gray-300 mx-auto mb-4" />
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Jadwal Shift</h2>
        <p class="text-gray-600 mb-6">Halaman ini akan menampilkan jadwal shift Anda</p>
        <div class="space-y-2 text-sm text-gray-500">
          <p>✓ Kalender jadwal bulanan</p>
          <p>✓ Detail shift harian</p>
          <p>✓ Lokasi penugasan</p>
          <p>✓ Jam kerja shift</p>
          <p>✓ Catatan khusus</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { CalendarIcon } from '@heroicons/vue/24/outline'
</script>
