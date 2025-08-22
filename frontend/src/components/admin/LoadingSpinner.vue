<!-- Loading Spinner Component -->
<template>
  <div :class="['bg-gray-50 rounded-lg flex items-center justify-center', className]">
    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
  </div>
</template>

<script setup>
// Props
defineProps({
  className: {
    type: String,
    default: 'h-32'
  }
})
</script>
