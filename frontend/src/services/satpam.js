import api from './api'

/**
 * Satpam API Services
 */
class SatpamAPI {
  // Dashboard
  async getDashboardStats() {
    return await api.get('/satpam/stats')
  }

  async getTodayAttendance() {
    return await api.get('/satpam/today-attendance')
  }

  async getMonthlyStats() {
    return await api.get('/satpam/monthly-stats')
  }

  async getTodaySchedule() {
    return await api.get('/satpam/today-schedule')
  }

  async getRecentActivities(limit = 5) {
    return await api.get('/satpam/recent-activities', {
      params: { limit }
    })
  }

  // QR Code Attendance
  async processQrAttendance(qrCode, latitude = null, longitude = null, photo = null, lateReason = null) {
    // Validate qrCode
    if (!qrCode || typeof qrCode !== 'string' || qrCode.includes('[object')) {
      throw new Error('Invalid QR code format')
    }
    
    // Clean up the qrCode string
    qrCode = qrCode.trim()
    
    console.log('Sending QR attendance data:', {
      qr_code: qrCode,
      latitude,
      longitude,
      photo: photo ? 'file provided' : 'no file',
      late_reason: lateReason,
      qrCodeType: typeof qrCode,
      qrCodeLength: qrCode.length
    })
    
    // Use FormData for photo upload
    const formData = new FormData()
    formData.append('qr_code', qrCode)
    
    if (latitude !== null) {
      formData.append('latitude', latitude)
    }
    if (longitude !== null) {
      formData.append('longitude', longitude)
    }
    if (photo) {
      formData.append('photo', photo)
    }
    if (lateReason) {
      formData.append('late_reason', lateReason)
    }
    
    return await api.post('/satpam/qr-attendance', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  // Face Recognition
  async registerFaceReference(photo) {
    const formData = new FormData()
    formData.append('photo', photo)
    
    return await api.post('/satpam/register-face', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }

  async checkFaceRegistration() {
    return await api.get('/satpam/check-face-registration')
  }

  // History
  async getAttendanceHistory(params = {}) {
    return await api.get('/satpam/attendance-history', { params })
  }

  // Schedule
  async getSchedule(month = null, year = null) {
    const params = {}
    if (month) params.month = month
    if (year) params.year = year

    return await api.get('/satpam/schedule', { params })
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
