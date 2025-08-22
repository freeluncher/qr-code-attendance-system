<!-- Refactored Admin Dashboard -->
<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <DashboardHeader
      :user="authStore.user"
      @logout="handleLogout"
    />

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Stats Overview -->
      <StatsOverview :stats="stats" />

      <!-- Charts and Tables Row -->
      <ChartsSection
        :chart-data="chartData"
        :stats="stats"
        :chart-period="chartPeriod"
        :loading="loading"
        @change-period="changeChartPeriod"
      />

      <!-- Additional Charts Row -->
      <AdditionalCharts
        :chart-data="chartData"
        :top-late-employees="topLateEmployees"
        :chart-period="chartPeriod"
        :loading="loading"
      />

      <!-- Recent Activities and Quick Actions -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <RecentActivities
          :activities="recentActivities"
          :loading="loading"
        />
        <QuickActions />
      </div>

      <!-- AI Predictions Section -->
      <AIPredictions
        :predictions="aiPredictions"
        :loading="predictionsLoading"
        @generate="generatePredictions"
      />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import dashboardAPI from '../../services/dashboard'

// Components
import DashboardHeader from '../../components/admin/DashboardHeader.vue'
import StatsOverview from '../../components/admin/StatsOverview.vue'
import ChartsSection from '../../components/admin/ChartsSection.vue'
import AdditionalCharts from '../../components/admin/AdditionalCharts.vue'
import RecentActivities from '../../components/admin/RecentActivities.vue'
import QuickActions from '../../components/admin/QuickActions.vue'
import AIPredictions from '../../components/admin/AIPredictions.vue'

const router = useRouter()
const authStore = useAuthStore()

// Reactive state
const loading = ref(false)
const predictionsLoading = ref(false)
const chartPeriod = ref(7) // Default to 7 days

// Stats data
const stats = ref({
  total_satpam: 0,
  total_locations: 0,
  total_qr_codes: 0,
  today: {
    total_attendance: 0,
    late_count: 0,
    on_time_count: 0,
    attendance_rate: 0
  }
})

// Dashboard data
const topLateEmployees = ref([])
const recentActivities = ref([])
const aiPredictions = ref([])
const chartData = ref([])

// Methods

// Handle user logout
const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}

const loadDashboardData = async () => {
  loading.value = true
  try {
    // Load all dashboard data using proper API endpoints
    const [statsData, activities, lateEmployees, chartDataResult] = await Promise.all([
      dashboardAPI.getAdminStats(),
      dashboardAPI.getRecentActivities(5),
      dashboardAPI.getTopLateEmployees(chartPeriod.value, 5),
      dashboardAPI.getAttendanceChartData(chartPeriod.value)
    ])

    stats.value = statsData
    recentActivities.value = dashboardAPI.formatActivities(activities)
    topLateEmployees.value = lateEmployees.map(emp => ({
      name: emp.name,
      location: emp.location,
      lateCount: emp.late_count
    }))
    chartData.value = chartDataResult

    // Load AI predictions
    loadAIPredictions()

  } catch (error) {
    console.error('Error loading dashboard data:', error)
    // Fallback to default data on error
    stats.value = {
      total_satpam: 0,
      total_locations: 0,
      total_qr_codes: 0,
      today: {
        total_attendance: 0,
        late_count: 0,
        on_time_count: 0,
        attendance_rate: 0
      }
    }
  } finally {
    loading.value = false
  }
}

// Load AI Predictions
const loadAIPredictions = async () => {
  predictionsLoading.value = true
  try {
    console.log('Loading AI predictions...')
    const predictions = await dashboardAPI.getAIPredictions(6)
    console.log('Raw predictions response:', predictions)

    // Convert object to array if needed
    let predictionsArray = []
    if (predictions && typeof predictions === 'object') {
      if (Array.isArray(predictions)) {
        predictionsArray = predictions
      } else {
        // Convert object with numeric keys to array
        predictionsArray = Object.values(predictions)
      }
    }

    aiPredictions.value = predictionsArray
    console.log('AI Predictions loaded:', aiPredictions.value)
    console.log('Predictions count:', aiPredictions.value.length)
  } catch (error) {
    console.error('Error loading AI predictions:', error)
    // Keep empty array on error
    aiPredictions.value = []
  } finally {
    predictionsLoading.value = false
  }
}

// Generate new AI predictions (admin action)
const generatePredictions = async () => {
  predictionsLoading.value = true
  try {
    const response = await dashboardAPI.generateAIPredictions()

    // Show success message
    console.log('Generated predictions:', response.message)

    // You could add a toast notification here if available
    alert(`✅ ${response.message || 'AI Predictions generated successfully!'}`)

    // Reload predictions after generation
    await loadAIPredictions()

  } catch (error) {
    console.error('Error generating AI predictions:', error)
  } finally {
    predictionsLoading.value = false
  }
}

// Change chart period and reload data
const changeChartPeriod = async (days) => {
  chartPeriod.value = days
  loading.value = true

  try {
    // Reload chart data and late employees with new period
    const [lateEmployees, chartDataResult] = await Promise.all([
      dashboardAPI.getTopLateEmployees(days, 5),
      dashboardAPI.getAttendanceChartData(days)
    ])

    topLateEmployees.value = lateEmployees.map(emp => ({
      name: emp.name,
      location: emp.location,
      lateCount: emp.late_count
    }))
    chartData.value = chartDataResult

  } catch (error) {
    console.error('Error loading chart data:', error)
  } finally {
    loading.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script>

<style scoped>
/* Custom animations */
.fade-in {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hover effects */
.hover-scale:hover {
  transform: scale(1.02);
  transition: transform 0.2s ease-in-out;
}

/* Gradient text */
.gradient-text {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  background-clip: text;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}
</style>
