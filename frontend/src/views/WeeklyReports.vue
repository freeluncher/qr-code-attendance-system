<template>
  <div class="weekly-reports-page">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Weekly Reports</h1>
        <p class="text-gray-600">Generate and manage weekly attendance reports</p>
      </div>
      <button
        @click="showGenerateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Generate Report
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
          <select v-model="filters.location_id" @change="loadReports" class="w-full p-2 border border-gray-300 rounded-md">
            <option value="">All Locations</option>
            <option v-for="location in locations" :key="location.id" :value="location.id">
              {{ location.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
          <input
            v-model="filters.from_date"
            @change="loadReports"
            type="date"
            class="w-full p-2 border border-gray-300 rounded-md"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
          <input
            v-model="filters.to_date"
            @change="loadReports"
            type="date"
            class="w-full p-2 border border-gray-300 rounded-md"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
          <button
            @click="clearFilters"
            class="w-full p-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Reports List -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
      <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Generated Reports</h2>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Loading reports...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="reports.length === 0" class="p-8 text-center">
        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No Reports Found</h3>
        <p class="text-gray-600">Generate your first weekly report to get started.</p>
      </div>

      <!-- Reports Table -->
      <div v-else class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Location
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Week Period
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Summary
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email Status
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Generated
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="report in reports" :key="report.id" class="hover:bg-gray-50">
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-400 rounded-full mr-2"></div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ report.location.name }}</div>
                    <div class="text-sm text-gray-500">{{ report.location.address }}</div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDateRange(report.week_start_date, report.week_end_date) }}</div>
                <div class="text-sm text-gray-500">Week {{ getWeekNumber(report.week_start_date) }}</div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <div class="grid grid-cols-2 gap-2 text-xs">
                  <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full">
                    {{ report.report_data.summary.on_time }} On-Time
                  </span>
                  <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                    {{ report.report_data.summary.late }} Late
                  </span>
                  <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full">
                    {{ report.report_data.summary.absent }} Absent
                  </span>
                  <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                    {{ report.report_data.summary.total_employees }} Total
                  </span>
                </div>
              </td>
              <td class="px-4 py-4 whitespace-nowrap">
                <span v-if="report.email_sent_at" class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                  Sent {{ formatDateTime(report.email_sent_at) }}
                </span>
                <span v-else class="bg-gray-100 text-gray-800 px-2 py-1 rounded-full text-xs">
                  Not Sent
                </span>
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDateTime(report.created_at) }}
              </td>
              <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button
                    @click="viewReport(report)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    View
                  </button>
                  <button
                    @click="downloadReport(report)"
                    class="text-green-600 hover:text-green-900"
                  >
                    Download
                  </button>
                  <button
                    @click="sendEmail(report)"
                    class="text-purple-600 hover:text-purple-900"
                  >
                    Email
                  </button>
                  <button
                    @click="deleteReport(report)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Generate Report Modal -->
    <div v-if="showGenerateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Generate Weekly Report</h3>
          <button @click="showGenerateModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="generateReport" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
            <select v-model="newReport.location_id" required class="w-full p-2 border border-gray-300 rounded-md">
              <option value="">Select Location</option>
              <option v-for="location in locations" :key="location.id" :value="location.id">
                {{ location.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Week Start Date</label>
            <input
              v-model="newReport.week_start_date"
              type="date"
              required
              class="w-full p-2 border border-gray-300 rounded-md"
            >
          </div>

          <div>
            <label class="flex items-center">
              <input v-model="newReport.send_email" type="checkbox" class="mr-2">
              Send via Email
            </label>
          </div>

          <div v-if="newReport.send_email" class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Email Recipients</label>
            <div v-for="(email, index) in newReport.email_recipients" :key="index" class="flex gap-2">
              <input
                v-model="newReport.email_recipients[index]"
                type="email"
                class="flex-1 p-2 border border-gray-300 rounded-md"
                placeholder="Enter email address"
              >
              <button
                type="button"
                @click="removeEmailRecipient(index)"
                class="text-red-600 hover:text-red-800"
              >
                Remove
              </button>
            </div>
            <button
              type="button"
              @click="addEmailRecipient"
              class="text-blue-600 hover:text-blue-800 text-sm"
            >
              + Add Email
            </button>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="showGenerateModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="generating"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {{ generating ? 'Generating...' : 'Generate' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Email Modal -->
    <div v-if="showEmailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Send Report via Email</h3>
          <button @click="showEmailModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <form @submit.prevent="sendReportEmail" class="space-y-4">
          <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Email Recipients</label>
            <div v-for="(email, index) in emailForm.recipients" :key="index" class="flex gap-2">
              <input
                v-model="emailForm.recipients[index]"
                type="email"
                class="flex-1 p-2 border border-gray-300 rounded-md"
                placeholder="Enter email address"
                required
              >
              <button
                type="button"
                @click="emailForm.recipients.splice(index, 1)"
                class="text-red-600 hover:text-red-800"
              >
                Remove
              </button>
            </div>
            <button
              type="button"
              @click="emailForm.recipients.push('')"
              class="text-blue-600 hover:text-blue-800 text-sm"
            >
              + Add Email
            </button>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="showEmailModal = false"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="sendingEmail"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {{ sendingEmail ? 'Sending...' : 'Send Email' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import api from '@/api'

export default {
  name: 'WeeklyReports',
  setup() {
    const loading = ref(false)
    const generating = ref(false)
    const sendingEmail = ref(false)
    const reports = ref([])
    const locations = ref([])
    const showGenerateModal = ref(false)
    const showEmailModal = ref(false)
    const selectedReport = ref(null)

    const filters = reactive({
      location_id: '',
      from_date: '',
      to_date: ''
    })

    const newReport = reactive({
      location_id: '',
      week_start_date: '',
      send_email: false,
      email_recipients: ['']
    })

    const emailForm = reactive({
      recipients: ['']
    })

    const loadReports = async () => {
      try {
        loading.value = true
        const params = {}

        if (filters.location_id) params.location_id = filters.location_id
        if (filters.from_date) params.from_date = filters.from_date
        if (filters.to_date) params.to_date = filters.to_date

        const response = await api.get('/weekly-reports', { params })
        reports.value = response.data.data.data || response.data.data
      } catch (error) {
        console.error('Error loading reports:', error)
        alert('Failed to load reports')
      } finally {
        loading.value = false
      }
    }

    const loadLocations = async () => {
      try {
        const response = await api.get('/weekly-reports/locations')
        locations.value = response.data.data
      } catch (error) {
        console.error('Error loading locations:', error)
      }
    }

    const generateReport = async () => {
      try {
        generating.value = true

        const data = {
          location_id: newReport.location_id,
          week_start_date: newReport.week_start_date,
          send_email: newReport.send_email,
          email_recipients: newReport.send_email ? newReport.email_recipients.filter(email => email.trim()) : []
        }

        await api.post('/weekly-reports', data)

        showGenerateModal.value = false
        resetNewReport()
        await loadReports()

        alert('Weekly report generated successfully!')
      } catch (error) {
        console.error('Error generating report:', error)
        alert('Failed to generate report: ' + (error.response?.data?.message || error.message))
      } finally {
        generating.value = false
      }
    }

    const downloadReport = async (report) => {
      try {
        const response = await api.get(`/weekly-reports/${report.id}/download`, {
          responseType: 'blob'
        })

        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.download = `weekly_report_${report.location.name}_${report.week_start_date}.csv`
        document.body.appendChild(link)
        link.click()
        document.body.removeChild(link)
        window.URL.revokeObjectURL(url)
      } catch (error) {
        console.error('Error downloading report:', error)
        alert('Failed to download report')
      }
    }

    const sendEmail = (report) => {
      selectedReport.value = report
      emailForm.recipients = ['']
      showEmailModal.value = true
    }

    const sendReportEmail = async () => {
      try {
        sendingEmail.value = true

        const data = {
          email_recipients: emailForm.recipients.filter(email => email.trim())
        }

        await api.post(`/weekly-reports/${selectedReport.value.id}/send-email`, data)

        showEmailModal.value = false
        alert('Report emails queued successfully!')
      } catch (error) {
        console.error('Error sending email:', error)
        alert('Failed to send emails: ' + (error.response?.data?.message || error.message))
      } finally {
        sendingEmail.value = false
      }
    }

    const deleteReport = async (report) => {
      if (!confirm('Are you sure you want to delete this report?')) return

      try {
        await api.delete(`/weekly-reports/${report.id}`)
        await loadReports()
        alert('Report deleted successfully!')
      } catch (error) {
        console.error('Error deleting report:', error)
        alert('Failed to delete report')
      }
    }

    const viewReport = (report) => {
      // Navigate to report details or open in modal
      console.log('View report:', report)
    }

    const clearFilters = () => {
      filters.location_id = ''
      filters.from_date = ''
      filters.to_date = ''
      loadReports()
    }

    const resetNewReport = () => {
      newReport.location_id = ''
      newReport.week_start_date = ''
      newReport.send_email = false
      newReport.email_recipients = ['']
    }

    const addEmailRecipient = () => {
      newReport.email_recipients.push('')
    }

    const removeEmailRecipient = (index) => {
      if (newReport.email_recipients.length > 1) {
        newReport.email_recipients.splice(index, 1)
      }
    }

    // Utility functions
    const formatDateRange = (startDate, endDate) => {
      const start = new Date(startDate).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short'
      })
      const end = new Date(endDate).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      })
      return `${start} - ${end}`
    }

    const formatDateTime = (dateTime) => {
      return new Date(dateTime).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getWeekNumber = (date) => {
      const d = new Date(date)
      const yearStart = new Date(d.getFullYear(), 0, 1)
      return Math.ceil(((d - yearStart) / 86400000 + 1) / 7)
    }

    onMounted(() => {
      loadReports()
      loadLocations()
    })

    return {
      loading,
      generating,
      sendingEmail,
      reports,
      locations,
      showGenerateModal,
      showEmailModal,
      selectedReport,
      filters,
      newReport,
      emailForm,
      loadReports,
      generateReport,
      downloadReport,
      sendEmail,
      sendReportEmail,
      deleteReport,
      viewReport,
      clearFilters,
      addEmailRecipient,
      removeEmailRecipient,
      formatDateRange,
      formatDateTime,
      getWeekNumber
    }
  }
}
</script>

<style scoped>
.weekly-reports-page {
  padding: 1.5rem;
  max-width: 100%;
}
</style>
