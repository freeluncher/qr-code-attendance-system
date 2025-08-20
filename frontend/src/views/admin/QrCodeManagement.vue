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
                  <h1 class="text-xl font-semibold text-gray-900">Kelola QR Code</h1>
                  <p class="text-sm text-gray-500">Manajemen QR Presensi</p>
                </div>
              </router-link>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <router-link to="/admin/dashboard" class="text-gray-400 hover:text-gray-600">
              <ArrowLeftIcon class="h-5 w-5" />
            </router-link>
            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-purple-600 flex items-center justify-center">
              <QrCodeIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
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
          <h2 class="text-2xl font-bold text-gray-900">Kelola QR Code</h2>
          <p class="text-gray-600 mt-1">Generate dan kelola QR code untuk presensi</p>
        </div>
        <button
          @click="openCreateModal"
          class="mt-4 sm:mt-0 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <PlusIcon class="h-5 w-5" />
          <span>Generate QR Code</span>
        </button>
      </div>

      <!-- Search and Filter -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari berdasarkan lokasi atau code..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            />
          </div>
          <div class="flex space-x-2">
            <select
              v-model="locationFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="">Semua Lokasi</option>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.name }}
              </option>
            </select>
            <select
              v-model="statusFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="expired">Expired</option>
            </select>
            <button
              @click="loadQrCodes"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              <MagnifyingGlassIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- QR Codes Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Loading State -->
        <div v-if="loading" class="col-span-full flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
        </div>

        <!-- QR Code Cards -->
        <div
          v-for="qrCode in filteredQrCodes"
          :key="qrCode.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
        >
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center">
                <div class="h-12 w-12 bg-purple-100 rounded-lg flex items-center justify-center">
                  <QrCodeIcon class="h-6 w-6 text-purple-600" />
                </div>
                <div class="ml-4">
                  <h3 class="text-lg font-medium text-gray-900">{{ qrCode.location?.name }}</h3>
                  <span
                    :class="[
                      'inline-flex px-2 text-xs font-semibold rounded-full',
                      qrCode.is_expired
                        ? 'bg-red-100 text-red-800'
                        : 'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ qrCode.is_expired ? 'Expired' : 'Aktif' }}
                  </span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="showQrCode(qrCode)"
                  class="text-purple-600 hover:text-purple-900 p-1"
                  title="Lihat QR Code"
                >
                  <EyeIcon class="h-4 w-4" />
                </button>
                <button
                  @click="downloadQrCode(qrCode)"
                  class="text-blue-600 hover:text-blue-900 p-1"
                  title="Download QR Code"
                >
                  <ArrowDownTrayIcon class="h-4 w-4" />
                </button>
                <button
                  @click="confirmDelete(qrCode)"
                  class="text-red-600 hover:text-red-900 p-1"
                  title="Hapus QR Code"
                >
                  <TrashIcon class="h-4 w-4" />
                </button>
              </div>
            </div>

            <!-- QR Code Preview -->
            <div class="mb-4 flex justify-center">
              <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center">
                <div v-if="qrCode.qr_image" class="w-full h-full">
                  <img :src="qrCode.qr_image" :alt="`QR Code for ${qrCode.location?.name}`" class="w-full h-full object-contain rounded-lg" />
                </div>
                <div v-else class="text-gray-400">
                  <QrCodeIcon class="h-16 w-16" />
                </div>
              </div>
            </div>

            <!-- Details -->
            <div class="space-y-2">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Code:</span>
                <span class="font-mono text-gray-900 text-xs">{{ qrCode.code.substring(0, 12) }}...</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Dibuat:</span>
                <span class="text-gray-900">{{ formatDate(qrCode.created_at) }}</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Expires:</span>
                <span
                  :class="[
                    'font-medium',
                    qrCode.is_expired ? 'text-red-600' : 'text-gray-900'
                  ]"
                >
                  {{ formatDate(qrCode.expires_at) }}
                </span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Scan Count:</span>
                <span class="font-medium text-gray-900">{{ qrCode.scan_count || 0 }}</span>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-4 pt-4 border-t border-gray-200">
              <div class="flex space-x-2">
                <button
                  v-if="qrCode.is_expired"
                  @click="renewQrCode(qrCode)"
                  class="flex-1 bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-2 rounded-lg transition-colors"
                >
                  Perpanjang
                </button>
                <button
                  @click="copyQrCode(qrCode)"
                  class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-3 py-2 rounded-lg transition-colors"
                >
                  Copy Link
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredQrCodes.length === 0" class="col-span-full text-center py-12">
          <QrCodeIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-500">Tidak ada QR code ditemukan</p>
          <p class="text-gray-400 text-sm">Generate QR code baru untuk memulai</p>
        </div>
      </div>
    </main>

    <!-- Create QR Code Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Generate QR Code Baru</h3>
          <button @click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="generateQrCode" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
            <select
              v-model="createForm.location_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="">Pilih Lokasi</option>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Aktif</label>
            <select
              v-model="createForm.duration_days"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
            >
              <option value="7">7 Hari</option>
              <option value="14">14 Hari</option>
              <option value="30">30 Hari</option>
              <option value="90">90 Hari</option>
            </select>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeCreateModal"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="generating"
              class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              {{ generating ? 'Generating...' : 'Generate QR Code' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- View QR Code Modal -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">QR Code - {{ viewingQrCode?.location?.name }}</h3>
          <button @click="closeViewModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <div class="text-center">
          <div class="w-64 h-64 mx-auto bg-gray-100 rounded-lg flex items-center justify-center mb-4">
            <div v-if="viewingQrCode?.qr_image" class="w-full h-full">
              <img :src="viewingQrCode.qr_image" :alt="`QR Code for ${viewingQrCode.location?.name}`" class="w-full h-full object-contain rounded-lg" />
            </div>
            <div v-else class="text-gray-400">
              <QrCodeIcon class="h-32 w-32" />
            </div>
          </div>

          <div class="space-y-2 mb-6">
            <p class="text-sm text-gray-600">Scan QR code ini untuk melakukan presensi di:</p>
            <p class="font-medium text-gray-900">{{ viewingQrCode?.location?.name }}</p>
            <p class="text-xs text-gray-500 font-mono">{{ viewingQrCode?.code }}</p>
          </div>

          <div class="flex space-x-2">
            <button
              @click="downloadQrCode(viewingQrCode)"
              class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
            >
              Download
            </button>
            <button
              @click="copyQrCode(viewingQrCode)"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors"
            >
              Copy Link
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex items-center mb-4">
          <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-3" />
          <h3 class="text-lg font-medium text-gray-900">Hapus QR Code</h3>
        </div>

        <p class="text-gray-600 mb-6">
          Apakah Anda yakin ingin menghapus QR code untuk lokasi <strong>{{ qrCodeToDelete?.location?.name }}</strong>?
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
            @click="deleteQrCode"
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
import api, { qrCodeAPI } from '../../services/api'
import {
  AcademicCapIcon,
  QrCodeIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  EyeIcon,
  ArrowDownTrayIcon,
  TrashIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const loading = ref(false)
const generating = ref(false)
const deleting = ref(false)
const showCreateModal = ref(false)
const showViewModal = ref(false)
const showDeleteModal = ref(false)
const searchQuery = ref('')
const locationFilter = ref('')
const statusFilter = ref('')

// Data
const qrCodes = ref([])
const locations = ref([])
const viewingQrCode = ref(null)
const qrCodeToDelete = ref(null)

// Create form
const createForm = ref({
  location_id: '',
  duration_days: '30'
})

// Computed
const filteredQrCodes = computed(() => {
  let filtered = qrCodes.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(qrCode =>
      qrCode.location?.name.toLowerCase().includes(query) ||
      qrCode.code.toLowerCase().includes(query)
    )
  }

  if (locationFilter.value) {
    filtered = filtered.filter(qrCode => qrCode.location_id == locationFilter.value)
  }

  if (statusFilter.value) {
    const isExpired = statusFilter.value === 'expired'
    filtered = filtered.filter(qrCode => qrCode.is_expired === isExpired)
  }

  return filtered
})

// Methods
const loadQrCodes = async () => {
  loading.value = true
  try {
    const response = await qrCodeAPI.getQrCodes()
    qrCodes.value = response.data
    console.log('QR Codes loaded:', qrCodes.value)
  } catch (error) {
    console.error('Error loading QR codes:', error)
    // Show error message to user instead of fallback data
    alert('Error loading QR codes: ' + (error.message || 'Unknown error'))
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
    // Fallback data
    locations.value = [
      { id: 1, name: 'Pos Utara' },
      { id: 2, name: 'Pos Selatan' },
      { id: 3, name: 'Pos Barat' }
    ]
  }
}

const openCreateModal = () => {
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  createForm.value = {
    location_id: '',
    duration_days: '30'
  }
}

const generateQrCode = async () => {
  generating.value = true
  try {
    await qrCodeAPI.createQrCode(createForm.value)
    closeCreateModal()
    await loadQrCodes()
  } catch (error) {
    console.error('Error generating QR code:', error)
    alert('Gagal generate QR code')
  } finally {
    generating.value = false
  }
}

const showQrCode = async (qrCode) => {
  try {
    // Load QR code with image if not already present
    if (!qrCode.qr_image) {
      const response = await qrCodeAPI.getQrCodeImage(qrCode.id)
      qrCode.qr_image = response.qr_image
    }
    viewingQrCode.value = qrCode
    showViewModal.value = true
  } catch (error) {
    console.error('Error loading QR code image:', error)
    // Still show the modal even if image fails to load
    viewingQrCode.value = qrCode
    showViewModal.value = true
  }
}

const closeViewModal = () => {
  showViewModal.value = false
  viewingQrCode.value = null
}

const downloadQrCode = (qrCode) => {
  // Create download link
  const link = document.createElement('a')
  link.href = qrCode.qr_image
  link.download = `qr-code-${qrCode.location?.name || qrCode.id}.png`
  link.click()
}

const copyQrCode = async (qrCode) => {
  try {
    const url = `${window.location.origin}/scan/${qrCode.code}`
    await navigator.clipboard.writeText(url)
    alert('Link QR code berhasil disalin')
  } catch (error) {
    console.error('Error copying QR code:', error)
    alert('Gagal menyalin link')
  }
}

const renewQrCode = async (qrCode) => {
  try {
    await qrCodeAPI.renewQrCode(qrCode.id, { duration_days: 30 })
    await loadQrCodes()
  } catch (error) {
    console.error('Error renewing QR code:', error)
    alert('Gagal memperpanjang QR code')
  }
}

const confirmDelete = (qrCode) => {
  qrCodeToDelete.value = qrCode
  showDeleteModal.value = true
}

const deleteQrCode = async () => {
  deleting.value = true
  try {
    await qrCodeAPI.deleteQrCode(qrCodeToDelete.value.id)
    showDeleteModal.value = false
    qrCodeToDelete.value = null
    await loadQrCodes()
  } catch (error) {
    console.error('Error deleting QR code:', error)
    alert('Gagal menghapus QR code')
  } finally {
    deleting.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadQrCodes()
  loadLocations()
})
</script>

<style scoped>
/* Additional custom styles if needed */
</style>
