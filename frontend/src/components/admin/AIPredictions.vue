<!-- AI Predictions Component -->
<template>
  <div class="mt-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h3 class="text-lg sm:text-xl font-bold flex items-center">
          <CpuChipIcon class="h-6 w-6 mr-2" />
          Prediksi AI - Risiko Terlambat Minggu Depan
        </h3>
        <p class="text-indigo-100 text-sm mt-1">
          Analisis berdasarkan pola kehadiran 7 hari terakhir - Top Satpam Berisiko
        </p>
        <p v-if="predictions.length > 0" class="text-indigo-200 text-xs mt-1">
          Menampilkan {{ predictions.length }} prediksi berisiko tinggi dari data terkini
        </p>
        <p v-if="predictions.length > 0 && predictions.length < 3" class="text-yellow-200 text-xs mt-1">
          ⚠️ Hanya {{ predictions.length }} satpam dengan risiko di atas 10% - kondisi kehadiran relatif baik
        </p>
      </div>
      <div class="flex items-center space-x-3">
        <button
          @click="$emit('generate')"
          :disabled="loading"
          class="bg-blue-500 bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 disabled:opacity-50"
        >
          <CpuChipIcon class="h-4 w-4" />
          <span>{{ loading ? 'Generating...' : 'Generate' }}</span>
        </button>
        <div class="bg-blue-500 bg-opacity-20 rounded-full p-3">
          <CpuChipIcon class="h-8 w-8 text-white" />
        </div>
      </div>
    </div>

    <!-- Content -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
    </div>

    <div v-else-if="predictions.length > 0" class="space-y-4">
      <!-- Predictions Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <PredictionCard
          v-for="(prediction, index) in predictions"
          :key="index"
          :prediction="prediction"
        />
      </div>

      <!-- AI Summary Info -->
      <AISummaryInfo :predictions-count="predictions.length" />
    </div>

    <!-- Empty State -->
    <EmptyState
      v-else
      :icon="CpuChipIcon"
      title="Belum ada prediksi AI tersedia"
      subtitle="Klik tombol Generate untuk membuat prediksi berdasarkan data 7 hari terakhir"
      icon-color="text-indigo-200"
      class="py-8"
    />
  </div>
</template>

<script setup>
import { CpuChipIcon } from '@heroicons/vue/24/outline'
import PredictionCard from './PredictionCard.vue'
import AISummaryInfo from './AISummaryInfo.vue'
import EmptyState from './EmptyState.vue'

// Props
defineProps({
  predictions: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// Emits
defineEmits(['generate'])
</script>
