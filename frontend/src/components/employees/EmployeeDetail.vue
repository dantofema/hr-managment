<template>
  <div class="employee-detail" role="main" aria-labelledby="employee-name">
    <!-- Loading State -->
    <div v-if="!employee" class="flex justify-center items-center py-12" role="status" aria-live="polite">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600" aria-hidden="true"></div>
      <span class="ml-3 text-gray-600">Cargando información del empleado...</span>
    </div>

    <!-- Employee Detail Content -->
    <div v-else>
      <!-- Header con foto y nombre -->
      <header class="flex flex-col sm:flex-row items-center sm:items-start mb-6 text-center sm:text-left">
        <div 
          class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center mb-4 sm:mb-0 sm:mr-6 shadow-lg"
          role="img"
          :aria-label="`Avatar de ${fullName}`"
        >
          <span class="text-2xl sm:text-3xl font-bold text-white" aria-hidden="true">
            {{ initials }}
          </span>
        </div>
        <div class="flex-1">
          <h1 id="employee-name" class="text-2xl sm:text-3xl font-bold text-gray-900 mb-1">{{ fullName }}</h1>
          <p class="text-lg text-gray-600 mb-2" role="text">{{ employee.position }}</p>
          <div class="flex justify-center sm:justify-start items-center">
            <span 
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
              role="status"
              aria-label="Estado del empleado: Activo"
            >
              <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
              Activo
            </span>
          </div>
        </div>
      </header>

      <!-- Grid de información -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mb-6">
        <!-- Información de contacto -->
        <div class="bg-white border border-gray-200 p-4 sm:p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Información de Contacto</h3>
          </div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Email</label>
              <a 
                :href="`mailto:${employee.email}`" 
                class="text-blue-600 hover:text-blue-800 underline font-medium break-all"
                :aria-label="`Enviar email a ${employee.email}`"
              >
                {{ employee.email }}
              </a>
            </div>
          </div>
        </div>

        <!-- Información laboral -->
        <div class="bg-white border border-gray-200 p-4 sm:p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-200">
          <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0V6a2 2 0 00-2 2H8a2 2 0 00-2-2V6m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2" />
              </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Información Laboral</h3>
          </div>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Salario</label>
              <p class="text-lg font-semibold text-gray-900">
                {{ formatCurrency(employee.salaryAmount, employee.salaryCurrency) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500 mb-1">Fecha de Contratación</label>
              <p class="text-gray-900 font-medium">{{ formatDate(employee.hiredAt) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Estadísticas y cálculos -->
      <section aria-labelledby="statistics-heading" class="mb-6">
        <h2 id="statistics-heading" class="sr-only">Estadísticas del empleado</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 p-4 sm:p-6 rounded-xl text-center hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2"
            role="region"
            aria-labelledby="years-service-label"
            tabindex="0"
          >
            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3" aria-hidden="true">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-blue-600 mb-1" aria-describedby="years-service-label">{{ yearsOfService }}</div>
            <div id="years-service-label" class="text-sm font-medium text-blue-800">Años de Servicio</div>
          </div>
          
          <div 
            class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 p-4 sm:p-6 rounded-xl text-center hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1 focus-within:ring-2 focus-within:ring-green-500 focus-within:ring-offset-2"
            role="region"
            aria-labelledby="vacation-days-label"
            tabindex="0"
          >
            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3" aria-hidden="true">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-green-600 mb-1" aria-describedby="vacation-days-label">{{ vacationDays }}</div>
            <div id="vacation-days-label" class="text-sm font-medium text-green-800">Días de Vacación</div>
          </div>
          
          <div 
            class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 p-4 sm:p-6 rounded-xl text-center hover:shadow-lg transition-all duration-200 transform hover:-translate-y-1 sm:col-span-2 lg:col-span-1 focus-within:ring-2 focus-within:ring-purple-500 focus-within:ring-offset-2"
            role="region"
            aria-labelledby="days-worked-label"
            tabindex="0"
          >
            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-3" aria-hidden="true">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="text-3xl font-bold text-purple-600 mb-1" aria-describedby="days-worked-label">{{ daysWorked }}</div>
            <div id="days-worked-label" class="text-sm font-medium text-purple-800">Días Trabajados</div>
          </div>
        </div>
      </section>

      <!-- Timeline o historial -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-3">Timeline</h3>
        <div class="space-y-3">
          <div class="flex items-center">
            <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
            <div>
              <p class="font-medium">Contratado</p>
              <p class="text-sm text-gray-600">{{ formatDate(employee.hiredAt) }}</p>
            </div>
          </div>
          <div class="flex items-center" v-if="nextAnniversary">
            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
            <div>
              <p class="font-medium">Próximo Aniversario</p>
              <p class="text-sm text-gray-600">{{ formatDate(nextAnniversary) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Acciones -->
      <div class="flex justify-end space-x-3 pt-4 border-t">
        <button 
          @click="$emit('edit', employee)"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
          :disabled="loading"
          :class="{ 'opacity-50 cursor-not-allowed': loading }"
        >
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Editar
        </button>
        
        <button 
          @click="confirmDelete"
          class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
          :disabled="loading"
          :class="{ 'opacity-50 cursor-not-allowed': loading }"
        >
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Eliminar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

/**
 * EmployeeDetail - Componente para mostrar información detallada de un empleado
 * 
 * Características principales:
 * - Muestra información personal y laboral completa
 * - Cálculos automáticos (años de servicio, días de vacación, etc.)
 * - Timeline con hitos importantes
 * - Acciones para editar y eliminar
 * - Responsive design
 * - Accesibilidad completa
 * 
 * @example
 * <EmployeeDetail 
 *   :employee="selectedEmployee"
 *   :loading="isLoading"
 *   @edit="handleEdit"
 *   @delete="handleDelete"
 * />
 */
export default {
  name: 'EmployeeDetail',
  props: {
    /**
     * Objeto empleado con toda la información
     */
    employee: {
      type: Object,
      required: true
    },
    /**
     * Estado de carga para operaciones
     */
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['edit', 'delete', 'close'],
  setup(props, { emit }) {
    // Computed properties para información básica
    const fullName = computed(() => {
      if (!props.employee) return 'Empleado no encontrado'
      const firstName = props.employee.firstName?.trim() || ''
      const lastName = props.employee.lastName?.trim() || ''
      const name = `${firstName} ${lastName}`.trim()
      return name || 'Nombre no disponible'
    })

    const initials = computed(() => {
      if (!props.employee) return '??'
      const first = props.employee.firstName?.charAt(0)?.toUpperCase() || '?'
      const last = props.employee.lastName?.charAt(0)?.toUpperCase() || '?'
      return first + last
    })

    // Validación de fecha
    const isValidDate = (dateString) => {
      if (!dateString) return false
      const date = new Date(dateString)
      return date instanceof Date && !isNaN(date) && date <= new Date()
    }

    // Cálculos automáticos con validación
    const yearsOfService = computed(() => {
      if (!props.employee?.hiredAt || !isValidDate(props.employee.hiredAt)) return 0
      try {
        const hired = new Date(props.employee.hiredAt)
        const now = new Date()
        const years = Math.floor((now - hired) / (365.25 * 24 * 60 * 60 * 1000))
        return Math.max(0, years) // Asegurar que no sea negativo
      } catch (error) {
        console.warn('Error calculating years of service:', error)
        return 0
      }
    })

    const vacationDays = computed(() => {
      if (!props.employee?.hiredAt || !isValidDate(props.employee.hiredAt)) return 0
      try {
        const hired = new Date(props.employee.hiredAt)
        const now = new Date()
        const monthsWorked = Math.floor((now - hired) / (30.44 * 24 * 60 * 60 * 1000))
        const days = Math.floor(Math.max(0, monthsWorked) * 2.5)
        return Math.min(days, 365) // Máximo 365 días de vacación
      } catch (error) {
        console.warn('Error calculating vacation days:', error)
        return 0
      }
    })

    const daysWorked = computed(() => {
      if (!props.employee?.hiredAt || !isValidDate(props.employee.hiredAt)) return 0
      try {
        const hired = new Date(props.employee.hiredAt)
        const now = new Date()
        const days = Math.floor((now - hired) / (24 * 60 * 60 * 1000))
        return Math.max(0, days)
      } catch (error) {
        console.warn('Error calculating days worked:', error)
        return 0
      }
    })

    const nextAnniversary = computed(() => {
      if (!props.employee?.hiredAt || !isValidDate(props.employee.hiredAt)) return null
      try {
        const hired = new Date(props.employee.hiredAt)
        const currentYear = new Date().getFullYear()
        const anniversary = new Date(currentYear, hired.getMonth(), hired.getDate())
        
        if (anniversary < new Date()) {
          anniversary.setFullYear(currentYear + 1)
        }
        
        return anniversary
      } catch (error) {
        console.warn('Error calculating next anniversary:', error)
        return null
      }
    })

    // Funciones utilitarias con manejo de errores
    const formatCurrency = (amount, currency = 'EUR') => {
      if (!amount || isNaN(amount)) return 'No especificado'
      try {
        // Validar que la moneda sea válida
        const validCurrencies = ['EUR', 'USD', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY']
        const currencyToUse = validCurrencies.includes(currency) ? currency : 'EUR'
        
        return new Intl.NumberFormat('es-ES', {
          style: 'currency',
          currency: currencyToUse,
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        }).format(Number(amount))
      } catch (error) {
        console.warn('Error formatting currency:', error)
        return `${amount} ${currency || 'EUR'}`
      }
    }

    const formatDate = (date) => {
      if (!date) return 'No especificado'
      try {
        const dateObj = new Date(date)
        if (isNaN(dateObj.getTime())) {
          return 'Fecha inválida'
        }
        
        // Verificar que la fecha no sea muy antigua o futura
        const currentYear = new Date().getFullYear()
        const dateYear = dateObj.getFullYear()
        if (dateYear < 1900 || dateYear > currentYear + 10) {
          return 'Fecha fuera de rango'
        }
        
        return new Intl.DateTimeFormat('es-ES', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        }).format(dateObj)
      } catch (error) {
        console.warn('Error formatting date:', error)
        return 'Error en fecha'
      }
    }

    // Validación de datos del empleado
    const hasValidEmployeeData = computed(() => {
      return props.employee && 
             props.employee.firstName && 
             props.employee.lastName && 
             props.employee.email &&
             props.employee.position
    })

    // Confirmación de eliminación
    const confirmDelete = () => {
      if (confirm(`¿Estás seguro de que quieres eliminar a ${fullName.value}?`)) {
        emit('delete', props.employee.id)
      }
    }

    return {
      fullName,
      initials,
      yearsOfService,
      vacationDays,
      daysWorked,
      nextAnniversary,
      formatCurrency,
      formatDate,
      confirmDelete
    }
  }
}
</script>

<style scoped>
/* Estilos adicionales si son necesarios */
.employee-detail {
  @apply max-w-4xl mx-auto;
}

/* Animaciones para las estadísticas */
.employee-detail .bg-blue-50,
.employee-detail .bg-green-50,
.employee-detail .bg-purple-50 {
  transition: transform 0.2s ease-in-out;
}

.employee-detail .bg-blue-50:hover,
.employee-detail .bg-green-50:hover,
.employee-detail .bg-purple-50:hover {
  transform: translateY(-2px);
}
</style>