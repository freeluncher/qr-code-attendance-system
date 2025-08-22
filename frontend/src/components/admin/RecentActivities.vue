<!-- Recent Activities Component -->
<template>
  <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-base sm:text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
      <button class="text-sm text-indigo-600 hover:text-indigo-500 hidden sm:block transition-colors duration-200">
        Lihat Semua
      </button>
    </div>

    <!-- Loading State -->
    <LoadingSpinner v-if="loading" class="py-8" />

    <!-- Activities List -->
    <div v-else-if="activities.length > 0" class="flow-root">
      <ul class="-mb-8">
        <li v-for="(activity, index) in activities" :key="index">
          <div class="relative pb-8">
            <span
              v-if="index !== activities.length - 1"
              class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
              aria-hidden="true"
            ></span>
            <div class="relative flex space-x-3">
              <div>
                <span
                  class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white transition-all duration-200 hover:ring-gray-50"
                  :class="getActivityIconBg(activity.type || 'default')"
                >
                  <component
                    :is="getActivityIcon(activity.icon)"
                    class="h-5 w-5 text-white"
                    aria-hidden="true"
                  />
                </span>
              </div>
              <div class="flex-1 min-w-0">
                <div>
                  <div class="text-sm">
                    <span class="font-medium text-gray-900">{{ activity.user }}</span>
                    {{ activity.action }}
                  </div>
                  <p class="mt-0.5 text-sm text-gray-500">{{ activity.time }}</p>
                </div>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Empty State -->
    <EmptyState
      v-else
      :icon="ChartBarIcon"
      title="Belum ada aktivitas terbaru"
      icon-color="text-gray-300"
      class="py-8"
    />
  </div>
</template>

<script setup>
import {
  CheckCircleIcon,
  ClockIcon,
  MapPinIcon,
  QrCodeIcon,
  ChartBarIcon
} from '@heroicons/vue/24/outline'
import LoadingSpinner from './LoadingSpinner.vue'
import EmptyState from './EmptyState.vue'

// Props
defineProps({
  activities: {
    type: Array,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  }
})

// Methods
const getActivityIcon = (iconName) => {
  const icons = {
    'CheckCircleIcon': CheckCircleIcon,
    'ClockIcon': ClockIcon,
    'MapPinIcon': MapPinIcon,
    'QrCodeIcon': QrCodeIcon
  }
  return icons[iconName] || CheckCircleIcon
}

const getActivityIconBg = (type) => {
  const backgrounds = {
    'success': 'bg-green-500 hover:bg-green-600',
    'warning': 'bg-red-500 hover:bg-red-600',
    'info': 'bg-blue-500 hover:bg-blue-600',
    'default': 'bg-purple-500 hover:bg-purple-600'
  }
  return backgrounds[type] || backgrounds.default
}
</script>
