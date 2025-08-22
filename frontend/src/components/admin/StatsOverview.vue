<!-- Stats Overview Component -->
<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Users -->
    <StatCard
      :icon="UsersIcon"
      icon-color="text-blue-600"
      title="Total Satpam"
      :value="stats.total_satpam || 0"
      :subtitle="`${stats.today?.attendance_rate || 0}% hadir hari ini`"
    />

    <!-- Total Locations -->
    <StatCard
      :icon="MapPinIcon"
      icon-color="text-green-600"
      title="Total Lokasi"
      :value="stats.total_locations || 0"
      subtitle="Pos keamanan aktif"
    />

    <!-- Today's Attendance -->
    <StatCard
      :icon="CheckCircleIcon"
      icon-color="text-emerald-600"
      title="Absen Hari Ini"
      :value="stats.today?.total_attendance || 0"
      :subtitle="`${stats.today?.on_time_count || 0} tepat waktu`"
    />

    <!-- Late Today -->
    <StatCard
      :icon="ClockIcon"
      icon-color="text-red-600"
      title="Terlambat Hari Ini"
      :value="stats.today?.late_count || 0"
      subtitle="Perlu perhatian"
    />
  </div>
</template>

<script setup>
import StatCard from './StatCard.vue'
import {
  UsersIcon,
  MapPinIcon,
  CheckCircleIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

// Props
defineProps({
  stats: {
    type: Object,
    required: true,
    default: () => ({
      total_satpam: 0,
      total_locations: 0,
      today: {
        total_attendance: 0,
        late_count: 0,
        on_time_count: 0,
        attendance_rate: 0
      }
    })
  }
})
</script>
