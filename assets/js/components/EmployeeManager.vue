<template>
  <div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h2 class="text-3xl font-bold text-gray-900">Empleados</h2>
        <p class="text-gray-600 mt-1">Gestiona la información de todos los
          empleados</p>
      </div>
      <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500">
          Total: {{ filteredEmployees.length }} de {{ employees.length }} empleados
        </span>
        <button
            :disabled="loading"
            class="bg-blue-500 hover:bg-blue-600 disabled:bg-blue-300 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
            @click="refreshEmployees"
        >
          <svg :class="{ 'animate-spin': loading }"
               class="w-4 h-4"
               fill="none"
               stroke="currentColor"
               viewBox="0 0 24 24">
            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"/>
          </svg>
          {{ loading ? 'Cargando...' : 'Actualizar' }}
        </button>
      </div>
    </div>

    <!-- Error Alert -->
    <div v-if="error"
         class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
      <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0"
           fill="none"
           stroke="currentColor"
           viewBox="0 0 24 24">
        <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"/>
      </svg>
      <div class="flex-1">
        <h3 class="text-red-800 font-medium">Error</h3>
        <p class="text-red-700 mt-1">{{ error }}</p>
      </div>
      <button class="text-red-500 hover:text-red-700"
              @click="clearError">
        <svg class="w-5 h-5"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
          <path d="M6 18L18 6M6 6l12 12"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"/>
        </svg>
      </button>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="bg-white rounded-lg shadow mb-6 p-6">
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Barra de Búsqueda -->
        <div class="flex-1">
          <div class="relative">
            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
              <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"/>
            </svg>
            <input
                v-model="searchTerm"
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Buscar por nombre o email..."
                type="text"
            />
          </div>
        </div>

        <!-- Filtros -->
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Filtro por Departamento -->
          <select
              v-model="selectedDepartment"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos los departamentos</option>
            <option v-for="dept in uniqueDepartments"
                    :key="dept"
                    :value="dept">
              {{ dept }}
            </option>
          </select>

          <!-- Filtro por Estado -->
          <select
              v-model="selectedStatus"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos los estados</option>
            <option value="active">Activo</option>
            <option value="inactive">Inactivo</option>
          </select>

          <!-- Filtro por Rol -->
          <select
              v-model="selectedRole"
              class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Todos los roles</option>
            <option v-for="role in uniqueRoles"
                    :key="role"
                    :value="role">
              {{ role }}
            </option>
          </select>

          <!-- Botón Limpiar Filtros -->
          <button
              :disabled="!hasActiveFilters"
              class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 disabled:text-gray-400 border border-gray-300 rounded-lg hover:bg-gray-50 disabled:hover:bg-white transition-colors"
              @click="clearAllFilters"
          >
            Limpiar filtros
          </button>
        </div>
      </div>

      <!-- Indicadores de Filtros Activos -->
      <div v-if="hasActiveFilters"
           class="mt-4 flex flex-wrap gap-2">
        <span v-if="searchTerm"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
          Búsqueda: "{{ searchTerm }}"
          <button class="ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-blue-400 hover:bg-blue-200 hover:text-blue-500"
                  @click="searchTerm = ''">
            <svg class="h-2 w-2"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 8 8">
              <path d="m1 1 6 6m0-6-6 6"
                    stroke-linecap="round"
                    stroke-width="1.5"/>
            </svg>
          </button>
        </span>

        <span v-if="selectedDepartment"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
          Departamento: {{ selectedDepartment }}
          <button class="ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-green-400 hover:bg-green-200 hover:text-green-500"
                  @click="selectedDepartment = ''">
            <svg class="h-2 w-2"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 8 8">
              <path d="m1 1 6 6m0-6-6 6"
                    stroke-linecap="round"
                    stroke-width="1.5"/>
            </svg>
          </button>
        </span>

        <span v-if="selectedStatus"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
          Estado: {{ selectedStatus === 'active' ? 'Activo' : 'Inactivo' }}
          <button class="ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-yellow-400 hover:bg-yellow-200 hover:text-yellow-500"
                  @click="selectedStatus = ''">
            <svg class="h-2 w-2"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 8 8">
              <path d="m1 1 6 6m0-6-6 6"
                    stroke-linecap="round"
                    stroke-width="1.5"/>
            </svg>
          </button>
        </span>

        <span v-if="selectedRole"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
          Rol: {{ selectedRole }}
          <button class="ml-1.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-purple-400 hover:bg-purple-200 hover:text-purple-500"
                  @click="selectedRole = ''">
            <svg class="h-2 w-2"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 8 8">
              <path d="m1 1 6 6m0-6-6 6"
                    stroke-linecap="round"
                    stroke-width="1.5"/>
            </svg>
          </button>
        </span>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-green-100 text-green-600">
            <svg class="w-6 h-6"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
              <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Empleados Activos</p>
            <p class="text-2xl font-semibold text-gray-900">
              {{ filteredActiveEmployees.length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
            <svg class="w-6 h-6"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
              <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Departamentos</p>
            <p class="text-2xl font-semibold text-gray-900">
              {{ uniqueDepartments.length }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 rounded-full bg-red-100 text-red-600">
            <svg class="w-6 h-6"
                 fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
              <path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"/>
            </svg>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Empleados Inactivos</p>
            <p class="text-2xl font-semibold text-gray-900">
              {{ filteredInactiveEmployees.length }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Employee Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Lista de Empleados</h3>
        <!-- Items per page selector -->
        <div class="flex items-center gap-2">
          <label class="text-sm text-gray-600">Mostrar:</label>
          <select
              v-model="itemsPerPage"
              class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              @change="setItemsPerPage(parseInt($event.target.value))"
          >
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
          </select>
          <span class="text-sm text-gray-600">por página</span>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading && employees.length === 0"
           class="p-8 text-center">
        <div class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white">
          <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500"
               fill="none"
               viewBox="0 0 24 24"
               xmlns="http://www.w3.org/2000/svg">
            <circle class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"></circle>
            <path class="opacity-75"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  fill="currentColor"></path>
          </svg>
          Cargando empleados...
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredEmployees.length === 0 && employees.length > 0"
           class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
          <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron
          empleados</h3>
        <p class="mt-1 text-sm text-gray-500">Intenta ajustar los filtros de
          búsqueda.</p>
        <button class="mt-4 text-blue-600 hover:text-blue-500 text-sm font-medium"
                @click="clearAllFilters">
          Limpiar todos los filtros
        </button>
      </div>

      <div v-else-if="employees.length === 0"
           class="p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
          <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No hay empleados</h3>
        <p class="mt-1 text-sm text-gray-500">No se encontraron empleados en el
          sistema.</p>
      </div>

      <!-- Table -->
      <div v-else
           class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Empleado
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Departamento
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Rol
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Estado
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Acciones
            </th>
          </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="employee in paginatedEmployees"
              :key="employee.id"
              class="hover:bg-gray-50">
            <!-- Employee Info -->
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">
                          {{ getInitials(employee.name) }}
                        </span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ employee.name }}
                  </div>
                  <div class="text-sm text-gray-500">{{ employee.email }}</div>
                </div>
              </div>
            </td>

            <!-- Department -->
            <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ employee.department }}
                  </span>
            </td>

            <!-- Role -->
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ employee.role }}
            </td>

            <!-- Status -->
            <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(employee.status)"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                    {{ getStatusText(employee.status) }}
                  </span>
            </td>

            <!-- Actions -->
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex items-center gap-2">
                <button
                    class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                    @click="openEmployeeModal(employee)"
                >
                  Ver detalles
                </button>
                <select
                    :value="employee.status"
                    class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                    @change="handleStatusChange(employee.id, $event.target.value)"
                >
                  <option value="active">Activo</option>
                  <option value="inactive">Inactivo</option>
                </select>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <div v-if="totalPages > 1"
           class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-200">
        <!-- Pagination Info -->
        <div class="flex-1 flex justify-between sm:hidden">
          <button
              :disabled="!paginationInfo.hasPreviousPage"
              class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400"
              @click="previousPage"
          >
            Anterior
          </button>
          <button
              :disabled="!paginationInfo.hasNextPage"
              class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400"
              @click="nextPage"
          >
            Siguiente
          </button>
        </div>

        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Mostrando
              <span class="font-medium">{{ paginationInfo.start }}</span>
              a
              <span class="font-medium">{{ paginationInfo.end }}</span>
              de
              <span class="font-medium">{{ paginationInfo.total }}</span>
              empleados
            </p>
          </div>

          <div>
            <nav aria-label="Pagination"
                 class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <!-- Previous Button -->
              <button
                  :disabled="!paginationInfo.hasPreviousPage"
                  class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-300"
                  @click="previousPage"
              >
                <svg class="h-5 w-5"
                     fill="currentColor"
                     viewBox="0 0 20 20">
                  <path clip-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        fill-rule="evenodd"/>
                </svg>
              </button>

              <!-- First page button (if not visible in range) -->
              <template v-if="visiblePages[0] > 1">
                <button
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                    @click="goToFirstPage"
                >
                  1
                </button>
                <span v-if="visiblePages[0] > 2"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                  ...
                </span>
              </template>

              <!-- Page Numbers -->
              <button
                  v-for="page in visiblePages"
                  :key="page"
                  :class="[
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                  page === currentPage
                    ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                    : 'bg-white border-gray-300 text-gray-700 hover:bg-gray-50'
                ]"
                  @click="setCurrentPage(page)"
              >
                {{ page }}
              </button>

              <!-- Last page button (if not visible in range) -->
              <template v-if="visiblePages[visiblePages.length - 1] < totalPages">
                <span v-if="visiblePages[visiblePages.length - 1] < totalPages - 1"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                  ...
                </span>
                <button
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50"
                    @click="goToLastPage"
                >
                  {{ totalPages }}
                </button>
              </template>

              <!-- Next Button -->
              <button
                  :disabled="!paginationInfo.hasNextPage"
                  class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-300"
                  @click="nextPage"
              >
                <svg class="h-5 w-5"
                     fill="currentColor"
                     viewBox="0 0 20 20">
                  <path clip-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        fill-rule="evenodd"/>
                </svg>
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Employee Modal -->
    <EmployeeModal
        :employee="selectedEmployee"
        :is-open="isModalOpen"
        @close="closeEmployeeModal"
        @update-status="handleModalStatusUpdate"
    />
  </div>
</template>

<script setup>
import {computed, onMounted, ref} from 'vue';
import {useEmployeeStore} from '../stores/employee.js';
import EmployeeModal from './EmployeeModal.vue';

const employeeStore = useEmployeeStore();

// Modal state
const isModalOpen = ref(false);
const selectedEmployee = ref(null);

// Computed properties
const employees = computed(() => employeeStore.employees);
const filteredEmployees = computed(() => employeeStore.filteredEmployees);
const loading = computed(() => employeeStore.loading);
const error = computed(() => employeeStore.error);
const filteredActiveEmployees = computed(() => employeeStore.filteredActiveEmployees);
const filteredInactiveEmployees = computed(() => employeeStore.filteredInactiveEmployees);
const uniqueDepartments = computed(() => employeeStore.uniqueDepartments);
const uniqueRoles = computed(() => employeeStore.uniqueRoles);

// Filter states
const searchTerm = computed({
  get: () => employeeStore.searchTerm,
  set: (value) => employeeStore.setSearchTerm(value)
});

const selectedDepartment = computed({
  get: () => employeeStore.selectedDepartment,
  set: (value) => employeeStore.setDepartmentFilter(value)
});

const selectedStatus = computed({
  get: () => employeeStore.selectedStatus,
  set: (value) => employeeStore.setStatusFilter(value)
});

const selectedRole = computed({
  get: () => employeeStore.selectedRole,
  set: (value) => employeeStore.setRoleFilter(value)
});

const hasActiveFilters = computed(() => {
  return !!(employeeStore.searchTerm || employeeStore.selectedDepartment ||
      employeeStore.selectedStatus || employeeStore.selectedRole);
});

// Pagination states
const currentPage = computed(() => employeeStore.currentPage);
const itemsPerPage = computed(() => employeeStore.itemsPerPage);
const totalPages = computed(() => Math.ceil(filteredEmployees.value.length / itemsPerPage.value));
const paginationInfo = computed(() => ({
  total: filteredEmployees.value.length,
  perPage: itemsPerPage.value,
  currentPage: currentPage.value,
  start: (currentPage.value - 1) * itemsPerPage.value + 1,
  end: Math.min(currentPage.value * itemsPerPage.value, filteredEmployees.value.length),
  hasPreviousPage: currentPage.value > 1,
  hasNextPage: currentPage.value < totalPages.value
}));
const visiblePages = computed(() => {
  const pages = [];
  for (let i = 1; i <= totalPages.value; i++) {
    if (i === 1 || i === totalPages.value || (i >= currentPage.value - 1 && i <= currentPage.value + 1)) {
      pages.push(i);
    } else if (i === currentPage.value - 2 || i === currentPage.value + 2) {
      pages.push('...');
    }
  }
  return pages;
});

// Methods
const refreshEmployees = () => {
  employeeStore.fetchEmployees();
};

const clearError = () => {
  employeeStore.clearError();
};

const clearAllFilters = () => {
  employeeStore.clearFilters();
};

const setItemsPerPage = (value) => {
  employeeStore.setItemsPerPage(value);
};

const setCurrentPage = (value) => {
  employeeStore.setCurrentPage(value);
};

const previousPage = () => {
  if (paginationInfo.value.hasPreviousPage) {
    employeeStore.setCurrentPage(currentPage.value - 1);
  }
};

const nextPage = () => {
  if (paginationInfo.value.hasNextPage) {
    employeeStore.setCurrentPage(currentPage.value + 1);
  }
};

const goToFirstPage = () => {
  employeeStore.setCurrentPage(1);
};

const goToLastPage = () => {
  employeeStore.setCurrentPage(totalPages.value);
};

const getInitials = (name) => {
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};

const getStatusClass = (status) => {
  return status === 'active'
      ? 'bg-green-100 text-green-800'
      : 'bg-red-100 text-red-800';
};

const getStatusText = (status) => {
  return status === 'active' ? 'Activo' : 'Inactivo';
};

const handleStatusChange = async (employeeId, newStatus) => {
  const success = await employeeStore.updateEmployeeStatus(employeeId, newStatus);
  if (success) {
    // Opcional: mostrar mensaje de éxito
    console.log('Estado del empleado actualizado correctamente');
  }
};

// Modal methods
const openEmployeeModal = (employee) => {
  selectedEmployee.value = employee;
  isModalOpen.value = true;
};

const closeEmployeeModal = () => {
  isModalOpen.value = false;
  selectedEmployee.value = null;
};

const handleModalStatusUpdate = async (employeeId, newStatus) => {
  const success = await employeeStore.updateEmployeeStatus(employeeId, newStatus);
  if (success) {
    console.log('Estado del empleado actualizado correctamente');
    // El modal se mantiene abierto para ver los cambios reflejados
  }
};

// Load employees on component mount
onMounted(() => {
  employeeStore.fetchEmployees();
});
</script>
