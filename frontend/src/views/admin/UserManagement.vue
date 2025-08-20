<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <!-- Logo and Title -->
          <div class="flex items-center">
            <div class="flex items-center">
              <router-link to="/admin/dashboard" class="flex items-center">
                <AcademicCapIcon class="h-8 w-8 text-indigo-600 mr-3" />
                <div class="hidden sm:block">
                  <h1 class="text-xl font-semibold text-gray-900">Kelola Satpam</h1>
                  <p class="text-sm text-gray-500">Manajemen Pengguna</p>
                </div>
              </router-link>
            </div>
          </div>

          <!-- User Menu -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <router-link to="/admin/dashboard" class="text-gray-400 hover:text-gray-600">
              <ArrowLeftIcon class="h-5 w-5" />
            </router-link>
            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-indigo-600 flex items-center justify-center">
              <UserIcon class="h-5 w-5 sm:h-6 sm:w-6 text-white" />
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
      <!-- Header Actions -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Kelola Satpam</h2>
          <p class="text-gray-600 mt-1">Kelola data satpam dan akses sistem</p>
        </div>
        <button
          @click="openCreateModal"
          class="mt-4 sm:mt-0 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors"
        >
          <PlusIcon class="h-5 w-5" />
          <span>Tambah Satpam</span>
        </button>
      </div>

      <!-- Search and Filter -->
      <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
        <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
          <div class="flex-1">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama atau email satpam..."
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          <div class="flex space-x-2">
            <select
              v-model="statusFilter"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Semua Status</option>
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
            <button
              @click="loadUsers"
              class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              <MagnifyingGlassIcon class="h-5 w-5" />
            </button>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Users Table -->
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Satpam
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Email
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Bergabung
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aksi
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                      <UserIcon class="h-5 w-5 text-indigo-600" />
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                      <div class="text-sm text-gray-500">{{ user.role }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ user.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex px-2 text-xs font-semibold rounded-full',
                      user.status === 'aktif'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ user.status === 'aktif' ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(user.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex justify-end space-x-2">
                    <button
                      @click="openEditModal(user)"
                      class="text-indigo-600 hover:text-indigo-900 p-1"
                    >
                      <PencilIcon class="h-4 w-4" />
                    </button>
                    <button
                      @click="confirmDelete(user)"
                      class="text-red-600 hover:text-red-900 p-1"
                    >
                      <TrashIcon class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Empty State -->
          <div v-if="filteredUsers.length === 0" class="text-center py-12">
            <UsersIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-500">Tidak ada satpam ditemukan</p>
            <p class="text-gray-400 text-sm">Tambah satpam baru untuk memulai</p>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.total > pagination.per_page" class="mt-6 flex justify-center">
        <nav class="flex space-x-2">
          <button
            v-for="page in paginationPages"
            :key="page"
            @click="changePage(page)"
            :class="[
              'px-3 py-2 rounded-lg text-sm',
              page === pagination.current_page
                ? 'bg-indigo-600 text-white'
                : 'bg-white text-gray-500 hover:text-gray-700 border border-gray-300'
            ]"
          >
            {{ page }}
          </button>
        </nav>
      </div>
    </main>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">
            {{ editingUser ? 'Edit Satpam' : 'Tambah Satpam Baru' }}
          </h3>
          <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
            <XMarkIcon class="h-6 w-6" />
          </button>
        </div>

        <form @submit.prevent="saveUser" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
            <input
              v-model="formData.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Masukkan nama lengkap"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input
              v-model="formData.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Masukkan email"
            />
          </div>

          <div v-if="!editingUser">
            <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
            <input
              v-model="formData.password"
              type="password"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
              placeholder="Masukkan password"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="formData.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="aktif">Aktif</option>
              <option value="nonaktif">Nonaktif</option>
            </select>
          </div>

          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Menyimpan...' : (editingUser ? 'Update' : 'Simpan') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg max-w-md w-full p-6">
        <div class="flex items-center mb-4">
          <ExclamationTriangleIcon class="h-8 w-8 text-red-500 mr-3" />
          <h3 class="text-lg font-medium text-gray-900">Hapus Satpam</h3>
        </div>

        <p class="text-gray-600 mb-6">
          Apakah Anda yakin ingin menghapus satpam <strong>{{ userToDelete?.name }}</strong>?
          Tindakan ini tidak dapat dibatalkan.
        </p>

        <div class="flex justify-end space-x-3">
          <button
            @click="showDeleteModal = false"
            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
          >
            Batal
          </button>
          <button
            @click="deleteUser"
            :disabled="deleting"
            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors disabled:opacity-50"
          >
            {{ deleting ? 'Menghapus...' : 'Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import api from '../../services/api'
import {
  AcademicCapIcon,
  UserIcon,
  UsersIcon,
  PlusIcon,
  MagnifyingGlassIcon,
  PencilIcon,
  TrashIcon,
  XMarkIcon,
  ExclamationTriangleIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

// Reactive state
const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)
const showModal = ref(false)
const showDeleteModal = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')

// Users data
const users = ref([])
const editingUser = ref(null)
const userToDelete = ref(null)

// Pagination
const pagination = ref({
  current_page: 1,
  per_page: 10,
  total: 0,
  last_page: 1
})

// Form data
const formData = ref({
  name: '',
  email: '',
  password: '',
  role: 'satpam',
  status: 'aktif'
})

// Computed
const filteredUsers = computed(() => {
  let filtered = users.value

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    )
  }

  if (statusFilter.value) {
    filtered = filtered.filter(user => user.status === statusFilter.value)
  }

  return filtered
})

const paginationPages = computed(() => {
  if (!pagination.value || !pagination.value.last_page) {
    return []
  }

  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page

  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }

  return pages
})

// Methods
const loadUsers = async (page = 1) => {
  loading.value = true
  try {
    const response = await api.get(`/users?page=${page}&role=satpam`)
    users.value = response.data
    pagination.value = response.pagination || {
      current_page: 1,
      per_page: 10,
      total: response.data.length,
      last_page: 1
    }
  } catch (error) {
    console.error('Error loading users:', error)
    // Fallback data for development
    users.value = [
      {
        id: 1,
        name: 'Pak Ahmad',
        email: 'ahmad@example.com',
        role: 'satpam',
        status: 'aktif',
        created_at: '2025-01-15T00:00:00Z'
      },
      {
        id: 2,
        name: 'Pak Budi',
        email: 'budi@example.com',
        role: 'satpam',
        status: 'aktif',
        created_at: '2025-01-10T00:00:00Z'
      },
      {
        id: 3,
        name: 'Pak Charlie',
        email: 'charlie@example.com',
        role: 'satpam',
        status: 'nonaktif',
        created_at: '2025-01-05T00:00:00Z'
      }
    ]
    // Set fallback pagination
    pagination.value = {
      current_page: 1,
      per_page: 10,
      total: 3,
      last_page: 1
    }
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingUser.value = null
  formData.value = {
    name: '',
    email: '',
    password: '',
    role: 'satpam',
    status: 'aktif'
  }
  showModal.value = true
}

const openEditModal = (user) => {
  editingUser.value = user
  formData.value = {
    name: user.name,
    email: user.email,
    password: '',
    role: user.role,
    status: user.status
  }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingUser.value = null
  formData.value = {
    name: '',
    email: '',
    password: '',
    role: 'satpam',
    status: 'aktif'
  }
}

const saveUser = async () => {
  saving.value = true
  try {
    if (editingUser.value) {
      // Update user
      await api.put(`/users/${editingUser.value.id}`, formData.value)
    } else {
      // Create user
      await api.post('/users', formData.value)
    }

    closeModal()
    await loadUsers(pagination.value.current_page)
  } catch (error) {
    console.error('Error saving user:', error)
    alert('Gagal menyimpan data satpam')
  } finally {
    saving.value = false
  }
}

const confirmDelete = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const deleteUser = async () => {
  deleting.value = true
  try {
    await api.delete(`/users/${userToDelete.value.id}`)
    showDeleteModal.value = false
    userToDelete.value = null
    await loadUsers(pagination.value.current_page)
  } catch (error) {
    console.error('Error deleting user:', error)
    alert('Gagal menghapus satpam')
  } finally {
    deleting.value = false
  }
}

const changePage = (page) => {
  loadUsers(page)
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
