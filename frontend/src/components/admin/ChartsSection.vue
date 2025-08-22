<!-- Charts Section Component -->
<template>
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Attendance Line Chart -->
    <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-base sm:text-lg font-medium text-gray-900">Statistik Kehadiran</h3>
        <div class="flex space-x-1 sm:space-x-2">
          <button
            v-for="period in periods"
            :key="period.value"
            @click="$emit('changePeriod', period.value)"
            :class="[
              'px-2 py-1 text-xs sm:px-3 sm:text-sm rounded-md transition-colors duration-200',
              chartPeriod === period.value
                ? 'bg-indigo-100 text-indigo-700'
                : 'text-gray-500 hover:text-gray-700'
            ]"
          >
            {{ period.label }}
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <LoadingSpinner v-if="loading" class="h-64" />

      <!-- Line Chart -->
      <AttendanceLineChart
        v-else
        :data="chartData"
        :title="`Trend Kehadiran ${chartPeriod} Hari Terakhir`"
      />
    </div>

    <!-- Attendance Distribution Donut Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
      <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Distribusi Hari Ini</h3>

      <!-- Loading State -->
      <LoadingSpinner v-if="loading" class="h-64" />

      <!-- Donut Chart -->
      <template v-else>
        <AttendanceDonutChart
          :data="stats.today"
          title="Kehadiran Hari Ini"
        />

        <!-- Summary Stats -->
        <div class="mt-4 grid grid-cols-2 gap-4 text-center">
          <div class="bg-green-50 rounded-lg p-3 hover:bg-green-100 transition-colors duration-200">
            <p class="text-2xl font-bold text-green-600">{{ stats.today?.on_time_count || 0 }}</p>
            <p class="text-xs text-green-700">Tepat Waktu</p>
          </div>
          <div class="bg-red-50 rounded-lg p-3 hover:bg-red-100 transition-colors duration-200">
            <p class="text-2xl font-bold text-red-600">{{ stats.today?.late_count || 0 }}</p>
            <p class="text-xs text-red-700">Terlambat</p>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import AttendanceLineChart from '../charts/AttendanceLineChart.vue'
import AttendanceDonutChart from '../charts/AttendanceDonutChart.vue'
import LoadingSpinner from './LoadingSpinner.vue'

// Props
defineProps({
  chartData: {
    type: Array,
    required: true
  },
  stats: {
    type: Object,
    required: true
  },
  chartPeriod: {
    type: Number,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// Emits
defineEmits(['changePeriod'])

// Computed
const periods = computed(() => [
  { value: 7, label: '7 Hari' },
  { value: 14, label: '14 Hari' },
  { value: 30, label: '30 Hari' }
])
</script>
