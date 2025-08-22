<!-- AI Prediction Card Component -->
<template>
  <div class="bg-white rounded-lg p-4 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center">
        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
          {{ prediction.name.split(' ')[1]?.charAt(0) || prediction.name.charAt(0) }}
        </div>
        <div>
          <p class="font-semibold text-gray-900 text-sm">{{ prediction.name }}</p>
          <p class="text-xs text-gray-500 flex items-center">
            <MapPinIcon class="h-3 w-3 mr-1" />
            {{ prediction.location }}
          </p>
          <p v-if="prediction.predicted_for_week" class="text-xs text-blue-600 font-medium mt-1">
            {{ prediction.predicted_for_week }}
          </p>
        </div>
      </div>
    </div>

    <!-- Risk Score with Progress Bar -->
    <div class="space-y-2">
      <div class="flex justify-between items-center">
        <span class="text-xs font-medium text-gray-700">Risk Score</span>
        <span
          class="text-sm font-bold"
          :class="getRiskScoreColor(prediction.riskScore)"
        >
          {{ prediction.riskScore }}%
        </span>
      </div>

      <div class="w-full bg-gray-200 rounded-full h-2">
        <div
          class="h-2 rounded-full transition-all duration-500"
          :class="getRiskScoreGradient(prediction.riskScore)"
          :style="{ width: `${prediction.riskScore}%` }"
        ></div>
      </div>

      <p class="text-xs text-gray-500 text-center">
        <span
          class="font-medium"
          :class="getRiskScoreColor(prediction.riskScore)"
        >
          {{ getRiskLabel(prediction.riskScore) }}
        </span>
      </p>

      <!-- Reason -->
      <div v-if="prediction.reason" class="mt-3 p-3 bg-gray-50 rounded-lg">
        <p class="text-xs text-gray-700 font-medium mb-1">Alasan Prediksi:</p>
        <p class="text-xs text-gray-600">{{ prediction.reason }}</p>
      </div>

      <!-- Action Badge -->
      <div class="mt-3 flex justify-center">
        <span
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
          :class="getRiskBadgeClass(prediction.riskScore)"
        >
          {{ getActionText(prediction.riskScore) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { MapPinIcon } from '@heroicons/vue/24/outline'

// Props
defineProps({
  prediction: {
    type: Object,
    required: true
  }
})

// Methods
const getRiskScoreColor = (score) => {
  if (score >= 80) return 'text-red-600'
  if (score >= 60) return 'text-yellow-600'
  return 'text-green-600'
}

const getRiskScoreGradient = (score) => {
  if (score >= 80) return 'bg-gradient-to-r from-red-400 to-red-600'
  if (score >= 60) return 'bg-gradient-to-r from-yellow-400 to-yellow-600'
  return 'bg-gradient-to-r from-green-400 to-green-600'
}

const getRiskLabel = (score) => {
  if (score >= 80) return 'Risiko Tinggi'
  if (score >= 60) return 'Risiko Sedang'
  return 'Risiko Rendah'
}

const getRiskBadgeClass = (score) => {
  if (score >= 80) return 'bg-red-100 text-red-800'
  if (score >= 60) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const getActionText = (score) => {
  if (score >= 80) return '⚠️ Perlu Perhatian Khusus'
  if (score >= 60) return '⚡ Pantau Lebih Ketat'
  return '✅ Kondisi Terkontrol'
}
</script>
