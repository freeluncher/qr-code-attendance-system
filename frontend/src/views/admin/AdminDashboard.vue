<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo and Title -->
          <div class="flex items-center">
            <div class="flex items-center">
              <AcademicCapIcon class="h-8 w-8 text-indigo-600 mr-3" />
              <div>
                <h1 class="text-xl font-semibold text-gray-900">QR Attendance System</h1>
                <p class="text-sm text-gray-500">Dashboard Admin</p>
              </div>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-4">
            <BellIcon class="h-6 w-6 text-gray-400 hover:text-gray-600 cursor-pointer" />
            <div class="flex items-center space-x-3">
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-500">Administrator</p>
              </div>
              <div class="h-10 w-10 rounded-full bg-indigo-600 flex items-center justify-center">
                <UserIcon class="h-6 w-6 text-white" />
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
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Users -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UsersIcon class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-4 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Satpam</dt>
                <dd class="text-2xl font-semibold text-gray-900">{{ stats.total_satpam || 0 }}</dd>
                <dd class="text-xs text-gray-500">
                  {{ stats.today?.attendance_rate || 0 }}% hadir hari ini
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- Total Locations -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <MapPinIcon class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-4 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Total Lokasi</dt>
                <dd class="text-2xl font-semibold text-gray-900">{{ stats.total_locations || 0 }}</dd>
                <dd class="text-xs text-gray-500">Pos keamanan aktif</dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- Today's Attendance -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CheckCircleIcon class="h-8 w-8 text-emerald-600" />
            </div>
            <div class="ml-4 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Absen Hari Ini</dt>
                <dd class="text-2xl font-semibold text-gray-900">{{ stats.today?.total_attendance || 0 }}</dd>
                <dd class="text-xs text-gray-500">
                  {{ stats.today?.on_time_count || 0 }} tepat waktu
                </dd>
              </dl>
            </div>
          </div>
        </div>

        <!-- Late Today -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-8 w-8 text-red-600" />
            </div>
            <div class="ml-4 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Terlambat Hari Ini</dt>
                <dd class="text-2xl font-semibold text-gray-900">{{ stats.today?.late_count || 0 }}</dd>
                <dd class="text-xs text-gray-500">Perlu perhatian</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and Tables Row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Attendance Line Chart -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Statistik Kehadiran 7 Hari Terakhir</h3>
            <div class="flex space-x-2">
              <button
                @click="changeChartPeriod(7)"
                :class="['px-3 py-1 text-sm rounded-md', chartPeriod === 7 ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700']"
              >
                7 Hari
              </button>
              <button
                @click="changeChartPeriod(14)"
                :class="['px-3 py-1 text-sm rounded-md', chartPeriod === 14 ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700']"
              >
                14 Hari
              </button>
              <button
                @click="changeChartPeriod(30)"
                :class="['px-3 py-1 text-sm rounded-md', chartPeriod === 30 ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700']"
              >
                30 Hari
              </button>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>

          <!-- Line Chart -->
          <AttendanceLineChart
            v-else
            :data="chartData"
            :title="`Trend Kehadiran ${chartPeriod} Hari Terakhir`"
          />
        </div>

        <!-- Attendance Distribution Donut Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Distribusi Kehadiran Hari Ini</h3>

          <!-- Loading State -->
          <div v-if="loading" class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
          </div>

          <!-- Donut Chart -->
          <AttendanceDonutChart
            v-else
            :data="stats.today"
            title="Kehadiran Hari Ini"
          />

          <!-- Summary Stats -->
          <div class="mt-4 grid grid-cols-2 gap-4 text-center">
            <div class="bg-green-50 rounded-lg p-3">
              <p class="text-2xl font-bold text-green-600">{{ stats.today?.on_time_count || 0 }}</p>
              <p class="text-xs text-green-700">Tepat Waktu</p>
            </div>
            <div class="bg-red-50 rounded-lg p-3">
              <p class="text-2xl font-bold text-red-600">{{ stats.today?.late_count || 0 }}</p>
              <p class="text-xs text-red-700">Terlambat</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Additional Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Weekly Bar Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Perbandingan Kehadiran per Hari</h3>

          <!-- Loading State -->
          <div v-if="loading" class="h-64 bg-gray-50 rounded-lg flex items-center justify-center">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
          </div>

          <!-- Bar Chart -->
          <AttendanceBarChart
            v-else
            :data="chartData"
            title="Kehadiran Harian"
            type="daily"
          />
        </div>

        <!-- Top Late Employees -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Satpam Paling Sering Terlambat</h3>

          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
          </div>

          <!-- Late Employees List -->
          <div v-else-if="topLateEmployees.length > 0" class="space-y-4">
            <div v-for="(item, index) in topLateEmployees" :key="index" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
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
          <div v-else class="text-center py-8">
            <CheckCircleIcon class="h-12 w-12 text-gray-300 mx-auto mb-2" />
            <p class="text-gray-500 text-sm">Tidak ada satpam yang terlambat</p>
            <p class="text-gray-400 text-xs">Performa sangat baik!</p>
          </div>
        </div>
      </div>

      <!-- Recent Activities and Quick Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activities -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
            <button class="text-sm text-indigo-600 hover:text-indigo-500">Lihat Semua</button>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
          </div>

          <!-- Activities List -->
          <div v-else-if="recentActivities.length > 0" class="flow-root">
            <ul class="-mb-8">
              <li v-for="(activity, index) in recentActivities" :key="index">
                <div class="relative pb-8">
                  <span v-if="index !== recentActivities.length - 1" class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                  <div class="relative flex space-x-3">
                    <div>
                      <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white" :class="getActivityIconBg(activity.type || 'default')">
                        <component :is="getActivityIcon(activity.icon)" class="h-5 w-5 text-white" aria-hidden="true" />
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
          <div v-else class="text-center py-8">
            <ChartBarIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">Belum ada aktivitas terbaru</p>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
          <div class="space-y-3">
            <router-link
              to="/admin/users"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <UsersIcon class="h-5 w-5 text-indigo-600 mr-3" />
              <span class="text-sm font-medium text-gray-900">Kelola Satpam</span>
            </router-link>

            <router-link
              to="/admin/locations"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <MapPinIcon class="h-5 w-5 text-green-600 mr-3" />
              <span class="text-sm font-medium text-gray-900">Kelola Lokasi</span>
            </router-link>

            <router-link
              to="/admin/qrcodes"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <QrCodeIcon class="h-5 w-5 text-purple-600 mr-3" />
              <span class="text-sm font-medium text-gray-900">Kelola QR Code</span>
            </router-link>

            <router-link
              to="/admin/reports"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <DocumentTextIcon class="h-5 w-5 text-blue-600 mr-3" />
              <span class="text-sm font-medium text-gray-900">Lihat Laporan</span>
            </router-link>

            <router-link
              to="/admin/attendances"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ClipboardDocumentCheckIcon class="h-5 w-5 text-emerald-600 mr-3" />
              <span class="text-sm font-medium text-gray-900">Data Presensi</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- AI Predictions Section -->
      <div class="mt-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg shadow-sm p-6 text-white">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-medium">Prediksi AI - Satpam Berisiko Terlambat</h3>
            <p class="text-purple-100 text-sm">Berdasarkan analisis 7 hari terakhir</p>
          </div>
          <CpuChipIcon class="h-8 w-8 text-purple-200" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="(prediction, index) in aiPredictions" :key="index" class="bg-white bg-opacity-20 rounded-lg p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="font-medium">{{ prediction.name }}</p>
                <p class="text-sm text-purple-100">{{ prediction.location }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold">{{ prediction.riskScore }}%</p>
                <p class="text-xs text-purple-100">risiko</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import dashboardAPI from '../../services/dashboard'

// Chart Components
import AttendanceLineChart from '../../components/charts/AttendanceLineChart.vue'
import AttendanceDonutChart from '../../components/charts/AttendanceDonutChart.vue'
import AttendanceBarChart from '../../components/charts/AttendanceBarChart.vue'

import {
  AcademicCapIcon,
  UsersIcon,
  MapPinIcon,
  CheckCircleIcon,
  ClockIcon,
  ChartBarIcon,
  UserIcon,
  BellIcon,
  ArrowRightOnRectangleIcon,
  QrCodeIcon,
  DocumentTextIcon,
  ClipboardDocumentCheckIcon,
  CpuChipIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

// Reactive state
const loading = ref(false)
const chartPeriod = ref(7) // Default to 7 days

// Stats data
const stats = ref({
  total_satpam: 0,
  total_locations: 0,
  total_qr_codes: 0,
  today: {
    total_attendance: 0,
    late_count: 0,
    on_time_count: 0,
    attendance_rate: 0
  }
})

// Top late employees data
const topLateEmployees = ref([])

// Recent activities data
const recentActivities = ref([])

// AI Predictions data (mock for now)
const aiPredictions = ref([
  { name: 'Pak Slamet', location: 'Pos Barat', riskScore: 85 },
  { name: 'Pak Joko', location: 'Pos Utara', riskScore: 72 },
  { name: 'Pak Wawan', location: 'Pos Selatan', riskScore: 68 }
])

// Chart data
const chartData = ref([])

// Methods
const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const loadDashboardData = async () => {
  loading.value = true
  try {
    // Load all dashboard data using proper API endpoints
    const [statsData, activities, lateEmployees, chartDataResult] = await Promise.all([
      dashboardAPI.getAdminStats(),
      dashboardAPI.getRecentActivities(5),
      dashboardAPI.getTopLateEmployees(chartPeriod.value, 5),
      dashboardAPI.getAttendanceChartData(chartPeriod.value)
    ])

    stats.value = statsData
    recentActivities.value = dashboardAPI.formatActivities(activities)
    topLateEmployees.value = lateEmployees.map(emp => ({
      name: emp.name,
      location: emp.location,
      lateCount: emp.late_count
    }))
    chartData.value = chartDataResult

  } catch (error) {
    console.error('Error loading dashboard data:', error)
    // Fallback to default data on error
    stats.value = {
      total_satpam: 0,
      total_locations: 0,
      total_qr_codes: 0,
      today: {
        total_attendance: 0,
        late_count: 0,
        on_time_count: 0,
        attendance_rate: 0
      }
    }
  } finally {
    loading.value = false
  }
}

// Change chart period and reload data
const changeChartPeriod = async (days) => {
  chartPeriod.value = days
  loading.value = true

  try {
    // Reload chart data and late employees with new period
    const [lateEmployees, chartDataResult] = await Promise.all([
      dashboardAPI.getTopLateEmployees(days, 5),
      dashboardAPI.getAttendanceChartData(days)
    ])

    topLateEmployees.value = lateEmployees.map(emp => ({
      name: emp.name,
      location: emp.location,
      lateCount: emp.late_count
    }))
    chartData.value = chartDataResult

  } catch (error) {
    console.error('Error loading chart data:', error)
  } finally {
    loading.value = false
  }
}

// Format activities with proper icons
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
    'success': 'bg-green-500',
    'warning': 'bg-red-500',
    'info': 'bg-blue-500',
    'default': 'bg-purple-500'
  }
  return backgrounds[type] || backgrounds.default
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script>

<style scoped>
/* Custom animations */
.fade-in {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hover effects */
.hover-scale:hover {
  transform: scale(1.02);
  transition: transform 0.2s ease-in-out;
}

/* Gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
</style>
