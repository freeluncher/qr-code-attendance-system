<!-- Dashboard Header Component -->
<template>
  <nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <!-- Logo and Title -->
        <div class="flex items-center">
          <div class="flex items-center">
            <AcademicCapIcon class="h-8 w-8 text-indigo-600 mr-3" />
            <div class="hidden sm:block">
              <h1 class="text-xl font-semibold text-gray-900">QR Attendance System</h1>
              <p class="text-sm text-gray-500">Dashboard Admin</p>
            </div>
          </div>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-2 sm:space-x-4">
          <BellIcon class="h-6 w-6 text-gray-400 hover:text-gray-600 cursor-pointer" />
          <div class="flex items-center space-x-2 sm:space-x-3">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
              <p class="text-xs text-gray-500">Administrator</p>
            </div>
            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-indigo-600 flex items-center justify-center">
              <UserIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
            </div>
            <button @click="$emit('logout')" class="text-gray-400 hover:text-gray-600">
              <ArrowRightOnRectangleIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import {
  AcademicCapIcon,
  BellIcon,
  UserIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline'

// Props
defineProps({
  user: {
    type: Object,
    default: () => ({})
  }
})

// Emits
defineEmits(['logout'])
</script>
