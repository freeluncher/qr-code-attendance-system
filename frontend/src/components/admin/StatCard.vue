<!-- Reusable Stat Card Component -->
<template>
  <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <component :is="icon" :class="['h-8 w-8', iconColor]" />
      </div>
      <div class="ml-4 w-0 flex-1">
        <dl>
          <dt class="text-sm font-medium text-gray-500 truncate">{{ title }}</dt>
          <dd class="text-2xl font-semibold text-gray-900">{{ value }}</dd>
          <dd class="text-xs text-gray-500">{{ subtitle }}</dd>
        </dl>
      </div>
    </div>
  </div>
</template>

<script setup>
// Props
defineProps({
  icon: {
    type: Object,
    required: true
  },
  iconColor: {
    type: String,
    default: 'text-gray-600'
  },
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  }
})
</script>
