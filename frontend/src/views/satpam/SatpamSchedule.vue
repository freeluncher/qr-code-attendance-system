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
              <CalendarIcon class="h-8 w-8 text-green-600 mr-3" />
              <div class="hidden sm:block">
                <h1 class="text-xl font-semibold text-gray-900">Jadwal Shift</h1>
                <p class="text-sm text-gray-500">{{ currentMonth }} - {{ authStore.user?.name }}</p>
              </div>
            </div>
          </div>

          <!-- View Toggle -->
          <div class="flex items-center space-x-2">
            <div class="bg-gray-100 rounded-lg p-1 flex">
              <button
                @click="viewMode = 'calendar'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'calendar' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                Kalender
              </button>
              <button
                @click="viewMode = 'list'"
                :class="[
                  'px-3 py-1 text-sm font-medium rounded-md transition-colors',
                  viewMode === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'
                ]"
              >
                Daftar
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Schedule Stats -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CalendarDaysIcon class="h-6 sm:h-8 w-6 sm:w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Shift</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ scheduleStats.totalShifts }}</p>
              <p class="text-xs text-gray-500">bulan ini</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <ClockIcon class="h-6 sm:h-8 w-6 sm:w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Jam</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ scheduleStats.totalHours }}</p>
              <p class="text-xs text-gray-500">jam kerja</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <SunIcon class="h-6 sm:h-8 w-6 sm:w-8 text-yellow-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Shift Pagi</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ scheduleStats.morningShifts }}</p>
              <p class="text-xs text-gray-500">06:00-14:00</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 border border-gray-200">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <MoonIcon class="h-6 sm:h-8 w-6 sm:w-8 text-indigo-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Shift Malam</p>
              <p class="text-xl sm:text-2xl font-semibold text-gray-900">{{ scheduleStats.nightShifts }}</p>
              <p class="text-xs text-gray-500">22:00-06:00</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Calendar View -->
      <div v-if="viewMode === 'calendar'" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <!-- Calendar Header -->
        <div class="bg-gray-50 px-4 sm:px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-lg font-medium text-gray-900">{{ currentMonth }}</h3>
              <p class="text-sm text-gray-500">Klik tanggal untuk melihat detail shift</p>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="previousMonth"
                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <ChevronLeftIcon class="h-5 w-5 text-gray-500" />
              </button>
              <button
                @click="nextMonth"
                class="p-2 hover:bg-gray-100 rounded-lg transition-colors"
              >
                <ChevronRightIcon class="h-5 w-5 text-gray-500" />
              </button>
            </div>
          </div>
        </div>

        <!-- Calendar Grid -->
        <div class="p-4 sm:p-6">
          <!-- Day Headers -->
          <div class="grid grid-cols-7 gap-1 mb-4">
            <div
              v-for="day in dayHeaders"
              :key="day"
              class="p-2 text-center text-xs sm:text-sm font-medium text-gray-500"
            >
              {{ day }}
            </div>
          </div>

          <!-- Calendar Days -->
          <div class="grid grid-cols-7 gap-1">
            <div
              v-for="(day, index) in calendarDays"
              :key="index"
              :class="[
                'p-1 sm:p-2 border border-gray-100 rounded-lg min-h-[60px] sm:min-h-[80px] cursor-pointer hover:bg-gray-50 transition-colors',
                day.isCurrentMonth ? 'bg-white' : 'bg-gray-50',
                day.isToday ? 'ring-2 ring-blue-500' : '',
                day.hasShift ? 'border-green-200' : ''
              ]"
              @click="selectDate(day)"
            >
              <div class="text-xs sm:text-sm font-medium" :class="[
                day.isCurrentMonth ? 'text-gray-900' : 'text-gray-400',
                day.isToday ? 'text-blue-600' : ''
              ]">
                {{ day.date }}
              </div>
              
              <!-- Shift Indicator -->
              <div v-if="day.shift" class="mt-1">
                <div :class="[
                  'text-xs px-1 sm:px-2 py-0.5 rounded text-center',
                  getShiftColor(day.shift.type)
                ]">
                  {{ day.shift.time }}
                </div>
                <div class="text-xs text-gray-500 mt-1 truncate">
                  {{ day.shift.location }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- List View -->
      <div v-if="viewMode === 'list'" class="space-y-4">
        <!-- Filter Controls -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-col sm:flex-row gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                <select
                  v-model="filterMonth"
                  class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option v-for="(month, index) in monthOptions" :key="index" :value="index">
                    {{ month }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Shift</label>
                <select
                  v-model="filterShiftType"
                  class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                  <option value="">Semua Shift</option>
                  <option value="pagi">Shift Pagi</option>
                  <option value="siang">Shift Siang</option>
                  <option value="malam">Shift Malam</option>
                </select>
              </div>
            </div>
            <button
              @click="exportSchedule"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
            >
              <DocumentArrowDownIcon class="h-5 w-5 inline mr-2" />
              Export
            </button>
          </div>
        </div>

        <!-- Schedule List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 divide-y divide-gray-200">
          <div v-if="loading" class="p-8 text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p class="text-gray-500">Memuat jadwal...</p>
          </div>

          <div v-else-if="filteredSchedule.length === 0" class="p-8 text-center">
            <CalendarIcon class="h-12 w-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500">Tidak ada jadwal ditemukan</p>
          </div>

          <div v-else>
            <div
              v-for="schedule in filteredSchedule"
              :key="schedule.id"
              class="p-4 sm:p-6 hover:bg-gray-50 transition-colors"
            >
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex items-start sm:items-center space-x-4">
                  <!-- Date -->
                  <div class="text-center min-w-[60px]">
                    <div class="text-lg font-bold text-gray-900">{{ schedule.day }}</div>
                    <div class="text-xs text-gray-500">{{ schedule.month }}</div>
                  </div>

                  <!-- Shift Details -->
                  <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-1">
                      <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getShiftColor(schedule.type)]">
                        {{ schedule.shiftName }}
                      </span>
                      <span class="text-sm text-gray-600">{{ schedule.time }}</span>
                    </div>
                    <div class="flex items-center text-sm text-gray-500">
                      <MapPinIcon class="h-4 w-4 mr-1" />
                      <span>{{ schedule.location }}</span>
                    </div>
                    <div v-if="schedule.notes" class="text-sm text-gray-600 mt-1">
                      {{ schedule.notes }}
                    </div>
                  </div>
                </div>

                <!-- Status -->
                <div class="flex items-center space-x-3">
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">{{ schedule.duration }} jam</div>
                    <div :class="['text-xs', getStatusColor(schedule.status)]">
                      {{ getStatusText(schedule.status) }}
                    </div>
                  </div>
                  <div :class="['w-3 h-3 rounded-full', getStatusDot(schedule.status)]"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Selected Date Modal -->
      <div v-if="selectedDate" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="selectedDate = null">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
          </div>

          <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
              <div class="text-center">
                <CalendarIcon class="mx-auto h-12 w-12 text-blue-600" />
                <h3 class="mt-2 text-lg leading-6 font-medium text-gray-900">
                  Detail Shift - {{ selectedDate.fullDate }}
                </h3>
              </div>

              <div v-if="selectedDate.shift" class="mt-4">
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-500">Shift</span>
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getShiftColor(selectedDate.shift.type)]">
                      {{ selectedDate.shift.name }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-500">Waktu</span>
                    <span class="text-sm text-gray-900">{{ selectedDate.shift.time }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-500">Lokasi</span>
                    <span class="text-sm text-gray-900">{{ selectedDate.shift.location }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-gray-500">Durasi</span>
                    <span class="text-sm text-gray-900">{{ selectedDate.shift.duration }} jam</span>
                  </div>
                  <div v-if="selectedDate.shift.notes" class="pt-2 border-t border-gray-200">
                    <span class="text-sm font-medium text-gray-500">Catatan:</span>
                    <p class="text-sm text-gray-900 mt-1">{{ selectedDate.shift.notes }}</p>
                  </div>
                </div>
              </div>

              <div v-else class="mt-4 text-center py-6">
                <p class="text-gray-500">Tidak ada shift pada tanggal ini</p>
              </div>
            </div>

            <div class="mt-5 sm:mt-6">
              <button
                @click="selectedDate = null"
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
import { useAuthStore } from '../../stores/auth'
import {
  CalendarIcon,
  ArrowLeftIcon,
  CalendarDaysIcon,
  ClockIcon,
  SunIcon,
  MoonIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  MapPinIcon,
  DocumentArrowDownIcon
} from '@heroicons/vue/24/outline'

const authStore = useAuthStore()

// Reactive state
const viewMode = ref('calendar')
const currentDate = ref(new Date())
const selectedDate = ref(null)
const loading = ref(false)
const filterMonth = ref(new Date().getMonth())
const filterShiftType = ref('')

// Sample schedule data
const schedules = ref([
  {
    id: 1,
    date: '2025-08-21',
    shiftName: 'Shift Pagi',
    type: 'pagi',
    time: '06:00 - 14:00',
    location: 'Pos Utama',
    duration: 8,
    notes: 'Shift normal hari kerja',
    status: 'scheduled'
  },
  {
    id: 2,
    date: '2025-08-22',
    shiftName: 'Shift Pagi',
    type: 'pagi',
    time: '06:00 - 14:00',
    location: 'Pos Selatan',
    duration: 8,
    notes: '',
    status: 'scheduled'
  },
  {
    id: 3,
    date: '2025-08-23',
    shiftName: 'Shift Malam',
    type: 'malam',
    time: '22:00 - 06:00',
    location: 'Pos Utama',
    duration: 8,
    notes: 'Patroli extra di area parkir',
    status: 'completed'
  }
])

// Computed properties
const currentMonth = computed(() => {
  return currentDate.value.toLocaleDateString('id-ID', {
    month: 'long',
    year: 'numeric'
  })
})

const dayHeaders = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']

const monthOptions = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
]

const scheduleStats = computed(() => {
  const currentMonth = new Date().getMonth()
  const monthSchedules = schedules.value.filter(s => {
    const scheduleMonth = new Date(s.date).getMonth()
    return scheduleMonth === currentMonth
  })

  return {
    totalShifts: monthSchedules.length,
    totalHours: monthSchedules.reduce((sum, s) => sum + s.duration, 0),
    morningShifts: monthSchedules.filter(s => s.type === 'pagi').length,
    nightShifts: monthSchedules.filter(s => s.type === 'malam').length
  }
})

const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())
  
  const days = []
  const today = new Date()
  
  for (let i = 0; i < 42; i++) {
    const date = new Date(startDate)
    date.setDate(startDate.getDate() + i)
    
    const dateString = date.toISOString().split('T')[0]
    const schedule = schedules.value.find(s => s.date === dateString)
    
    days.push({
      date: date.getDate(),
      fullDate: date.toLocaleDateString('id-ID'),
      isCurrentMonth: date.getMonth() === month,
      isToday: date.toDateString() === today.toDateString(),
      hasShift: !!schedule,
      shift: schedule ? {
        name: schedule.shiftName,
        type: schedule.type,
        time: schedule.time,
        location: schedule.location,
        duration: schedule.duration,
        notes: schedule.notes
      } : null
    })
  }
  
  return days
})

const filteredSchedule = computed(() => {
  return schedules.value
    .filter(schedule => {
      const scheduleDate = new Date(schedule.date)
      if (scheduleDate.getMonth() !== filterMonth.value) return false
      if (filterShiftType.value && schedule.type !== filterShiftType.value) return false
      return true
    })
    .map(schedule => ({
      ...schedule,
      day: new Date(schedule.date).getDate(),
      month: new Date(schedule.date).toLocaleDateString('id-ID', { month: 'short' })
    }))
    .sort((a, b) => new Date(a.date) - new Date(b.date))
})

// Methods
const previousMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() - 1, 1)
}

const nextMonth = () => {
  currentDate.value = new Date(currentDate.value.getFullYear(), currentDate.value.getMonth() + 1, 1)
}

const selectDate = (day) => {
  selectedDate.value = day
}

const getShiftColor = (type) => {
  const colors = {
    'pagi': 'bg-yellow-100 text-yellow-800',
    'siang': 'bg-orange-100 text-orange-800',
    'malam': 'bg-indigo-100 text-indigo-800'
  }
  return colors[type] || 'bg-gray-100 text-gray-800'
}

const getStatusColor = (status) => {
  const colors = {
    'scheduled': 'text-blue-600',
    'completed': 'text-green-600',
    'missed': 'text-red-600'
  }
  return colors[status] || 'text-gray-600'
}

const getStatusDot = (status) => {
  const colors = {
    'scheduled': 'bg-blue-500',
    'completed': 'bg-green-500',
    'missed': 'bg-red-500'
  }
  return colors[status] || 'bg-gray-500'
}

const getStatusText = (status) => {
  const texts = {
    'scheduled': 'Dijadwalkan',
    'completed': 'Selesai',
    'missed': 'Terlewat'
  }
  return texts[status] || 'Unknown'
}

const exportSchedule = () => {
  // Simulate export functionality
  alert('Fitur export akan segera tersedia')
}

const loadScheduleData = async () => {
  loading.value = true
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    // Data already loaded in schedules ref
    
  } catch (error) {
    console.error('Error loading schedule:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadScheduleData()
})
</script>
