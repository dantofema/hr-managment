<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Test BaseModal Component</h1>
    
    <div class="space-y-4">
      <button 
        @click="showSmallModal = true"
        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
      >
        Abrir Modal Pequeño (sm)
      </button>
      
      <button 
        @click="showMediumModal = true"
        class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
      >
        Abrir Modal Mediano (md)
      </button>
      
      <button 
        @click="showLargeModal = true"
        class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
      >
        Abrir Modal Grande (lg)
      </button>
      
      <button 
        @click="showXLModal = true"
        class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
      >
        Abrir Modal Extra Grande (xl)
      </button>
    </div>

    <!-- Modal Pequeño -->
    <BaseModal 
      :isOpen="showSmallModal" 
      title="Modal Pequeño" 
      size="sm"
      @close="showSmallModal = false"
    >
      <template #default>
        <p>Este es un modal pequeño para contenido mínimo.</p>
        <input type="text" placeholder="Campo de prueba" class="mt-4 w-full p-2 border rounded">
      </template>
      <template #footer>
        <div class="flex justify-end space-x-2">
          <button 
            @click="showSmallModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
          >
            Cancelar
          </button>
          <button 
            @click="handleSave('small')"
            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
          >
            Guardar
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Modal Mediano -->
    <BaseModal 
      :isOpen="showMediumModal" 
      title="Modal Mediano" 
      size="md"
      @close="showMediumModal = false"
    >
      <template #default>
        <p>Este es un modal mediano con más contenido.</p>
        <div class="mt-4 space-y-4">
          <input type="text" placeholder="Nombre" class="w-full p-2 border rounded">
          <input type="email" placeholder="Email" class="w-full p-2 border rounded">
          <textarea placeholder="Descripción" class="w-full p-2 border rounded h-20"></textarea>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-end space-x-2">
          <button 
            @click="showMediumModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
          >
            Cancelar
          </button>
          <button 
            @click="handleSave('medium')"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          >
            Guardar
          </button>
        </div>
      </template>
    </BaseModal>

    <!-- Modal Grande -->
    <BaseModal 
      :isOpen="showLargeModal" 
      title="Modal Grande" 
      size="lg"
      @close="showLargeModal = false"
    >
      <template #default>
        <p>Este es un modal grande con mucho contenido.</p>
        <div class="mt-4 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <input type="text" placeholder="Nombre" class="p-2 border rounded">
            <input type="text" placeholder="Apellido" class="p-2 border rounded">
          </div>
          <input type="email" placeholder="Email" class="w-full p-2 border rounded">
          <input type="tel" placeholder="Teléfono" class="w-full p-2 border rounded">
          <textarea placeholder="Descripción larga" class="w-full p-2 border rounded h-32"></textarea>
          <div class="flex items-center space-x-2">
            <input type="checkbox" id="terms">
            <label for="terms">Acepto los términos y condiciones</label>
          </div>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-between">
          <button 
            @click="showLargeModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
          >
            Cancelar
          </button>
          <div class="space-x-2">
            <button 
              @click="handleSave('large')"
              class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600"
            >
              Guardar
            </button>
            <button 
              @click="handleSaveAndContinue('large')"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
            >
              Guardar y Continuar
            </button>
          </div>
        </div>
      </template>
    </BaseModal>

    <!-- Modal Extra Grande -->
    <BaseModal 
      :isOpen="showXLModal" 
      title="Modal Extra Grande" 
      size="xl"
      @close="showXLModal = false"
    >
      <template #default>
        <p>Este es un modal extra grande para formularios complejos.</p>
        <div class="mt-4 space-y-6">
          <div class="grid grid-cols-3 gap-4">
            <input type="text" placeholder="Nombre" class="p-2 border rounded">
            <input type="text" placeholder="Apellido" class="p-2 border rounded">
            <input type="text" placeholder="DNI" class="p-2 border rounded">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <input type="email" placeholder="Email" class="p-2 border rounded">
            <input type="tel" placeholder="Teléfono" class="p-2 border rounded">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <input type="text" placeholder="Dirección" class="p-2 border rounded">
            <input type="text" placeholder="Ciudad" class="p-2 border rounded">
          </div>
          <textarea placeholder="Notas adicionales" class="w-full p-2 border rounded h-24"></textarea>
          <div class="border p-4 rounded">
            <h3 class="font-semibold mb-2">Configuraciones</h3>
            <div class="space-y-2">
              <label class="flex items-center space-x-2">
                <input type="checkbox">
                <span>Recibir notificaciones por email</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox">
                <span>Recibir notificaciones por SMS</span>
              </label>
              <label class="flex items-center space-x-2">
                <input type="checkbox">
                <span>Permitir contacto comercial</span>
              </label>
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <div class="flex justify-between">
          <button 
            @click="showXLModal = false"
            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400"
          >
            Cancelar
          </button>
          <div class="space-x-2">
            <button 
              @click="handleSave('xl')"
              class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
            >
              Guardar
            </button>
          </div>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import { ref } from 'vue'
import BaseModal from './ui/BaseModal.vue'

export default {
  name: 'ModalTest',
  components: {
    BaseModal
  },
  setup() {
    const showSmallModal = ref(false)
    const showMediumModal = ref(false)
    const showLargeModal = ref(false)
    const showXLModal = ref(false)

    const handleSave = (size) => {
      alert(`Guardado desde modal ${size}`)
      // Cerrar el modal correspondiente
      switch(size) {
        case 'small':
          showSmallModal.value = false
          break
        case 'medium':
          showMediumModal.value = false
          break
        case 'large':
          showLargeModal.value = false
          break
        case 'xl':
          showXLModal.value = false
          break
      }
    }

    const handleSaveAndContinue = (size) => {
      alert(`Guardado y continuando desde modal ${size}`)
      // No cerrar el modal para simular "continuar"
    }

    return {
      showSmallModal,
      showMediumModal,
      showLargeModal,
      showXLModal,
      handleSave,
      handleSaveAndContinue
    }
  }
}
</script>