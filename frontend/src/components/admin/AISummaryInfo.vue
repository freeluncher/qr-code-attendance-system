<!-- AI Summary Info Component -->
<template>
  <div class="mt-6 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-20">
    <h4 class="text-sm font-semibold text-gray-800 mb-2">📊 Ringkasan Analisis AI</h4>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-700 mb-3">
      <div>
        <span class="font-medium text-gray-800">🎯 Model:</span> Weekly Risk Assessment
      </div>
      <div>
        <span class="font-medium text-gray-800">📅 Periode:</span> Prediksi 7 hari ke depan
      </div>
      <div>
        <span class="font-medium text-gray-800">🔍 Data:</span> Analisis pola 7 hari terakhir
      </div>
    </div>

    <!-- Special message for few predictions -->
    <div
      v-if="predictionsCount < 3 && predictionsCount > 0"
      class="bg-green-600 bg-opacity-90 rounded-lg p-3 mt-3"
    >
      <div class="flex items-center">
        <svg
          class="h-5 w-5 text-white mr-2"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <p class="text-white text-xs font-medium">
          ✅ Kabar Baik! Hanya {{ predictionsCount }} dari 7 satpam yang berisiko tinggi.
          Tingkat kehadiran secara keseluruhan dalam kondisi baik.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
// Props
defineProps({
  predictionsCount: {
    type: Number,
    required: true
  }
})
</script>
