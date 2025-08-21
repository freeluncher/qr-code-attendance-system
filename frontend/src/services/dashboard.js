import api from './api'

export const dashboardAPI = {
  // Admin dashboard endpoints
  async getAdminStats() {
    try {
      const response = await api.get('/dashboard/stats')
      return response
    } catch (error) {
      console.error('Error fetching admin stats:', error)
      throw error
    }
  },

  async getRecentActivities(limit = 10) {
    try {
      const response = await api.get('/dashboard/activities', {
        params: { limit }
      })
      return response.data || []
    } catch (error) {
      console.error('Error fetching recent activities:', error)
      return []
    }
  },

  async getTopLateEmployees(days = 7, limit = 5) {
    try {
      const response = await api.get('/dashboard/late-employees', {
        params: { days, limit }
      })
      return response.data || []
    } catch (error) {
      console.error('Error fetching top late employees:', error)
      return []
    }
  },

  async getAttendanceChartData(days = 7) {
    try {
      const response = await api.get('/dashboard/attendance-chart', {
        params: { days }
      })
      return response.data || []
    } catch (error) {
      console.error('Error fetching chart data:', error)
      return []
    }
  },

  // AI Predictions endpoints
  async getAIPredictions(limit = 6) {
    try {
      const response = await api.get('/dashboard/ai-predictions', {
        params: { limit }
      })
      return response.data || []
    } catch (error) {
      console.error('Error fetching AI predictions:', error)
      return []
    }
  },

  async generateAIPredictions() {
    try {
      const response = await api.post('/dashboard/ai-predictions/generate')
      return response
    } catch (error) {
      console.error('Error generating AI predictions:', error)
      throw error
    }
  },

  // Satpam dashboard endpoints
  async getSatpamStats() {
    try {
      const response = await api.get('/dashboard/my-stats')
      return response
    } catch (error) {
      console.error('Error fetching satpam stats:', error)
      throw error
    }
  },

  async getSatpamHistory(limit = 7) {
    try {
      const response = await api.get('/dashboard/my-history', {
        params: { limit }
      })
      return response.data || []
    } catch (error) {
      console.error('Error fetching satpam history:', error)
      return []
    }
  },

  // Helper function to format time ago
  formatTimeAgo(dateString) {
    const now = new Date()
    const past = new Date(dateString)
    const diffInMinutes = Math.floor((now - past) / (1000 * 60))

    if (diffInMinutes < 1) return 'Baru saja'
    if (diffInMinutes < 60) return `${diffInMinutes} menit yang lalu`

    const diffInHours = Math.floor(diffInMinutes / 60)
    if (diffInHours < 24) return `${diffInHours} jam yang lalu`

    const diffInDays = Math.floor(diffInHours / 24)
    return `${diffInDays} hari yang lalu`
  },

  // Format activities for display
  formatActivities(activities) {
    return activities.map(activity => ({
      id: activity.id,
      user: activity.user_name,
      action: `${activity.action} di ${activity.location_name}`,
      time: activity.formatted_time,
      type: activity.status === 'tepat_waktu' ? 'success' : 'warning',
      icon: activity.status === 'tepat_waktu' ? 'CheckCircleIcon' : 'ClockIcon'
    }))
  }
}

export default dashboardAPI
