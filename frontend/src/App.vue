<template>
  <div class="vue-app min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">HR System</h1>
          </div>
          <div class="flex items-center space-x-4">
            <button 
              @click="currentView = 'home'"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                currentView === 'home' 
                  ? 'bg-blue-100 text-blue-700' 
                  : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
              ]"
            >
              Inicio
            </button>
            <button 
              @click="currentView = 'employees'"
              :class="[
                'px-3 py-2 rounded-md text-sm font-medium transition-colors',
                currentView === 'employees' 
                  ? 'bg-blue-100 text-blue-700' 
                  : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
              ]"
            >
              Empleados
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1">
      <!-- Home View -->
      <div v-if="currentView === 'home'" class="max-w-4xl mx-auto p-6 space-y-6">
        <div class="text-center">
          <h2 class="text-3xl font-bold text-gray-800 mb-2">
            Bienvenido al Sistema HR
          </h2>
          <p class="text-gray-600 mb-8">
            Sistema de gestión de recursos humanos con Vue.js y Tailwind CSS
          </p>
        </div>

        <!-- Feature Cards -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="text-blue-600 text-3xl mb-4">👥</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Empleados</h3>
            <p class="text-gray-600 mb-4">Gestiona la información de todos los empleados</p>
            <button 
              @click="currentView = 'employees'"
              class="text-blue-600 hover:text-blue-800 font-medium"
            >
              Ver empleados →
            </button>
          </div>

          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="text-green-600 text-3xl mb-4">📊</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Nóminas</h3>
            <p class="text-gray-600 mb-4">Administra las nóminas y pagos</p>
            <button class="text-gray-400 cursor-not-allowed font-medium">
              Próximamente
            </button>
          </div>

          <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
            <div class="text-purple-600 text-3xl mb-4">🏖️</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Vacaciones</h3>
            <p class="text-gray-600 mb-4">Gestiona solicitudes de vacaciones</p>
            <button class="text-gray-400 cursor-not-allowed font-medium">
              Próximamente
            </button>
          </div>
        </div>

        <!-- Demo Counter Component -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Demo Component</h3>
          <Counter/>
        </div>
      </div>

      <!-- Employees View -->
      <div v-else-if="currentView === 'employees'">
        <EmployeesList/>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-12">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500">
          HR System - Vue.js 3 + Tailwind CSS + Symfony API Platform
        </p>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref } from 'vue'
import Counter from './components/Counter.vue'
import EmployeesList from './components/EmployeesList.vue'

export default {
  name: 'App',
  components: {
    Counter,
    EmployeesList
  },
  setup() {
    // Current view state - simple routing alternative
    const currentView = ref('home')

    return {
      currentView
    }
  }
}
</script>

<style>
/* Global styles if needed */
.vue-app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>