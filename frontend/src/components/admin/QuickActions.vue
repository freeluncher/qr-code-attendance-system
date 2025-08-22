<!-- Quick Actions Component -->
<template>
  <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
    <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
    <div class="space-y-3">
      <QuickActionLink
        v-for="action in actions"
        :key="action.route"
        :to="action.route"
        :icon="action.icon"
        :icon-color="action.iconColor"
        :label="action.label"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import {
  UsersIcon,
  MapPinIcon,
  ClockIcon,
  QrCodeIcon,
  DocumentTextIcon,
  ClipboardDocumentCheckIcon,
  ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline'
import QuickActionLink from './QuickActionLink.vue'

// Computed
const actions = computed(() => [
  {
    route: '/admin/users',
    icon: UsersIcon,
    iconColor: 'text-indigo-600',
    label: 'Kelola Satpam'
  },
  {
    route: '/admin/locations',
    icon: MapPinIcon,
    iconColor: 'text-green-600',
    label: 'Kelola Lokasi'
  },
  {
    route: '/admin/shifts',
    icon: ClockIcon,
    iconColor: 'text-purple-600',
    label: 'Kelola Shift'
  },
  {
    route: '/admin/qrcodes',
    icon: QrCodeIcon,
    iconColor: 'text-purple-600',
    label: 'Kelola QR Code'
  },
  {
    route: '/admin/reports',
    icon: DocumentTextIcon,
    iconColor: 'text-blue-600',
    label: 'Lihat Laporan'
  },
  {
    route: '/admin/attendances',
    icon: ClipboardDocumentCheckIcon,
    iconColor: 'text-emerald-600',
    label: 'Data Presensi'
  },
  {
    route: '/admin/telegram',
    icon: ChatBubbleLeftRightIcon,
    iconColor: 'text-blue-500',
    label: 'Notifikasi Telegram'
  }
])
</script>
