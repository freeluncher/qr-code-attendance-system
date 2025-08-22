<!-- Quick Action Link Component -->
<template>
  <router-link
    :to="to"
    class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-all duration-200 hover:shadow-sm group"
  >
    <component
      :is="icon"
      :class="['h-5 w-5 mr-3 transition-colors duration-200', iconColor, 'group-hover:scale-110']"
    />
    <span class="text-sm font-medium text-gray-900 group-hover:text-gray-800">{{ label }}</span>
  </router-link>
</template>

<script setup>
// Props
defineProps({
  to: {
    type: String,
    required: true
  },
  icon: {
    type: Object,
    required: true
  },
  iconColor: {
    type: String,
    default: 'text-gray-600'
  },
  label: {
    type: String,
    required: true
  }
})
</script>
