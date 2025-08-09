/**
 * useEmployees Composable - Vue 3 Composition API
 * Manages employee CRUD operations, loading states, errors, and pagination
 * 
 * @example
 * import { useEmployees } from '@/composables/useEmployees';
 * 
 * export default {
 *   setup() {
 *     const {
 *       employees,
 *       loading,
 *       error,
 *       fetchEmployees,
 *       createEmployee
 *     } = useEmployees();
 * 
 *     onMounted(() => {
 *       fetchEmployees();
 *     });
 * 
 *     return { employees, loading, error };
 *   }
 * };
 */

import { ref, reactive, computed, watch, readonly } from 'vue';
import employeeService from '../services/employeeService.js';

export function useEmployees() {
  // ==================== REACTIVE STATES ====================
  
  /**
   * Lista de empleados
   * @type {Ref<Array>}
   */
  const employees = ref([]);
  
  /**
   * Empleado seleccionado/actual
   * @type {Ref<Object|null>}
   */
  const currentEmployee = ref(null);
  
  /**
   * Estado de carga general (deprecated - usar isLoading computed)
   * @type {Ref<boolean>}
   */
  const loading = ref(false);
  
  /**
   * Error actual
   * @type {Ref<string|null>}
   */
  const error = ref(null);
  
  /**
   * Información de paginación
   * @type {Object}
   */
  const pagination = ref({
    currentPage: 1,
    totalPages: 1,
    totalItems: 0
  });

  /**
   * Estados de carga específicos para diferentes operaciones
   * @type {Object}
   */
  const loadingStates = reactive({
    fetching: false,    // Cargando lista
    creating: false,    // Creando empleado
    updating: false,    // Actualizando empleado
    deleting: false,    // Eliminando empleado
    fetchingOne: false  // Cargando un empleado específico
  });

  // ==================== COMPUTED PROPERTIES ====================
  
  /**
   * Indica si hay empleados en la lista
   * @type {ComputedRef<boolean>}
   */
  const hasEmployees = computed(() => employees.value.length > 0);
  
  /**
   * Indica si hay alguna operación de carga en progreso
   * @type {ComputedRef<boolean>}
   */
  const isLoading = computed(() => Object.values(loadingStates).some(state => state));
  
  /**
   * Indica si hay un error actual
   * @type {ComputedRef<boolean>}
   */
  const hasError = computed(() => error.value !== null);
  
  /**
   * Indica si se pueden cargar más páginas
   * @type {ComputedRef<boolean>}
   */
  const canLoadMore = computed(() => pagination.value.currentPage < pagination.value.totalPages);

  // ==================== UTILITY FUNCTIONS ====================
  
  /**
   * Limpiar el estado de error actual
   */
  const clearError = () => {
    error.value = null;
  };

  /**
   * Resetear paginación a valores iniciales
   */
  const resetPagination = () => {
    pagination.value = {
      currentPage: 1,
      totalPages: 1,
      totalItems: 0
    };
  };

  /**
   * Establecer empleado actual para edición/vista
   * @param {Object} employee - Empleado a establecer como actual
   */
  const setCurrentEmployee = (employee) => {
    if (employee && typeof employee === 'object') {
      currentEmployee.value = { ...employee };
    } else {
      console.warn('setCurrentEmployee: Invalid employee object provided');
    }
  };

  /**
   * Limpiar empleado actual
   */
  const clearCurrentEmployee = () => {
    currentEmployee.value = null;
  };

  /**
   * Calcular información de paginación basada en respuesta de API
   * @param {number} totalItems - Total de elementos
   * @param {number} currentPage - Página actual
   * @param {number} itemsPerPage - Elementos por página (default: 20)
   */
  const updatePagination = (totalItems, currentPage, itemsPerPage = 20) => {
    pagination.value = {
      currentPage,
      totalPages: Math.ceil(totalItems / itemsPerPage),
      totalItems
    };
  };

  // ==================== CRUD FUNCTIONS ====================

  /**
   * Cargar lista de empleados con paginación
   * @param {number} page - Número de página (default: 1)
   * @param {Object} filters - Filtros opcionales para la consulta
   * @returns {Promise<Object>} Datos de empleados y paginación
   */
  const fetchEmployees = async (page = 1, filters = {}) => {
    loadingStates.fetching = true;
    loading.value = true; // Mantener compatibilidad
    clearError();

    try {
      const response = await employeeService.getEmployees(page, filters);
      
      employees.value = response.employees;
      updatePagination(response.totalItems, response.currentPage);
      
      return response;
    } catch (err) {
      error.value = err.message;
      console.error('Error in fetchEmployees:', err);
      throw err;
    } finally {
      loadingStates.fetching = false;
      loading.value = false;
    }
  };

  /**
   * Cargar un empleado específico por ID
   * @param {number|string} id - ID del empleado
   * @returns {Promise<Object>} Datos del empleado
   */
  const fetchEmployee = async (id) => {
    loadingStates.fetchingOne = true;
    clearError();

    try {
      const employee = await employeeService.getEmployee(id);
      setCurrentEmployee(employee);
      return employee;
    } catch (err) {
      error.value = err.message;
      console.error('Error in fetchEmployee:', err);
      throw err;
    } finally {
      loadingStates.fetchingOne = false;
    }
  };

  /**
   * Crear nuevo empleado
   * @param {Object} employeeData - Datos del empleado a crear
   * @returns {Promise<Object>} Empleado creado
   */
  const createEmployee = async (employeeData) => {
    loadingStates.creating = true;
    clearError();

    try {
      const newEmployee = await employeeService.createEmployee(employeeData);
      
      // Agregar a la lista local si estamos en la primera página
      if (pagination.value.currentPage === 1) {
        employees.value.unshift(newEmployee);
      }
      
      // Actualizar contadores de paginación
      updatePagination(
        pagination.value.totalItems + 1,
        pagination.value.currentPage
      );
      
      return newEmployee;
    } catch (err) {
      error.value = err.message;
      console.error('Error in createEmployee:', err);
      throw err;
    } finally {
      loadingStates.creating = false;
    }
  };

  /**
   * Actualizar empleado existente
   * @param {number|string} id - ID del empleado
   * @param {Object} employeeData - Datos actualizados del empleado
   * @returns {Promise<Object>} Empleado actualizado
   */
  const updateEmployee = async (id, employeeData) => {
    loadingStates.updating = true;
    clearError();

    try {
      const updatedEmployee = await employeeService.updateEmployee(id, employeeData);
      
      // Actualización optimista: actualizar en la lista local
      const index = employees.value.findIndex(emp => emp.id == id);
      if (index !== -1) {
        employees.value[index] = { ...updatedEmployee };
      }
      
      // Actualizar currentEmployee si es el mismo
      if (currentEmployee.value && currentEmployee.value.id == id) {
        setCurrentEmployee(updatedEmployee);
      }
      
      return updatedEmployee;
    } catch (err) {
      error.value = err.message;
      console.error('Error in updateEmployee:', err);
      throw err;
    } finally {
      loadingStates.updating = false;
    }
  };

  /**
   * Eliminar empleado
   * @param {number|string} id - ID del empleado a eliminar
   * @returns {Promise<boolean>} Estado de éxito
   */
  const deleteEmployee = async (id) => {
    loadingStates.deleting = true;
    clearError();

    try {
      await employeeService.deleteEmployee(id);
      
      // Remover de la lista local
      const index = employees.value.findIndex(emp => emp.id == id);
      if (index !== -1) {
        employees.value.splice(index, 1);
      }
      
      // Limpiar currentEmployee si es el mismo
      if (currentEmployee.value && currentEmployee.value.id == id) {
        clearCurrentEmployee();
      }
      
      // Actualizar contadores de paginación
      updatePagination(
        Math.max(0, pagination.value.totalItems - 1),
        pagination.value.currentPage
      );
      
      return true;
    } catch (err) {
      error.value = err.message;
      console.error('Error in deleteEmployee:', err);
      throw err;
    } finally {
      loadingStates.deleting = false;
    }
  };

  /**
   * Recargar la página actual de empleados
   * Mantiene filtros y página actual
   * @param {Object} filters - Filtros opcionales
   * @returns {Promise<Object>} Datos actualizados
   */
  const refreshEmployees = async (filters = {}) => {
    return await fetchEmployees(pagination.value.currentPage, filters);
  };

  // ==================== WATCHERS ====================
  
  /**
   * Watcher para errores - log automático
   */
  watch(error, (newError) => {
    if (newError) {
      console.error('Employee operation error:', newError);
    }
  });

  // Auto-clear de errores después de 10 segundos (opcional)
  watch(error, (newError) => {
    if (newError) {
      setTimeout(() => {
        if (error.value === newError) {
          clearError();
        }
      }, 10000);
    }
  });

  // ==================== PUBLIC API ====================
  
  return {
    // Estados (readonly para inmutabilidad)
    employees: readonly(employees),
    currentEmployee: readonly(currentEmployee),
    loading: readonly(loading),
    error: readonly(error),
    pagination: readonly(pagination),
    loadingStates: readonly(loadingStates),

    // Computed properties
    hasEmployees,
    isLoading,
    hasError,
    canLoadMore,

    // CRUD functions
    fetchEmployees,
    fetchEmployee,
    createEmployee,
    updateEmployee,
    deleteEmployee,
    refreshEmployees,

    // Utility functions
    clearError,
    resetPagination,
    setCurrentEmployee,
    clearCurrentEmployee
  };
}