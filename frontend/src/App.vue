<template>
  <div class="vue-app min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <AppNavigation :current-view="currentView" @navigate="handleNavigation" />

    <!-- Main Content -->
    <main class="flex-1">
      <!-- Home View -->
      <Home v-if="currentView === 'home'" @navigate="handleNavigation" />

      <!-- Employees View (Protected) -->
      <div v-else-if="currentView === 'employees' && isAuthenticated">
        <EmployeesList/>
      </div>

      <!-- Login View -->
      <div v-else-if="currentView === 'login'">
        <LoginView @login-success="handleLoginSuccess" />
      </div>

      <!-- Profile View (Protected) -->
      <div v-else-if="currentView === 'profile' && isAuthenticated">
        <UserProfile />
      </div>

      <!-- Settings View (Protected) -->
      <div v-else-if="currentView === 'settings' && isAuthenticated">
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-8">Configuración</h1>
          <div class="bg-white shadow rounded-lg p-6">
            <p class="text-gray-600">Página de configuración en desarrollo...</p>
          </div>
        </div>
      </div>

      <!-- Unauthorized Access -->
      <div v-else-if="!isAuthenticated && ['employees', 'profile', 'settings'].includes(currentView)">
        <div class="max-w-md mx-auto mt-16 p-6 bg-white rounded-lg shadow-md">
          <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Acceso Restringido</h2>
            <p class="mt-2 text-sm text-gray-600">
              Necesitas iniciar sesión para acceder a esta página.
            </p>
            <button
              @click="handleNavigation('login')"
              class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Iniciar Sesión
            </button>
          </div>
        </div>
      </div>

      <!-- 404 Not Found -->
      <div v-else>
        <div class="max-w-md mx-auto mt-16 p-6 bg-white rounded-lg shadow-md">
          <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.5-.9-6.1-2.4" />
            </svg>
            <h2 class="mt-4 text-lg font-medium text-gray-900">Página No Encontrada</h2>
            <p class="mt-2 text-sm text-gray-600">
              La página que buscas no existe.
            </p>
            <button
              @click="handleNavigation('home')"
              class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
            >
              Ir al Inicio
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <AppFooter />

    <!-- Loading Overlay -->
    <div
      v-if="isLoading"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-700">Cargando...</span>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useAuth } from '@/composables/useAuth'
import Home from './components/Home.vue'
import EmployeesList from './components/EmployeesList.vue'
import LoginView from './views/LoginView.vue'
import UserProfile from './components/auth/UserProfile.vue'
import AppNavigation from './components/layout/AppNavigation.vue'
import AppFooter from './components/layout/AppFooter.vue'

export default {
  name: 'App',
  components: {
    Home,
    EmployeesList,
    LoginView,
    UserProfile,
    AppNavigation,
    AppFooter
  },
  setup() {
    const { isAuthenticated, isLoading } = useAuth()
    
    // Current view state - simple routing alternative
    const currentView = ref('home')

    const handleNavigation = (view) => {
      // Validate navigation based on authentication state
      if (!isAuthenticated.value && ['employees', 'profile', 'settings'].includes(view)) {
        currentView.value = 'login'
        return
      }
      
      currentView.value = view
    }

    const handleLoginSuccess = () => {
      // Redirect to employees page after successful login
      currentView.value = 'employees'
    }

    // Watch for authentication changes
    watch(isAuthenticated, (newValue) => {
      if (!newValue && ['employees', 'profile', 'settings'].includes(currentView.value)) {
        // Redirect to home if user logs out while on protected page
        currentView.value = 'home'
      }
    })

    return {
      currentView,
      isAuthenticated,
      isLoading,
      handleNavigation,
      handleLoginSuccess
    }
  }
}
</script>

<style>
/* Global styles */
.vue-app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Smooth transitions for view changes */
main > div {
  animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loading overlay styles */
.fixed.inset-0 {
  backdrop-filter: blur(2px);
}
</style>