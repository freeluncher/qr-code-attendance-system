<template>
  <div class="w-full h-64">
    <Line
      v-if="chartData.datasets && chartData.datasets.length > 0"
      :data="chartData"
      :options="chartOptions"
      class="max-h-64"
    />
    <div v-else class="h-full bg-gray-50 rounded-lg flex items-center justify-center">
      <div class="text-center">
        <ChartBarIcon class="h-12 w-12 text-gray-400 mx-auto mb-2" />
        <p class="text-gray-500">Memuat data chart...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { ChartBarIcon } from '@heroicons/vue/24/outline'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'Attendance Chart'
  }
})

// Computed chart data
const chartData = computed(() => {
  if (!props.data || props.data.length === 0) {
    return { datasets: [] }
  }

  return {
    labels: props.data.map(item => item.formatted_date || item.day_name),
    datasets: [
      {
        label: 'Total Kehadiran',
        data: props.data.map(item => item.total || 0),
        borderColor: 'rgb(99, 102, 241)',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointBackgroundColor: 'rgb(99, 102, 241)',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 6,
        pointHoverRadius: 8,
      },
      {
        label: 'Tepat Waktu',
        data: props.data.map(item => item.on_time || 0),
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        borderWidth: 2,
        fill: false,
        tension: 0.4,
        pointBackgroundColor: 'rgb(34, 197, 94)',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
      },
      {
        label: 'Terlambat',
        data: props.data.map(item => item.late || 0),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        borderWidth: 2,
        fill: false,
        tension: 0.4,
        pointBackgroundColor: 'rgb(239, 68, 68)',
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        pointRadius: 4,
        pointHoverRadius: 6,
      }
    ]
  }
})

// Chart options
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false,
  },
  plugins: {
    title: {
      display: true,
      text: props.title,
      font: {
        size: 16,
        weight: 'bold'
      },
      color: '#374151'
    },
    legend: {
      display: true,
      position: 'top',
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
        title: function(context) {
          const dataPoint = props.data[context[0].dataIndex]
          return dataPoint?.formatted_date || context[0].label
        },
        afterTitle: function(context) {
          const dataPoint = props.data[context[0].dataIndex]
          return dataPoint?.day_name ? `(${dataPoint.day_name})` : ''
        },
        label: function(context) {
          let label = context.dataset.label || ''
          if (label) {
            label += ': '
          }
          label += context.parsed.y

          // Add percentage for on-time rate
          if (context.datasetIndex === 1) { // On-time dataset
            const dataPoint = props.data[context.dataIndex]
            if (dataPoint?.on_time_percentage !== undefined) {
              label += ` (${dataPoint.on_time_percentage}%)`
            }
          }

          return label
        }
      }
    }
  },
  scales: {
    x: {
      display: true,
      title: {
        display: true,
        text: 'Tanggal',
        font: {
          size: 12,
          weight: 'bold'
        },
        color: '#6b7280'
      },
      grid: {
        display: true,
        color: '#f3f4f6'
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        }
      }
    },
    y: {
      display: true,
      title: {
        display: true,
        text: 'Jumlah Kehadiran',
        font: {
          size: 12,
          weight: 'bold'
        },
        color: '#6b7280'
      },
      beginAtZero: true,
      grid: {
        display: true,
        color: '#f3f4f6'
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        },
        stepSize: 1,
        callback: function(value) {
          return Math.round(value)
        }
      }
    }
  },
  elements: {
    point: {
      hoverBackgroundColor: '#ffffff',
      hoverBorderWidth: 3
    }
  }
}))
</script>

<style scoped>
canvas {
  max-height: 250px !important;
}
</style>
