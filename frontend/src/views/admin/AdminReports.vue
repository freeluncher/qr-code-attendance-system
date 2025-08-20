<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/admin/dashboard" class="flex items-center">
              <DocumentTextIcon class="h-8 w-8 text-blue-600 mr-3" />
              <div>
                <h1 class="text-xl font-semibold text-gray-900">Laporan</h1>
                <p class="text-sm text-gray-500">Generate dan download laporan</p>
              </div>
            </router-link>
          </div>
          <router-link
            to="/admin/dashboard"
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
        <DocumentTextIcon class="h-24 w-24 text-gray-300 mx-auto mb-4" />
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Laporan</h2>
        <p class="text-gray-600 mb-6">Halaman ini akan menyediakan berbagai laporan presensi</p>
        <div class="space-y-2 text-sm text-gray-500">
          <p>✓ Laporan harian, mingguan, bulanan</p>
          <p>✓ Laporan keterlambatan</p>
          <p>✓ Laporan kinerja satpam</p>
          <p>✓ Statistik dan grafik</p>
          <p>✓ Export ke PDF dan Excel</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { DocumentTextIcon } from '@heroicons/vue/24/outline'
</script>
