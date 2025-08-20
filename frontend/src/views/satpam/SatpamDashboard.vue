<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo and Title -->
          <div class="flex items-center">
            <div class="flex items-center">
              <ShieldCheckIcon class="h-8 w-8 text-blue-600 mr-3" />
              <div class="hidden sm:block">
                <h1 class="text-xl font-semibold text-gray-900">QR Attendance System</h1>
                <p class="text-sm text-gray-500">Dashboard Satpam</p>
              </div>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <BellIcon class="h-6 w-6 text-gray-400 hover:text-gray-600 cursor-pointer" />
            <div class="flex items-center space-x-2 sm:space-x-3">
              <div class="text-right hidden sm:block">
                <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-500">Satpam</p>
              </div>
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-blue-600 flex items-center justify-center">
                <UserIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
              </div>
              <button @click="handleLogout" class="text-gray-400 hover:text-gray-600">
                <ArrowRightOnRectangleIcon class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Welcome Banner -->
      <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg p-4 sm:p-6 mb-6 sm:mb-8 text-white">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between">
          <div class="mb-4 sm:mb-0">
            <h2 class="text-xl sm:text-2xl font-bold mb-2">Selamat datang, {{ authStore.user?.name }}!</h2>
            <p class="text-blue-100 text-sm sm:text-base">{{ getCurrentGreeting() }} - {{ formatDate(new Date()) }}</p>
            <p class="text-xs sm:text-sm text-blue-100 mt-1">Shift: {{ currentShift?.name || 'Belum ada shift aktif' }}</p>
          </div>
          <div class="text-left sm:text-right">
            <div class="text-2xl sm:text-3xl font-bold">{{ currentTime }}</div>
            <div class="text-blue-100 text-sm">{{ formatDate(new Date()) }}</div>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <!-- Today's Attendance -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CheckCircleIcon class="h-6 w-6 sm:h-8 sm:w-8 text-green-600" />
            </div>
            <div class="ml-3 sm:ml-4">
              <p class="text-sm font-medium text-gray-500">Presensi Hari Ini</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ todayStats.attendance }}</p>
              <p class="text-xs text-gray-500">dari {{ todayStats.totalShifts }} shift</p>
            </div>
          </div>
        </div>

        <!-- This Month Performance -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ChartBarIcon class="h-6 w-6 sm:h-8 sm:w-8 text-blue-600" />
            </div>
            <div class="ml-3 sm:ml-4">
              <p class="text-sm font-medium text-gray-500">Performa Bulan Ini</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ monthStats.percentage }}%</p>
              <p class="text-xs text-gray-500">{{ monthStats.onTime }} tepat waktu</p>
            </div>
          </div>
        </div>

        <!-- Status -->
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200 sm:col-span-2 lg:col-span-1">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-6 w-6 sm:h-8 sm:w-8" :class="getStatusColor()" />
            </div>
            <div class="ml-3 sm:ml-4">
              <p class="text-sm font-medium text-gray-500">Status Saat Ini</p>
              <p class="text-xl sm:text-2xl font-semibold" :class="getStatusTextColor()">{{ getCurrentStatus() }}</p>
              <p class="text-xs text-gray-500">{{ getStatusDescription() }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8 mb-6 sm:mb-8">
        <!-- QR Attendance -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-4 sm:p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Presensi QR Code</h3>
            <p class="text-sm text-gray-500 mt-1">Scan QR Code untuk melakukan presensi</p>
          </div>
          <div class="p-4 sm:p-6">
            <div class="text-center">
              <div class="mx-auto h-20 w-20 sm:h-24 sm:w-24 bg-blue-50 rounded-full flex items-center justify-center mb-4">
                <QrCodeIcon class="h-10 w-10 sm:h-12 sm:w-12 text-blue-600" />
              </div>
              <button
                @click="openQRScanner"
                class="inline-flex items-center px-4 sm:px-6 py-2 sm:py-3 border border-transparent text-sm sm:text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto"
              >
                <QrCodeIcon class="h-5 w-5 mr-2" />
                Scan QR Code
              </button>
            </div>
          </div>
        </div>

        <!-- Current Location & Shift -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
          <div class="p-4 sm:p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi Shift</h3>
            <p class="text-sm text-gray-500 mt-1">Detail shift dan lokasi saat ini</p>
          </div>
          <div class="p-4 sm:p-6">
            <div class="space-y-4">
              <div class="flex items-start">
                <MapPinIcon class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">Lokasi</p>
                  <p class="text-sm text-gray-600 break-words">{{ currentLocation?.name || 'Belum ada lokasi' }}</p>
                  <p class="text-xs text-gray-500 break-words">{{ currentLocation?.address || '' }}</p>
                </div>
              </div>
              <div class="flex items-start">
                <ClockIcon class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">Jam Shift</p>
                  <p class="text-sm text-gray-600">
                    {{ currentShift ? `${currentShift.start_time} - ${currentShift.end_time}` : 'Belum ada shift aktif' }}
                  </p>
                </div>
              </div>
              <div class="flex items-start">
                <InformationCircleIcon class="h-5 w-5 text-gray-400 mt-0.5 mr-3 flex-shrink-0" />
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900">Catatan</p>
                  <p class="text-sm text-gray-600 break-words">{{ currentShift?.notes || 'Tidak ada catatan khusus' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 sm:mb-8">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Riwayat Presensi Terakhir</h3>
          <p class="text-sm text-gray-500 mt-1">7 hari terakhir</p>
        </div>
        <div class="p-4 sm:p-6">
          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>

          <!-- Attendance Records -->
          <div v-else-if="recentAttendance.length > 0" class="space-y-4">
            <div v-for="(record, index) in recentAttendance" :key="index" class="flex flex-col sm:flex-row sm:items-center justify-between p-3 sm:p-4 bg-gray-50 rounded-lg space-y-3 sm:space-y-0">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full flex items-center justify-center" :class="record.statusBg">
                    <CheckCircleIcon v-if="record.status === 'Tepat Waktu'" class="h-4 w-4 sm:h-5 sm:w-5" :class="record.iconColor" />
                    <ClockIcon v-else class="h-4 w-4 sm:h-5 sm:w-5" :class="record.iconColor" />
                  </div>
                </div>
                <div class="ml-3 sm:ml-4 min-w-0 flex-1">
                  <p class="text-sm font-medium text-gray-900 truncate">{{ record.date }}</p>
                  <p class="text-xs text-gray-500 truncate">{{ record.location }}</p>
                </div>
              </div>
              <div class="flex items-center justify-between sm:justify-end space-x-4 sm:space-x-6 text-right ml-11 sm:ml-0">
                <div class="flex-shrink-0">
                  <p class="text-sm font-medium text-gray-900">{{ record.checkIn || '-' }}</p>
                  <p class="text-xs text-gray-500">Check In</p>
                </div>
                <div class="flex-shrink-0">
                  <p class="text-sm font-medium text-gray-900">{{ record.checkOut || '-' }}</p>
                  <p class="text-xs text-gray-500">Check Out</p>
                </div>
                <div class="flex-shrink-0">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="record.statusClass">
                    {{ record.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-8">
            <ClipboardDocumentListIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">Belum ada riwayat presensi</p>
            <p class="text-gray-400 text-sm">Mulai presensi untuk melihat riwayat</p>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-4 sm:p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Aksi Cepat</h3>
        </div>
        <div class="p-4 sm:p-6">
          <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <button
              @click="viewSchedule"
              class="flex flex-col sm:flex-row items-center p-3 sm:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center sm:text-left"
            >
              <CalendarIcon class="h-6 w-6 text-blue-600 mb-2 sm:mb-0 sm:mr-3 flex-shrink-0" />
              <div>
                <p class="text-sm font-medium text-gray-900">Jadwal Shift</p>
                <p class="text-xs text-gray-500 hidden sm:block">Lihat jadwal</p>
              </div>
            </button>

            <button
              @click="viewHistory"
              class="flex flex-col sm:flex-row items-center p-3 sm:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center sm:text-left"
            >
              <ClipboardDocumentListIcon class="h-6 w-6 text-green-600 mb-2 sm:mb-0 sm:mr-3 flex-shrink-0" />
              <div>
                <p class="text-sm font-medium text-gray-900">Riwayat</p>
                <p class="text-xs text-gray-500 hidden sm:block">Presensi lengkap</p>
              </div>
            </button>

            <button
              @click="reportIssue"
              class="flex flex-col sm:flex-row items-center p-3 sm:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center sm:text-left"
            >
              <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600 mb-2 sm:mb-0 sm:mr-3 flex-shrink-0" />
              <div>
                <p class="text-sm font-medium text-gray-900">Lapor Masalah</p>
                <p class="text-xs text-gray-500 hidden sm:block">Kirim laporan</p>
              </div>
            </button>

            <button
              @click="viewProfile"
              class="flex flex-col sm:flex-row items-center p-3 sm:p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors text-center sm:text-left"
            >
              <UserCircleIcon class="h-6 w-6 text-purple-600 mb-2 sm:mb-0 sm:mr-3 flex-shrink-0" />
              <div>
                <p class="text-sm font-medium text-gray-900">Profil</p>
                <p class="text-xs text-gray-500 hidden sm:block">Ubah profil</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import satpamAPI from '../../services/satpam'
import {
  ShieldCheckIcon,
  UserIcon,
  BellIcon,
  ArrowRightOnRectangleIcon,
  CheckCircleIcon,
  ChartBarIcon,
  ClockIcon,
  QrCodeIcon,
  MapPinIcon,
  InformationCircleIcon,
  CalendarIcon,
  ClipboardDocumentListIcon,
  ExclamationTriangleIcon,
  UserCircleIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const currentTime = ref('')
const currentShift = ref(null)
const currentLocation = ref(null)
const loading = ref(false)

// Stats
const todayStats = ref({
  attendance: 0,
  totalShifts: 3
})

const monthStats = ref({
  percentage: 0,
  onTime: 0
})

// Recent attendance records
const recentAttendance = ref([])

// Methods
const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}

const formatDate = (date) => {
  return date.toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const getCurrentGreeting = () => {
  const hour = new Date().getHours()
  if (hour < 12) return 'Selamat pagi'
  if (hour < 15) return 'Selamat siang'
  if (hour < 18) return 'Selamat sore'
  return 'Selamat malam'
}

const getCurrentStatus = () => {
  const now = new Date()
  const hour = now.getHours()

  if (hour >= 8 && hour < 16) return 'Bertugas'
  if (hour >= 16 && hour < 18) return 'Selesai Shift'
  return 'Tidak Bertugas'
}

const getStatusColor = () => {
  const status = getCurrentStatus()
  if (status === 'Bertugas') return 'text-green-600'
  if (status === 'Selesai Shift') return 'text-blue-600'
  return 'text-gray-600'
}

const getStatusTextColor = () => {
  const status = getCurrentStatus()
  if (status === 'Bertugas') return 'text-green-900'
  if (status === 'Selesai Shift') return 'text-blue-900'
  return 'text-gray-900'
}

const getStatusDescription = () => {
  const status = getCurrentStatus()
  if (status === 'Bertugas') return 'Sedang dalam jam kerja'
  if (status === 'Selesai Shift') return 'Shift telah selesai'
  return 'Di luar jam kerja'
}

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const openQRScanner = () => {
  router.push('/satpam/attendance')
}

const viewSchedule = () => {
  router.push('/satpam/schedule')
}

const viewHistory = () => {
  router.push('/satpam/history')
}

const reportIssue = () => {
  alert('Fitur lapor masalah akan segera tersedia')
}

const viewProfile = () => {
  alert('Halaman profil akan segera tersedia')
}

const loadData = async () => {
  if (!authStore.user?.id) return

  loading.value = true
  try {
    // Load satpam dashboard data using proper API endpoints
    const [statsData, activitiesData] = await Promise.all([
      satpamAPI.getDashboardStats(),
      satpamAPI.getRecentActivities(7)
    ])

    console.log('Stats Data:', statsData)
    console.log('Activities Data:', activitiesData)

    // Check if statsData has the expected structure or error
    if (!statsData || statsData.error) {
      throw new Error(statsData?.error || 'Invalid stats data structure')
    }
    if (!activitiesData || activitiesData.error) {
      throw new Error(activitiesData?.error || 'Invalid activities data structure')
    }

    // Check if statsData has the required properties
    if (!statsData.today) {
      throw new Error('Stats data missing required properties')
    }

    // Update today stats
    todayStats.value = {
      attendance: statsData.today.has_attendance ? 1 : 0,
      totalShifts: 1 // This could come from shifts API later
    }

    // Update month stats
    monthStats.value = {
      percentage: statsData.this_month.on_time_rate || 0,
      onTime: statsData.this_month.on_time_count || 0
    }

    // Format attendance data for display
    if (activitiesData && activitiesData.length > 0) {
      recentAttendance.value = activitiesData.map(activity => ({
        id: activity.id || Math.random(),
        date: activity.date || formatDate(new Date()),
        location: activity.location || 'Pos Utama',
        checkIn: activity.time || '-',
        // ...existing code...
      }))
    } else {
      recentAttendance.value = []
    }
  } catch (error) {
    console.error('Error loading satpam data:', error)

    // If it's an authentication error, redirect to login
    if (error.error === 'User not authenticated' || error.message?.includes('authenticated')) {
      router.push('/login')
      return
    }

    // Otherwise, set fallback data
    todayStats.value = { attendance: 0, totalShifts: 1 }
    monthStats.value = { percentage: 0, onTime: 0 }
    recentAttendance.value = []
  } finally {
    loading.value = false
  }
}

// Timer for clock
let timeInterval = null

onMounted(() => {
  updateTime()
  timeInterval = setInterval(updateTime, 1000)
  loadData()
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
})
</script>
