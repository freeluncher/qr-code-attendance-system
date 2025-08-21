<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Telegram Notifications</h1>
          <p class="text-gray-600">Kelola bot Telegram dan notifikasi otomatis</p>
        </div>
        <div class="flex items-center space-x-2">
          <div v-if="botInfo" class="text-sm">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
              Bot Active: {{ botInfo.result?.first_name }}
            </span>
          </div>
          <button
            @click="loadBotInfo"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
          >
            Refresh Status
          </button>
        </div>
      </div>
    </div>

    <!-- Bot Configuration -->
    <div class="bg-white rounded-lg shadow-sm">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Bot Configuration</h3>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Bot Info -->
          <div>
            <h4 class="text-sm font-medium text-gray-900 mb-3">Bot Information</h4>
            <div v-if="botInfo" class="space-y-2 text-sm">
              <div><strong>Name:</strong> {{ botInfo.result?.first_name }}</div>
              <div><strong>Username:</strong> @{{ botInfo.result?.username }}</div>
              <div><strong>Can Read Messages:</strong> {{ botInfo.result?.can_read_all_group_messages ? 'Yes' : 'No' }}</div>
            </div>
            <div v-else class="text-gray-500 text-sm">
              Bot information not loaded
            </div>
          </div>

          <!-- Webhook Setup -->
          <div>
            <h4 class="text-sm font-medium text-gray-900 mb-3">Webhook Configuration</h4>
            <div class="space-y-3">
              <input
                v-model="webhookUrl"
                type="url"
                placeholder="https://yourdomain.com/api/telegram/webhook"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm"
              />
              <button
                @click="setWebhook"
                :disabled="!webhookUrl || settingWebhook"
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="settingWebhook" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                {{ settingWebhook ? 'Setting...' : 'Set Webhook' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Registered Users -->
    <div class="bg-white rounded-lg shadow-sm">
      <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">Registered Users</h3>
        <button
          @click="loadTelegramUsers"
          class="text-sm text-purple-600 hover:text-purple-700 font-medium"
        >
          Refresh
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telegram</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notifications</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loadingUsers">
              <td colspan="6" class="px-6 py-4 text-center">
                <div class="flex justify-center">
                  <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                </div>
              </td>
            </tr>
            <tr v-for="user in telegramUsers" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ user.username }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">@{{ user.telegram_username }}</div>
                <div class="text-xs text-gray-500">{{ user.telegram_chat_id }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'">
                  {{ user.role }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <button
                  @click="toggleNotifications(user)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer"
                  :class="user.telegram_notifications_enabled
                    ? 'bg-green-100 text-green-800 hover:bg-green-200'
                    : 'bg-red-100 text-red-800 hover:bg-red-200'"
                >
                  {{ user.telegram_notifications_enabled ? 'Enabled' : 'Disabled' }}
                </button>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="openTestNotification(user)"
                  class="text-purple-600 hover:text-purple-700 mr-3"
                >
                  Test
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Broadcast & Reports -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Broadcast Message -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Broadcast Message</h3>
        </div>
        <div class="p-6">
          <form @submit.prevent="sendBroadcast">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Target</label>
                <select
                  v-model="broadcast.target"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                >
                  <option value="all">All Users</option>
                  <option value="admins">Admin Only</option>
                  <option value="users">Satpam Only</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea
                  v-model="broadcast.message"
                  rows="4"
                  required
                  placeholder="Enter broadcast message..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                ></textarea>
              </div>
              <button
                type="submit"
                :disabled="sendingBroadcast || !broadcast.message"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="sendingBroadcast" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                {{ sendingBroadcast ? 'Sending...' : 'Send Broadcast' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Daily Report -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Daily Report</h3>
        </div>
        <div class="p-6">
          <form @submit.prevent="sendDailyReport">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <input
                  v-model="report.date"
                  type="date"
                  :max="today"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <select
                  v-model="report.location_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
                >
                  <option value="">All Locations</option>
                  <option v-for="location in locations" :key="location.id" :value="location.id">
                    {{ location.name }}
                  </option>
                </select>
              </div>
              <button
                type="submit"
                :disabled="sendingReport"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="sendingReport" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                {{ sendingReport ? 'Sending...' : 'Send Daily Report' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Test Notification Modal -->
    <div v-if="showTestModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Send Test Notification</h3>
          <button @click="closeTestModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="sendTestNotification">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
              <div class="text-sm text-gray-900">{{ testUser?.name }} (@{{ testUser?.telegram_username }})</div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
              <textarea
                v-model="testMessage"
                rows="3"
                required
                placeholder="Enter test message..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500"
              ></textarea>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button
              type="button"
              @click="closeTestModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="sendingTest || !testMessage"
              class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-50"
            >
              <span v-if="sendingTest" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ sendingTest ? 'Sending...' : 'Send Test' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import * as telegramAPI from '@/api/telegram'
import * as locationAPI from '@/api/locations'

// Reactive data
const botInfo = ref(null)
const webhookUrl = ref('')
const telegramUsers = ref([])
const locations = ref([])
const loadingUsers = ref(false)
const settingWebhook = ref(false)
const sendingBroadcast = ref(false)
const sendingReport = ref(false)
const sendingTest = ref(false)

// Modal states
const showTestModal = ref(false)
const testUser = ref(null)
const testMessage = ref('')

// Form data
const broadcast = ref({
  target: 'all',
  message: ''
})

const report = ref({
  date: new Date().toISOString().split('T')[0],
  location_id: ''
})

// Computed
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})

// Methods
const loadBotInfo = async () => {
  try {
    const response = await telegramAPI.getBotInfo()
    botInfo.value = response.data
  } catch (error) {
    console.error('Error loading bot info:', error)
    alert('Failed to load bot information')
  }
}

const setWebhook = async () => {
  if (!webhookUrl.value) return

  settingWebhook.value = true
  try {
    await telegramAPI.setWebhook({ webhook_url: webhookUrl.value })
    alert('Webhook set successfully!')
  } catch (error) {
    console.error('Error setting webhook:', error)
    alert('Failed to set webhook')
  } finally {
    settingWebhook.value = false
  }
}

const loadTelegramUsers = async () => {
  loadingUsers.value = true
  try {
    const response = await telegramAPI.getTelegramUsers()
    telegramUsers.value = response.data
  } catch (error) {
    console.error('Error loading telegram users:', error)
  } finally {
    loadingUsers.value = false
  }
}

const loadLocations = async () => {
  try {
    const response = await locationAPI.getLocations()
    locations.value = response.data.data || response.data
  } catch (error) {
    console.error('Error loading locations:', error)
  }
}

const toggleNotifications = async (user) => {
  try {
    const response = await telegramAPI.toggleNotifications(user.id)
    user.telegram_notifications_enabled = response.data.telegram_notifications_enabled
  } catch (error) {
    console.error('Error toggling notifications:', error)
    alert('Failed to toggle notifications')
  }
}

const openTestNotification = (user) => {
  testUser.value = user
  testMessage.value = 'This is a test message from QR Code Attendance System.'
  showTestModal.value = true
}

const closeTestModal = () => {
  showTestModal.value = false
  testUser.value = null
  testMessage.value = ''
}

const sendTestNotification = async () => {
  if (!testUser.value || !testMessage.value) return

  sendingTest.value = true
  try {
    await telegramAPI.sendTestNotification({
      user_id: testUser.value.id,
      message: testMessage.value
    })
    alert('Test notification sent successfully!')
    closeTestModal()
  } catch (error) {
    console.error('Error sending test notification:', error)
    alert('Failed to send test notification')
  } finally {
    sendingTest.value = false
  }
}

const sendBroadcast = async () => {
  if (!broadcast.value.message) return

  sendingBroadcast.value = true
  try {
    const response = await telegramAPI.sendBroadcast(broadcast.value)
    alert(`Broadcast sent to ${response.data.success_count} users`)
    broadcast.value.message = ''
  } catch (error) {
    console.error('Error sending broadcast:', error)
    alert('Failed to send broadcast')
  } finally {
    sendingBroadcast.value = false
  }
}

const sendDailyReport = async () => {
  sendingReport.value = true
  try {
    await telegramAPI.sendDailyReport(report.value)
    alert('Daily report sent to admin users!')
  } catch (error) {
    console.error('Error sending daily report:', error)
    alert('Failed to send daily report')
  } finally {
    sendingReport.value = false
  }
}

// Lifecycle
onMounted(() => {
  loadBotInfo()
  loadTelegramUsers()
  loadLocations()
})
</script>
