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
                <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
                <p class="text-xs text-gray-500">Administrator</p>
              </div>
              <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-indigo-600 flex items-center justify-center">
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
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Statistik Kehadiran</h3>
            <div class="flex space-x-1 sm:space-x-2">
              <button
                @click="changeChartPeriod(7)"
                :class="['px-2 py-1 text-xs sm:px-3 sm:text-sm rounded-md', chartPeriod === 7 ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700']"
              >
                7 Hari
              </button>
              <button
                @click="changeChartPeriod(14)"
                :class="['px-2 py-1 text-xs sm:px-3 sm:text-sm rounded-md', chartPeriod === 14 ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700']"
              >
                14 Hari
              </button>
              <button
                @click="changeChartPeriod(30)"
                :class="['px-2 py-1 text-xs sm:px-3 sm:text-sm rounded-md', chartPeriod === 30 ? 'bg-indigo-100 text-indigo-700' : 'text-gray-500 hover:text-gray-700']"
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
          <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Distribusi Hari Ini</h3>

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
          <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Perbandingan Harian</h3>

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
          <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Top Terlambat</h3>

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
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Aktivitas Terbaru</h3>
            <button class="text-sm text-indigo-600 hover:text-indigo-500 hidden sm:block">Lihat Semua</button>
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
          <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-4">Aksi Cepat</h3>
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
              to="/admin/shifts"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ClockIcon class="h-5 w-5 text-purple-600 mr-3" />
              <span class="text-sm font-medium text-gray-900">Kelola Shift</span>
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

            <router-link
              to="/admin/telegram"
              class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ChatBubbleLeftRightIcon class="h-5 w-5 text-blue-500 mr-3" />
              <span class="text-sm font-medium text-gray-900">Notifikasi Telegram</span>
            </router-link>
          </div>
        </div>
      </div>

      <!-- AI Predictions Section -->
      <div class="mt-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h3 class="text-lg sm:text-xl font-bold flex items-center">
              <CpuChipIcon class="h-6 w-6 mr-2" />
              Prediksi AI - Risiko Terlambat Minggu Depan
            </h3>
            <p class="text-indigo-100 text-sm mt-1">Analisis berdasarkan pola kehadiran 7 hari terakhir - Top Satpam Berisiko</p>
            <p v-if="aiPredictions.length > 0" class="text-indigo-200 text-xs mt-1">
              Menampilkan {{ aiPredictions.length }} prediksi berisiko tinggi dari data terkini
            </p>
            <p v-if="aiPredictions.length > 0 && aiPredictions.length < 3" class="text-yellow-200 text-xs mt-1">
              ⚠️ Hanya {{ aiPredictions.length }} satpam dengan risiko di atas 10% - kondisi kehadiran relatif baik
            </p>
          </div>
          <div class="flex items-center space-x-3">
            <button
              @click="generatePredictions"
              :disabled="predictionsLoading"
              class="bg-blue-500 bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 disabled:opacity-50"
            >
              <CpuChipIcon class="h-4 w-4" />
              <span>{{ predictionsLoading ? 'Generating...' : 'Generate' }}</span>
            </button>
            <div class="bg-blue-500 bg-opacity-20 rounded-full p-3">
              <CpuChipIcon class="h-8 w-8 text-white" />
            </div>
          </div>
        </div>

        <!-- Predictions Cards -->
        <div v-if="predictionsLoading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
        </div>

        <div v-else-if="aiPredictions.length > 0" class="space-y-4">
          <!-- Predictions Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="(prediction, index) in aiPredictions" :key="index"
                 class="bg-white rounded-lg p-4 shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
              <div class="flex items-center justify-between mb-3">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm mr-3">
                    {{ prediction.name.split(' ')[1]?.charAt(0) || prediction.name.charAt(0) }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900 text-sm">{{ prediction.name }}</p>
                    <p class="text-xs text-gray-500 flex items-center">
                      <MapPinIcon class="h-3 w-3 mr-1" />
                      {{ prediction.location }}
                    </p>
                    <p v-if="prediction.predicted_for_week" class="text-xs text-blue-600 font-medium mt-1">
                      {{ prediction.predicted_for_week }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Risk Score with Progress Bar -->
              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-xs font-medium text-gray-700">Risk Score</span>
                  <span class="text-sm font-bold" :class="{
                    'text-red-600': prediction.riskScore >= 80,
                    'text-yellow-600': prediction.riskScore >= 60 && prediction.riskScore < 80,
                    'text-green-600': prediction.riskScore < 60
                  }">{{ prediction.riskScore }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="h-2 rounded-full transition-all duration-500"
                       :class="{
                         'bg-gradient-to-r from-red-400 to-red-600': prediction.riskScore >= 80,
                         'bg-gradient-to-r from-yellow-400 to-yellow-600': prediction.riskScore >= 60 && prediction.riskScore < 80,
                         'bg-gradient-to-r from-green-400 to-green-600': prediction.riskScore < 60
                       }"
                       :style="{ width: `${prediction.riskScore}%` }">
                  </div>
                </div>
                <p class="text-xs text-gray-500 text-center">
                  <span v-if="prediction.riskScore >= 80" class="text-red-600 font-medium">Risiko Tinggi</span>
                  <span v-else-if="prediction.riskScore >= 60" class="text-yellow-600 font-medium">Risiko Sedang</span>
                  <span v-else class="text-green-600 font-medium">Risiko Rendah</span>
                </p>

                <!-- Reason -->
                <div v-if="prediction.reason" class="mt-3 p-3 bg-gray-50 rounded-lg">
                  <p class="text-xs text-gray-700 font-medium mb-1">Alasan Prediksi:</p>
                  <p class="text-xs text-gray-600">{{ prediction.reason }}</p>
                </div>

                <!-- Action Badge -->
                <div class="mt-3 flex justify-center">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium"
                        :class="{
                          'bg-red-100 text-red-800': prediction.riskScore >= 80,
                          'bg-yellow-100 text-yellow-800': prediction.riskScore >= 60 && prediction.riskScore < 80,
                          'bg-green-100 text-green-800': prediction.riskScore < 60
                        }">
                    <span v-if="prediction.riskScore >= 80">⚠️ Perlu Perhatian Khusus</span>
                    <span v-else-if="prediction.riskScore >= 60">⚡ Pantau Lebih Ketat</span>
                    <span v-else>✅ Kondisi Terkontrol</span>
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- AI Summary Info -->
          <div class="mt-6 bg-white bg-opacity-20 backdrop-blur-sm rounded-lg p-4 border border-white border-opacity-20">
            <h4 class="text-sm font-semibold text-gray-800 mb-2">📊 Ringkasan Analisis AI</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-700 mb-3">
              <div>
                <span class="font-medium text-gray-800">🎯 Model:</span> Weekly Risk Assessment
              </div>
              <div>
                <span class="font-medium text-gray-800">📅 Periode:</span> Prediksi 7 hari ke depan
              </div>
              <div>
                <span class="font-medium text-gray-800">🔍 Data:</span> Analisis pola 7 hari terakhir
              </div>
            </div>

            <!-- Special message for few predictions -->
            <div v-if="aiPredictions.length < 3 && aiPredictions.length > 0" class="bg-green-600 bg-opacity-90 rounded-lg p-3 mt-3">
              <div class="flex items-center">
                <svg class="h-5 w-5 text-white mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-white text-xs font-medium">
                  ✅ Kabar Baik! Hanya {{ aiPredictions.length }} dari 7 satpam yang berisiko tinggi. Tingkat kehadiran secara keseluruhan dalam kondisi baik.
                </p>
              </div>
            </div>
          </div>
        </div>        <!-- Empty State -->
        <div v-else class="text-center py-8">
          <CpuChipIcon class="h-12 w-12 text-indigo-200 mx-auto mb-4" />
          <p class="text-indigo-100">Belum ada prediksi AI tersedia</p>
          <p class="text-indigo-200 text-sm">Klik tombol "Generate" untuk membuat prediksi berdasarkan data 7 hari terakhir</p>
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
  CpuChipIcon,
  ChatBubbleLeftRightIcon
} from '@heroicons/vue/24/outline'

const router = useRouter()
const authStore = useAuthStore()

// Reactive state
const loading = ref(false)
const predictionsLoading = ref(false)
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

// AI Predictions data
const aiPredictions = ref([])

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

    // Load AI predictions
    loadAIPredictions()

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

// Load AI Predictions
const loadAIPredictions = async () => {
  predictionsLoading.value = true
  try {
    console.log('Loading AI predictions...')
    const predictions = await dashboardAPI.getAIPredictions(6)
    console.log('Raw predictions response:', predictions)

    // Convert object to array if needed
    let predictionsArray = []
    if (predictions && typeof predictions === 'object') {
      if (Array.isArray(predictions)) {
        predictionsArray = predictions
      } else {
        // Convert object with numeric keys to array
        predictionsArray = Object.values(predictions)
      }
    }

    aiPredictions.value = predictionsArray
    console.log('AI Predictions loaded:', aiPredictions.value)
    console.log('Predictions count:', aiPredictions.value.length)
  } catch (error) {
    console.error('Error loading AI predictions:', error)
    // Keep empty array on error
    aiPredictions.value = []
  } finally {
    predictionsLoading.value = false
  }
}

// Generate new AI predictions (admin action)
const generatePredictions = async () => {
  predictionsLoading.value = true
  try {
    const response = await dashboardAPI.generateAIPredictions()

    // Show success message
    console.log('Generated predictions:', response.message)

    // You could add a toast notification here if available
    alert(`✅ ${response.message || 'AI Predictions generated successfully!'}`)

    // Reload predictions after generation
    await loadAIPredictions()

  } catch (error) {
    console.error('Error generating AI predictions:', error)
  } finally {
    predictionsLoading.value = false
  }
}// Change chart period and reload data
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
