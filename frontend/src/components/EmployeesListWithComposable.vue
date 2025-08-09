<!--
  Example integration of EmployeesList.vue with useEmployees composable
  This demonstrates how to refactor the existing component to use the new composable
-->
<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Empleados (Con Composable)</h1>
      <p class="text-gray-600">Gestión de empleados usando useEmployees composable</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <span class="ml-3 text-gray-600">
        <span v-if="loadingStates.fetching">Cargando empleados...</span>
        <span v-else-if="loadingStates.creating">Creando empleado...</span>
        <span v-else-if="loadingStates.updating">Actualizando empleado...</span>
        <span v-else-if="loadingStates.deleting">Eliminando empleado...</span>
        <span v-else>Procesando...</span>
      </span>
    </div>

    <!-- Error State -->
    <div v-else-if="hasError" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <span class="text-red-400">⚠️</span>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error</h3>
          <p class="mt-1 text-sm text-red-700">{{ error }}</p>
          <div class="mt-2 space-x-2">
            <button 
              @click="refreshEmployees" 
              class="text-sm bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded"
            >
              Reintentar
            </button>
            <button 
              @click="clearError" 
              class="text-sm bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-1 rounded"
            >
              Cerrar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Employees Table -->
    <div v-else class="bg-white shadow-lg rounded-lg overflow-hidden">
      <!-- Empty State -->
      <div v-if="!hasEmployees" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">👥</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No hay empleados</h3>
        <p class="text-gray-600">No se encontraron empleados en el sistema.</p>
      </div>

      <!-- Desktop Table -->
      <div v-else class="hidden md:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nombre Completo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Email
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Posición
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Salario
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Fecha de Contratación
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="employee in employees" :key="employee.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ employee.fullName || `${employee.firstName} ${employee.lastName}` }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ employee.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ employee.position }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatCurrency(employee.salaryAmount, employee.salaryCurrency) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ formatDate(employee.hiredAt) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button 
                    @click="handleViewEmployee(employee.id)"
                    class="text-blue-600 hover:text-blue-900 transition-colors p-1 rounded hover:bg-blue-50"
                    title="Ver detalles"
                    :disabled="loadingStates.fetchingOne"
                  >
                    👁️
                  </button>
                  <button 
                    @click="handleEditEmployee(employee.id)"
                    class="text-green-600 hover:text-green-900 transition-colors p-1 rounded hover:bg-green-50"
                    title="Editar empleado"
                    :disabled="loadingStates.updating"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="handleDeleteEmployee(employee)"
                    class="text-red-600 hover:text-red-900 transition-colors p-1 rounded hover:bg-red-50"
                    title="Eliminar empleado"
                    :disabled="loadingStates.deleting"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.totalItems > 20" class="mt-6 flex justify-between items-center">
      <div class="text-sm text-gray-700">
        Mostrando {{ ((pagination.currentPage - 1) * 20) + 1 }} a 
        {{ Math.min(pagination.currentPage * 20, pagination.totalItems) }} 
        de {{ pagination.totalItems }} empleados
      </div>
      <div class="flex space-x-2">
        <button 
          @click="handleChangePage(pagination.currentPage - 1)"
          :disabled="pagination.currentPage <= 1 || isLoading"
          class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Anterior
        </button>
        <button 
          @click="handleChangePage(pagination.currentPage + 1)"
          :disabled="!canLoadMore || isLoading"
          class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Siguiente
        </button>
      </div>
    </div>

    <!-- Debug Info (remove in production) -->
    <div v-if="false" class="mt-8 p-4 bg-gray-100 rounded-lg text-xs">
      <h4 class="font-bold mb-2">Debug Info:</h4>
      <p>Employees: {{ employees.length }}</p>
      <p>Current Employee: {{ currentEmployee?.id || 'None' }}</p>
      <p>Loading States: {{ JSON.stringify(loadingStates) }}</p>
      <p>Pagination: {{ JSON.stringify(pagination) }}</p>
      <p>Error: {{ error || 'None' }}</p>
    </div>
  </div>
</template>

<script>
import { onMounted } from 'vue'
import { useEmployees } from '../composables/useEmployees.js'

export default {
  name: 'EmployeesListWithComposable',
  setup() {
    // Use the composable
    const {
      employees,
      currentEmployee,
      error,
      pagination,
      loadingStates,
      hasEmployees,
      isLoading,
      hasError,
      canLoadMore,
      fetchEmployees,
      fetchEmployee,
      deleteEmployee,
      refreshEmployees,
      clearError,
      setCurrentEmployee
    } = useEmployees()

    // Event handlers
    const handleViewEmployee = async (employeeId) => {
      try {
        await fetchEmployee(employeeId)
        // Here you could open a modal or navigate to detail view
        console.log('Viewing employee:', currentEmployee.value)
        alert(`Ver detalles del empleado: ${currentEmployee.value?.firstName} ${currentEmployee.value?.lastName}`)
      } catch (err) {
        console.error('Error viewing employee:', err)
      }
    }

    const handleEditEmployee = async (employeeId) => {
      try {
        await fetchEmployee(employeeId)
        // Here you could open an edit modal
        console.log('Editing employee:', currentEmployee.value)
        alert(`Editar empleado: ${currentEmployee.value?.firstName} ${currentEmployee.value?.lastName}`)
      } catch (err) {
        console.error('Error loading employee for edit:', err)
      }
    }

    const handleDeleteEmployee = async (employee) => {
      const fullName = employee.fullName || `${employee.firstName} ${employee.lastName}`
      const confirmed = confirm(`¿Estás seguro de que deseas eliminar al empleado ${fullName}?`)
      
      if (confirmed) {
        try {
          await deleteEmployee(employee.id)
          console.log('Employee deleted successfully')
        } catch (err) {
          console.error('Error deleting employee:', err)
        }
      }
    }

    const handleChangePage = async (page) => {
      if (page >= 1 && page <= pagination.value.totalPages) {
        try {
          await fetchEmployees(page)
        } catch (err) {
          console.error('Error changing page:', err)
        }
      }
    }

    // Utility functions (same as original component)
    const formatCurrency = (amount, currency) => {
      if (amount === undefined || amount === null || isNaN(amount)) {
        return '-'
      }
      return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: currency || 'EUR'
      }).format(amount)
    }

    const formatDate = (dateString) => {
      if (!dateString) {
        return '-'
      }
      const date = new Date(dateString)
      if (isNaN(date.getTime())) {
        return '-'
      }
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }

    // Load employees on component mount
    onMounted(async () => {
      try {
        await fetchEmployees()
      } catch (err) {
        console.error('Error loading employees on mount:', err)
      }
    })

    return {
      // Composable state and methods
      employees,
      currentEmployee,
      error,
      pagination,
      loadingStates,
      hasEmployees,
      isLoading,
      hasError,
      canLoadMore,
      refreshEmployees,
      clearError,
      
      // Event handlers
      handleViewEmployee,
      handleEditEmployee,
      handleDeleteEmployee,
      handleChangePage,
      
      // Utility functions
      formatCurrency,
      formatDate
    }
  }
}
</script>