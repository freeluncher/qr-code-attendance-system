import { api } from './index'

export const getBotInfo = () => {
  return api.get('/telegram/bot-info')
}

export const setWebhook = (data) => {
  return api.post('/telegram/webhook', data)
}

export const getTelegramUsers = () => {
  return api.get('/telegram/users')
}

export const sendTestNotification = (data) => {
  return api.post('/telegram/test-notification', data)
}

export const sendBroadcast = (data) => {
  return api.post('/telegram/broadcast', data)
}

export const sendDailyReport = (data) => {
  return api.post('/telegram/daily-report', data)
}

export const toggleNotifications = (userId) => {
  return api.put(`/telegram/users/${userId}/notifications`)
}
