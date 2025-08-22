<!-- Additional Charts Row Component -->
<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Weekly Bar Chart -->
    <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
      <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Perbandingan Harian</h3>

      <!-- Loading State -->
      <LoadingSpinner v-if="loading" class="h-64" />

      <!-- Bar Chart -->
      <AttendanceBarChart
        v-else
        :data="chartData"
        title="Kehadiran Harian"
        type="daily"
      />
    </div>

    <!-- Top Late Employees -->
    <TopLateEmployees
      :employees="topLateEmployees"
      :chart-period="chartPeriod"
      :loading="loading"
    />
  </div>
</template>

<script setup>
import AttendanceBarChart from '../charts/AttendanceBarChart.vue'
import TopLateEmployees from './TopLateEmployees.vue'
import LoadingSpinner from './LoadingSpinner.vue'

// Props
defineProps({
  chartData: {
    type: Array,
    required: true
  },
  topLateEmployees: {
    type: Array,
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
</script>
