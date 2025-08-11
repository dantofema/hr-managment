<template>
  <div id="modal-test-app" class="p-8">
    <h1 class="text-2xl font-bold mb-4">BaseModal Test Page</h1>
    <button 
      id="open-modal" 
      @click="showModal = true"
      class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
    >
      Abrir Modal
    </button>
    
    <BaseModal 
      :isOpen="showModal" 
      title="Modal de Prueba" 
      size="md"
      @close="showModal = false"
    >
      <template #default>
        <p id="modal-content">Este es el contenido del modal de prueba</p>
      </template>
      <template #footer>
        <div class="flex justify-end space-x-2">
          <button 
            id="save-btn" 
            @click="save"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
          >
            Guardar
          </button>
          <button 
            id="cancel-btn" 
            @click="showModal = false"
            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
          >
            Cancelar
          </button>
        </div>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import { ref } from 'vue'
import BaseModal from './ui/BaseModal.vue'

export default {
  name: 'BaseModalTest',
  components: {
    BaseModal
  },
  setup() {
    const showModal = ref(false)
    
    const save = () => {
      console.log('Save clicked')
      showModal.value = false
    }
    
    return {
      showModal,
      save
    }
  }
}
</script>