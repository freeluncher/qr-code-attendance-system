<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="flex items-center">
            <router-link to="/admin/dashboard" class="flex items-center mr-4">
              <ArrowLeftIcon class="h-5 w-5 text-gray-500 mr-2" />
            </router-link>
            <QrCodeIcon class="h-8 w-8 text-purple-600 mr-3" />
            <div>
              <h1 class="text-xl font-semibold text-gray-900">Kelola QR Code</h1>
              <p class="text-sm text-gray-500">Manajemen QR Code presensi</p>
            </div>
          </div>

          <button
            @click="showCreateModal = true"
            class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors flex items-center"
          >
            <PlusIcon class="h-4 w-4 mr-2" />
            Tambah QR Code
          </button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-green-100">
              <QrCodeIcon class="h-6 w-6 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total QR Code</p>
              <p class="text-2xl font-bold text-gray-900">{{ qrCodes.length }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100">
              <ClockIcon class="h-6 w-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Aktif</p>
              <p class="text-2xl font-bold text-gray-900">{{ activeQrCodes }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-yellow-100">
              <ExclamationTriangleIcon class="h-6 w-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Expired</p>
              <p class="text-2xl font-bold text-gray-900">{{ expiredQrCodes }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
          <div class="flex items-center">
            <div class="p-3 rounded-full bg-purple-100">
              <MapPinIcon class="h-6 w-6 text-purple-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Lokasi</p>
              <p class="text-2xl font-bold text-gray-900">{{ uniqueLocations }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari QR Code..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
            />
          </div>
          <select
            v-model="selectedLocation"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
          >
            <option value="">Semua Lokasi</option>
            <option v-for="location in locations" :key="location.id" :value="location.id">
              {{ location.name }}
            </option>
          </select>
          <select
            v-model="statusFilter"
            class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
          >
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="expired">Expired</option>
          </select>
        </div>
      </div>

      <!-- QR Codes Table -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div v-if="loading" class="p-8 text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto"></div>
          <p class="mt-2 text-gray-500">Memuat data...</p>
        </div>

        <div v-else-if="filteredQrCodes.length === 0" class="p-8 text-center">
          <QrCodeIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
          <p class="text-gray-500">Tidak ada QR Code yang ditemukan</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  QR Code
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Lokasi
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Shift
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Scan Count
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Expires At
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
              <tr v-for="qrCode in paginatedQrCodes" :key="qrCode.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-16 w-16">
                      <div
                        class="h-16 w-16 bg-gray-100 rounded-lg flex items-center justify-center cursor-pointer hover:bg-gray-200 transition-colors"
                        @click="showQrCodeModal(qrCode)"
                      >
                        <QrCodeIcon class="h-8 w-8 text-gray-600" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ qrCode.code.substring(0, 20) }}...
                      </div>
                      <div class="text-sm text-gray-500">
                        ID: {{ qrCode.id }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ qrCode.location?.name || 'N/A' }}</div>
                  <div class="text-sm text-gray-500">{{ qrCode.location?.address || '' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ qrCode.shift?.name || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ qrCode.scan_count || 0 }}</div>
                  <div class="text-xs text-gray-500">kali digunakan</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ formatDateTime(qrCode.expires_at) }}</div>
                  <div class="text-xs text-gray-500">{{ getTimeRemaining(qrCode.expires_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                      isExpired(qrCode.expires_at)
                        ? 'bg-red-100 text-red-800'
                        : 'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ isExpired(qrCode.expires_at) ? 'Expired' : 'Aktif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                  <button
                    @click="showQrCodeModal(qrCode)"
                    class="text-purple-600 hover:text-purple-900"
                  >
                    <EyeIcon class="h-4 w-4" />
                  </button>
                  <button
                    @click="editQrCode(qrCode)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    <PencilIcon class="h-4 w-4" />
                  </button>
                  <button
                    @click="deleteQrCode(qrCode)"
                    class="text-red-600 hover:text-red-900"
                  >
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200">
          <div class="flex-1 flex justify-between sm:hidden">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Previous
            </button>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              Next
            </button>
          </div>
          <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
              <p class="text-sm text-gray-700">
                Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, filteredQrCodes.length) }} of {{ filteredQrCodes.length }} results
              </p>
            </div>
            <div>
              <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <button
                  v-for="page in visiblePages"
                  :key="page"
                  @click="currentPage = page"
                  :class="[
                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                    page === currentPage
                      ? 'z-10 bg-purple-50 border-purple-500 text-purple-600'
                      : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                  ]"
                >
                  {{ page }}
                </button>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showCreateModal || showEditModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <form @submit.prevent="submitForm">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 sm:mx-0 sm:h-10 sm:w-10">
                  <QrCodeIcon class="h-6 w-6 text-purple-600" />
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                    {{ showCreateModal ? 'Tambah QR Code' : 'Edit QR Code' }}
                  </h3>

                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                      <select
                        v-model="form.location_id"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                      >
                        <option value="">Pilih Lokasi</option>
                        <option v-for="location in locations" :key="location.id" :value="location.id">
                          {{ location.name }}
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                      <select
                        v-model="form.shift_id"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                      >
                        <option value="">Pilih Shift</option>
                        <option v-for="shift in shifts" :key="shift.id" :value="shift.id">
                          {{ shift.name }} ({{ shift.start_time }} - {{ shift.end_time }})
                        </option>
                      </select>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-1">Expires At</label>
                      <input
                        v-model="form.expires_at"
                        type="datetime-local"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-purple-500 focus:border-purple-500"
                      />
                    </div>

                    <div v-if="showEditModal && selectedQrCode">
                      <label class="block text-sm font-medium text-gray-700 mb-1">QR Code</label>
                      <div class="bg-gray-50 p-3 rounded-md">
                        <p class="text-sm text-gray-600 font-mono break-all">{{ selectedQrCode.code }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button
                type="submit"
                :disabled="formLoading"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
              >
                {{ formLoading ? 'Loading...' : (showCreateModal ? 'Buat QR Code' : 'Update QR Code') }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              >
                Batal
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- QR Code View Modal -->
    <div v-if="showViewModal && selectedQrCode" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showViewModal = false"></div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
            <div class="text-center">
              <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">QR Code</h3>

              <!-- QR Code Display (placeholder - in real app, use QR code generator library) -->
              <div class="bg-gray-100 w-48 h-48 mx-auto mb-4 flex items-center justify-center rounded-lg">
                <div class="text-center">
                  <QrCodeIcon class="h-16 w-16 text-gray-400 mx-auto mb-2" />
                  <p class="text-xs text-gray-500">QR Code akan ditampilkan di sini</p>
                </div>
              </div>

              <div class="space-y-2 text-left bg-gray-50 p-4 rounded-lg">
                <div>
                  <span class="font-medium text-gray-600">Code:</span>
                  <p class="font-mono text-sm break-all text-gray-900">{{ selectedQrCode.code }}</p>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Lokasi:</span>
                  <span class="ml-2 text-gray-900">{{ selectedQrCode.location?.name || 'N/A' }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Shift:</span>
                  <span class="ml-2 text-gray-900">{{ selectedQrCode.shift?.name || 'N/A' }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Scan Count:</span>
                  <span class="ml-2 text-gray-900">{{ selectedQrCode.scan_count || 0 }} kali</span>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Expires:</span>
                  <span class="ml-2 text-gray-900">{{ formatDateTime(selectedQrCode.expires_at) }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Status:</span>
                  <span
                    :class="[
                      'ml-2 px-2 py-1 text-xs rounded-full',
                      isExpired(selectedQrCode.expires_at)
                        ? 'bg-red-100 text-red-800'
                        : 'bg-green-100 text-green-800'
                    ]"
                  >
                    {{ isExpired(selectedQrCode.expires_at) ? 'Expired' : 'Aktif' }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="showViewModal = false"
              class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <div
      v-if="showToast"
      class="fixed top-4 right-4 z-50 bg-white rounded-lg shadow-lg border border-gray-200 p-4 max-w-sm"
    >
      <div class="flex items-center">
        <div
          :class="[
            'flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center',
            toastType === 'success' ? 'bg-green-100' : 'bg-red-100'
          ]"
        >
          <CheckCircleIcon
            v-if="toastType === 'success'"
            class="w-5 h-5 text-green-600"
          />
          <ExclamationCircleIcon
            v-else
            class="w-5 h-5 text-red-600"
          />
        </div>
        <div class="ml-3 flex-1">
          <p class="text-sm font-medium text-gray-900">{{ toastMessage }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import {
  QrCodeIcon,
  ArrowLeftIcon,
  PlusIcon,
  EyeIcon,
  PencilIcon,
  TrashIcon,
  ClockIcon,
  ExclamationTriangleIcon,
  MapPinIcon,
  CheckCircleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'
import { qrCodeAPI, locationAPI, shiftAPI } from '../../services/api'

// Reactive data
const qrCodes = ref([])
const locations = ref([])
const shifts = ref([])
const loading = ref(true)
const formLoading = ref(false)

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const selectedQrCode = ref(null)

// Form data
const form = ref({
  location_id: '',
  shift_id: '',
  expires_at: ''
})

// Filters
const searchQuery = ref('')
const selectedLocation = ref('')
const statusFilter = ref('')

// Pagination
const currentPage = ref(1)
const perPage = ref(10)

// Toast
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

// Computed properties
const filteredQrCodes = computed(() => {
  let filtered = qrCodes.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(qr =>
      qr.code.toLowerCase().includes(query) ||
      qr.location?.name?.toLowerCase().includes(query) ||
      qr.shift?.name?.toLowerCase().includes(query)
    )
  }

  // Location filter
  if (selectedLocation.value) {
    filtered = filtered.filter(qr => qr.location_id == selectedLocation.value)
  }

  // Status filter
  if (statusFilter.value) {
    filtered = filtered.filter(qr => {
      const expired = isExpired(qr.expires_at)
      return statusFilter.value === 'expired' ? expired : !expired
    })
  }

  return filtered
})

const paginatedQrCodes = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  const end = start + perPage.value
  return filteredQrCodes.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredQrCodes.value.length / perPage.value)
})

const visiblePages = computed(() => {
  const delta = 2
  const range = []
  const rangeWithDots = []

  for (let i = Math.max(2, currentPage.value - delta);
       i <= Math.min(totalPages.value - 1, currentPage.value + delta);
       i++) {
    range.push(i)
  }

  if (currentPage.value - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }

  rangeWithDots.push(...range)

  if (currentPage.value + delta < totalPages.value - 1) {
    rangeWithDots.push('...', totalPages.value)
  } else {
    rangeWithDots.push(totalPages.value)
  }

  return rangeWithDots
})

const activeQrCodes = computed(() => {
  return qrCodes.value.filter(qr => !isExpired(qr.expires_at)).length
})

const expiredQrCodes = computed(() => {
  return qrCodes.value.filter(qr => isExpired(qr.expires_at)).length
})

const uniqueLocations = computed(() => {
  return new Set(qrCodes.value.map(qr => qr.location_id)).size
})

// Methods
const loadData = async () => {
  try {
    loading.value = true
    const [qrCodesData, locationsData, shiftsData] = await Promise.all([
      qrCodeAPI.getQrCodes(),
      locationAPI.getLocations(),
      shiftAPI.getShifts()
    ])

    qrCodes.value = qrCodesData.data || qrCodesData
    locations.value = locationsData.data || locationsData
    shifts.value = shiftsData.data || shiftsData
  } catch (error) {
    console.error('Error loading data:', error)
    showToastMessage('Gagal memuat data', 'error')
  } finally {
    loading.value = false
  }
}

const submitForm = async () => {
  try {
    formLoading.value = true

    if (showCreateModal.value) {
      await qrCodeAPI.createQrCode(form.value)
      showToastMessage('QR Code berhasil dibuat')
    } else {
      await qrCodeAPI.updateQrCode(selectedQrCode.value.id, form.value)
      showToastMessage('QR Code berhasil diperbarui')
    }

    closeModal()
    await loadData()
  } catch (error) {
    console.error('Error submitting form:', error)
    showToastMessage('Gagal menyimpan QR Code', 'error')
  } finally {
    formLoading.value = false
  }
}

const editQrCode = (qrCode) => {
  selectedQrCode.value = qrCode
  form.value = {
    location_id: qrCode.location_id,
    shift_id: qrCode.shift_id,
    expires_at: new Date(qrCode.expires_at).toISOString().slice(0, 16)
  }
  showEditModal.value = true
}

const deleteQrCode = async (qrCode) => {
  if (confirm(`Apakah Anda yakin ingin menghapus QR Code ini?`)) {
    try {
      await qrCodeAPI.deleteQrCode(qrCode.id)
      showToastMessage('QR Code berhasil dihapus')
      await loadData()
    } catch (error) {
      console.error('Error deleting QR code:', error)
      showToastMessage('Gagal menghapus QR Code', 'error')
    }
  }
}

const showQrCodeModal = (qrCode) => {
  selectedQrCode.value = qrCode
  showViewModal.value = true
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedQrCode.value = null
  form.value = {
    location_id: '',
    shift_id: '',
    expires_at: ''
  }
}

const showToastMessage = (message, type = 'success') => {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

const isExpired = (expiresAt) => {
  return new Date(expiresAt) < new Date()
}

const formatDateTime = (datetime) => {
  return new Date(datetime).toLocaleString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getTimeRemaining = (expiresAt) => {
  const now = new Date()
  const expires = new Date(expiresAt)
  const diff = expires - now

  if (diff <= 0) return 'Expired'

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))

  if (days > 0) return `${days} hari lagi`
  if (hours > 0) return `${hours} jam lagi`
  return 'Kurang dari 1 jam'
}

// Set default expires_at to 24 hours from now
const setDefaultExpiresAt = () => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  form.value.expires_at = tomorrow.toISOString().slice(0, 16)
}

// Lifecycle
onMounted(() => {
  loadData()
  setDefaultExpiresAt()
})
</script>
