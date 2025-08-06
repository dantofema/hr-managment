<template>
  <div
      v-if="isOpen"
      aria-labelledby="modal-title"
      aria-modal="true"
      class="fixed inset-0 z-50 overflow-y-auto"
      role="dialog"
  >
    <!-- Background overlay -->
    <div
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="closeModal"
    ></div>

    <!-- Modal container -->
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
      <div
          class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl"
          @click.stop
      >
        <!-- Header -->
        <div class="bg-white px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 id="modal-title"
                class="text-lg font-medium text-gray-900">
              Detalles del Empleado
            </h3>
            <button
                class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
                @click="closeModal"
            >
              <svg class="h-6 w-6"
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
        </div>

        <!-- Content -->
        <div v-if="employee"
             class="bg-white px-6 py-4">
          <!-- Employee Avatar and Basic Info -->
          <div class="flex items-center mb-6">
            <div class="flex-shrink-0 h-20 w-20">
              <div class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center">
                <span class="text-2xl font-bold text-white">
                  {{ getInitials(employee.name) }}
                </span>
              </div>
            </div>
            <div class="ml-6">
              <h2 class="text-2xl font-bold text-gray-900">{{
                  employee.name
                }}</h2>
              <div class="flex items-center mt-1">
                <svg class="h-4 w-4 text-gray-400 mr-1"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                  <path d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"/>
                </svg>
                <span class="text-gray-600">{{ employee.email }}</span>
              </div>
              <div class="mt-2">
                <span :class="getStatusClass(employee.status)"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ getStatusText(employee.status) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Employee Details Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Basic Information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                <svg class="h-4 w-4 text-gray-400 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                  <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"/>
                </svg>
                Información Personal
              </h3>
              <div class="space-y-3">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    ID
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ employee.id }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    Nombre Completo
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900">{{
                      employee.name
                    }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    Email
                  </dt>
                  <dd class="mt-1 text-sm text-blue-600">
                    <a :href="`mailto:${employee.email}`"
                       class="hover:text-blue-500">
                      {{ employee.email }}
                    </a>
                  </dd>
                </div>
              </div>
            </div>

            <!-- Work Information -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                <svg class="h-4 w-4 text-gray-400 mr-2"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                  <path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0v2a2 2 0 002 2v8a2 2 0 01-2 2H8a2 2 0 01-2-2v-8a2 2 0 012-2V8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"/>
                </svg>
                Información Laboral
              </h3>
              <div class="space-y-3">
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    Departamento
                  </dt>
                  <dd class="mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ employee.department }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    Rol/Posición
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900">{{
                      employee.role
                    }}
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                    Estado
                  </dt>
                  <dd class="mt-1">
                    <span :class="getStatusClass(employee.status)"
                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                      {{ getStatusText(employee.status) }}
                    </span>
                  </dd>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
              <svg class="h-4 w-4 text-gray-400 mr-2"
                   fill="none"
                   stroke="currentColor"
                   viewBox="0 0 24 24">
                <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"/>
              </svg>
              Acciones Rápidas
            </h3>
            <div class="flex flex-wrap gap-3">
              <button
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  @click="sendEmail"
              >
                <svg class="h-4 w-4 mr-2 text-gray-400"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                  <path d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"/>
                </svg>
                Enviar Email
              </button>

              <select
                  :value="employee.status"
                  class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                  @change="updateEmployeeStatus($event.target.value)"
              >
                <option value="active">Cambiar a Activo</option>
                <option value="inactive">Cambiar a Inactivo</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-3 flex justify-end space-x-3">
          <button
              class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              @click="closeModal"
          >
            Cerrar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {defineEmits, defineProps} from 'vue';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  employee: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['close', 'update-status']);

const closeModal = () => {
  emit('close');
};

const getInitials = (name) => {
  if (!name) return '';
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

const sendEmail = () => {
  if (props.employee?.email) {
    window.open(`mailto:${props.employee.email}`, '_blank');
  }
};

const updateEmployeeStatus = (newStatus) => {
  if (newStatus !== props.employee.status) {
    emit('update-status', props.employee.id, newStatus);
  }
};
</script>
