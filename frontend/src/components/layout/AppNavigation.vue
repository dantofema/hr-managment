<template>
  <nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo/Brand -->
        <div class="flex items-center">
          <h1 class="text-xl font-semibold text-gray-900">HR System</h1>
        </div>

        <!-- Navigation Links (for authenticated users) -->
        <div v-if="isAuthenticated" class="hidden md:flex items-center space-x-4">
          <button
            :class="[
              'px-3 py-2 rounded-md text-sm font-medium transition-colors',
              currentView === 'home'
                ? 'bg-blue-100 text-blue-700'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
            @click="$emit('navigate', 'home')"
          >
            Inicio
          </button>
          <button
            :class="[
              'px-3 py-2 rounded-md text-sm font-medium transition-colors',
              currentView === 'employees'
                ? 'bg-blue-100 text-blue-700'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
            @click="$emit('navigate', 'employees')"
          >
            Empleados
          </button>
        </div>

        <!-- User Menu / Auth Actions -->
        <div class="flex items-center space-x-4">
          <!-- Loading state -->
          <div v-if="isLoading" class="flex items-center">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
          </div>

          <!-- Authenticated User Menu -->
          <div v-else-if="isAuthenticated" class="relative">
            <UserMenu 
              :user="user" 
              @logout="handleLogout"
              @profile="handleProfile"
            />
          </div>

          <!-- Login Button (for non-authenticated users) -->
          <div v-else>
            <button
              @click="$emit('navigate', 'login')"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Iniciar Sesión
            </button>
          </div>

          <!-- Mobile menu button -->
          <div class="md:hidden">
            <button
              @click="toggleMobileMenu"
              class="text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700"
              aria-label="Toggle menu"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path v-if="!showMobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Menu -->
      <div v-if="showMobileMenu" class="md:hidden border-t border-gray-200 py-2">
        <!-- Mobile Navigation Links (for authenticated users) -->
        <div v-if="isAuthenticated" class="space-y-1">
          <button
            :class="[
              'block w-full text-left px-3 py-2 rounded-md text-base font-medium transition-colors',
              currentView === 'home'
                ? 'bg-blue-100 text-blue-700'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
            @click="handleMobileNavigation('home')"
          >
            Inicio
          </button>
          <button
            :class="[
              'block w-full text-left px-3 py-2 rounded-md text-base font-medium transition-colors',
              currentView === 'employees'
                ? 'bg-blue-100 text-blue-700'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
            @click="handleMobileNavigation('employees')"
          >
            Empleados
          </button>
          
          <!-- Mobile User Info -->
          <div class="border-t border-gray-200 pt-2 mt-2">
            <div class="px-3 py-2">
              <div class="text-sm font-medium text-gray-900">{{ user?.name || user?.email }}</div>
              <div class="text-sm text-gray-500">{{ user?.email }}</div>
            </div>
            <button
              @click="handleProfile"
              class="block w-full text-left px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100"
            >
              Perfil
            </button>
            <button
              @click="handleLogout"
              class="block w-full text-left px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100"
            >
              Cerrar Sesión
            </button>
          </div>
        </div>

        <!-- Mobile Login Button (for non-authenticated users) -->
        <div v-else class="px-3 py-2">
          <button
            @click="handleMobileNavigation('login')"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
          >
            Iniciar Sesión
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { ref } from 'vue'
import { useAuth } from '@/composables/useAuth'
import UserMenu from '@/components/auth/UserMenu.vue'

export default {
  name: 'AppNavigation',
  components: {
    UserMenu
  },
  props: {
    currentView: {
      type: String,
      required: true
    }
  },
  emits: ['navigate'],
  setup(props, { emit }) {
    const { isAuthenticated, user, logout, isLoading } = useAuth()
    const showMobileMenu = ref(false)

    const toggleMobileMenu = () => {
      showMobileMenu.value = !showMobileMenu.value
    }

    const handleMobileNavigation = (view) => {
      showMobileMenu.value = false
      emit('navigate', view)
    }

    const handleLogout = async () => {
      try {
        await logout()
        showMobileMenu.value = false
        emit('navigate', 'home')
      } catch (error) {
        console.error('Error during logout:', error)
      }
    }

    const handleProfile = () => {
      showMobileMenu.value = false
      emit('navigate', 'profile')
    }

    return {
      isAuthenticated,
      user,
      isLoading,
      showMobileMenu,
      toggleMobileMenu,
      handleMobileNavigation,
      handleLogout,
      handleProfile
    }
  }
}
</script>