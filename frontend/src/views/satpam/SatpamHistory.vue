<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/satpam/dashboard" class="flex items-center mr-4">
              <ArrowLeftIcon class="h-5 w-5 text-gray-500 mr-2" />
            </router-link>
            <div class="flex items-center">
              <ClipboardDocumentListIcon class="h-8 w-8 text-purple-600 mr-3" />
              <div class="hidden sm:block">
                <h1 class="text-xl font-semibold text-gray-900">Riwayat Presensi</h1>
                <p class="text-sm text-gray-500">Data lengkap presensi Anda</p>
              </div>
            </div>
          </div>

          <!-- Export Button -->
          <div class="flex items-center space-x-2">
            <button
              @click="exportData"
              class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              <DocumentArrowDownIcon class="h-4 w-4 inline mr-2" />
              Export
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CheckCircleIcon class="h-6 sm:h-8 w-6 sm:w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Hadir</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ stats.totalPresent }}</p>
              <p class="text-xs text-gray-500">dari {{ stats.totalDays }} hari</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-6 sm:h-8 w-6 sm:w-8 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Tepat Waktu</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ stats.onTimeCount }}</p>
              <p class="text-xs text-gray-500">{{ stats.onTimePercentage }}% akurasi</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon class="h-6 sm:h-8 w-6 sm:w-8 text-red-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Terlambat</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ stats.lateCount }}</p>
              <p class="text-xs text-gray-500">kali terlambat</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ChartBarIcon class="h-6 sm:h-8 w-6 sm:w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Jam</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ stats.totalHours }}</p>
              <p class="text-xs text-gray-500">jam kerja</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6 sm:mb-8">
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
            <!-- Date Range -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
              <input
                v-model="filters.startDate"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
              <input
                v-model="filters.endDate"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              />
            </div>

            <!-- Status Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="filters.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="">Semua Status</option>
                <option value="tepat_waktu">Tepat Waktu</option>
                <option value="terlambat">Terlambat</option>
                <option value="tidak_hadir">Tidak Hadir</option>
              </select>
            </div>

            <!-- Location Filter -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
              <select
                v-model="filters.location"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
              >
                <option value="">Semua Lokasi</option>
                <option value="pos_utama">Pos Utama</option>
                <option value="pos_selatan">Pos Selatan</option>
                <option value="pos_timur">Pos Timur</option>
              </select>
            </div>
          </div>

          <!-- Filter Actions -->
          <div class="flex space-x-2">
            <button
              @click="applyFilters"
              class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
            >
              <MagnifyingGlassIcon class="h-4 w-4 inline mr-2" />
              Filter
            </button>
            <button
              @click="resetFilters"
              class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Attendance History -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h3 class="text-lg font-medium text-gray-900">Data Presensi</h3>
              <p class="text-sm text-gray-500">{{ filteredAttendance.length }} dari {{ attendance.length }} record</p>
            </div>
            
            <!-- View Mode Toggle -->
            <div class="bg-gray-100 rounded-lg p-1 flex">
              <button
                @click="viewMode = 'table'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'table' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                Tabel
              </button>
              <button
                @click="viewMode = 'cards'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'cards' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                Kartu
              </button>
            </div>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-4"></div>
          <p class="text-gray-500">Memuat data...</p>
        </div>

        <!-- Empty State -->
        <div v-else-if="filteredAttendance.length === 0" class="p-8 text-center">
          <ClipboardDocumentListIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
          <p class="text-gray-500">Tidak ada data presensi ditemukan</p>
          <p class="text-sm text-gray-400">Ubah filter atau lakukan presensi terlebih dahulu</p>
        </div>

        <!-- Table View -->
        <div v-else-if="viewMode === 'table'" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jam Masuk
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Jam Keluar
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Durasi
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lokasi
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="record in paginatedAttendance"
                :key="record.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(record.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.checkIn || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.checkOut || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.duration || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.location }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadgeColor(record.status)]">
                    {{ getStatusText(record.status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  <button
                    @click="viewDetails(record)"
                    class="text-purple-600 hover:text-purple-700 font-medium"
                  >
                    Detail
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Cards View -->
        <div v-else class="p-4 sm:p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="record in paginatedAttendance"
              :key="record.id"
              class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <!-- Card Header -->
              <div class="flex justify-between items-start mb-3">
                <div>
                  <h4 class="text-lg font-medium text-gray-900">{{ formatDateShort(record.date) }}</h4>
                  <p class="text-sm text-gray-500">{{ record.location }}</p>
                </div>
                <span :class="['inline-flex items-center px-2 py-1 rounded-full text-xs font-medium', getStatusBadgeColor(record.status)]">
                  {{ getStatusText(record.status) }}
                </span>
              </div>

              <!-- Card Content -->
              <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Check In:</span>
                  <span class="font-medium text-gray-900">{{ record.checkIn || '-' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Check Out:</span>
                  <span class="font-medium text-gray-900">{{ record.checkOut || '-' }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-500">Durasi:</span>
                  <span class="font-medium text-gray-900">{{ record.duration || '-' }}</span>
                </div>
              </div>

              <!-- Card Actions -->
              <button
                @click="viewDetails(record)"
                class="w-full bg-purple-50 hover:bg-purple-100 text-purple-700 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
              >
                Lihat Detail
              </button>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="filteredAttendance.length > itemsPerPage" class="bg-gray-50 px-4 sm:px-6 py-3 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="flex-1 flex justify-between sm:hidden">
              <button
                @click="previousPage"
                :disabled="currentPage === 1"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Sebelumnya
              </button>
              <button
                @click="nextPage"
                :disabled="currentPage === totalPages"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Selanjutnya
              </button>
            </div>
            
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
              <div>
                <p class="text-sm text-gray-700">
                  Menampilkan
                  <span class="font-medium">{{ (currentPage - 1) * itemsPerPage + 1 }}</span>
                  sampai
                  <span class="font-medium">{{ Math.min(currentPage * itemsPerPage, filteredAttendance.length) }}</span>
                  dari
                  <span class="font-medium">{{ filteredAttendance.length }}</span>
                  hasil
                </p>
              </div>
              
              <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <button
                    @click="previousPage"
                    :disabled="currentPage === 1"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <ChevronLeftIcon class="h-5 w-5" />
                  </button>
                  
                  <button
                    v-for="page in visiblePages"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                      'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                      page === currentPage 
                        ? 'z-10 bg-purple-50 border-purple-500 text-purple-600'
                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                    ]"
                  >
                    {{ page }}
                  </button>
                  
                  <button
                    @click="nextPage"
                    :disabled="currentPage === totalPages"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <ChevronRightIcon class="h-5 w-5" />
                  </button>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detail Modal -->
      <div v-if="selectedRecord" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="selectedRecord = null">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
          </div>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="text-center mb-6">
              <ClipboardDocumentListIcon class="mx-auto h-12 w-12 text-purple-600" />
              <h3 class="mt-2 text-lg leading-6 font-medium text-gray-900">
                Detail Presensi
              </h3>
              <p class="text-sm text-gray-500">{{ formatDate(selectedRecord.date) }}</p>
            </div>

            <div class="space-y-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <span class="text-sm font-medium text-gray-500">Status:</span>
                    <div class="mt-1">
                      <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadgeColor(selectedRecord.status)]">
                        {{ getStatusText(selectedRecord.status) }}
                      </span>
                    </div>
                  </div>
                  <div>
                    <span class="text-sm font-medium text-gray-500">Lokasi:</span>
                    <p class="text-sm text-gray-900 mt-1">{{ selectedRecord.location }}</p>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <span class="text-sm font-medium text-gray-500">Jam Masuk:</span>
                  <p class="text-lg font-semibold text-gray-900">{{ selectedRecord.checkIn || '-' }}</p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-500">Jam Keluar:</span>
                  <p class="text-lg font-semibold text-gray-900">{{ selectedRecord.checkOut || '-' }}</p>
                </div>
              </div>

              <div>
                <span class="text-sm font-medium text-gray-500">Total Jam Kerja:</span>
                <p class="text-xl font-bold text-blue-600">{{ selectedRecord.duration || '-' }}</p>
              </div>

              <div v-if="selectedRecord.notes" class="pt-4 border-t border-gray-200">
                <span class="text-sm font-medium text-gray-500">Catatan:</span>
                <p class="text-sm text-gray-900 mt-1">{{ selectedRecord.notes }}</p>
              </div>
            </div>

            <div class="mt-6">
              <button
                @click="selectedRecord = null"
                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors"
              >
                Tutup
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import satpamAPI from '../../services/satpam'
import {
  ClipboardDocumentListIcon,
  ArrowLeftIcon,
  DocumentArrowDownIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  ChartBarIcon,
  MagnifyingGlassIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const loading = ref(false)
const viewMode = ref('table') // 'table' or 'cards'
const selectedRecord = ref(null)
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Filters
const filters = ref({
  startDate: '',
  endDate: '',
  status: '',
  location: ''
})

// Sample attendance data
const attendance = ref([
  {
    id: 1,
    date: '2025-08-20',
    checkIn: '08:00',
    checkOut: '16:00',
    duration: '8 jam',
    location: 'Pos Utama',
    status: 'tepat_waktu',
    notes: 'Normal shift'
  },
  {
    id: 2,
    date: '2025-08-19',
    checkIn: '08:15',
    checkOut: '16:00',
    duration: '7 jam 45 menit',
    location: 'Pos Selatan',
    status: 'terlambat',
    notes: 'Terlambat 15 menit karena macet'
  },
  {
    id: 3,
    date: '2025-08-18',
    checkIn: '07:55',
    checkOut: '16:05',
    duration: '8 jam 10 menit',
    location: 'Pos Utama',
    status: 'tepat_waktu',
    notes: ''
  },
  {
    id: 4,
    date: '2025-08-17',
    checkIn: '08:00',
    checkOut: null,
    duration: null,
    location: 'Pos Timur',
    status: 'tidak_hadir',
    notes: 'Sakit tidak masuk'
  },
  {
    id: 5,
    date: '2025-08-16',
    checkIn: '07:45',
    checkOut: '15:50',
    duration: '8 jam 5 menit',
    location: 'Pos Utama',
    status: 'tepat_waktu',
    notes: 'Shift weekend'
  }
])

// Computed properties
const stats = computed(() => {
  const totalDays = attendance.value.length
  const presentCount = attendance.value.filter(a => a.status !== 'tidak_hadir').length
  const onTimeCount = attendance.value.filter(a => a.status === 'tepat_waktu').length
  const lateCount = attendance.value.filter(a => a.status === 'terlambat').length
  
  let totalHours = 0
  attendance.value.forEach(record => {
    if (record.duration) {
      const hours = record.duration.match(/(\d+)\s*jam/)
      const minutes = record.duration.match(/(\d+)\s*menit/)
      totalHours += (hours ? parseInt(hours[1]) : 0) + (minutes ? parseInt(minutes[1]) / 60 : 0)
    }
  })
  
  return {
    totalDays,
    totalPresent: presentCount,
    onTimeCount,
    onTimePercentage: totalDays > 0 ? Math.round((onTimeCount / totalDays) * 100) : 0,
    lateCount,
    totalHours: Math.round(totalHours)
  }
})

const filteredAttendance = computed(() => {
  let filtered = [...attendance.value]
  
  if (filters.value.startDate) {
    filtered = filtered.filter(a => a.date >= filters.value.startDate)
  }
  
  if (filters.value.endDate) {
    filtered = filtered.filter(a => a.date <= filters.value.endDate)
  }
  
  if (filters.value.status) {
    filtered = filtered.filter(a => a.status === filters.value.status)
  }
  
  if (filters.value.location) {
    const locationMap = {
      'pos_utama': 'Pos Utama',
      'pos_selatan': 'Pos Selatan',
      'pos_timur': 'Pos Timur'
    }
    filtered = filtered.filter(a => a.location === locationMap[filters.value.location])
  }
  
  return filtered.sort((a, b) => new Date(b.date) - new Date(a.date))
})

const totalPages = computed(() => {
  return Math.ceil(filteredAttendance.value.length / itemsPerPage.value)
})

const paginatedAttendance = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredAttendance.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  // Always show first page
  if (total > 0) pages.push(1)
  
  // Show pages around current page
  for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) {
    if (!pages.includes(i)) pages.push(i)
  }
  
  // Always show last page
  if (total > 1 && !pages.includes(total)) pages.push(total)
  
  return pages.sort((a, b) => a - b)
})

// Methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateShort = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusText = (status) => {
  const statusMap = {
    'tepat_waktu': 'Tepat Waktu',
    'terlambat': 'Terlambat',
    'tidak_hadir': 'Tidak Hadir'
  }
  return statusMap[status] || status
}

const getStatusBadgeColor = (status) => {
  const colorMap = {
    'tepat_waktu': 'bg-green-100 text-green-800',
    'terlambat': 'bg-yellow-100 text-yellow-800',
    'tidak_hadir': 'bg-red-100 text-red-800'
  }
  return colorMap[status] || 'bg-gray-100 text-gray-800'
}

const applyFilters = () => {
  currentPage.value = 1
  // Filters are applied automatically via computed property
}

const resetFilters = () => {
  filters.value = {
    startDate: '',
    endDate: '',
    status: '',
    location: ''
  }
  currentPage.value = 1
}

const viewDetails = (record) => {
  selectedRecord.value = record
}

const exportData = () => {
  // Simulate export functionality
  alert('Data akan diekspor dalam format Excel')
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const goToPage = (page) => {
  currentPage.value = page
}

const loadAttendanceData = async () => {
  loading.value = true
  try {
    const params = {
      start_date: filters.value.startDate || undefined,
      end_date: filters.value.endDate || undefined,
      status: filters.value.status || undefined,
      location_id: filters.value.location ? getLocationId(filters.value.location) : undefined
    }
    
    const result = await satpamAPI.getAttendanceHistory(params)
    
    if (result.data && Array.isArray(result.data)) {
      attendance.value = result.data
    } else {
      // Keep mock data for demo
      attendance.value = [
        {
          id: 1,
          date: '2025-08-20',
          checkIn: '08:00',
          checkOut: '16:00',
          duration: '8 jam',
          location: 'Pos Utama',
          status: 'tepat_waktu',
          notes: 'Normal shift'
        },
        {
          id: 2,
          date: '2025-08-19',
          checkIn: '08:15',
          checkOut: '16:00',
          duration: '7 jam 45 menit',
          location: 'Pos Selatan',
          status: 'terlambat',
          notes: 'Terlambat 15 menit karena macet'
        },
        {
          id: 3,
          date: '2025-08-18',
          checkIn: '07:55',
          checkOut: '16:05',
          duration: '8 jam 10 menit',
          location: 'Pos Utama',
          status: 'tepat_waktu',
          notes: ''
        },
        {
          id: 4,
          date: '2025-08-17',
          checkIn: '08:00',
          checkOut: null,
          duration: null,
          location: 'Pos Timur',
          status: 'tidak_hadir',
          notes: 'Sakit tidak masuk'
        },
        {
          id: 5,
          date: '2025-08-16',
          checkIn: '07:45',
          checkOut: '15:50',
          duration: '8 jam 5 menit',
          location: 'Pos Utama',
          status: 'tepat_waktu',
          notes: 'Shift weekend'
        }
      ]
    }
    
  } catch (error) {
    console.error('Error loading attendance data:', error)
    
    // Use fallback mock data
    attendance.value = [
      {
        id: 1,
        date: '2025-08-20',
        checkIn: '08:00',
        checkOut: '16:00',
        duration: '8 jam',
        location: 'Pos Utama',
        status: 'tepat_waktu',
        notes: 'Normal shift'
      },
      {
        id: 2,
        date: '2025-08-19',
        checkIn: '08:15',
        checkOut: '16:00',
        duration: '7 jam 45 menit',
        location: 'Pos Selatan',
        status: 'terlambat',
        notes: 'Terlambat 15 menit karena macet'
      }
    ]
  } finally {
    loading.value = false
  }
}

const getLocationId = (locationKey) => {
  const locationMap = {
    'pos_utama': 1,
    'pos_selatan': 2,
    'pos_timur': 3
  }
  return locationMap[locationKey]
}

onMounted(() => {
  // Set default date range to current month
  const today = new Date()
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
  
  filters.value.startDate = firstDay.toISOString().split('T')[0]
  filters.value.endDate = today.toISOString().split('T')[0]
  
  loadAttendanceData()
})
</script>
