<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/admin/dashboard" class="text-gray-400 hover:text-gray-600">
              <ArrowLeftIcon class="h-5 w-5" />
            </router-link>
            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-purple-600 flex items-center justify-center ml-3">
              <ClockIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
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
          <h2 class="text-2xl font-bold text-gray-900">Kelola Shift</h2>
          <p class="text-gray-600 mt-1">Atur jadwal shift untuk setiap lokasi</p>
        </div>
        <button
          @click="openCreateModal"
          class="mt-4 sm:mt-0 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <PlusIcon class="h-5 w-5" />
          <span>Tambah Shift</span>
        </button>
      </div>

      <!-- Filter Panel -->
      <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Shift</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
            <select
              v-model="filters.location_id"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="">Semua Lokasi</option>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Hari</label>
            <select
              v-model="filters.day_of_week"
              @change="applyFilters"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="">Semua Hari</option>
              <option value="1">Senin</option>
              <option value="2">Selasa</option>
              <option value="3">Rabu</option>
              <option value="4">Kamis</option>
              <option value="5">Jumat</option>
              <option value="6">Sabtu</option>
              <option value="7">Minggu</option>
            </select>
          </div>
          <div class="flex items-end">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari shift..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            >
          </div>
        </div>
      </div>

      <!-- Shifts Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Daftar Shift</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Shift</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hari Aktif</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kapasitas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="loading">
                <td colspan="7" class="px-6 py-4 text-center">
                  <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                  </div>
                </td>
              </tr>
              <tr v-for="shift in filteredShifts" :key="shift.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ shift.name }}</div>
                  <div v-if="shift.description" class="text-sm text-gray-500">{{ shift.description }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ shift.location ? shift.location.name : 'Semua Lokasi' }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    {{ formatShiftTime(shift.start_time, shift.end_time) }}
                  </div>
                  <div v-if="isOvernightShift(shift.start_time, shift.end_time)" class="text-xs text-amber-600 font-medium">
                    Shift Malam
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="day in getActiveDaysText(shift.active_days)"
                      :key="day"
                      class="inline-flex px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded"
                    >
                      {{ day }}
                    </span>
                    <span v-if="!shift.active_days || shift.active_days.length === 0" class="text-sm text-gray-500">
                      Setiap Hari
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900">{{ shift.capacity || 1 }} orang</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex px-2 text-xs font-semibold rounded-full',
                    shift.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ shift.status === 'active' ? 'Aktif' : 'Tidak Aktif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <button
                    @click="openEditModal(shift)"
                    class="text-purple-600 hover:text-purple-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(shift)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
              <tr v-if="!loading && filteredShifts.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                  Tidak ada data shift
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-lg w-full p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ editingShift ? 'Edit Shift' : 'Tambah Shift' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="saveShift">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nama Shift</label>
              <input
                v-model="formData.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                placeholder="misal: Shift Pagi"
              >
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
              <select
                v-model="formData.location_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
              >
                <option value="">Berlaku untuk Semua Lokasi</option>
                <option v-for="location in locations" :key="location.id" :value="location.id">
                  {{ location.name }}
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai</label>
                <input
                  v-model="formData.start_time"
                  type="time"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai</label>
                <input
                  v-model="formData.end_time"
                  type="time"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                >
              </div>
            </div>

            <!-- Overnight shift warning -->
            <div v-if="isOvernightShift(formData.start_time, formData.end_time)" class="bg-amber-50 border border-amber-200 rounded-lg p-3">
              <div class="flex items-center">
                <div class="text-amber-600 text-sm">
                  <strong>Shift Malam:</strong> Shift ini melewati tengah malam (berlanjut ke hari berikutnya)
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Hari Aktif</label>
              <div class="grid grid-cols-4 gap-2">
                <label v-for="(day, index) in dayOptions" :key="index" class="flex items-center">
                  <input
                    v-model="formData.active_days"
                    :value="index + 1"
                    type="checkbox"
                    class="rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                  >
                  <span class="ml-2 text-sm text-gray-700">{{ day }}</span>
                </label>
              </div>
              <p class="text-xs text-gray-500 mt-1">Kosongkan untuk aktif setiap hari</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                <input
                  v-model.number="formData.capacity"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                  placeholder="1"
                >
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select
                  v-model="formData.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                >
                  <option value="active">Aktif</option>
                  <option value="inactive">Tidak Aktif</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
              <textarea
                v-model="formData.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                placeholder="Deskripsi shift (opsional)"
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan' }}
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
          <h3 class="text-lg font-medium text-gray-900">Hapus Shift</h3>
        </div>
        <p class="text-gray-600 mb-6">
          Yakin ingin menghapus shift "{{ shiftToDelete?.name }}"?
          Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            Batal
          </button>
          <button
            @click="deleteShift"
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
import { shiftAPI, locationAPI } from '../../services/api'
import {
  ArrowLeftIcon,
  ClockIcon,
  PlusIcon,
  XMarkIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const searchQuery = ref('')

// Data
const shifts = ref([])
const locations = ref([])
const editingShift = ref(null)
const shiftToDelete = ref(null)

// Filters
const filters = ref({
  location_id: '',
  day_of_week: ''
})

// Form data
const formData = ref({
  name: '',
  location_id: '',
  start_time: '',
  end_time: '',
  active_days: [],
  capacity: 1,
  status: 'active',
  description: ''
})

// Day options
const dayOptions = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']

// Computed
const filteredShifts = computed(() => {
  let filtered = shifts.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(shift =>
      shift.name.toLowerCase().includes(query) ||
      shift.description?.toLowerCase().includes(query)
    )
  }

  return filtered
})

// Methods
const loadShifts = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.location_id) params.location_id = filters.value.location_id
    if (filters.value.day_of_week) params.day_of_week = filters.value.day_of_week

    const response = await shiftAPI.getShifts(params)
    shifts.value = response.data || response
  } catch (error) {
    console.error('Error loading shifts:', error)
    alert('Gagal memuat data shift')
  } finally {
    loading.value = false
  }
}

const loadLocations = async () => {
  try {
    const response = await locationAPI.getLocations()
    locations.value = response.data || response
  } catch (error) {
    console.error('Error loading locations:', error)
  }
}

const applyFilters = () => {
  loadShifts()
}

const openCreateModal = () => {
  editingShift.value = null
  formData.value = {
    name: '',
    location_id: '',
    start_time: '',
    end_time: '',
    active_days: [],
    capacity: 1,
    status: 'active',
    description: ''
  }
  showModal.value = true
}

const openEditModal = (shift) => {
  editingShift.value = shift
  formData.value = {
    name: shift.name,
    location_id: shift.location_id || '',
    start_time: shift.start_time,
    end_time: shift.end_time,
    active_days: shift.active_days || [],
    capacity: shift.capacity || 1,
    status: shift.status,
    description: shift.description || ''
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingShift.value = null
}

const saveShift = async () => {
  saving.value = true
  try {
    // Clean up form data
    const data = { ...formData.value }
    if (!data.location_id) data.location_id = null
    if (!data.description) data.description = null
    if (data.active_days.length === 0) data.active_days = null

    if (editingShift.value) {
      await shiftAPI.updateShift(editingShift.value.id, data)
    } else {
      await shiftAPI.createShift(data)
    }

    closeModal()
    await loadShifts()
  } catch (error) {
    console.error('Error saving shift:', error)
    alert('Gagal menyimpan data shift')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (shift) => {
  shiftToDelete.value = shift
  showDeleteModal.value = true
}

const deleteShift = async () => {
  deleting.value = true
  try {
    await shiftAPI.deleteShift(shiftToDelete.value.id)
    showDeleteModal.value = false
    shiftToDelete.value = null
    await loadShifts()
  } catch (error) {
    console.error('Error deleting shift:', error)
    alert('Gagal menghapus shift')
  } finally {
    deleting.value = false
  }
}

const formatTime = (time) => {
  return time ? time.substring(0, 5) : ''
}

const formatShiftTime = (startTime, endTime) => {
  const start = formatTime(startTime)
  const end = formatTime(endTime)

  // Check if it's an overnight shift (start time > end time)
  if (start && end && start > end) {
    return `${start} - ${end} (+1 hari)`
  }

  return `${start} - ${end}`
}

const isOvernightShift = (startTime, endTime) => {
  const start = formatTime(startTime)
  const end = formatTime(endTime)
  return start && end && start > end
}

const getActiveDaysText = (activeDays) => {
  if (!activeDays || activeDays.length === 0) return []
  return activeDays.map(day => dayOptions[day - 1])
}

// Lifecycle
onMounted(() => {
  loadShifts()
  loadLocations()
})
</script>
