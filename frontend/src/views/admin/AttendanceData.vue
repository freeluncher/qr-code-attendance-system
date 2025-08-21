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
                  <h1 class="text-xl font-semibold text-gray-900">Data Presensi</h1>
                  <p class="text-sm text-gray-500">Kelola Data Kehadiran Satpam</p>
                </div>
              </router-link>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <router-link to="/admin/dashboard" class="text-gray-400 hover:text-gray-600">
              <ArrowLeftIcon class="h-5 w-5" />
            </router-link>
            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-blue-600 flex items-center justify-center">
              <ClipboardDocumentListIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
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
          <h2 class="text-2xl font-bold text-gray-900">Data Presensi</h2>
          <p class="text-gray-600 mt-1">Kelola dan monitor data kehadiran satpam</p>
        </div>
        <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 mt-4 sm:mt-0">
          <button
            @click="openCreateModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
          >
            <PlusIcon class="h-5 w-5" />
            <span>Manual Entry</span>
          </button>
          <button
            @click="exportData"
            :disabled="exporting"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors disabled:opacity-50"
          >
            <ArrowDownTrayIcon class="h-5 w-5" />
            <span>{{ exporting ? 'Exporting...' : 'Export' }}</span>
          </button>
        </div>
      </div>

      <!-- Filter and Search -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
          <div>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari satpam atau lokasi..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <select
              v-model="filters.user_id"
              @change="loadAttendanceData"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Satpam</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
          <div>
            <select
              v-model="filters.location_id"
              @change="loadAttendanceData"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Lokasi</option>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.name }}
              </option>
            </select>
          </div>
          <div>
            <input
              v-model="filters.date"
              @change="loadAttendanceData"
              type="date"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <select
              v-model="filters.status"
              @change="loadAttendanceData"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Semua Status</option>
              <option value="present">Hadir</option>
              <option value="late">Terlambat</option>
              <option value="absent">Tidak Hadir</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Attendance Data Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <input
                    type="checkbox"
                    :checked="selectedAll"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Satpam
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tanggal
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lokasi
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Check In
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Check Out
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Durasi Kerja
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading">
                <td colspan="9" class="px-6 py-4 text-center">
                  <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                  </div>
                </td>
              </tr>
              <tr v-for="attendance in filteredAttendanceData" :key="attendance.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :checked="selectedItems.includes(attendance.id)"
                    @change="toggleSelect(attendance.id)"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">
                        {{ attendance.user.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ attendance.user.name }}</div>
                      <div class="text-sm text-gray-500">{{ attendance.user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(attendance.date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ attendance.location?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ attendance.check_in ? formatTime(attendance.check_in) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ attendance.check_out ? formatTime(attendance.check_out) : '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex px-2 text-xs font-semibold rounded-full',
                      getStatusClass(attendance)
                    ]"
                  >
                    {{ getStatusText(attendance) }}
                  </span>
                  <div v-if="attendance.is_late && attendance.late_minutes > 0" class="text-xs text-red-600 mt-1">
                    Terlambat {{ attendance.late_minutes }} menit
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ getWorkDuration(attendance) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="editAttendance(attendance)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Edit"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="confirmDelete(attendance)"
                      class="text-red-600 hover:text-red-900"
                      title="Hapus"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!loading && filteredAttendanceData.length === 0">
                <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                  Tidak ada data presensi ditemukan
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="previousPage"
              :disabled="!pagination || pagination.current_page === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="nextPage"
              :disabled="!pagination || pagination.current_page === pagination.last_page"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Menampilkan
                <span class="font-medium">{{ pagination?.from || 0 }}</span>
                sampai
                <span class="font-medium">{{ pagination?.to || 0 }}</span>
                dari
                <span class="font-medium">{{ pagination?.total || 0 }}</span>
                hasil
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  @click="previousPage"
                  :disabled="!pagination || pagination.current_page === 1"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <ChevronLeftIcon class="h-5 w-5" />
                </button>
                <button
                  v-for="page in paginationPages"
                  :key="page"
                  @click="goToPage(page)"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    page === (pagination?.current_page || 1)
                      ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
                <button
                  @click="nextPage"
                  :disabled="!pagination || pagination.current_page === pagination.last_page"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                >
                  <ChevronRightIcon class="h-5 w-5" />
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <div v-if="selectedItems.length > 0" class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-center justify-between">
          <span class="text-sm text-blue-800">
            {{ selectedItems.length }} item dipilih
          </span>
          <div class="flex space-x-2">
            <button
              @click="bulkDelete"
              class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg transition-colors"
            >
              Hapus Terpilih
            </button>
            <button
              @click="clearSelection"
              class="text-sm bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded-lg transition-colors"
            >
              Batal Pilih
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ showEditModal ? 'Edit Presensi' : 'Manual Entry Presensi' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Satpam</label>
            <select
              v-model="form.user_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Pilih Satpam</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
            <select
              v-model="form.location_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Pilih Lokasi</option>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <input
              v-model="form.date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Check In</label>
              <input
                v-model="form.check_in"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Check Out</label>
              <input
                v-model="form.check_out"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="form.status"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="present">Hadir</option>
              <option value="absent">Tidak Hadir</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
              placeholder="Catatan tambahan (opsional)"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              {{ submitting ? 'Menyimpan...' : (showEditModal ? 'Update' : 'Simpan') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex items-center mb-4">
          <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-3" />
          <h3 class="text-lg font-medium text-gray-900">Hapus Data Presensi</h3>
        </div>

        <p class="text-gray-600 mb-6">
          Apakah Anda yakin ingin menghapus data presensi ini? Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            Batal
          </button>
          <button
            @click="deleteAttendance"
            :disabled="deleting"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50"
          >
            {{ deleting ? 'Menghapus...' : 'Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import {
  AcademicCapIcon,
  ClipboardDocumentListIcon,
  PlusIcon,
  ArrowDownTrayIcon,
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  ChevronLeftIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const loading = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const exporting = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const searchQuery = ref('')
const selectedItems = ref([])

// Data
const attendanceData = ref([])
const locations = ref([])
const users = ref([])
const attendanceToDelete = ref(null)

// Filters
const filters = ref({
  user_id: '',
  location_id: '',
  date: '',
  status: ''
})

// Pagination
const pagination = ref({
  current_page: 1,
  last_page: 1,
  from: 1,
  to: 10,
  total: 0,
  per_page: 10
})

// Form
const form = ref({
  user_id: '',
  location_id: '',
  date: '',
  check_in: '',
  check_out: '',
  status: 'present',
  notes: ''
})

// Computed
const filteredAttendanceData = computed(() => {
  let filtered = attendanceData.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(attendance =>
      attendance.user.name.toLowerCase().includes(query) ||
      attendance.location?.name.toLowerCase().includes(query)
    )
  }

  return filtered
})

const selectedAll = computed(() => {
  return attendanceData.value.length > 0 && selectedItems.value.length === attendanceData.value.length
})

const paginationPages = computed(() => {
  if (!pagination.value || !pagination.value.last_page) {
    return []
  }

  const pages = []
  const start = Math.max(1, pagination.value.current_page - 2)
  const end = Math.min(pagination.value.last_page, pagination.value.current_page + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})

// Methods
const loadAttendanceData = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.append('page', page)
    params.append('per_page', pagination.value.per_page)

    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key])
      }
    })

    const response = await api.get(`/attendances?${params}`)
    attendanceData.value = response.data.data
    pagination.value = response.data.pagination
  } catch (error) {
    console.error('Error loading attendance data:', error)
    // Fallback data for development
    attendanceData.value = [
      {
        id: 1,
        user: { id: 1, name: 'Ahmad Wijaya', email: 'ahmad@example.com' },
        location: { id: 1, name: 'Pos Utara' },
        date: '2025-01-15',
        check_in: '08:05:00',
        check_out: '17:00:00',
        status: 'present',
        is_late: true,
        late_minutes: 5,
        notes: 'Manual entry oleh admin'
      },
      {
        id: 2,
        user: { id: 2, name: 'Budi Santoso', email: 'budi@example.com' },
        location: { id: 2, name: 'Pos Selatan' },
        date: '2025-01-15',
        check_in: '08:00:00',
        check_out: '17:05:00',
        status: 'present',
        is_late: false,
        late_minutes: 0,
        notes: ''
      }
    ]
    pagination.value = {
      current_page: 1,
      last_page: 1,
      from: 1,
      to: 2,
      total: 2,
      per_page: 10
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

const openCreateModal = () => {
  showCreateModal.value = true
  form.value = {
    user_id: '',
    location_id: '',
    date: new Date().toISOString().split('T')[0],
    check_in: '',
    check_out: '',
    status: 'present',
    notes: ''
  }
}

const editAttendance = (attendance) => {
  showEditModal.value = true
  form.value = {
    id: attendance.id,
    user_id: attendance.user.id,
    location_id: attendance.location?.id,
    date: attendance.date,
    check_in: attendance.check_in,
    check_out: attendance.check_out,
    status: attendance.status,
    notes: attendance.notes || ''
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  form.value = {
    user_id: '',
    location_id: '',
    date: '',
    check_in: '',
    check_out: '',
    status: 'present',
    notes: ''
  }
}

const submitForm = async () => {
  submitting.value = true
  try {
    if (showEditModal.value) {
      await api.put(`/attendances/${form.value.id}`, form.value)
    } else {
      await api.post('/attendances', form.value)
    }
    closeModal()
    await loadAttendanceData(pagination.value.current_page)
  } catch (error) {
    console.error('Error saving attendance:', error)
    alert('Gagal menyimpan data presensi')
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (attendance) => {
  attendanceToDelete.value = attendance
  showDeleteModal.value = true
}

const deleteAttendance = async () => {
  deleting.value = true
  try {
    await api.delete(`/attendances/${attendanceToDelete.value.id}`)
    showDeleteModal.value = false
    attendanceToDelete.value = null
    await loadAttendanceData(pagination.value.current_page)
  } catch (error) {
    console.error('Error deleting attendance:', error)
    alert('Gagal menghapus data presensi')
  } finally {
    deleting.value = false
  }
}

const exportData = async () => {
  exporting.value = true
  try {
    const params = new URLSearchParams()
    Object.keys(filters.value).forEach(key => {
      if (filters.value[key]) {
        params.append(key, filters.value[key])
      }
    })

    const response = await api.get(`/attendances/export?${params}`, {
      responseType: 'blob'
    })

    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `data-presensi-${new Date().toISOString().split('T')[0]}.xlsx`
    link.click()
    window.URL.revokeObjectURL(url)
  } catch (error) {
    console.error('Error exporting data:', error)
    alert('Gagal export data')
  } finally {
    exporting.value = false
  }
}

const toggleSelect = (id) => {
  const index = selectedItems.value.indexOf(id)
  if (index > -1) {
    selectedItems.value.splice(index, 1)
  } else {
    selectedItems.value.push(id)
  }
}

const toggleSelectAll = () => {
  if (selectedAll.value) {
    selectedItems.value = []
  } else {
    selectedItems.value = attendanceData.value.map(item => item.id)
  }
}

const clearSelection = () => {
  selectedItems.value = []
}

const bulkDelete = async () => {
  if (confirm(`Hapus ${selectedItems.value.length} data presensi terpilih?`)) {
    try {
      await api.delete('/attendances/bulk', {
        data: { ids: selectedItems.value }
      })
      selectedItems.value = []
      await loadAttendanceData(pagination.value.current_page)
    } catch (error) {
      console.error('Error bulk deleting attendance:', error)
      alert('Gagal menghapus data terpilih')
    }
  }
}

// Pagination methods
const previousPage = () => {
  if (pagination.value && pagination.value.current_page > 1) {
    loadAttendanceData(pagination.value.current_page - 1)
  }
}

const nextPage = () => {
  if (pagination.value && pagination.value.current_page < pagination.value.last_page) {
    loadAttendanceData(pagination.value.current_page + 1)
  }
}

const goToPage = (page) => {
  loadAttendanceData(page)
}

// Utility methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatTime = (timeString) => {
  if (!timeString) return '-'
  return timeString.substring(0, 5) // HH:MM format
}

const getStatusClass = (attendance) => {
  if (attendance.status === 'absent') return 'bg-red-100 text-red-800'
  if (attendance.is_late) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

const getStatusText = (attendance) => {
  if (attendance.status === 'absent') return 'Tidak Hadir'
  if (attendance.is_late) return 'Terlambat'
  return 'Hadir'
}

const getWorkDuration = (attendance) => {
  if (!attendance.check_in || !attendance.check_out) return '-'

  const checkIn = new Date(`2000-01-01 ${attendance.check_in}`)
  const checkOut = new Date(`2000-01-01 ${attendance.check_out}`)
  const diff = checkOut - checkIn
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

  return `${hours}j ${minutes}m`
}

// Lifecycle
onMounted(() => {
  loadAttendanceData()
  loadLocations()
  loadUsers()
})
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
