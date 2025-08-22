<!-- Empty State Component -->
<template>
  <div :class="['text-center', className]">
    <component :is="icon" :class="['h-12 w-12 mx-auto mb-2', iconColor]" />
    <p class="text-gray-500 text-sm">{{ title }}</p>
    <p v-if="subtitle" class="text-gray-400 text-xs">{{ subtitle }}</p>
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
    default: 'text-gray-300'
  },
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  },
  className: {
    type: String,
    default: ''
  }
})
</script>
