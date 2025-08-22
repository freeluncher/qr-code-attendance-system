<!-- Top Late Employees Component -->
<template>
  <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Top Terlambat</h3>

    <!-- Loading State -->
    <LoadingSpinner v-if="loading" class="py-4" />

    <!-- Late Employees List -->
    <div v-else-if="employees.length > 0" class="space-y-4">
      <div
        v-for="(item, index) in employees"
        :key="index"
        class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0 h-10 w-10">
            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
              <UserIcon class="h-5 w-5 text-red-600" />
            </div>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-900">{{ item.name }}</p>
            <p class="text-xs text-gray-500">{{ item.location }}</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-sm font-semibold text-red-600">{{ item.lateCount }}x</p>
          <p class="text-xs text-gray-500">{{ chartPeriod }} hari</p>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <EmptyState
      v-else
      :icon="CheckCircleIcon"
      title="Tidak ada satpam yang terlambat"
      subtitle="Performa sangat baik!"
      icon-color="text-gray-300"
      class="py-8"
    />
  </div>
</template>

<script setup>
import { UserIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import LoadingSpinner from './LoadingSpinner.vue'
import EmptyState from './EmptyState.vue'

// Props
defineProps({
  employees: {
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
