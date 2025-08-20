import { defineStore } from 'pinia'
import { authAPI } from '../services/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    isLoading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isSatpam: (state) => state.user?.role === 'satpam'
  },

  actions: {
    async login(credentials) {
      this.isLoading = true
      this.error = null

      try {
        const response = await authAPI.login(credentials)

        // Store token and user data
        this.token = response.token
        this.user = response.user

        localStorage.setItem('auth_token', response.token)
        localStorage.setItem('user', JSON.stringify(response.user))

        return response
      } catch (error) {
        this.error = error.message || 'Login failed'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async register(userData) {
      this.isLoading = true
      this.error = null

      try {
        const response = await authAPI.register(userData)
        return response
      } catch (error) {
        this.error = error.message || 'Registration failed'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    async logout() {
      try {
        if (this.token) {
          await authAPI.logout()
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        // Clear local storage and state
        this.user = null
        this.token = null
        this.error = null

        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      }
    },

    async fetchUser() {
      if (!this.token) return

      try {
        const user = await authAPI.me()
        this.user = user
        localStorage.setItem('user', JSON.stringify(user))
      } catch {
        // If user fetch fails, logout
        this.logout()
      }
    },

    clearError() {
      this.error = null
    }
  }
})
