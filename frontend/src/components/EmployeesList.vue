<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Empleados</h1>
      <p class="text-gray-600">Gestión de empleados del sistema HR</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <span class="ml-3 text-gray-600">Cargando empleados...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <span class="text-red-400">⚠️</span>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error al cargar empleados</h3>
          <p class="mt-1 text-sm text-red-700">{{ error }}</p>
          <button 
            @click="fetchEmployees" 
            class="mt-2 text-sm bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded"
          >
            Reintentar
          </button>
        </div>
      </div>
    </div>

    <!-- Employees Table -->
    <div v-else class="bg-white shadow-lg rounded-lg overflow-hidden">
      <!-- Empty State -->
      <div v-if="employees.length === 0" class="text-center py-12">
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
                <div class="text-sm font-medium text-gray-900">{{ employee.fullName || `${employee.firstName} ${employee.lastName}` }}</div>
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
                    @click="viewEmployee(employee.id)"
                    class="text-blue-600 hover:text-blue-900 transition-colors p-1 rounded hover:bg-blue-50"
                    title="Ver detalles"
                  >
                    👁️
                  </button>
                  <button 
                    @click="editEmployee(employee.id)"
                    class="text-green-600 hover:text-green-900 transition-colors p-1 rounded hover:bg-green-50"
                    title="Editar empleado"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="confirmDelete(employee)"
                    class="text-red-600 hover:text-red-900 transition-colors p-1 rounded hover:bg-red-50"
                    title="Eliminar empleado"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Cards -->
      <div v-if="employees.length > 0" class="md:hidden">
        <div v-for="employee in employees" :key="employee.id" class="border-b border-gray-200 p-4">
          <div class="flex justify-between items-start mb-2">
            <h3 class="text-lg font-medium text-gray-900">
              {{ employee.fullName || `${employee.firstName} ${employee.lastName}` }}
            </h3>
            <div class="flex space-x-2">
              <button 
                @click="viewEmployee(employee.id)"
                class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50"
                title="Ver detalles"
              >
                👁️
              </button>
              <button 
                @click="editEmployee(employee.id)"
                class="text-green-600 hover:text-green-900 p-1 rounded hover:bg-green-50"
                title="Editar empleado"
              >
                ✏️
              </button>
              <button 
                @click="confirmDelete(employee)"
                class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50"
                title="Eliminar empleado"
              >
                🗑️
              </button>
            </div>
          </div>
          <div class="space-y-1 text-sm text-gray-600">
            <p><span class="font-medium">Email:</span> {{ employee.email }}</p>
            <p><span class="font-medium">Posición:</span> {{ employee.position }}</p>
            <p><span class="font-medium">Salario:</span> {{ formatCurrency(employee.salaryAmount, employee.salaryCurrency) }}</p>
            <p><span class="font-medium">Contratado:</span> {{ formatDate(employee.hiredAt) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.totalItems > pagination.itemsPerPage" class="mt-6 flex justify-between items-center">
      <div class="text-sm text-gray-700">
        Mostrando {{ ((pagination.currentPage - 1) * pagination.itemsPerPage) + 1 }} a 
        {{ Math.min(pagination.currentPage * pagination.itemsPerPage, pagination.totalItems) }} 
        de {{ pagination.totalItems }} empleados
      </div>
      <div class="flex space-x-2">
        <button 
          @click="changePage(pagination.currentPage - 1)"
          :disabled="pagination.currentPage <= 1"
          class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Anterior
        </button>
        <button 
          @click="changePage(pagination.currentPage + 1)"
          :disabled="pagination.currentPage >= pagination.totalPages"
          class="px-3 py-2 text-sm bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'

export default {
  name: 'EmployeesList',
  setup() {
    // Reactive state
    const employees = ref([])
    const loading = ref(false)
    const error = ref(null)
    const pagination = reactive({
      currentPage: 1,
      itemsPerPage: 20,
      totalItems: 0,
      totalPages: 0
    })

    // API base URL
    const API_BASE_URL = 'http://localhost:8000/api'

    /**
     * Fetch employees from API
     */
    const fetchEmployees = async (page = 1) => {
      loading.value = true
      error.value = null
      
      try {
        const response = await fetch(`${API_BASE_URL}/employees?page=${page}`)
        
        if (!response.ok) {
          throw new Error(`Error ${response.status}: ${response.statusText}`)
        }
        
        const data = await response.json()
        
        // Handle API Platform response format
        employees.value = data.member || data['hydra:member'] || []
        
        // Update pagination info
        pagination.currentPage = page
        pagination.totalItems = data.totalItems || data['hydra:totalItems'] || 0
        pagination.totalPages = Math.ceil(pagination.totalItems / pagination.itemsPerPage)
        
      } catch (err) {
        error.value = err.message || 'Error al cargar los empleados'
        console.error('Error fetching employees:', err)
      } finally {
        loading.value = false
      }
    }

    /**
     * Delete employee with confirmation
     */
    const deleteEmployee = async (employeeId) => {
      try {
        const response = await fetch(`${API_BASE_URL}/employees/${employeeId}`, {
          method: 'DELETE'
        })
        
        if (!response.ok) {
          throw new Error(`Error ${response.status}: ${response.statusText}`)
        }
        
        // Refresh the list after successful deletion
        await fetchEmployees(pagination.currentPage)
        
      } catch (err) {
        error.value = err.message || 'Error al eliminar el empleado'
        console.error('Error deleting employee:', err)
      }
    }

    /**
     * Confirm deletion with user
     */
    const confirmDelete = (employee) => {
      const fullName = employee.fullName || `${employee.firstName} ${employee.lastName}`
      const confirmed = confirm(`¿Estás seguro de que deseas eliminar al empleado ${fullName}?`)
      
      if (confirmed) {
        deleteEmployee(employee.id)
      }
    }

    /**
     * Navigate to employee details (placeholder)
     */
    const viewEmployee = (employeeId) => {
      // For now, just log - can be extended with routing
      console.log('View employee:', employeeId)
      alert(`Ver detalles del empleado: ${employeeId}`)
    }

    /**
     * Navigate to employee edit (placeholder)
     */
    const editEmployee = (employeeId) => {
      // For now, just log - can be extended with routing
      console.log('Edit employee:', employeeId)
      alert(`Editar empleado: ${employeeId}`)
    }

    /**
     * Change page for pagination
     */
    const changePage = (page) => {
      if (page >= 1 && page <= pagination.totalPages) {
        fetchEmployees(page)
      }
    }

    /**
     * Format currency display
     */
    const formatCurrency = (amount, currency) => {
      if (amount === undefined || amount === null || isNaN(amount)) {
        return '-'
      }
      return new Intl.NumberFormat('es-ES', {
        style: 'currency',
        currency: currency || 'EUR'
      }).format(amount)
    }

    /**
     * Format date display
     */
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
    onMounted(() => {
      fetchEmployees()
    })

    return {
      employees,
      loading,
      error,
      pagination,
      fetchEmployees,
      confirmDelete,
      viewEmployee,
      editEmployee,
      changePage,
      formatCurrency,
      formatDate
    }
  }
}
</script>