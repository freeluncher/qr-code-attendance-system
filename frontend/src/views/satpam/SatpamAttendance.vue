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

                    <!-- Scanning status -->
                    <div v-if="cameraActive && !processing" class="absolute -bottom-10 left-1/2 transform -translate-x-1/2 text-white text-sm bg-black bg-opacity-50 px-3 py-1 rounded">
                      🔍 Mencari QR Code...
                    </div>
                    <div v-if="processing" class="absolute -bottom-10 left-1/2 transform -translate-x-1/2 text-green-400 text-sm bg-black bg-opacity-50 px-3 py-1 rounded">
                      ✅ Memproses...
                    </div>
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
                @click="startCamera('environment')"
                class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors"
              >
                <CameraIcon class="h-5 w-5 inline mr-2" />
                Mulai Kamera (QR Scan)
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
    <Transition name="modal">
      <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Overlay -->
        <div
          class="absolute inset-0 bg-black bg-opacity-50 transition-opacity duration-300"
          @click="closeSuccessModal"
        ></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-100">
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
            <div class="flex flex-col space-y-3">
              <button
                @click="closeSuccessModal"
                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors"
              >
                OK
              </button>
              <div v-if="autoCloseCountdown > 0" class="text-xs text-gray-500">
                <span>Auto tutup dalam {{ autoCloseCountdown }} detik</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Error Modal -->
    <Transition name="modal">
      <div v-if="showErrorModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Overlay -->
        <div
          class="absolute inset-0 bg-black bg-opacity-50 transition-opacity duration-300"
          @click="closeErrorModal"
        ></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-sm w-full p-6 transform transition-all duration-300 scale-100">
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
    </Transition>

    <!-- Face Capture Modal -->
    <Transition name="modal">
      <div v-if="showFaceCapture" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-75 transition-opacity duration-300"></div>

        <!-- Modal Content -->
        <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6 transform transition-all duration-300 scale-100">
          <div class="text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
              🔐 Verifikasi Wajah
            </h3>

            <!-- Camera Preview for Face Verification -->
            <div class="relative mb-4 flex justify-center">
              <div class="w-80 h-60 bg-gray-900 rounded-lg overflow-hidden relative">
                <video
                  ref="faceVideoPreview"
                  v-show="cameraActive && !faceDetectionLoading"
                  class="w-full h-full object-cover transform scaleX-[-1]"
                  autoplay
                  muted
                  playsinline>
                </video>

                <!-- Face Guide Overlay -->
                <div v-show="cameraActive && !faceDetectionLoading"
                     class="absolute inset-0 pointer-events-none">
                  <!-- Face oval guide - properly centered with custom CSS -->
                  <div class="face-guide-container">
                    <div class="face-guide-oval w-40 h-48 border-4 border-green-400 border-dashed rounded-full opacity-80 flex items-center justify-center relative">
                      <!-- Face guide text -->
                      <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 text-green-400 text-xs font-medium bg-black bg-opacity-70 px-3 py-1 rounded-full">
                        POSISIKAN WAJAH
                      </div>
                      <!-- Corner guides for better alignment -->
                      <div class="absolute top-4 left-4 w-3 h-3 border-l-2 border-t-2 border-green-400 opacity-60"></div>
                      <div class="absolute top-4 right-4 w-3 h-3 border-r-2 border-t-2 border-green-400 opacity-60"></div>
                      <div class="absolute bottom-4 left-4 w-3 h-3 border-l-2 border-b-2 border-green-400 opacity-60"></div>
                      <div class="absolute bottom-4 right-4 w-3 h-3 border-r-2 border-b-2 border-green-400 opacity-60"></div>
                      <!-- Center dot -->
                      <div class="w-2 h-2 bg-green-400 rounded-full opacity-80"></div>
                    </div>
                  </div>
                </div>

                <!-- Instructions overlay -->
                <div v-show="cameraActive && !faceDetectionLoading"
                     class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black to-transparent p-3 text-center">
                  <p class="text-white text-sm font-medium">
                    📸 Posisikan wajah di dalam oval hijau
                  </p>
                  <p class="text-green-300 text-xs mt-1">
                    Pastikan wajah terlihat jelas dan menghadap kamera
                  </p>
                </div>

                <!-- Loading state -->
                <div v-if="faceDetectionLoading"
                     class="absolute inset-0 bg-black bg-opacity-90 flex items-center justify-center">
                  <div class="text-center text-white">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-400 mx-auto mb-4"></div>
                    <p class="text-sm font-medium">🔍 Menganalisis wajah...</p>
                    <p class="text-xs mt-2 opacity-75">Mohon jangan bergerak</p>
                  </div>
                </div>

                <!-- Preview captured photo -->
                <div v-if="facePhoto && !faceDetectionLoading"
                     class="absolute inset-0 bg-white flex items-center justify-center">
                  <div class="text-center">
                    <img :src="facePhoto" alt="Captured Face"
                         class="w-48 h-48 object-cover rounded-lg mx-auto border-2 border-green-400 mb-2" />
                    <p class="text-sm text-green-600 font-medium">✅ Wajah berhasil diverifikasi</p>
                  </div>
                </div>

                <!-- Camera not ready state -->
                <div v-if="!cameraActive"
                     class="absolute inset-0 bg-gray-800 flex items-center justify-center">
                  <div class="text-center text-gray-300">
                    <div class="text-4xl mb-2">📷</div>
                    <p class="text-sm">Menyiapkan kamera...</p>
                  </div>
                </div>
              </div>
            </div>            <!-- Instructions -->
            <div v-if="!faceDetectionLoading" class="mb-4">
              <p class="text-sm text-gray-600 mb-2">
                📸 Pastikan wajah Anda terlihat jelas dan berada di dalam panduan oval
              </p>
              <div class="flex justify-center space-x-4 text-xs text-gray-500">
                <span>✓ Cahaya cukup</span>
                <span>✓ Wajah menghadap kamera</span>
                <span>✓ Tidak ada penghalang</span>
              </div>
            </div>

            <!-- Processing status -->
            <div v-if="faceDetectionLoading" class="mb-4">
              <p class="text-sm text-blue-600 font-medium">Sedang memproses verifikasi wajah...</p>
              <p class="text-xs text-gray-500 mt-1">Proses ini memakan waktu beberapa detik</p>
            </div>

            <!-- Security note -->
            <p class="text-xs text-gray-400 mt-4">
              🔒 Foto wajah disimpan hanya untuk keamanan dan audit presensi
            </p>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import satpamAPI from '../../services/satpam'
import jsQR from 'jsqr'
import faceDetection from '../../services/faceDetection'
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
const faceVideoPreview = ref(null)
const canvasElement = ref(null)
const cameraActive = ref(false)
const cameraLoading = ref(false)
const cameraError = ref('')
const cameraMode = ref('qr') // 'qr' or 'face'

// Face detection
const showFaceCapture = ref(false)
const faceDetectionLoading = ref(false)
const facePhoto = ref(null)
const faceData = ref({
  landmarks: null,
  descriptor: null,
  quality: null,
  message: null
})
const qrCodeData = ref('')

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
const autoCloseCountdown = ref(0)

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

const startCamera = async (facingMode = 'environment') => {
  cameraLoading.value = true
  cameraError.value = ''

  try {
    console.log(`📹 Starting camera with facingMode: ${facingMode}`)

    // Set camera mode based on facingMode
    cameraMode.value = facingMode === 'user' ? 'face' : 'qr'

    const stream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: facingMode,
        width: { ideal: facingMode === 'user' ? 640 : 1280 },
        height: { ideal: facingMode === 'user' ? 480 : 720 }
      }
    })

    if (videoElement.value) {
      videoElement.value.srcObject = stream
      cameraActive.value = true

      // Start auto-scanning when video is ready (only for QR scan)
      if (facingMode === 'environment') {
        videoElement.value.onloadedmetadata = () => {
          console.log('📹 Back camera started, beginning QR scan...')
          setTimeout(() => {
            if (cameraActive.value && cameraMode.value === 'qr') {
              captureQR()
            }
          }, 1000) // Wait 1 second for camera to stabilize
        }
      } else {
        console.log('📹 Front camera started for face capture')
      }
    }
  } catch (error) {
    console.error('Camera error:', error)
    cameraError.value = 'Gagal mengakses kamera. Pastikan izin kamera telah diberikan.'
  } finally {
    cameraLoading.value = false
  }
}

// Switch to front camera for face capture
const switchToFrontCamera = async () => {
  try {
    console.log('🔄 Switching to front camera for face capture...')

    // Stop current stream
    if (videoElement.value && videoElement.value.srcObject) {
      const tracks = videoElement.value.srcObject.getTracks()
      tracks.forEach(track => track.stop())
    }

    // Use startCamera with 'user' facingMode
    await startCamera('user')
    console.log('✅ Front camera activated')

  } catch (error) {
    console.error('Error switching to front camera:', error)
    throw new Error('Gagal mengaktifkan kamera depan. Pastikan perangkat memiliki kamera depan.')
  }
}

// Auto-detect QR codes from camera feed

const stopCamera = () => {
  if (videoElement.value?.srcObject) {
    const tracks = videoElement.value.srcObject.getTracks()
    tracks.forEach(track => track.stop())
    videoElement.value.srcObject = null
  }
  cameraActive.value = false
}

const captureQR = async () => {
  if (!videoElement.value || !canvasElement.value) {
    console.warn('⚠️ Video or canvas element not available')
    return
  }

  if (videoElement.value.readyState < 2) {
    console.warn('⚠️ Video not ready, readyState:', videoElement.value.readyState)
    // Retry after video is ready
    setTimeout(() => {
      if (cameraActive.value && cameraMode.value === 'qr') {
        captureQR()
      }
    }, 500)
    return
  }

  processing.value = true

  try {
    // Capture frame from video
    const canvas = canvasElement.value
    const context = canvas.getContext('2d', { willReadFrequently: true })

    // Debug video dimensions
    console.log('🎥 Video dimensions:', {
      videoWidth: videoElement.value.videoWidth,
      videoHeight: videoElement.value.videoHeight,
      readyState: videoElement.value.readyState
    })

    canvas.width = videoElement.value.videoWidth
    canvas.height = videoElement.value.videoHeight
    context.drawImage(videoElement.value, 0, 0)

    // Get image data for QR detection
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height)

    // Debug image data
    console.log('🖼️ Image data:', {
      width: imageData.width,
      height: imageData.height,
      dataLength: imageData.data.length,
      hasData: imageData.data.some(pixel => pixel > 0)
    })

    // Try to decode QR code using jsQR
    const qrCodeResult = jsQR(imageData.data, imageData.width, imageData.height, {
      inversionAttempts: "dontInvert"
    })

    if (qrCodeResult) {
      console.log('🎯 QR Code detected:', qrCodeResult.data)

      // Don't stop camera yet - we need it for face capture
      // stopCamera() will be called after face verification
      await processQRCode(qrCodeResult.data)
    } else {
      // No QR code found, continue scanning
      console.log('🔍 No QR code detected, continuing scan...')

      // Auto-retry after a short delay
      setTimeout(() => {
        if (cameraActive.value && !processing.value && cameraMode.value === 'qr') {
          captureQR()
        }
      }, 500) // Retry every 500ms
    }

  } catch (err) {
    console.error('Camera capture error:', err)
    showError('Error Capture', 'Gagal menangkap QR code. Silakan coba lagi atau gunakan mode manual.')
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

/* ==========================================================
Kode ini untuk menangani kasus di mana fungsi processQrCode()
dipanggil sebagai event handler dari elemen input atau button
karena objek event memiliki properti target, sedangkan data QRcode
yang valid seharusnya berupa string.
Kode ini memeriksa apakah argumen qrData adalah objek dan punya
properti target, jika iya, berarti itu adalah event object, bukan
data QR code. Maka, variabel qrCode di-set ke null agar proses
selanjutnya menggunakan input manual (manualQrCode.value)
sebagai data QR code.
==================================================================== */
  let qrCode = qrData
  if (qrData && typeof qrData === 'object' && qrData.target !== undefined) {
    // Ini adalah objek event, abaikan dan gunakan input manual
    qrCode = null
  }

  qrCode = qrCode || manualQrCode.value.trim()

  if (!qrCode) {
    showError('QR Code Kosong', 'Mohon masukkan kode QR.')
    processing.value = false
    return
  }

  // Jika qrCode adalah JSON string, parsing untuk mendapatkan kode yang valid
  try {
    if (typeof qrCode === 'string' && (qrCode.startsWith('{') || qrCode.includes('code'))) {
      const parsedData = JSON.parse(qrCode)
      if (parsedData.code) {
        qrCode = parsedData.code
      }
    }
  } catch {
    // Jika parsing gagal, gunakan nilai qrCode yang asli
    console.log('QR Code is not JSON, using as string:', qrCode)
  }

  // Memastikan qrCode adalah string dan bukan representasi objek
  if (typeof qrCode !== 'string' || qrCode.includes('[object')) {
    showError('QR Code Tidak Valid', 'Format QR code tidak valid. Mohon coba lagi.')
    processing.value = false
    return
  }

  console.log('Processing QR Code:', qrCode)

  // Simpan QR code untuk face capture step
  qrCodeData.value = qrCode

  // Mulai proses face capture
  await captureFacePhoto()
}

// Face capture method
const captureFacePhoto = async () => {
  try {
    showFaceCapture.value = true
    // Mulai dengan loading false untuk menampilkan pratinjau kamera terlebih dahulu
    faceDetectionLoading.value = false

    // Beralih ke kamera depan untuk verifikasi wajah
    console.log('🔄 Beralih ke kamera depan untuk verifikasi wajah...')
    await switchToFrontCamera()

    // Setup face video preview dengan front camera stream
    await new Promise(resolve => setTimeout(resolve, 300)) // Wait for modal and camera to be ready

    if (faceVideoPreview.value && videoElement.value && videoElement.value.srcObject) {
      console.log('Setting up face video preview with front camera...')
      faceVideoPreview.value.srcObject = videoElement.value.srcObject
      console.log('✅ Face video preview stream set with front camera')
    } else {
      console.warn('⚠️ Face video preview setup failed')
    }

    // Load face detection models first
    console.log('📋 Loading face detection models...')
    await faceDetection.loadModels()
    console.log('✅ Models berhasil dimuat')

    // Debug camera state
    console.log('🔍 Debug camera state:')
    console.log('- cameraActive:', cameraActive.value)
    console.log('- videoElement exists:', !!videoElement.value)
    console.log('- videoElement srcObject:', videoElement.value?.srcObject)
    console.log('- videoElement readyState:', videoElement.value?.readyState)
    console.log('- videoElement videoWidth:', videoElement.value?.videoWidth)
    console.log('- videoElement videoHeight:', videoElement.value?.videoHeight)

    // Cek apakah kamera aktif dan elemen video siap
    if (!cameraActive.value) {
      throw new Error('Camera tidak aktif. Status: ' + cameraActive.value)
    }

    if (!videoElement.value) {
      throw new Error('Video element tidak ditemukan.')
    }

    if (!videoElement.value.srcObject) {
      throw new Error('Video stream tidak tersedia.')
    }

    // Debug video element readyState
    console.log('Video element readyState:', videoElement.value.readyState)
    console.log('Camera active:', cameraActive.value)

    // Memberikan waktu kepada pengguna untuk memposisikan wajah (menampilkan pratinjau selama 3 detik)
    console.log('👤 Menampilkan pratinjau pemposisian wajah...')
    await new Promise(resolve => setTimeout(resolve, 3000))

    // Sekarang mulai proses deteksi wajah
    faceDetectionLoading.value = true
    console.log('📸 Mengambil foto wajah dari video...')

    // Tambahkan delay untuk memastikan frame video stabil
    await new Promise(resolve => setTimeout(resolve, 500))

    // Capture photo dari video
    const captureResult = await faceDetection.captureImageFromVideo(videoElement.value)
    facePhoto.value = captureResult.dataUrl

    console.log('✅ Photo berhasil diambil')

    // Buat elemen gambar sementara untuk deteksi wajah
    const img = new Image()

    img.onload = async () => {
      try {
        console.log('🔍 Memulai deteksi wajah pada gambar yang diambil...')

        // Deteksi face and landmarks
        const detection = await faceDetection.detectFaceWithLandmarks(img)
        console.log('Face detection result:', detection)

        // Validasi face quality
        const validation = faceDetection.validateFaceQuality(detection)
        console.log('Face validation result:', validation)

        if (!validation.isValid) {
          showError('Validasi Wajah Gagal', validation.message)
          showFaceCapture.value = false
          faceDetectionLoading.value = false
          processing.value = false

          // Stop kamera ketika validasi error
          if (cameraActive.value) {
            stopCamera()
          }
          return
        }

        // Simpan face data
        faceData.value = {
          landmarks: detection.landmarks.positions,
          descriptor: Array.from(detection.descriptor),
          quality: 'good',
          message: validation.message
        }

        console.log('✅ Data wajah disimpan, melanjutkan ke proses presensi...')
        faceDetectionLoading.value = false

        // Proses presensi
        await processAttendance()
      } catch (faceError) {
        console.error('Face detection error:', faceError)
        showError('Error Deteksi Wajah', 'Gagal menganalisis wajah: ' + faceError.message)
        showFaceCapture.value = false
        faceDetectionLoading.value = false
        processing.value = false

        // Stop kamera ketika face detection error
        if (cameraActive.value) {
          stopCamera()
        }
      }
    }

    img.onerror = (error) => {
      console.error('Failed to load captured image:', error)
      showError('Error Gambar', 'Gagal memuat foto yang diambil. Pastikan camera berfungsi dengan baik.')
      showFaceCapture.value = false
      faceDetectionLoading.value = false
      processing.value = false

      // Stop kamera ketika image load error
      if (cameraActive.value) {
        stopCamera()
      }
    }

    // Set sumber gambar dengan error handling
    console.log('🖼️ Memuat gambar yang diambil untuk pemrosesan...')
    img.crossOrigin = 'anonymous'
    img.src = captureResult.dataUrl

  } catch (error) {
    console.error('Face capture error:', error)
    showError('Error Ambil Foto', error.message)
    showFaceCapture.value = false
    faceDetectionLoading.value = false
    processing.value = false

    // Stop kamera ketika face capture error
    if (cameraActive.value) {
      stopCamera()
    }
  }
}

// Proses presensi dengan face data
const processAttendance = async () => {
  try {
    // Dapatkan lokasi saat ini
    let location = null
    try {
      location = await satpamAPI.getCurrentLocation()
    } catch (locationError) {
      console.warn('Tidak dapat menentukan lokasi:', locationError)
    }

    // Proses QR attendance dengan face data
    const result = await satpamAPI.processQrAttendance(
      qrCodeData.value,
      location?.latitude,
      location?.longitude,
      facePhoto.value, // base64 image
      faceData.value.landmarks,
      faceData.value.descriptor,
      faceData.value.quality,
      faceData.value.message
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

      // Stop kamera setelah scan sukses
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
    // Cleanup face capture
    showFaceCapture.value = false
    faceDetectionLoading.value = false
    processing.value = false

    // Reset face data
    facePhoto.value = null
    faceData.value = {
      landmarks: null,
      descriptor: null,
      quality: null,
      message: null
    }
    qrCodeData.value = ''
  }
}

const showSuccess = (title, description) => {
  successMessage.value = { title, description }
  showSuccessModal.value = true
  autoCloseCountdown.value = 3

  // Prevent body scrolling
  document.body.style.overflow = 'hidden'

  // Countdown timer
  const countdownTimer = setInterval(() => {
    autoCloseCountdown.value--
    if (autoCloseCountdown.value <= 0) {
      clearInterval(countdownTimer)
      if (showSuccessModal.value) {
        closeSuccessModal()
      }
    }
  }, 1000)
  modalTimers.push(countdownTimer)
}

const showError = (title, description) => {
  errorMessage.value = { title, description }
  showErrorModal.value = true

  // Prevent body scrolling
  document.body.style.overflow = 'hidden'

  // Auto dismiss after 5 seconds
  const timer = setTimeout(() => {
    if (showErrorModal.value) {
      closeErrorModal()
    }
  }, 5000)
  modalTimers.push(timer)
}

const closeSuccessModal = () => {
  showSuccessModal.value = false
  successMessage.value = { title: '', description: '' }
  autoCloseCountdown.value = 0

  // Restore body scrolling
  document.body.style.overflow = 'auto'

  // Clear any pending timers for this modal
  modalTimers.forEach(timer => {
    clearTimeout(timer)
    clearInterval(timer)
  })
  modalTimers = []
}

const closeErrorModal = () => {
  showErrorModal.value = false
  errorMessage.value = { title: '', description: '' }

  // Restore body scrolling
  document.body.style.overflow = 'auto'

  // Clear any pending timers for this modal
  modalTimers.forEach(timer => {
    clearTimeout(timer)
    clearInterval(timer)
  })
  modalTimers = []
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
let modalTimers = []

const handleEscapeKey = (event) => {
  if (event.key === 'Escape') {
    if (showSuccessModal.value) {
      closeSuccessModal()
    } else if (showErrorModal.value) {
      closeErrorModal()
    }
  }
}

onMounted(() => {
  // Start time update
  timeInterval = setInterval(() => {
    currentTime.value = new Date()
  }, 1000)

  // Add escape key listener
  document.addEventListener('keydown', handleEscapeKey)

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
  // Remove escape key listener
  document.removeEventListener('keydown', handleEscapeKey)

  // Clear any pending modal timers
  modalTimers.forEach(timer => {
    clearTimeout(timer)
    clearInterval(timer)
  })
  modalTimers = []

  // Restore body scrolling if modals are open
  if (showSuccessModal.value || showErrorModal.value) {
    document.body.style.overflow = 'auto'
  }
})
</script>

<style scoped>
/* Modal transition animations */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.3s ease;
}

.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.95) translateY(-20px);
}

/* Face guide centering styles */
.face-guide-container {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}

.face-guide-oval {
  animation: pulse-border 2s infinite;
}

@keyframes pulse-border {
  0%, 100% {
    border-opacity: 0.8;
  }
  50% {
    border-opacity: 0.4;
  }
}
</style>
