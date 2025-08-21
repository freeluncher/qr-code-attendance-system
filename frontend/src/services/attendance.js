import api from './api'

export const attendanceAPI = {
  // Get all attendance records with pagination and filters
  async getAttendances(params = {}) {
    try {
      const queryParams = new URLSearchParams()
      Object.keys(params).forEach(key => {
        if (params[key] !== null && params[key] !== undefined && params[key] !== '') {
          queryParams.append(key, params[key])
        }
      })

      const response = await api.get(`/attendances?${queryParams}`)
      return response
    } catch (error) {
      console.error('Error fetching attendances:', error)
      throw error
    }
  },

  // Create new attendance record (for manual entry)
  async createAttendance(data) {
    try {
      const response = await api.post('/attendances', data)
      return response
    } catch (error) {
      console.error('Error creating attendance:', error)
      throw error
    }
  },

  // Update attendance record
  async updateAttendance(id, data) {
    try {
      const response = await api.put(`/attendances/${id}`, data)
      return response
    } catch (error) {
      console.error('Error updating attendance:', error)
      throw error
    }
  },

  // Delete attendance record
  async deleteAttendance(id) {
    try {
      const response = await api.delete(`/attendances/${id}`)
      return response
    } catch (error) {
      console.error('Error deleting attendance:', error)
      throw error
    }
  },

  // Bulk delete attendance records
  async bulkDeleteAttendances(ids) {
    try {
      const response = await api.post('/attendances/bulk-delete', {
        ids
      })
      return response
    } catch (error) {
      console.error('Error bulk deleting attendances:', error)
      throw error
    }
  },

  // Export attendance data
  async exportAttendances(filters = {}, format = 'excel') {
    try {
      const params = new URLSearchParams()
      Object.keys(filters).forEach(key => {
        if (filters[key]) {
          params.append(key, filters[key])
        }
      })
      params.append('format', format)

      const response = await api.get(`/attendances/export?${params}`, {
        responseType: 'blob'
      })
      return response
    } catch (error) {
      console.error('Error exporting attendances:', error)
      throw error
    }
  },

  // QR code attendance processing (for satpam)
  async processQRAttendance(data) {
    try {
      const formData = new FormData()
      Object.keys(data).forEach(key => {
        formData.append(key, data[key])
      })

      const response = await api.post('/attendances', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })
      return response
    } catch (error) {
      console.error('Error processing QR attendance:', error)
      throw error
    }
  }
}

export default attendanceAPI
