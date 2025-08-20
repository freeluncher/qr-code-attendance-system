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
                  <h1 class="text-xl font-semibold text-gray-900">Kelola Lokasi</h1>
                  <p class="text-sm text-gray-500">Manajemen Pos Keamanan</p>
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
              <MapPinIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
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
          <h2 class="text-2xl font-bold text-gray-900">Kelola Lokasi</h2>
          <p class="text-gray-600 mt-1">Kelola pos keamanan dan titik presensi</p>
        </div>
        <button
          @click="openCreateModal"
          class="mt-4 sm:mt-0 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <PlusIcon class="h-5 w-5" />
          <span>Tambah Lokasi</span>
        </button>
      </div>

      <!-- Search and Filter -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama lokasi atau deskripsi..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            />
          </div>
          <div class="flex space-x-2">
            <select
              v-model="statusFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
            <button
              @click="loadLocations"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              <MagnifyingGlassIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Locations Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Loading State -->
        <div v-if="loading" class="col-span-full flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
        </div>

        <!-- Location Cards -->
        <div
          v-for="location in filteredLocations"
          :key="location.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center">
                <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                  <MapPinIcon class="h-6 w-6 text-green-600" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">{{ location.name }}</h3>
                  <span
                    :class="[
                      'inline-flex px-2 text-xs font-semibold rounded-full',
                      location.status === 'aktif'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ location.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="openEditModal(location)"
                  class="text-green-600 hover:text-green-900 p-1"
                >
                  <PencilIcon class="h-4 w-4" />
                </button>
                <button
                  @click="confirmDelete(location)"
                  class="text-red-600 hover:text-red-900 p-1"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <p class="text-gray-600 text-sm mb-4">{{ location.description }}</p>

            <div class="space-y-2">
              <div class="flex items-center text-sm text-gray-500">
                <MapPinIcon class="h-4 w-4 mr-2" />
                <span>{{ location.address }}</span>
              </div>
              <div class="flex items-center text-sm text-gray-500">
                <ClockIcon class="h-4 w-4 mr-2" />
                <span>Dibuat: {{ formatDate(location.created_at) }}</span>
              </div>
            </div>

            <!-- QR Code Info -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">QR Code:</span>
                <span class="font-medium text-gray-900">
                  {{ location.qr_codes_count || 0 }} aktif
                </span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Total Presensi:</span>
                <span class="font-medium text-gray-900">
                  {{ location.attendances_count || 0 }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredLocations.length === 0" class="col-span-full text-center py-12">
          <MapPinIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-500">Tidak ada lokasi ditemukan</p>
          <p class="text-gray-400 text-sm">Tambah lokasi baru untuk memulai</p>
        </div>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ editingLocation ? 'Edit Lokasi' : 'Tambah Lokasi Baru' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="saveLocation" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
              placeholder="Masukkan nama lokasi"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea
              v-model="formData.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
              placeholder="Masukkan deskripsi lokasi"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
            <textarea
              v-model="formData.address"
              rows="2"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
              placeholder="Masukkan alamat lengkap"
            ></textarea>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Latitude</label>
              <input
                v-model="formData.latitude"
                type="number"
                step="any"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                placeholder="-6.2088"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Longitude</label>
              <input
                v-model="formData.longitude"
                type="number"
                step="any"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
                placeholder="106.8456"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="formData.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500"
            >
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
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
              :disabled="saving"
              class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Menyimpan...' : (editingLocation ? 'Update' : 'Simpan') }}
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
          <h3 class="text-lg font-medium text-gray-900">Hapus Lokasi</h3>
        </div>

        <p class="text-gray-600 mb-6">
          Apakah Anda yakin ingin menghapus lokasi <strong>{{ locationToDelete?.name }}</strong>?
          Semua data QR code dan presensi terkait akan terpengaruh.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            Batal
          </button>
          <button
            @click="deleteLocation"
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
  MapPinIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  ArrowLeftIcon,
  ClockIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')

// Locations data
const locations = ref([])
const editingLocation = ref(null)
const locationToDelete = ref(null)

// Form data
const formData = ref({
  name: '',
  description: '',
  address: '',
  latitude: null,
  longitude: null,
  status: 'aktif'
})

// Computed
const filteredLocations = computed(() => {
  let filtered = locations.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(location =>
      location.name.toLowerCase().includes(query) ||
      location.description.toLowerCase().includes(query) ||
      location.address.toLowerCase().includes(query)
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(location => location.status === statusFilter.value)
  }

  return filtered
})

// Methods
const loadLocations = async () => {
  loading.value = true
  try {
    const response = await api.get('/locations')
    locations.value = response.data
  } catch (error) {
    console.error('Error loading locations:', error)
    // Fallback data for development
    locations.value = [
      {
        id: 1,
        name: 'Pos Utara',
        description: 'Pos keamanan pintu masuk utara',
        address: 'Jl. Raya Utara No. 1, Jakarta',
        latitude: -6.2088,
        longitude: 106.8456,
        status: 'aktif',
        qr_codes_count: 2,
        attendances_count: 150,
        created_at: '2025-01-15T00:00:00Z'
      },
      {
        id: 2,
        name: 'Pos Selatan',
        description: 'Pos keamanan pintu masuk selatan',
        address: 'Jl. Raya Selatan No. 2, Jakarta',
        latitude: -6.2188,
        longitude: 106.8556,
        status: 'aktif',
        qr_codes_count: 1,
        attendances_count: 89,
        created_at: '2025-01-10T00:00:00Z'
      },
      {
        id: 3,
        name: 'Pos Barat',
        description: 'Pos keamanan area parkir barat',
        address: 'Jl. Barat Raya No. 3, Jakarta',
        latitude: -6.2088,
        longitude: 106.8356,
        status: 'nonaktif',
        qr_codes_count: 0,
        attendances_count: 45,
        created_at: '2025-01-05T00:00:00Z'
      }
    ]
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingLocation.value = null
  formData.value = {
    name: '',
    description: '',
    address: '',
    latitude: null,
    longitude: null,
    status: 'aktif'
  }
  showModal.value = true
}

const openEditModal = (location) => {
  editingLocation.value = location
  formData.value = {
    name: location.name,
    description: location.description,
    address: location.address,
    latitude: location.latitude,
    longitude: location.longitude,
    status: location.status
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingLocation.value = null
  formData.value = {
    name: '',
    description: '',
    address: '',
    latitude: null,
    longitude: null,
    status: 'aktif'
  }
}

const saveLocation = async () => {
  saving.value = true
  try {
    if (editingLocation.value) {
      // Update location
      await api.put(`/locations/${editingLocation.value.id}`, formData.value)
    } else {
      // Create location
      await api.post('/locations', formData.value)
    }

    closeModal()
    await loadLocations()
  } catch (error) {
    console.error('Error saving location:', error)
    alert('Gagal menyimpan data lokasi')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (location) => {
  locationToDelete.value = location
  showDeleteModal.value = true
}

const deleteLocation = async () => {
  deleting.value = true
  try {
    await api.delete(`/locations/${locationToDelete.value.id}`)
    showDeleteModal.value = false
    locationToDelete.value = null
    await loadLocations()
  } catch (error) {
    console.error('Error deleting location:', error)
    alert('Gagal menghapus lokasi')
  } finally {
    deleting.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadLocations()
})
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
