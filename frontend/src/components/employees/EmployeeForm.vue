<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Nombres en una fila -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <!-- First Name -->
      <div>
        <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">
          Nombre <span class="text-red-500">*</span>
        </label>
        <input
          id="firstName"
          v-model="formData.firstName"
          type="text"
          :disabled="loading"
          :class="[
            'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
            errors.firstName 
              ? 'border-red-300 bg-red-50' 
              : 'border-gray-300 hover:border-gray-400',
            loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
          ]"
          placeholder="Ingrese el nombre"
          aria-describedby="firstName-error"
          @blur="validateField('firstName')"
          @input="validateField('firstName')"
        />
        <p v-if="errors.firstName" id="firstName-error" class="mt-1 text-sm text-red-600">
          {{ errors.firstName }}
        </p>
      </div>

      <!-- Last Name -->
      <div>
        <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">
          Apellido <span class="text-red-500">*</span>
        </label>
        <input
          id="lastName"
          v-model="formData.lastName"
          type="text"
          :disabled="loading"
          :class="[
            'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
            errors.lastName 
              ? 'border-red-300 bg-red-50' 
              : 'border-gray-300 hover:border-gray-400',
            loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
          ]"
          placeholder="Ingrese el apellido"
          aria-describedby="lastName-error"
          @blur="validateField('lastName')"
          @input="validateField('lastName')"
        />
        <p v-if="errors.lastName" id="lastName-error" class="mt-1 text-sm text-red-600">
          {{ errors.lastName }}
        </p>
      </div>
    </div>

    <!-- Email completo -->
    <div>
      <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
        Email <span class="text-red-500">*</span>
      </label>
      <input
        id="email"
        v-model="formData.email"
        type="email"
        :disabled="loading"
        :class="[
          'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
          errors.email 
            ? 'border-red-300 bg-red-50' 
            : 'border-gray-300 hover:border-gray-400',
          loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
        ]"
        placeholder="ejemplo@empresa.com"
        aria-describedby="email-error"
        @blur="validateField('email')"
        @input="validateField('email')"
      />
      <p v-if="errors.email" id="email-error" class="mt-1 text-sm text-red-600">
        {{ errors.email }}
      </p>
    </div>

    <!-- Posición completa -->
    <div>
      <label for="position" class="block text-sm font-medium text-gray-700 mb-1">
        Posición <span class="text-red-500">*</span>
      </label>
      <input
        id="position"
        v-model="formData.position"
        type="text"
        :disabled="loading"
        :class="[
          'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
          errors.position 
            ? 'border-red-300 bg-red-50' 
            : 'border-gray-300 hover:border-gray-400',
          loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
        ]"
        placeholder="Ej: Desarrollador Frontend"
        maxlength="100"
        aria-describedby="position-error"
        @blur="validateField('position')"
        @input="validateField('position')"
      />
      <p v-if="errors.position" id="position-error" class="mt-1 text-sm text-red-600">
        {{ errors.position }}
      </p>
      <p class="mt-1 text-xs text-gray-500">
        {{ formData.position.length }}/100 caracteres
      </p>
    </div>

    <!-- Salario en una fila -->
    <div class="grid grid-cols-2 gap-4">
      <!-- Salary Amount -->
      <div>
        <label for="salaryAmount" class="block text-sm font-medium text-gray-700 mb-1">
          Salario <span class="text-red-500">*</span>
        </label>
        <input
          id="salaryAmount"
          v-model.number="formData.salaryAmount"
          type="number"
          step="0.01"
          min="0"
          :disabled="loading"
          :class="[
            'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
            errors.salaryAmount 
              ? 'border-red-300 bg-red-50' 
              : 'border-gray-300 hover:border-gray-400',
            loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
          ]"
          placeholder="0.00"
          aria-describedby="salaryAmount-error"
          @blur="validateField('salaryAmount')"
          @input="validateField('salaryAmount')"
        />
        <p v-if="errors.salaryAmount" id="salaryAmount-error" class="mt-1 text-sm text-red-600">
          {{ errors.salaryAmount }}
        </p>
      </div>

      <!-- Salary Currency -->
      <div>
        <label for="salaryCurrency" class="block text-sm font-medium text-gray-700 mb-1">
          Moneda <span class="text-red-500">*</span>
        </label>
        <select
          id="salaryCurrency"
          v-model="formData.salaryCurrency"
          :disabled="loading"
          :class="[
            'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
            errors.salaryCurrency 
              ? 'border-red-300 bg-red-50' 
              : 'border-gray-300 hover:border-gray-400',
            loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
          ]"
          aria-describedby="salaryCurrency-error"
          @change="validateField('salaryCurrency')"
        >
          <option value="">Seleccionar moneda</option>
          <option value="EUR">EUR - Euro</option>
          <option value="USD">USD - Dólar Americano</option>
          <option value="GBP">GBP - Libra Esterlina</option>
          <option value="CAD">CAD - Dólar Canadiense</option>
          <option value="AUD">AUD - Dólar Australiano</option>
          <option value="JPY">JPY - Yen Japonés</option>
        </select>
        <p v-if="errors.salaryCurrency" id="salaryCurrency-error" class="mt-1 text-sm text-red-600">
          {{ errors.salaryCurrency }}
        </p>
      </div>
    </div>

    <!-- Fecha de contratación -->
    <div>
      <label for="hiredAt" class="block text-sm font-medium text-gray-700 mb-1">
        Fecha de Contratación <span class="text-red-500">*</span>
      </label>
      <input
        id="hiredAt"
        v-model="formData.hiredAt"
        type="date"
        :disabled="loading"
        :max="maxDate"
        :class="[
          'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors',
          errors.hiredAt 
            ? 'border-red-300 bg-red-50' 
            : 'border-gray-300 hover:border-gray-400',
          loading ? 'bg-gray-100 cursor-not-allowed' : 'bg-white'
        ]"
        aria-describedby="hiredAt-error"
        @blur="validateField('hiredAt')"
        @change="validateField('hiredAt')"
      />
      <p v-if="errors.hiredAt" id="hiredAt-error" class="mt-1 text-sm text-red-600">
        {{ errors.hiredAt }}
      </p>
      <p class="mt-1 text-xs text-gray-500">
        La fecha no puede ser futura
      </p>
    </div>

    <!-- Botones de acción -->
    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
      <button
        type="button"
        @click="handleCancel"
        :disabled="loading"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
      >
        Cancelar
      </button>
      <button
        type="submit"
        :disabled="!isValid || loading"
        :class="[
          'px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors',
          !isValid || loading
            ? 'bg-gray-400 cursor-not-allowed'
            : 'bg-blue-600 hover:bg-blue-700'
        ]"
      >
        <span v-if="loading" class="flex items-center">
          <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          Procesando...
        </span>
        <span v-else>
          {{ mode === 'create' ? 'Crear Empleado' : 'Actualizar Empleado' }}
        </span>
      </button>
    </div>
  </form>
  </div>
</template>

<script>
import { ref, reactive, computed, watch, onMounted } from 'vue'

/**
 * EmployeeForm.vue - Formulario reutilizable para crear y editar empleados
 * 
 * Este componente maneja tanto la creación como la edición de empleados
 * con validaciones en tiempo real y una interfaz responsive.
 * 
 * @component
 * @example
 * <!-- Para crear -->
 * <EmployeeForm 
 *   mode="create"
 *   :loading="creating"
 *   @submit="createEmployee"
 *   @cancel="closeModal"
 * />
 * 
 * <!-- Para editar -->
 * <EmployeeForm 
 *   mode="edit"
 *   :employee="selectedEmployee"
 *   :loading="updating"
 *   @submit="updateEmployee"
 *   @cancel="closeModal"
 * />
 */
export default {
  name: 'EmployeeForm',
  props: {
    /**
     * Datos del empleado para edición (null para creación)
     */
    employee: {
      type: Object,
      default: null
    },
    /**
     * Modo del formulario: 'create' o 'edit'
     */
    mode: {
      type: String,
      required: true,
      validator: (value) => ['create', 'edit'].includes(value)
    },
    /**
     * Estado de carga durante submit
     */
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['submit', 'cancel', 'input'],
  setup(props, { emit }) {
    // Datos reactivos del formulario
    const formData = reactive({
      firstName: '',
      lastName: '',
      email: '',
      position: '',
      salaryAmount: null,
      salaryCurrency: '',
      hiredAt: ''
    })

    // Datos iniciales para detectar cambios
    const initialData = ref({})

    // Errores de validación
    const errors = reactive({
      firstName: '',
      lastName: '',
      email: '',
      position: '',
      salaryAmount: '',
      salaryCurrency: '',
      hiredAt: ''
    })

    // Fecha máxima permitida (hoy)
    const maxDate = computed(() => {
      return new Date().toISOString().split('T')[0]
    })

    /**
     * Validaciones por campo
     */
    const validators = {
      firstName: (value) => {
        if (!value || value.trim().length === 0) {
          return 'El nombre es obligatorio'
        }
        if (value.trim().length < 2) {
          return 'El nombre debe tener al menos 2 caracteres'
        }
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value.trim())) {
          return 'El nombre solo puede contener letras y espacios'
        }
        return ''
      },
      lastName: (value) => {
        if (!value || value.trim().length === 0) {
          return 'El apellido es obligatorio'
        }
        if (value.trim().length < 2) {
          return 'El apellido debe tener al menos 2 caracteres'
        }
        if (!/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(value.trim())) {
          return 'El apellido solo puede contener letras y espacios'
        }
        return ''
      },
      email: (value) => {
        if (!value || value.trim().length === 0) {
          return 'El email es obligatorio'
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailRegex.test(value.trim())) {
          return 'Ingrese un email válido'
        }
        return ''
      },
      position: (value) => {
        if (!value || value.trim().length === 0) {
          return 'La posición es obligatoria'
        }
        if (value.trim().length > 100) {
          return 'La posición no puede exceder 100 caracteres'
        }
        return ''
      },
      salaryAmount: (value) => {
        if (value === null || value === undefined || value === '') {
          return 'El salario es obligatorio'
        }
        const numValue = Number(value)
        if (isNaN(numValue) || numValue <= 0) {
          return 'El salario debe ser un número positivo'
        }
        // Validar máximo 2 decimales
        if (!/^\d+(\.\d{1,2})?$/.test(value.toString())) {
          return 'El salario puede tener máximo 2 decimales'
        }
        return ''
      },
      salaryCurrency: (value) => {
        if (!value || value.trim().length === 0) {
          return 'La moneda es obligatoria'
        }
        const validCurrencies = ['EUR', 'USD', 'GBP', 'CAD', 'AUD', 'JPY']
        if (!validCurrencies.includes(value)) {
          return 'Seleccione una moneda válida'
        }
        return ''
      },
      hiredAt: (value) => {
        if (!value || value.trim().length === 0) {
          return 'La fecha de contratación es obligatoria'
        }
        const selectedDate = new Date(value)
        const today = new Date()
        today.setHours(23, 59, 59, 999) // Final del día actual
        
        if (isNaN(selectedDate.getTime())) {
          return 'Ingrese una fecha válida'
        }
        if (selectedDate > today) {
          return 'La fecha no puede ser futura'
        }
        return ''
      }
    }

    /**
     * Validar un campo específico
     */
    const validateField = (fieldName) => {
      if (validators[fieldName]) {
        errors[fieldName] = validators[fieldName](formData[fieldName])
        emit('input', { field: fieldName, value: formData[fieldName], error: errors[fieldName] })
      }
    }

    /**
     * Validar todos los campos
     */
    const validateAllFields = () => {
      Object.keys(validators).forEach(field => {
        validateField(field)
      })
    }

    /**
     * Computed: Formulario válido
     */
    const isValid = computed(() => {
      return Object.keys(validators).every(field => {
        const error = validators[field](formData[field])
        return error === ''
      })
    })

    /**
     * Computed: Formulario modificado
     */
    const isDirty = computed(() => {
      return Object.keys(formData).some(key => {
        return formData[key] !== initialData.value[key]
      })
    })

    /**
     * Resetear formulario
     */
    const resetForm = () => {
      Object.keys(formData).forEach(key => {
        formData[key] = key === 'salaryAmount' ? null : ''
      })
      Object.keys(errors).forEach(key => {
        errors[key] = ''
      })
    }

    /**
     * Cargar datos del empleado para edición
     */
    const loadEmployeeData = (employee) => {
      if (employee) {
        formData.firstName = employee.firstName || ''
        formData.lastName = employee.lastName || ''
        formData.email = employee.email || ''
        formData.position = employee.position || ''
        formData.salaryAmount = employee.salaryAmount || null
        formData.salaryCurrency = employee.salaryCurrency || ''
        
        // Formatear fecha para input date
        if (employee.hiredAt) {
          const date = new Date(employee.hiredAt)
          formData.hiredAt = date.toISOString().split('T')[0]
        } else {
          formData.hiredAt = ''
        }
      }
      
      // Guardar datos iniciales
      initialData.value = { ...formData }
    }

    /**
     * Manejar envío del formulario
     */
    const handleSubmit = () => {
      validateAllFields()
      
      if (isValid.value) {
        // Preparar datos para envío
        const submitData = {
          firstName: formData.firstName.trim(),
          lastName: formData.lastName.trim(),
          email: formData.email.trim(),
          position: formData.position.trim(),
          salaryAmount: Number(formData.salaryAmount),
          salaryCurrency: formData.salaryCurrency,
          hiredAt: formData.hiredAt
        }
        
        emit('submit', submitData)
      }
    }

    /**
     * Manejar cancelación
     */
    const handleCancel = () => {
      if (isDirty.value) {
        const confirmed = confirm('¿Estás seguro de que deseas cancelar? Se perderán los cambios no guardados.')
        if (!confirmed) {
          return
        }
      }
      
      emit('cancel')
    }

    // Watchers
    watch(() => props.employee, (newEmployee) => {
      if (props.mode === 'edit' && newEmployee) {
        loadEmployeeData(newEmployee)
      }
    }, { immediate: true })

    watch(() => props.mode, (newMode) => {
      if (newMode === 'create') {
        resetForm()
      }
    })

    // Lifecycle
    onMounted(() => {
      if (props.mode === 'create') {
        resetForm()
      } else if (props.mode === 'edit' && props.employee) {
        loadEmployeeData(props.employee)
      }
    })

    return {
      formData,
      errors,
      maxDate,
      isValid,
      isDirty,
      validateField,
      handleSubmit,
      handleCancel,
      resetForm
    }
  }
}
</script>