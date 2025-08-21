import api from './api'

export const reportsAPI = {
  // Get attendance reports
  async getAttendanceReports(filters = {}) {
    try {
      const params = new URLSearchParams()
      Object.keys(filters).forEach(key => {
        if (filters[key]) {
          params.append(key, filters[key])
        }
      })

      const response = await api.get(`/reports/attendance?${params}`)
      return response
    } catch (error) {
      console.error('Error fetching attendance reports:', error)
      throw error
    }
  },

  // Export attendance report
  async exportAttendanceReport(filters = {}, format = 'excel') {
    try {
      const params = new URLSearchParams()
      Object.keys(filters).forEach(key => {
        if (filters[key]) {
          params.append(key, filters[key])
        }
      })
      params.append('format', format)

      const response = await api.get(`/reports/attendance/export?${params}`, {
        responseType: 'blob'
      })
      return response
    } catch (error) {
      console.error('Error exporting attendance report:', error)
      throw error
    }
  },

  // Get weekly reports
  async getWeeklyReports() {
    try {
      const response = await api.get('/weekly-reports')
      return response
    } catch (error) {
      console.error('Error fetching weekly reports:', error)
      throw error
    }
  },

  // Generate weekly report
  async generateWeeklyReport(data) {
    try {
      const response = await api.post('/weekly-reports', data)
      return response
    } catch (error) {
      console.error('Error generating weekly report:', error)
      throw error
    }
  },

  // Download weekly report
  async downloadWeeklyReport(reportId) {
    try {
      const response = await api.get(`/weekly-reports/${reportId}/download`, {
        responseType: 'blob'
      })
      return response
    } catch (error) {
      console.error('Error downloading weekly report:', error)
      throw error
    }
  },

  // Send weekly report via email
  async sendWeeklyReportEmail(reportId, emails) {
    try {
      const response = await api.post(`/weekly-reports/${reportId}/send-email`, {
        emails
      })
      return response
    } catch (error) {
      console.error('Error sending weekly report email:', error)
      throw error
    }
  }
}

export default reportsAPI
