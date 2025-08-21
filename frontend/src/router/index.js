import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: () => {
        // Redirect based on user role after authentication check
        const authStore = useAuthStore()
        if (authStore.user?.role === 'admin') {
          return '/admin/dashboard'
        } else if (authStore.user?.role === 'satpam') {
          return '/satpam/dashboard'
        }
        return '/login'
      }
    },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: {
        requiresGuest: true,
        title: 'Masuk - QR Attendance System'
      }
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: {
        requiresGuest: true,
        title: 'Daftar - QR Attendance System'
      }
    },
    // Admin Routes
    {
      path: '/admin',
      redirect: '/admin/dashboard',
      meta: {
        requiresAuth: true,
        requiresRole: 'admin'
      }
    },
    {
      path: '/admin/dashboard',
      name: 'admin-dashboard',
      component: () => import('../views/admin/AdminDashboard.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Dashboard Admin - QR Attendance System'
      }
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: () => import('../views/admin/UserManagement.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Kelola Satpam - QR Attendance System'
      }
    },
    {
      path: '/admin/locations',
      name: 'admin-locations',
      component: () => import('../views/admin/LocationManagement.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Kelola Lokasi - QR Attendance System'
      }
    },
    {
      path: '/admin/shifts',
      name: 'admin-shifts',
      component: () => import('../views/admin/ShiftManagement.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Kelola Shift - QR Attendance System'
      }
    },
    {
      path: '/admin/qrcodes',
      name: 'admin-qrcodes',
      component: () => import('../views/admin/QrCodeManagement.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Kelola QR Code - QR Attendance System'
      }
    },
    {
      path: '/admin/attendances',
      name: 'admin-attendances',
      component: () => import('../views/admin/AttendanceData.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Data Presensi - QR Attendance System'
      }
    },
    {
      path: '/admin/telegram',
      name: 'admin-telegram',
      component: () => import('../views/admin/TelegramNotifications.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Notifikasi Telegram - QR Attendance System'
      }
    },
    {
      path: '/admin/reports',
      name: 'admin-reports',
      component: () => import('../views/admin/ReportsView.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'admin',
        title: 'Laporan - QR Attendance System'
      }
    },
    // Satpam Routes
    {
      path: '/satpam',
      redirect: '/satpam/dashboard',
      meta: {
        requiresAuth: true,
        requiresRole: 'satpam'
      }
    },
    {
      path: '/satpam/dashboard',
      name: 'satpam-dashboard',
      component: () => import('../views/satpam/SatpamDashboard.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'satpam',
        title: 'Dashboard Satpam - QR Attendance System'
      }
    },
    {
      path: '/satpam/attendance',
      name: 'satpam-attendance',
      component: () => import('../views/satpam/SatpamAttendance.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'satpam',
        title: 'Presensi - QR Attendance System'
      }
    },
    {
      path: '/satpam/schedule',
      name: 'satpam-schedule',
      component: () => import('../views/satpam/SatpamSchedule.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'satpam',
        title: 'Jadwal Shift - QR Attendance System'
      }
    },
    {
      path: '/satpam/history',
      name: 'satpam-history',
      component: () => import('../views/satpam/SatpamHistory.vue'),
      meta: {
        requiresAuth: true,
        requiresRole: 'satpam',
        title: 'Riwayat Presensi - QR Attendance System'
      }
    },
    // Legacy dashboard route for backward compatibility
    {
      path: '/dashboard',
      redirect: () => {
        const authStore = useAuthStore()
        if (authStore.user?.role === 'admin') {
          return '/admin/dashboard'
        } else if (authStore.user?.role === 'satpam') {
          return '/satpam/dashboard'
        }
        return '/login'
      }
    },
    // 404 Not Found
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('../views/NotFound.vue'),
      meta: {
        title: 'Halaman Tidak Ditemukan - QR Attendance System'
      }
    }
  ]
})

// Navigation guards
router.beforeEach(async (to, from, next) => {
  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title
  }

  const authStore = useAuthStore()
  const isAuthenticated = !!authStore.token
  const userRole = authStore.user?.role

  // Handle guest routes (login, register)
  if (to.meta.requiresGuest && isAuthenticated) {
    // Redirect authenticated users away from login/register
    if (userRole === 'admin') {
      return next('/admin/dashboard')
    } else if (userRole === 'satpam') {
      return next('/satpam/dashboard')
    }
    return next('/dashboard')
  }

  // Handle routes that require authentication
  if (to.meta.requiresAuth && !isAuthenticated) {
    return next('/login')
  }

  // Handle role-based access
  if (to.meta.requiresRole && userRole !== to.meta.requiresRole) {
    // Redirect to appropriate dashboard based on role
    if (userRole === 'admin') {
      return next('/admin/dashboard')
    } else if (userRole === 'satpam') {
      return next('/satpam/dashboard')
    }
    return next('/login')
  }

  next()
})

export default router
