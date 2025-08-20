<template>
  <div class="w-full h-64">
    <Bar
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
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { Bar } from 'vue-chartjs'
import { ChartBarIcon } from '@heroicons/vue/24/outline'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
)

const props = defineProps({
  data: {
    type: Array,
    default: () => []
  },
  title: {
    type: String,
    default: 'Attendance Bar Chart'
  },
  type: {
    type: String,
    default: 'daily', // daily, weekly, monthly
    validator: (value) => ['daily', 'weekly', 'monthly'].includes(value)
  }
})

// Computed chart data
const chartData = computed(() => {
  if (!props.data || props.data.length === 0) {
    return { datasets: [] }
  }

  return {
    labels: props.data.map(item => item.formatted_date || item.day_name || item.label),
    datasets: [
      {
        label: 'Tepat Waktu',
        data: props.data.map(item => item.on_time || 0),
        backgroundColor: 'rgba(34, 197, 94, 0.8)',
        borderColor: 'rgb(34, 197, 94)',
        borderWidth: 1,
        borderRadius: 4,
        borderSkipped: false,
      },
      {
        label: 'Terlambat',
        data: props.data.map(item => item.late || 0),
        backgroundColor: 'rgba(239, 68, 68, 0.8)',
        borderColor: 'rgb(239, 68, 68)',
        borderWidth: 1,
        borderRadius: 4,
        borderSkipped: false,
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
          return label
        },
        footer: function(context) {
          const dataPoint = props.data[context[0].dataIndex]
          if (dataPoint?.total) {
            return `Total: ${dataPoint.total}`
          }
          return ''
        }
      }
    }
  },
  scales: {
    x: {
      display: true,
      title: {
        display: true,
        text: getXAxisTitle(),
        font: {
          size: 12,
          weight: 'bold'
        },
        color: '#6b7280'
      },
      grid: {
        display: false
      },
      ticks: {
        color: '#6b7280',
        font: {
          size: 11
        },
        maxRotation: 45,
        minRotation: 0
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
    bar: {
      borderWidth: 2,
    }
  }
}))

function getXAxisTitle() {
  switch (props.type) {
    case 'weekly':
      return 'Minggu'
    case 'monthly':
      return 'Bulan'
    default:
      return 'Tanggal'
  }
}
</script>

<style scoped>
canvas {
  max-height: 250px !important;
}
</style>
