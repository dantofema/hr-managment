<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Perfil de Usuario</h1>
      <p class="mt-2 text-gray-600">Gestiona tu información personal y configuración de cuenta</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <p class="mt-1 text-sm text-red-700">{{ error }}</p>
        </div>
      </div>
    </div>

    <!-- Profile Content -->
    <div v-else-if="user" class="space-y-8">
      <!-- Profile Information Card -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Información Personal</h2>
        </div>
        <div class="px-6 py-4">
          <div class="flex items-center space-x-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
              <div class="h-20 w-20 rounded-full bg-blue-600 flex items-center justify-center">
                <span class="text-2xl font-medium text-white">
                  {{ userInitials }}
                </span>
              </div>
            </div>
            
            <!-- User Info -->
            <div class="flex-1">
              <h3 class="text-xl font-semibold text-gray-900">{{ user.name || 'Usuario' }}</h3>
              <p class="text-gray-600">{{ user.email }}</p>
              <div class="mt-2 flex flex-wrap gap-2">
                <span 
                  v-for="role in user.roles" 
                  :key="role"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                >
                  {{ formatRole(role) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Details Card -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Detalles de la Cuenta</h2>
        </div>
        <div class="px-6 py-4">
          <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-gray-500">Nombre completo</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ user.name || 'No especificado' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Correo electrónico</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ user.email }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">ID de usuario</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ user.id }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Fecha de registro</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.createdAt) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Último acceso</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ formatDate(user.lastLoginAt) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Estado</dt>
              <dd class="mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                  Activo
                </span>
              </dd>
            </div>
          </dl>
        </div>
      </div>

      <!-- Actions Card -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-medium text-gray-900">Acciones</h2>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <button
              @click="handleEditProfile"
              class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              Editar Perfil
            </button>
            
            <button
              @click="handleChangePassword"
              class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors ml-0 sm:ml-3"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Cambiar Contraseña
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- No User State -->
    <div v-else class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No se pudo cargar el perfil</h3>
      <p class="mt-1 text-sm text-gray-500">Inicia sesión para ver tu información de perfil.</p>
      <div class="mt-6">
        <button
          @click="$emit('navigate', 'login')"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Iniciar Sesión
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useAuth } from '@/composables/useAuth'

export default {
  name: 'UserProfile',
  emits: ['navigate'],
  setup(props, { emit }) {
    const { user, isLoading, error } = useAuth()

    // Computed properties
    const userInitials = computed(() => {
      if (!user.value) return '?'
      
      const name = user.value.name || user.value.email || ''
      const parts = name.split(' ')
      
      if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase()
      } else if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase()
      }
      
      return '?'
    })

    // Helper functions
    const formatRole = (role) => {
      const roleMap = {
        'ROLE_ADMIN': 'Administrador',
        'ROLE_USER': 'Usuario',
        'ROLE_HR': 'Recursos Humanos',
        'ROLE_MANAGER': 'Gerente'
      }
      return roleMap[role] || role.replace('ROLE_', '').toLowerCase()
    }

    const formatDate = (dateString) => {
      if (!dateString) return 'No disponible'
      
      try {
        const date = new Date(dateString)
        return date.toLocaleDateString('es-ES', {
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch (error) {
        return 'Fecha inválida'
      }
    }

    // Event handlers
    const handleEditProfile = () => {
      // TODO: Implement edit profile functionality
      console.log('Edit profile clicked')
      // emit('navigate', 'edit-profile')
    }

    const handleChangePassword = () => {
      // TODO: Implement change password functionality
      console.log('Change password clicked')
      // emit('navigate', 'change-password')
    }

    return {
      user,
      isLoading,
      error,
      userInitials,
      formatRole,
      formatDate,
      handleEditProfile,
      handleChangePassword
    }
  }
}
</script>