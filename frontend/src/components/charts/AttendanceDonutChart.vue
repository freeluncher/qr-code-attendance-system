<template>
  <div class="w-full h-64">
    <Doughnut 
      v-if="chartData.datasets && chartData.datasets.length > 0"
      :data="chartData" 
      :options="chartOptions" 
      class="max-h-64"
    />
    <div v-else class="h-full bg-gray-50 rounded-lg flex items-center justify-center">
      <div class="text-center">
        <ChartPieIcon class="h-12 w-12 text-gray-400 mx-auto mb-2" />
        <p class="text-gray-500">Memuat data chart...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'
import { Doughnut } from 'vue-chartjs'
import { ChartPieIcon } from '@heroicons/vue/24/outline'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  },
  title: {
    type: String,
    default: 'Attendance Distribution'
  }
})

// Computed chart data
const chartData = computed(() => {
  if (!props.data || (!props.data.on_time_count && !props.data.late_count)) {
    return { datasets: [] }
  }

  const onTimeCount = props.data.on_time_count || 0
  const lateCount = props.data.late_count || 0
  const total = onTimeCount + lateCount

  if (total === 0) {
    return { datasets: [] }
  }

  return {
    labels: ['Tepat Waktu', 'Terlambat'],
    datasets: [
      {
        data: [onTimeCount, lateCount],
        backgroundColor: [
          'rgba(34, 197, 94, 0.8)',   // Green for on-time
          'rgba(239, 68, 68, 0.8)'    // Red for late
        ],
        borderColor: [
          'rgb(34, 197, 94)',
          'rgb(239, 68, 68)'
        ],
        borderWidth: 2,
        hoverBackgroundColor: [
          'rgba(34, 197, 94, 0.9)',
          'rgba(239, 68, 68, 0.9)'
        ],
        hoverBorderWidth: 3,
        cutout: '60%' // Makes it a donut chart
      }
    ]
  }
})

// Chart options
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    title: {
      display: true,
      text: props.title,
      font: {
        size: 16,
        weight: 'bold'
      },
      color: '#374151',
      padding: {
        bottom: 20
      }
    },
    legend: {
      display: true,
      position: 'bottom',
      labels: {
        usePointStyle: true,
        padding: 20,
        font: {
          size: 12
        }
      }
    },
    tooltip: {
      backgroundColor: '#1f2937',
      titleColor: '#f9fafb',
      bodyColor: '#f9fafb',
      borderColor: '#374151',
      borderWidth: 1,
      cornerRadius: 8,
      displayColors: true,
      titleFont: {
        size: 14,
        weight: 'bold'
      },
      bodyFont: {
        size: 13
      },
      callbacks: {
        label: function(context) {
          const label = context.label || ''
          const value = context.parsed
          const total = context.dataset.data.reduce((a, b) => a + b, 0)
          const percentage = total > 0 ? Math.round((value / total) * 100) : 0
          return `${label}: ${value} (${percentage}%)`
        }
      }
    }
  },
  interaction: {
    intersect: false
  },
  animation: {
    animateRotate: true,
    animateScale: true,
    duration: 1000
  }
}))
</script>

<style scoped>
canvas {
  max-height: 250px !important;
}
</style>
