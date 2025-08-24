
// Import Pinia untuk state management dan authAPI untuk komunikasi dengan backend
import { defineStore } from 'pinia'
import { authAPI } from '../services/api'


// Store utama untuk autentikasi user
export const useAuthStore = defineStore('auth', {
  // State: menyimpan data user, token, status loading, dan error
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('auth_token') || null,
    isLoading: false,
    error: null
  }),


  // Getters: akses cepat ke status autentikasi dan role user
  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isSatpam: (state) => state.user?.role === 'satpam',
    dashboardRoute: (state) => { // Route dashboard sesuai role
      if (state.user?.role === 'admin') return '/admin/dashboard'
      if (state.user?.role === 'satpam') return '/satpam/dashboard'
      return '/login'
    }
  },


  // Actions: fungsi-fungsi utama autentikasi
  actions: {
    /**
     * Login user dengan kredensial
     * - Mengirim request ke backend
     * - Menyimpan token dan user ke state & localStorage
     */
    async login(credentials) {
      this.isLoading = true
      this.error = null

      try {
        const response = await authAPI.login(credentials)

        // Simpan token dan data user ke state dan localStorage
        this.token = response.token
        this.user = response.user

        localStorage.setItem('auth_token', response.token)
        localStorage.setItem('user', JSON.stringify(response.user))

        return response
      } catch (error) {
        // Tangani error login
        this.error = error.message || 'Login failed'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Register user baru
     * - Mengirim data ke backend
     * - Tidak langsung login
     */
    async register(userData) {
      this.isLoading = true
      this.error = null

      try {
        const response = await authAPI.register(userData)
        return response
      } catch (error) {
        // Tangani error registrasi
        this.error = error.message || 'Registration failed'
        throw error
      } finally {
        this.isLoading = false
      }
    },

    /**
     * Logout user
     * - Menghapus token dan user dari state & localStorage
     * - Memanggil endpoint logout backend
     */
    async logout() {
      try {
        if (this.token) {
          await authAPI.logout()
        }
      } catch (error) {
        // Error saat logout tetap lanjut clear state
        console.error('Logout error:', error)
      } finally {
        // Bersihkan state dan localStorage
        this.user = null
        this.token = null
        this.error = null

        localStorage.removeItem('auth_token')
        localStorage.removeItem('user')
      }
    },

    /**
     * Fetch user profile dari backend
     * - Update state dan localStorage
     * - Jika gagal, otomatis logout
     */
    async fetchUser() {
      if (!this.token) return

      try {
        const user = await authAPI.me()
        this.user = user
        localStorage.setItem('user', JSON.stringify(user))
      } catch {
        // Jika gagal ambil user, lakukan logout
        this.logout()
      }
    },

    /**
     * Bersihkan pesan error autentikasi
     */
    clearError() {
      this.error = null
    }
  }
})
