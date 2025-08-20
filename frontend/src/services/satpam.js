import api from './api'

/**
 * Satpam API Services
 */
class SatpamAPI {
  // Dashboard
  async getDashboardStats() {
    const response = await api.get('/satpam/stats')
    return response.data
  }

  async getTodayAttendance() {
    const response = await api.get('/satpam/today-attendance')
    return response.data
  }

  async getMonthlyStats() {
    const response = await api.get('/satpam/monthly-stats')
    return response.data
  }

  async getTodaySchedule() {
    const response = await api.get('/satpam/today-schedule')
    return response.data
  }

  async getRecentActivities(limit = 5) {
    const response = await api.get('/satpam/recent-activities', {
      params: { limit }
    })
    return response.data
  }

  // QR Code Attendance
  async processQrAttendance(qrCode, latitude = null, longitude = null) {
    const response = await api.post('/satpam/qr-attendance', {
      qr_code: qrCode,
      latitude,
      longitude
    })
    return response.data
  }

  // History
  async getAttendanceHistory(params = {}) {
    const response = await api.get('/satpam/attendance-history', { params })
    return response.data
  }

  // Schedule
  async getSchedule(month = null, year = null) {
    const params = {}
    if (month) params.month = month
    if (year) params.year = year

    const response = await api.get('/satpam/schedule', { params })
    return response.data
  }

  // Location services
  async getCurrentLocation() {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error('Geolocation is not supported'))
        return
      }

      navigator.geolocation.getCurrentPosition(
        position => {
          resolve({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude,
            accuracy: position.coords.accuracy
          })
        },
        error => {
          reject(error)
        },
        {
          enableHighAccuracy: true,
          timeout: 10000,
          maximumAge: 600000
        }
      )
    })
  }
}

export default new SatpamAPI()
