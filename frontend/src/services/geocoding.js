import api from './api'

export const geocodingAPI = {
  /**
   * Get coordinates from address using backend geocoding service
   */
  async getCoordinatesFromAddress(address) {
    try {
      console.log('🔍 Geocoding request:', address)
      const response = await api.post('/geocode/address', { address })

      // Debug logging
      console.log('✅ Geocoding response (full):', response)
      console.log('✅ Response type:', typeof response)
      console.log('✅ Response keys:', Object.keys(response || {}))

      // The axios interceptor already extracts response.data,
      // so we should receive the full response object from backend
      return response
    } catch (error) {
      console.error('❌ Geocoding error:', error)
      console.error('Response data:', error.response?.data)
      throw error
    }
  },

  /**
   * Validate coordinates format
   */
  validateCoordinates(latitude, longitude) {
    const lat = parseFloat(latitude)
    const lng = parseFloat(longitude)

    // Basic validation for Indonesia bounds
    const isValid = !isNaN(lat) && !isNaN(lng) &&
                   lat >= -11.0 && lat <= 6.0 &&
                   lng >= 95.0 && lng <= 141.0

    return {
      isValid,
      latitude: lat,
      longitude: lng
    }
  },

  /**
   * Format coordinates for display
   */
  formatCoordinates(latitude, longitude, precision = 6) {
    return {
      latitude: parseFloat(latitude).toFixed(precision),
      longitude: parseFloat(longitude).toFixed(precision)
    }
  }
}

export default geocodingAPI
