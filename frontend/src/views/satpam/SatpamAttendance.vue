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
              <QrCodeIcon class="h-8 w-8 text-blue-600 mr-3" />
              <div class="hidden sm:block">
                <h1 class="text-xl font-semibold text-gray-900">Presensi QR Code</h1>
                <p class="text-sm text-gray-500">Scan QR Code untuk check-in/check-out</p>
              </div>
            </div>
          </div>

          <!-- Current Status -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-medium text-gray-900">{{ formatTime(currentTime) }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(new Date()) }}</p>
            </div>
            <div :class="[
              'flex items-center px-3 py-1 rounded-full text-sm font-medium',
              getCurrentStatusStyle()
            ]">
              <div class="w-2 h-2 rounded-full mr-2" :class="getCurrentStatusDot()"></div>
              {{ getCurrentStatusText() }}
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Today's Status Card -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold text-gray-900">Status Presensi Hari Ini</h2>
          <div class="text-right">
            <p class="text-2xl font-bold text-gray-900">{{ formatTime(currentTime) }}</p>
            <p class="text-sm text-gray-500">{{ formatDate(new Date()) }}</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Check In Status -->
          <div class="flex items-center p-4 bg-gray-50 rounded-lg">
            <div :class="[
              'h-12 w-12 rounded-full flex items-center justify-center mr-4',
              todayAttendance.check_in ? 'bg-green-100' : 'bg-gray-100'
            ]">
              <CheckCircleIcon :class="[
                'h-6 w-6',
                todayAttendance.check_in ? 'text-green-600' : 'text-gray-400'
              ]" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Check In</p>
              <p class="text-lg font-semibold" :class="todayAttendance.check_in ? 'text-green-600' : 'text-gray-500'">
                {{ todayAttendance.check_in || 'Belum check-in' }}
              </p>
            </div>
          </div>

          <!-- Check Out Status -->
          <div class="flex items-center p-4 bg-gray-50 rounded-lg">
            <div :class="[
              'h-12 w-12 rounded-full flex items-center justify-center mr-4',
              todayAttendance.check_out ? 'bg-red-100' : 'bg-gray-100'
            ]">
              <ArrowRightOnRectangleIcon :class="[
                'h-6 w-6',
                todayAttendance.check_out ? 'text-red-600' : 'text-gray-400'
              ]" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Check Out</p>
              <p class="text-lg font-semibold" :class="todayAttendance.check_out ? 'text-red-600' : 'text-gray-500'">
                {{ todayAttendance.check_out || 'Belum check-out' }}
              </p>
            </div>
          </div>

          <!-- Work Duration -->
          <div class="flex items-center p-4 bg-gray-50 rounded-lg">
            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
              <ClockIcon class="h-6 w-6 text-blue-600" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Durasi Kerja</p>
              <p class="text-lg font-semibold text-blue-600">
                {{ calculateWorkDuration() }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- QR Scanner Section -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Scanner Header -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 text-white text-center">
          <QrCodeIcon class="h-16 w-16 mx-auto mb-4" />
          <h2 class="text-2xl font-bold mb-2">{{ getScannerTitle() }}</h2>
          <p class="text-blue-100">{{ getScannerDescription() }}</p>
        </div>

        <!-- Scanner Content -->
        <div class="p-6">
          <!-- Manual QR Input Mode -->
          <div v-if="scanMode === 'manual'" class="text-center">
            <div class="max-w-md mx-auto">
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Masukkan Kode QR
                </label>
                <input
                  v-model="manualQrCode"
                  type="text"
                  placeholder="Masukkan kode QR..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  @keyup.enter="processQRCode"
                />
              </div>

              <div class="space-y-3">
                <button
                  @click="processQRCode()"
                  :disabled="!manualQrCode.trim() || processing"
                  class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white px-6 py-3 rounded-lg font-medium transition-colors"
                >
                  <div v-if="processing" class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                    Memproses...
                  </div>
                  <div v-else class="flex items-center justify-center">
                    <QrCodeIcon class="h-5 w-5 mr-2" />
                    {{ getProcessButtonText() }}
                  </div>
                </button>

                <button
                  @click="toggleScanMode"
                  class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors"
                >
                  <CameraIcon class="h-5 w-5 inline mr-2" />
                  Gunakan Kamera
                </button>
              </div>
            </div>
          </div>

          <!-- Camera Scanner Mode -->
          <div v-else class="text-center">
            <!-- Camera Preview -->
            <div class="max-w-md mx-auto mb-6">
              <div class="relative bg-black rounded-lg overflow-hidden" style="aspect-ratio: 1;">
                <video
                  ref="videoElement"
                  class="w-full h-full object-cover"
                  autoplay
                  muted
                  playsinline
                ></video>
                <canvas ref="canvasElement" class="hidden"></canvas>

                <!-- Scanner Overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="w-64 h-64 border-2 border-white rounded-lg relative">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-4 border-l-4 border-blue-400 rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-4 border-r-4 border-blue-400 rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-4 border-l-4 border-blue-400 rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-4 border-r-4 border-blue-400 rounded-br-lg"></div>
                  </div>
                </div>

                <!-- Loading overlay -->
                <div v-if="cameraLoading" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                  <div class="text-center text-white">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
                    <p>Memulai kamera...</p>
                  </div>
                </div>

                <!-- Error overlay -->
                <div v-if="cameraError" class="absolute inset-0 bg-red-500 bg-opacity-90 flex items-center justify-center">
                  <div class="text-center text-white p-4">
                    <ExclamationTriangleIcon class="h-8 w-8 mx-auto mb-2" />
                    <p class="text-sm">{{ cameraError }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Camera Controls -->
            <div class="space-y-3 max-w-md mx-auto">
              <button
                v-if="!cameraActive"
                @click="startCamera"
                class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors"
              >
                <CameraIcon class="h-5 w-5 inline mr-2" />
                Mulai Kamera
              </button>

              <div v-if="cameraActive" class="flex space-x-3">
                <button
                  @click="captureQR"
                  :disabled="processing"
                  class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                  {{ processing ? 'Memproses...' : 'Capture QR' }}
                </button>
                <button
                  @click="stopCamera"
                  class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
                >
                  Stop Kamera
                </button>
              </div>

              <button
                @click="toggleScanMode"
                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg font-medium transition-colors"
              >
                <PencilIcon class="h-5 w-5 inline mr-2" />
                Input Manual
              </button>
            </div>
          </div>

          <!-- Current Location Info -->
          <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex items-center justify-center space-x-4 text-sm text-gray-600">
              <div class="flex items-center">
                <MapPinIcon class="h-4 w-4 mr-1" />
                <span>Lokasi: {{ currentLocation || 'Mendeteksi lokasi...' }}</span>
              </div>
              <div class="flex items-center" :class="gpsStatus.color">
                <div class="w-2 h-2 rounded-full mr-1" :class="gpsStatus.dot"></div>
                <span>GPS: {{ gpsStatus.text }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Attendance -->
      <div v-if="recentAttendance.length > 0" class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Riwayat Presensi Terbaru</h3>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div
              v-for="(record, index) in recentAttendance"
              :key="index"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center">
                <div :class="[
                  'h-10 w-10 rounded-full flex items-center justify-center mr-4',
                  record.type === 'check_in' ? 'bg-green-100' : 'bg-red-100'
                ]">
                  <CheckCircleIcon v-if="record.type === 'check_in'" class="h-5 w-5 text-green-600" />
                  <ArrowRightOnRectangleIcon v-else class="h-5 w-5 text-red-600" />
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ record.action }}</p>
                  <p class="text-xs text-gray-500">{{ record.location }}</p>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">{{ record.time }}</p>
                <p class="text-xs text-gray-500">{{ record.date }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
          <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
              <CheckCircleIcon class="h-6 w-6 text-green-600" />
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
              {{ successMessage.title }}
            </h3>
            <p class="text-sm text-gray-500 mb-4">
              {{ successMessage.description }}
            </p>
            <button
              @click="closeSuccessModal"
              class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
            >
              OK
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Modal -->
    <div v-if="showErrorModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
          <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
          <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-2">
              {{ errorMessage.title }}
            </h3>
            <p class="text-sm text-gray-500 mb-4">
              {{ errorMessage.description }}
            </p>
            <button
              @click="closeErrorModal"
              class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
            >
              Tutup
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import satpamAPI from '../../services/satpam'
import {
  QrCodeIcon,
  ArrowLeftIcon,
  CheckCircleIcon,
  ArrowRightOnRectangleIcon,
  ClockIcon,
  CameraIcon,
  PencilIcon,
  MapPinIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const currentTime = ref(new Date())
const scanMode = ref('manual') // 'manual' or 'camera'
const manualQrCode = ref('')
const processing = ref(false)

// Camera related
const videoElement = ref(null)
const canvasElement = ref(null)
const cameraActive = ref(false)
const cameraLoading = ref(false)
const cameraError = ref('')

// Attendance data
const todayAttendance = ref({
  check_in: null,
  check_out: null,
  location: null
})

const recentAttendance = ref([])
const currentLocation = ref('Mendeteksi lokasi...')
const gpsStatus = ref({
  text: 'Tidak aktif',
  color: 'text-red-600',
  dot: 'bg-red-500'
})

// Modals
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successMessage = ref({ title: '', description: '' })
const errorMessage = ref({ title: '', description: '' })

// Methods
const formatTime = (date) => {
  return date.toLocaleTimeString('id-ID', {
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

const getCurrentStatusText = () => {
  if (todayAttendance.value.check_out) return 'Selesai'
  if (todayAttendance.value.check_in) return 'Sedang Bertugas'
  return 'Belum Check-in'
}

const getCurrentStatusStyle = () => {
  if (todayAttendance.value.check_out) return 'bg-gray-100 text-gray-800'
  if (todayAttendance.value.check_in) return 'bg-green-100 text-green-800'
  return 'bg-yellow-100 text-yellow-800'
}

const getCurrentStatusDot = () => {
  if (todayAttendance.value.check_out) return 'bg-gray-500'
  if (todayAttendance.value.check_in) return 'bg-green-500'
  return 'bg-yellow-500'
}

const getScannerTitle = () => {
  if (todayAttendance.value.check_out) return 'Presensi Selesai'
  if (todayAttendance.value.check_in) return 'Scan untuk Check-out'
  return 'Scan untuk Check-in'
}

const getScannerDescription = () => {
  if (todayAttendance.value.check_out) return 'Anda sudah menyelesaikan presensi hari ini'
  if (todayAttendance.value.check_in) return 'Arahkan kamera ke QR Code untuk check-out'
  return 'Arahkan kamera ke QR Code untuk check-in'
}

const getProcessButtonText = () => {
  if (todayAttendance.value.check_out) return 'Presensi Selesai'
  if (todayAttendance.value.check_in) return 'Check-out'
  return 'Check-in'
}

const calculateWorkDuration = () => {
  if (!todayAttendance.value.check_in) return '0 jam 0 menit'

  const checkIn = new Date(`${new Date().toDateString()} ${todayAttendance.value.check_in}`)
  const checkOut = todayAttendance.value.check_out
    ? new Date(`${new Date().toDateString()} ${todayAttendance.value.check_out}`)
    : new Date()

  const diff = checkOut - checkIn
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

  return `${hours} jam ${minutes} menit`
}

const toggleScanMode = () => {
  if (cameraActive.value) {
    stopCamera()
  }
  scanMode.value = scanMode.value === 'manual' ? 'camera' : 'manual'
}

const startCamera = async () => {
  cameraLoading.value = true
  cameraError.value = ''

  try {
    const stream = await navigator.mediaDevices.getUserMedia({
      video: { facingMode: 'environment' }
    })

    if (videoElement.value) {
      videoElement.value.srcObject = stream
      cameraActive.value = true
    }
  } catch (error) {
    console.error('Camera error:', error)
    cameraError.value = 'Gagal mengakses kamera. Pastikan izin kamera telah diberikan.'
  } finally {
    cameraLoading.value = false
  }
}

const stopCamera = () => {
  if (videoElement.value?.srcObject) {
    const tracks = videoElement.value.srcObject.getTracks()
    tracks.forEach(track => track.stop())
    videoElement.value.srcObject = null
  }
  cameraActive.value = false
}

const captureQR = async () => {
  if (!videoElement.value || !canvasElement.value) return

  processing.value = true

  try {
    // Capture frame from video
    const canvas = canvasElement.value
    const context = canvas.getContext('2d')
    canvas.width = videoElement.value.videoWidth
    canvas.height = videoElement.value.videoHeight
    context.drawImage(videoElement.value, 0, 0)

    // For demo purposes, simulate QR detection
    // In real implementation, use a QR code library like jsQR
    const simulatedQRData = 'QR_LOC_001_' + new Date().getTime()
    await processQRCode(simulatedQRData)

  } catch (err) {
    console.error('Camera capture error:', err)
    showError('Error Capture', 'Gagal menangkap QR code. Coba lagi.')
  } finally {
    processing.value = false
  }
}

const processQRCode = async (qrData = null) => {
  if (todayAttendance.value.check_out) {
    showError('Presensi Selesai', 'Anda sudah menyelesaikan presensi hari ini.')
    return
  }

  processing.value = true

  // Handle event object passed accidentally
  let qrCode = qrData
  if (qrData && typeof qrData === 'object' && qrData.target !== undefined) {
    // This is an event object, ignore it and use manual input
    qrCode = null
  }

  qrCode = qrCode || manualQrCode.value.trim()

  if (!qrCode) {
    showError('QR Code Kosong', 'Mohon masukkan kode QR.')
    processing.value = false
    return
  }

  // If qrCode is a JSON string, parse it to get the actual code
  try {
    if (typeof qrCode === 'string' && (qrCode.startsWith('{') || qrCode.includes('code'))) {
      const parsedData = JSON.parse(qrCode)
      if (parsedData.code) {
        qrCode = parsedData.code
      }
    }
  } catch (error) {
    // If parsing fails, use the original qrCode value
    console.log('QR Code is not JSON, using as string:', qrCode)
  }

  // Ensure qrCode is a string and not an object representation
  if (typeof qrCode !== 'string' || qrCode.includes('[object')) {
    showError('QR Code Tidak Valid', 'Format QR code tidak valid. Mohon coba lagi.')
    processing.value = false
    return
  }

  console.log('Processing QR Code:', qrCode)

  try {
    // Get current location
    let location = null
    try {
      location = await satpamAPI.getCurrentLocation()
    } catch (locationError) {
      console.warn('Could not get location:', locationError)
    }

    // Process QR attendance
    const result = await satpamAPI.processQrAttendance(
      qrCode,
      location?.latitude,
      location?.longitude
    )

    if (result.success) {
      // Update attendance state
      if (result.type === 'check_in') {
        todayAttendance.value.check_in = result.time
        showSuccess('Check-in Berhasil!', `Anda telah check-in pada ${result.time} di ${result.location}`)
      } else {
        todayAttendance.value.check_out = result.time
        showSuccess('Check-out Berhasil!', `Anda telah check-out pada ${result.time}. Durasi kerja: ${result.work_duration}`)
      }

      // Add to recent attendance
      recentAttendance.value.unshift({
        type: result.type,
        action: result.type === 'check_in' ? 'Check-in berhasil' : 'Check-out berhasil',
        location: result.location,
        time: result.time,
        date: formatDate(new Date())
      })

      // Clear manual input
      manualQrCode.value = ''

      // Stop camera after successful scan
      if (cameraActive.value) {
        stopCamera()
      }

      // Reload today's attendance
      await loadTodayAttendance()

    } else {
      showError('Error Presensi', result.message)
    }

  } catch (error) {
    console.error('Attendance error:', error)
    showError('Error Presensi', error.response?.data?.message || 'Gagal melakukan presensi. Coba lagi.')
  } finally {
    processing.value = false
  }
}

const showSuccess = (title, description) => {
  successMessage.value = { title, description }
  showSuccessModal.value = true
}

const showError = (title, description) => {
  errorMessage.value = { title, description }
  showErrorModal.value = true
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
}

const closeErrorModal = () => {
  showErrorModal.value = false
}

const loadTodayAttendance = async () => {
  try {
    const data = await satpamAPI.getTodayAttendance()
    todayAttendance.value = {
      check_in: data.check_in,
      check_out: data.check_out,
      location: data.location,
      status: data.status
    }
  } catch (error) {
    console.error('Error loading attendance:', error)
    // Keep default values
    todayAttendance.value = {
      check_in: null,
      check_out: null,
      location: null,
      status: null
    }
  }
}

const loadRecentAttendance = async () => {
  try {
    const activities = await satpamAPI.getRecentActivities(5)
    recentAttendance.value = activities || []
  } catch (error) {
    console.error('Error loading recent attendance:', error)
    recentAttendance.value = []
  }
}

const startLocationTracking = () => {
  if ('geolocation' in navigator) {
    navigator.geolocation.getCurrentPosition(
      () => {
        currentLocation.value = 'Pos Utama - Koordinat terdeteksi'
        gpsStatus.value = {
          text: 'Aktif',
          color: 'text-green-600',
          dot: 'bg-green-500'
        }
      },
      () => {
        gpsStatus.value = {
          text: 'Error',
          color: 'text-red-600',
          dot: 'bg-red-500'
        }
      }
    )
  }
}

// Timer for current time
let timeInterval = null

onMounted(() => {
  // Start time update
  timeInterval = setInterval(() => {
    currentTime.value = new Date()
  }, 1000)

  // Load data
  loadTodayAttendance()
  loadRecentAttendance()
  startLocationTracking()
})

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval)
  }
  if (cameraActive.value) {
    stopCamera()
  }
})
</script>
