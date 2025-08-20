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
  async processQrAttendance(qrCode, latitude = null, longitude = null, facePhoto = null, faceLandmarks = null, faceDescriptor = null, faceQuality = null, faceMessage = null) {
    // Validate qrCode
    if (!qrCode || typeof qrCode !== 'string' || qrCode.includes('[object')) {
      throw new Error('Invalid QR code format')
    }

    // Clean up the qrCode string
    qrCode = qrCode.trim()

    const requestData = {
      qr_code: qrCode,
      latitude,
      longitude
    }

    // Add face data if provided
    if (facePhoto) {
      requestData.face_photo = facePhoto
      requestData.face_landmarks = faceLandmarks
      requestData.face_descriptor = faceDescriptor
      requestData.face_quality_status = faceQuality
      requestData.face_validation_message = faceMessage
    }

    console.log('Sending QR attendance data:', {
      qr_code: qrCode,
      latitude,
      longitude,
      qrCodeType: typeof qrCode,
      qrCodeLength: qrCode.length,
      hasFaceData: !!facePhoto
    })

    return await api.post('/satpam/qr-attendance', requestData)
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
