<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo and Title -->
          <div class="flex items-center">
            <div class="flex items-center">
              <router-link to="/admin/dashboard" class="flex items-center">
                <AcademicCapIcon class="h-8 w-8 text-indigo-600 mr-3" />
                <div class="hidden sm:block">
                  <h1 class="text-xl font-semibold text-gray-900">Lihat Laporan</h1>
                  <p class="text-sm text-gray-500">Laporan Presensi & Analisis</p>
                </div>
              </router-link>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <router-link to="/admin/dashboard" class="text-gray-400 hover:text-gray-600">
              <ArrowLeftIcon class="h-5 w-5" />
            </router-link>
            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-green-600 flex items-center justify-center">
              <DocumentTextIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Header Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Laporan Presensi</h2>
          <p class="text-gray-600 mt-1">Analisis data presensi dan kinerja karyawan</p>
        </div>
        <button
          @click="exportReport"
          :disabled="exporting"
          class="mt-4 sm:mt-0 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors disabled:opacity-50"
        >
          <ArrowDownTrayIcon class="h-5 w-5" />
          <span>{{ exporting ? 'Exporting...' : 'Export Laporan' }}</span>
        </button>
      </div>

      <!-- Filter Panel -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Laporan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
            <select
              v-model="filters.period"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            >
              <option value="today">Hari Ini</option>
              <option value="week">Minggu Ini</option>
              <option value="month">Bulan Ini</option>
              <option value="quarter">Kuartal Ini</option>
              <option value="custom">Custom</option>
            </select>
          </div>

          <div v-if="filters.period === 'custom'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input
              v-model="filters.start_date"
              @change="applyFilters"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            />
          </div>

          <div v-if="filters.period === 'custom'">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
            <input
              v-model="filters.end_date"
              @change="applyFilters"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
            <select
              v-model="filters.location_id"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Semua Lokasi</option>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Satpam</label>
            <select
              v-model="filters.user_id"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Semua Satpam</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <UsersIcon class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Presensi</p>
              <p class="text-2xl font-semibold text-gray-900">{{ reportSummary.total_attendance }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CheckCircleIcon class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Hadir Tepat Waktu</p>
              <p class="text-2xl font-semibold text-gray-900">{{ reportSummary.on_time_attendance }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-8 w-8 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Terlambat</p>
              <p class="text-2xl font-semibold text-gray-900">{{ reportSummary.late_attendance }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <XCircleIcon class="h-8 w-8 text-red-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Tidak Hadir</p>
              <p class="text-2xl font-semibold text-gray-900">{{ reportSummary.absent_count }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Attendance Trend Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Tren Presensi</h3>
          <div class="h-64">
            <AttendanceLineChart :data="attendanceTrendData" />
          </div>
        </div>

        <!-- Location Distribution Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Distribusi per Lokasi</h3>
          <div class="h-64">
            <AttendanceDonutChart :data="chartData.location_distribution" />
          </div>
        </div>
      </div>

      <!-- Detailed Report Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h3 class="text-lg font-medium text-gray-900">Detail Laporan Presensi</h3>
            <div class="mt-4 sm:mt-0 flex space-x-2">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari satpam..."
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
              />
              <select
                v-model="sortBy"
                @change="sortReportData"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
              >
                <option value="name">Urutkan: Nama</option>
                <option value="total_attendance">Total Presensi</option>
                <option value="on_time_percentage">Ketepatan Waktu</option>
                <option value="late_count">Jumlah Terlambat</option>
              </select>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Satpam</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Presensi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tepat Waktu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terlambat</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tidak Hadir</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Persentase Kehadiran</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Keterlambatan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading">
                <td colspan="8" class="px-6 py-4 text-center">
                  <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-600"></div>
                  </div>
                </td>
              </tr>
              <tr v-for="report in filteredReportData" :key="report.user_id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">{{ report.user.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ report.user.name }}</div>
                      <div class="text-sm text-gray-500">{{ report.user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ report.total_attendance }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ report.on_time_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ report.late_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ report.absent_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span class="text-sm text-gray-900">{{ report.attendance_percentage }}%</span>
                    <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                      <div
                        class="bg-green-600 h-2 rounded-full"
                        :style="{ width: `${report.attendance_percentage}%` }"
                      ></div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ report.avg_late_minutes > 0 ? `${report.avg_late_minutes} menit` : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="viewUserDetail(report.user)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Detail
                  </button>
                </td>
              </tr>
              <tr v-if="!loading && filteredReportData.length === 0">
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                  Tidak ada data laporan untuk periode yang dipilih
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- User Detail Modal -->
    <div v-if="showUserDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Detail Presensi - {{ selectedUser?.name }}</h3>
            <button @click="closeUserDetailModal" class="text-gray-400 hover:text-gray-600">
              <XMarkIcon class="h-6 w-6" />
            </button>
          </div>
        </div>

        <div class="p-6">
          <!-- User Info -->
          <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-center">
              <div class="h-16 w-16 rounded-full bg-gray-300 flex items-center justify-center">
                <span class="text-2xl font-bold text-gray-700">{{ selectedUser?.name.charAt(0).toUpperCase() }}</span>
              </div>
              <div class="ml-4">
                <h4 class="text-xl font-semibold text-gray-900">{{ selectedUser?.name }}</h4>
                <p class="text-gray-600">{{ selectedUser?.email }}</p>
                <p class="text-sm text-gray-500">{{ selectedUser?.role === 'security_guard' ? 'Satpam' : selectedUser?.role }}</p>
              </div>
            </div>
          </div>

          <!-- Attendance History -->
          <div class="overflow-x-auto">
            <h4 class="text-lg font-medium text-gray-900 mb-4">Riwayat Presensi</h4>
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check In</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Out</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterlambatan</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="attendance in userAttendanceHistory" :key="attendance.id">
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(attendance.date) }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ attendance.location?.name }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ attendance.check_in ? formatTime(attendance.check_in) : '-' }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ attendance.check_out ? formatTime(attendance.check_out) : '-' }}
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex px-2 text-xs font-semibold rounded-full',
                        attendance.status === 'present' && !attendance.is_late
                          ? 'bg-green-100 text-green-800'
                          : attendance.status === 'present' && attendance.is_late
                          ? 'bg-yellow-100 text-yellow-800'
                          : attendance.status === 'absent'
                          ? 'bg-red-100 text-red-800'
                          : 'bg-gray-100 text-gray-800'
                      ]"
                    >
                      {{ getAttendanceStatusText(attendance) }}
                    </span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ attendance.late_minutes > 0 ? `${attendance.late_minutes} menit` : '-' }}
                  </td>
                </tr>
                <tr v-if="userAttendanceHistory.length === 0">
                  <td colspan="6" class="px-4 py-4 text-center text-gray-500">
                    Tidak ada data presensi
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import reportsAPI from '../../services/reports'
import AttendanceLineChart from '../../components/charts/AttendanceLineChart.vue'
import AttendanceDonutChart from '../../components/charts/AttendanceDonutChart.vue'
import {
  AcademicCapIcon,
  DocumentTextIcon,
  ArrowDownTrayIcon,
  ArrowLeftIcon,
  UsersIcon,
  CheckCircleIcon,
  ClockIcon,
  XCircleIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const loading = ref(false)
const exporting = ref(false)
const showUserDetailModal = ref(false)
const searchQuery = ref('')
const sortBy = ref('name')

// Data
const locations = ref([])
const users = ref([])
const reportData = ref([])
const reportSummary = ref({
  total_attendance: 0,
  on_time_attendance: 0,
  late_attendance: 0,
  absent_count: 0
})
const chartData = ref({
  attendance_trend: [],
  location_distribution: {}
})
const selectedUser = ref(null)
const userAttendanceHistory = ref([])

// Filters
const filters = ref({
  period: 'month',
  start_date: '',
  end_date: '',
  location_id: '',
  user_id: ''
})

// Computed
const filteredReportData = computed(() => {
  let filtered = reportData.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(report =>
      report.user.name.toLowerCase().includes(query) ||
      report.user.email.toLowerCase().includes(query)
    )
  }

  return filtered
})

// Computed property for attendance trend data in correct format
const attendanceTrendData = computed(() => {
  if (!chartData.value.attendance_trend || !Array.isArray(chartData.value.attendance_trend)) {
    // Return mock data for development
    return [
      { date: '2025-01-15', day_name: 'Sen', formatted_date: '15 Jan', total: 12, on_time: 10, late: 2 },
      { date: '2025-01-16', day_name: 'Sel', formatted_date: '16 Jan', total: 15, on_time: 13, late: 2 },
      { date: '2025-01-17', day_name: 'Rab', formatted_date: '17 Jan', total: 18, on_time: 15, late: 3 },
      { date: '2025-01-18', day_name: 'Kam', formatted_date: '18 Jan', total: 14, on_time: 12, late: 2 },
      { date: '2025-01-19', day_name: 'Jum', formatted_date: '19 Jan', total: 16, on_time: 14, late: 2 },
      { date: '2025-01-20', day_name: 'Sab', formatted_date: '20 Jan', total: 10, on_time: 8, late: 2 },
      { date: '2025-01-21', day_name: 'Min', formatted_date: '21 Jan', total: 8, on_time: 7, late: 1 }
    ]
  }
  return chartData.value.attendance_trend
})

// Methods
const loadReportData = async () => {
  loading.value = true
  try {
    const response = await reportsAPI.getAttendanceReports(filters.value)
    reportData.value = response.data.report_data || []
    reportSummary.value = response.data.summary || {}
    chartData.value = response.data.charts || { attendance_trend: [], location_distribution: {} }
  } catch (error) {
    console.error('Error loading report data:', error)
    // Fallback data for development
    reportData.value = [
      {
        user_id: 1,
        user: { id: 1, name: 'Ahmad Wijaya', email: 'ahmad@example.com', role: 'security_guard' },
        total_attendance: 22,
        on_time_count: 18,
        late_count: 4,
        absent_count: 3,
        attendance_percentage: 88,
        avg_late_minutes: 15
      },
      {
        user_id: 2,
        user: { id: 2, name: 'Budi Santoso', email: 'budi@example.com', role: 'security_guard' },
        total_attendance: 25,
        on_time_count: 23,
        late_count: 2,
        absent_count: 0,
        attendance_percentage: 100,
        avg_late_minutes: 8
      }
    ]
    reportSummary.value = {
      total_attendance: 47,
      on_time_attendance: 41,
      late_attendance: 6,
      absent_count: 3
    }
    chartData.value = {
      attendance_trend: [
        { date: '2025-01-15', day_name: 'Sen', formatted_date: '15 Jan', total: 12, on_time: 10, late: 2 },
        { date: '2025-01-16', day_name: 'Sel', formatted_date: '16 Jan', total: 15, on_time: 13, late: 2 },
        { date: '2025-01-17', day_name: 'Rab', formatted_date: '17 Jan', total: 18, on_time: 15, late: 3 },
        { date: '2025-01-18', day_name: 'Kam', formatted_date: '18 Jan', total: 14, on_time: 12, late: 2 },
        { date: '2025-01-19', day_name: 'Jum', formatted_date: '19 Jan', total: 16, on_time: 14, late: 2 },
        { date: '2025-01-20', day_name: 'Sab', formatted_date: '20 Jan', total: 10, on_time: 8, late: 2 },
        { date: '2025-01-21', day_name: 'Min', formatted_date: '21 Jan', total: 8, on_time: 7, late: 1 }
      ],
      location_distribution: {
        labels: ['Pos Utara', 'Pos Selatan', 'Pos Barat'],
        datasets: [{
          data: [30, 25, 20],
          backgroundColor: ['#3B82F6', '#10B981', '#F59E0B']
        }]
      }
    }
  } finally {
    loading.value = false
  }
}

const loadLocations = async () => {
  try {
    const response = await api.get('/locations')
    locations.value = response.data
  } catch (error) {
    console.error('Error loading locations:', error)
    locations.value = [
      { id: 1, name: 'Pos Utara' },
      { id: 2, name: 'Pos Selatan' },
      { id: 3, name: 'Pos Barat' }
    ]
  }
}

const loadUsers = async () => {
  try {
    const response = await api.get('/users', {
      params: { role: 'security_guard' }
    })
    users.value = response.data
  } catch (error) {
    console.error('Error loading users:', error)
    users.value = [
      { id: 1, name: 'Ahmad Wijaya', email: 'ahmad@example.com' },
      { id: 2, name: 'Budi Santoso', email: 'budi@example.com' }
    ]
  }
}

const applyFilters = () => {
  loadReportData()
}

const sortReportData = () => {
  reportData.value.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.user.name.localeCompare(b.user.name)
      case 'total_attendance':
        return b.total_attendance - a.total_attendance
      case 'on_time_percentage':
        return b.attendance_percentage - a.attendance_percentage
      case 'late_count':
        return b.late_count - a.late_count
      default:
        return 0
    }
  })
}

const exportReport = async () => {
  exporting.value = true
  try {
    const params = new URLSearchParams()
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key])
      }
    })

    const response = await api.get(`/reports/export?${params}`, {
      responseType: 'blob'
    })

    // Create download link
    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `laporan-presensi-${new Date().toISOString().split('T')[0]}.xlsx`
    link.click()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error exporting report:', error)
    alert('Gagal export laporan')
  } finally {
    exporting.value = false
  }
}

const viewUserDetail = async (user) => {
  selectedUser.value = user
  showUserDetailModal.value = true

  try {
    const params = new URLSearchParams()
    params.append('user_id', user.id)
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key])
      }
    })

    const response = await api.get(`/reports/user-attendance?${params}`)
    userAttendanceHistory.value = response.data
  } catch (error) {
    console.error('Error loading user attendance history:', error)
    // Fallback data
    userAttendanceHistory.value = [
      {
        id: 1,
        date: '2025-01-15',
        location: { name: 'Pos Utara' },
        check_in: '08:05:00',
        check_out: '17:00:00',
        status: 'present',
        is_late: true,
        late_minutes: 5
      },
      {
        id: 2,
        date: '2025-01-14',
        location: { name: 'Pos Selatan' },
        check_in: '08:00:00',
        check_out: '17:05:00',
        status: 'present',
        is_late: false,
        late_minutes: 0
      }
    ]
  }
}

const closeUserDetailModal = () => {
  showUserDetailModal.value = false
  selectedUser.value = null
  userAttendanceHistory.value = []
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (timeString) => {
  return timeString.substring(0, 5) // HH:MM format
}

const getAttendanceStatusText = (attendance) => {
  if (attendance.status === 'absent') return 'Tidak Hadir'
  if (attendance.status === 'present' && attendance.is_late) return 'Terlambat'
  if (attendance.status === 'present') return 'Hadir'
  return 'Unknown'
}

// Lifecycle
onMounted(() => {
  loadReportData()
  loadLocations()
  loadUsers()
})
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
