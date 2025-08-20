import axios from 'axios'

// Create axios instance with base configuration
const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle errors
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      // Instead of hard redirect, let the error propagate so components can handle it
      // window.location.href = '/login'
    }

    // Return error data in a consistent format
    const errorData = error.response?.data || { error: error.message || 'Unknown error' }
    return Promise.reject(errorData)
  }
)

// Auth API endpoints
export const authAPI = {
  login: (credentials) => api.post('/login', credentials),
  register: (userData) => api.post('/register', userData),
  logout: () => api.post('/logout'),
  me: () => api.get('/user')
}

// User API endpoints
export const userAPI = {
  getUsers: (params) => api.get('/users', { params }),
  getUserById: (id) => api.get(`/users/${id}`),
  createUser: (userData) => api.post('/users', userData),
  updateUser: (id, userData) => api.patch(`/users/${id}`, userData),
  deleteUser: (id) => api.delete(`/users/${id}`)
}

// Location API endpoints
export const locationAPI = {
  getLocations: (params) => api.get('/locations', { params }),
  getLocationById: (id) => api.get(`/locations/${id}`),
  createLocation: (locationData) => api.post('/locations', locationData),
  updateLocation: (id, locationData) => api.patch(`/locations/${id}`, locationData),
  deleteLocation: (id) => api.delete(`/locations/${id}`)
}

// Shift API endpoints
export const shiftAPI = {
  getShifts: (params) => api.get('/shifts', { params }),
  getShiftById: (id) => api.get(`/shifts/${id}`),
  createShift: (shiftData) => api.post('/shifts', shiftData),
  updateShift: (id, shiftData) => api.patch(`/shifts/${id}`, shiftData),
  deleteShift: (id) => api.delete(`/shifts/${id}`)
}

// QR Code API endpoints
export const qrCodeAPI = {
  getQrCodes: (params) => api.get('/qrcodes', { params }),
  getQrCodeById: (id) => api.get(`/qrcodes/${id}`),
  createQrCode: (qrCodeData) => api.post('/qrcodes', qrCodeData),
  updateQrCode: (id, qrCodeData) => api.patch(`/qrcodes/${id}`, qrCodeData),
  deleteQrCode: (id) => api.delete(`/qrcodes/${id}`)
}

// Attendance API endpoints
export const attendanceAPI = {
  getAttendances: (params) => api.get('/attendances', { params }),
  getAttendanceById: (id) => api.get(`/attendances/${id}`),
  createAttendance: (attendanceData) => {
    // Use FormData for file upload (photo)
    const formData = new FormData()
    Object.keys(attendanceData).forEach(key => {
      formData.append(key, attendanceData[key])
    })
    return api.post('/attendances', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  }
}

export default api
