<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Empleados</h1>
        <p class="text-gray-600">Gestión de empleados del sistema HR</p>
      </div>
      <button 
        @click="openCreateModal"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nuevo Empleado
      </button>
    </div>

    <!-- Filters -->
    <div class="mb-6 bg-white p-4 rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label for="search" class="block text-sm font-medium text-gray-700 mb-1">
            Buscar
          </label>
          <input
            id="search"
            v-model="filters.search"
            type="text"
            placeholder="Nombre, email, posición..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @input="debouncedApplyFilters"
          />
        </div>
        
        <!-- Position Filter -->
        <div>
          <label for="position-filter" class="block text-sm font-medium text-gray-700 mb-1">
            Posición
          </label>
          <select
            id="position-filter"
            v-model="filters.position"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @change="applyFilters"
          >
            <option value="">Todas las posiciones</option>
            <option v-for="position in uniquePositions" :key="position" :value="position">
              {{ position }}
            </option>
          </select>
        </div>
        
        <!-- Salary Range -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Salario mínimo
          </label>
          <input
            v-model.number="filters.salaryMin"
            type="number"
            placeholder="0"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            @input="debouncedApplyFilters"
          />
        </div>
        
        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="w-full px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors"
          >
            Limpiar Filtros
          </button>
        </div>
      </div>
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
              <th 
                @click="sortEmployees('firstName')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Nombre Completo
                  <svg v-if="sortBy === 'firstName'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th 
                @click="sortEmployees('email')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Email
                  <svg v-if="sortBy === 'email'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th 
                @click="sortEmployees('position')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Posición
                  <svg v-if="sortBy === 'position'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th 
                @click="sortEmployees('salaryAmount')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Salario
                  <svg v-if="sortBy === 'salaryAmount'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th 
                @click="sortEmployees('hiredAt')"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 transition-colors"
              >
                <div class="flex items-center gap-1">
                  Fecha de Contratación
                  <svg v-if="sortBy === 'hiredAt'" class="w-4 h-4" :class="sortOrder === 'asc' ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
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

    <!-- Modales -->
    <!-- Modal de Creación -->
    <BaseModal 
      :isOpen="showCreateModal" 
      title="Crear Nuevo Empleado"
      size="lg"
      @close="closeModals"
    >
      <EmployeeForm 
        mode="create"
        :loading="loadingStates.creating"
        @submit="handleCreateEmployee"
        @cancel="closeModals"
      />
    </BaseModal>

    <!-- Modal de Edición -->
    <BaseModal 
      :isOpen="showEditModal" 
      title="Editar Empleado"
      size="lg"
      @close="closeModals"
    >
      <EmployeeForm 
        mode="edit"
        :employee="selectedEmployee"
        :loading="loadingStates.updating"
        @submit="handleUpdateEmployee"
        @cancel="closeModals"
      />
    </BaseModal>

    <!-- Modal de Vista Detallada -->
    <BaseModal 
      :isOpen="showViewModal" 
      title="Detalles del Empleado"
      size="xl"
      @close="closeModals"
    >
      <EmployeeDetail 
        v-if="selectedEmployee"
        :employee="selectedEmployee"
        :loading="loadingStates.deleting"
        @edit="handleEditFromDetail"
        @delete="handleDeleteFromDetail"
        @close="closeModals"
      />
    </BaseModal>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import EmployeeForm from '@/components/employees/EmployeeForm.vue'
import EmployeeDetail from '@/components/employees/EmployeeDetail.vue'
import employeeService from '@/services/employeeService.js'

export default {
  name: 'EmployeesList',
  components: {
    BaseModal,
    EmployeeForm,
    EmployeeDetail
  },
  setup() {
    // Modal states
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const showViewModal = ref(false)
    const selectedEmployee = ref(null)

    // Loading states
    const loadingStates = reactive({
      fetching: false,
      creating: false,
      updating: false,
      deleting: false
    })

    // Data states
    const employees = ref([])
    const error = ref(null)
    const pagination = reactive({
      currentPage: 1,
      itemsPerPage: 20,
      totalItems: 0,
      totalPages: 0
    })

    // Filtering and sorting
    const filters = reactive({
      search: '',
      position: '',
      salaryMin: null,
      salaryMax: null
    })
    const sortBy = ref('firstName')
    const sortOrder = ref('asc')

    // Computed properties
    const loading = computed(() => loadingStates.fetching)

    const uniquePositions = computed(() => {
      const positions = employees.value.map(emp => emp.position).filter(Boolean)
      return [...new Set(positions)].sort()
    })

    const filteredAndSortedEmployees = computed(() => {
      let result = [...employees.value]

      // Apply filters
      if (filters.search) {
        const searchTerm = filters.search.toLowerCase()
        result = result.filter(emp => 
          emp.firstName?.toLowerCase().includes(searchTerm) ||
          emp.lastName?.toLowerCase().includes(searchTerm) ||
          emp.email?.toLowerCase().includes(searchTerm) ||
          emp.position?.toLowerCase().includes(searchTerm)
        )
      }

      if (filters.position) {
        result = result.filter(emp => emp.position === filters.position)
      }

      if (filters.salaryMin !== null && filters.salaryMin !== '') {
        result = result.filter(emp => emp.salaryAmount >= filters.salaryMin)
      }

      if (filters.salaryMax !== null && filters.salaryMax !== '') {
        result = result.filter(emp => emp.salaryAmount <= filters.salaryMax)
      }

      // Apply sorting
      result.sort((a, b) => {
        let aValue = a[sortBy.value]
        let bValue = b[sortBy.value]

        // Handle different data types
        if (sortBy.value === 'salaryAmount') {
          aValue = Number(aValue) || 0
          bValue = Number(bValue) || 0
        } else if (sortBy.value === 'hiredAt') {
          aValue = new Date(aValue)
          bValue = new Date(bValue)
        } else {
          aValue = String(aValue || '').toLowerCase()
          bValue = String(bValue || '').toLowerCase()
        }

        if (aValue < bValue) return sortOrder.value === 'asc' ? -1 : 1
        if (aValue > bValue) return sortOrder.value === 'asc' ? 1 : -1
        return 0
      })

      return result
    })

    // Debounced filter application
    let filterTimeout = null
    const debouncedApplyFilters = () => {
      clearTimeout(filterTimeout)
      filterTimeout = setTimeout(() => {
        applyFilters()
      }, 300)
    }

    /**
     * Fetch employees from API
     */
    const fetchEmployees = async (page = 1) => {
      loadingStates.fetching = true
      error.value = null
      
      try {
        const response = await employeeService.fetchEmployees(page)
        
        employees.value = response.data
        pagination.currentPage = response.pagination.currentPage
        pagination.totalItems = response.pagination.totalItems
        pagination.totalPages = response.pagination.totalPages
        
      } catch (err) {
        error.value = err.message || 'Error al cargar los empleados'
        console.error('Error fetching employees:', err)
      } finally {
        loadingStates.fetching = false
      }
    }

    /**
     * Modal handlers
     */
    const openCreateModal = () => {
      selectedEmployee.value = null
      showCreateModal.value = true
    }

    const openEditModal = async (employee) => {
      try {
        // Fetch fresh employee data
        const freshEmployee = await employeeService.getEmployee(employee.id)
        selectedEmployee.value = freshEmployee
        showEditModal.value = true
        showViewModal.value = false
      } catch (err) {
        error.value = err.message || 'Error al cargar los datos del empleado'
      }
    }

    const openViewModal = async (employee) => {
      try {
        // Fetch fresh employee data
        const freshEmployee = await employeeService.getEmployee(employee.id)
        selectedEmployee.value = freshEmployee
        showViewModal.value = true
      } catch (err) {
        error.value = err.message || 'Error al cargar los datos del empleado'
      }
    }

    const closeModals = () => {
      showCreateModal.value = false
      showEditModal.value = false
      showViewModal.value = false
      selectedEmployee.value = null
    }

    /**
     * CRUD Operations
     */
    const handleCreateEmployee = async (employeeData) => {
      loadingStates.creating = true
      
      try {
        await employeeService.createEmployee(employeeData)
        closeModals()
        await fetchEmployees(pagination.currentPage)
        // TODO: Show success toast
      } catch (err) {
        error.value = err.message || 'Error al crear el empleado'
      } finally {
        loadingStates.creating = false
      }
    }

    const handleUpdateEmployee = async (employeeData) => {
      if (!selectedEmployee.value) return
      
      loadingStates.updating = true
      
      try {
        await employeeService.updateEmployee(selectedEmployee.value.id, employeeData)
        closeModals()
        await fetchEmployees(pagination.currentPage)
        // TODO: Show success toast
      } catch (err) {
        error.value = err.message || 'Error al actualizar el empleado'
      } finally {
        loadingStates.updating = false
      }
    }

    const deleteEmployee = async (employeeId) => {
      loadingStates.deleting = true
      
      try {
        await employeeService.deleteEmployee(employeeId)
        await fetchEmployees(pagination.currentPage)
        closeModals()
        // TODO: Show success toast
      } catch (err) {
        error.value = err.message || 'Error al eliminar el empleado'
      } finally {
        loadingStates.deleting = false
      }
    }

    const confirmDelete = (employee) => {
      const fullName = employee.fullName || `${employee.firstName} ${employee.lastName}`
      const confirmed = confirm(`¿Estás seguro de que deseas eliminar al empleado ${fullName}?`)
      
      if (confirmed) {
        deleteEmployee(employee.id)
      }
    }

    /**
     * Filtering and sorting
     */
    const applyFilters = () => {
      // Filters are applied via computed property
      // This function can be used for manual refresh if needed
    }

    const sortEmployees = (column) => {
      if (sortBy.value === column) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
      } else {
        sortBy.value = column
        sortOrder.value = 'asc'
      }
    }

    const clearFilters = () => {
      filters.search = ''
      filters.position = ''
      filters.salaryMin = null
      filters.salaryMax = null
    }

    /**
     * Pagination
     */
    const changePage = (page) => {
      if (page >= 1 && page <= pagination.totalPages) {
        fetchEmployees(page)
      }
    }

    /**
     * Utility functions
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

    const calculateWorkingTime = (hiredAt) => {
      if (!hiredAt) return '-'
      
      const hiredDate = new Date(hiredAt)
      const now = new Date()
      const diffTime = Math.abs(now - hiredDate)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      
      if (diffDays < 30) {
        return `${diffDays} días`
      } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30)
        return `${months} ${months === 1 ? 'mes' : 'meses'}`
      } else {
        const years = Math.floor(diffDays / 365)
        const remainingMonths = Math.floor((diffDays % 365) / 30)
        return `${years} ${years === 1 ? 'año' : 'años'}${remainingMonths > 0 ? ` y ${remainingMonths} ${remainingMonths === 1 ? 'mes' : 'meses'}` : ''}`
      }
    }

    // Placeholder functions for compatibility
    const viewEmployee = (employeeId) => {
      const employee = employees.value.find(emp => emp.id === employeeId)
      if (employee) {
        openViewModal(employee)
      }
    }

    const editEmployee = (employeeId) => {
      const employee = employees.value.find(emp => emp.id === employeeId)
      if (employee) {
        openEditModal(employee)
      }
    }

    // Event handlers for EmployeeDetail component
    const handleEditFromDetail = (employee) => {
      closeModals()
      openEditModal(employee)
    }

    const handleDeleteFromDetail = (employeeId) => {
      const employee = employees.value.find(emp => emp.id === employeeId)
      if (employee) {
        confirmDelete(employee)
      }
    }

    // Load employees on component mount
    onMounted(() => {
      fetchEmployees()
    })

    return {
      // Data
      employees: filteredAndSortedEmployees,
      loading,
      error,
      pagination,
      
      // Modal states
      showCreateModal,
      showEditModal,
      showViewModal,
      selectedEmployee,
      loadingStates,
      
      // Filters and sorting
      filters,
      sortBy,
      sortOrder,
      uniquePositions,
      
      // Methods
      fetchEmployees,
      openCreateModal,
      openEditModal,
      openViewModal,
      closeModals,
      handleCreateEmployee,
      handleUpdateEmployee,
      confirmDelete,
      applyFilters,
      debouncedApplyFilters,
      sortEmployees,
      clearFilters,
      changePage,
      formatCurrency,
      formatDate,
      calculateWorkingTime,
      
      // Compatibility
      viewEmployee,
      editEmployee,
      
      // EmployeeDetail event handlers
      handleEditFromDetail,
      handleDeleteFromDetail
    }
  }
}
</script>