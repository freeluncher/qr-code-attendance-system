import api from './api'

export const telegramAPI = {
  // Bot information
  async getBotInfo() {
    try {
      const response = await api.get('/telegram/bot-info')
      return response
    } catch (error) {
      console.error('Error getting bot info:', error)
      throw error
    }
  },

  // Webhook management
  async setWebhook(data) {
    try {
      const response = await api.post('/telegram/webhook', data)
      return response
    } catch (error) {
      console.error('Error setting webhook:', error)
      throw error
    }
  },

  // Telegram users
  async getTelegramUsers() {
    try {
      const response = await api.get('/telegram/users')
      return response
    } catch (error) {
      console.error('Error getting telegram users:', error)
      throw error
    }
  },

  // Notifications
  async sendTestNotification(data) {
    try {
      const response = await api.post('/telegram/test-notification', data)
      return response
    } catch (error) {
      console.error('Error sending test notification:', error)
      throw error
    }
  },

  async sendBroadcast(data) {
    try {
      const response = await api.post('/telegram/broadcast', data)
      return response
    } catch (error) {
      console.error('Error sending broadcast:', error)
      throw error
    }
  },

  async sendDailyReport() {
    try {
      const response = await api.post('/telegram/daily-report')
      return response
    } catch (error) {
      console.error('Error sending daily report:', error)
      throw error
    }
  },

  // User notification settings
  async toggleNotifications(userId, enabled) {
    try {
      const response = await api.put(`/telegram/users/${userId}/notifications`, {
        notifications_enabled: enabled
      })
      return response
    } catch (error) {
      console.error('Error toggling notifications:', error)
      throw error
    }
  }
}

export default telegramAPI
