<template>
  <!-- Modal Backdrop -->
  <Teleport to="body">
    <Transition
      name="modal"
      enter-active-class="transition-opacity duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50"
        @click="handleBackdropClick"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
      >
        <!-- Modal Content -->
        <Transition
          name="modal-content"
          enter-active-class="transition-all duration-300 ease-out"
          enter-from-class="opacity-0 scale-95 translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-active-class="transition-all duration-200 ease-in"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 translate-y-4"
        >
          <div
            v-if="isOpen"
            ref="modalContent"
            :class="modalSizeClasses"
            class="relative w-full bg-white rounded-lg shadow-xl overflow-hidden"
            @click.stop
          >
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
              <h2 :id="titleId" class="text-xl font-semibold text-gray-900">
                {{ title }}
              </h2>
              <button
                type="button"
                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                @click="closeModal"
                aria-label="Cerrar modal"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 max-h-96 overflow-y-auto">
              <slot />
            </div>

            <!-- Modal Footer -->
            <div v-if="$slots.footer" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
              <slot name="footer" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<script>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'

/**
 * BaseModal - Componente modal base reutilizable
 * 
 * Características principales:
 * - Cierre con backdrop click y tecla ESC
 * - Animaciones suaves de entrada/salida
 * - Focus trap para accesibilidad
 * - Scroll lock del body
 * - Responsive design
 * - Múltiples tamaños configurables
 * 
 * @example
 * <BaseModal 
 *   :isOpen="showModal" 
 *   title="Mi Modal" 
 *   size="lg"
 *   @close="showModal = false"
 * >
 *   <template #default>
 *     Contenido del modal
 *   </template>
 *   <template #footer>
 *     <button>Guardar</button>
 *   </template>
 * </BaseModal>
 */
export default {
  name: 'BaseModal',
  props: {
    /**
     * Controla la visibilidad del modal
     */
    isOpen: {
      type: Boolean,
      required: true
    },
    /**
     * Título que se muestra en el header del modal
     */
    title: {
      type: String,
      required: true
    },
    /**
     * Tamaño del modal: 'sm', 'md', 'lg', 'xl'
     */
    size: {
      type: String,
      default: 'md',
      validator: (value) => ['sm', 'md', 'lg', 'xl'].includes(value)
    }
  },
  emits: ['close'],
  setup(props, { emit }) {
    const modalContent = ref(null)
    const titleId = `modal-title-${Math.random().toString(36).substr(2, 9)}`
    
    // Elementos focusables para el focus trap
    const focusableElements = ref([])
    const firstFocusableElement = ref(null)
    const lastFocusableElement = ref(null)

    /**
     * Clases CSS para los diferentes tamaños de modal
     */
    const modalSizeClasses = computed(() => {
      const sizeMap = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl'
      }
      return sizeMap[props.size] || sizeMap.md
    })

    /**
     * Cierra el modal emitiendo el evento close
     */
    const closeModal = () => {
      emit('close')
    }

    /**
     * Maneja el click en el backdrop para cerrar el modal
     */
    const handleBackdropClick = (event) => {
      if (event.target === event.currentTarget) {
        closeModal()
      }
    }

    /**
     * Maneja el evento de teclado para cerrar con ESC y focus trap
     */
    const handleKeydown = (event) => {
      if (event.key === 'Escape') {
        closeModal()
        return
      }

      // Focus trap - manejar Tab y Shift+Tab
      if (event.key === 'Tab') {
        if (focusableElements.value.length === 0) return

        if (event.shiftKey) {
          // Shift + Tab - ir hacia atrás
          if (document.activeElement === firstFocusableElement.value) {
            event.preventDefault()
            lastFocusableElement.value?.focus()
          }
        } else {
          // Tab - ir hacia adelante
          if (document.activeElement === lastFocusableElement.value) {
            event.preventDefault()
            firstFocusableElement.value?.focus()
          }
        }
      }
    }

    /**
     * Configura el focus trap cuando el modal se abre
     */
    const setupFocusTrap = async () => {
      await nextTick()
      
      if (!modalContent.value) return

      // Buscar elementos focusables
      const focusableSelectors = [
        'button:not([disabled])',
        'input:not([disabled])',
        'textarea:not([disabled])',
        'select:not([disabled])',
        'a[href]',
        '[tabindex]:not([tabindex="-1"])'
      ]

      focusableElements.value = Array.from(
        modalContent.value.querySelectorAll(focusableSelectors.join(', '))
      )

      if (focusableElements.value.length > 0) {
        firstFocusableElement.value = focusableElements.value[0]
        lastFocusableElement.value = focusableElements.value[focusableElements.value.length - 1]
        
        // Enfocar el primer elemento focusable
        firstFocusableElement.value?.focus()
      }
    }

    /**
     * Previene el scroll del body cuando el modal está abierto
     */
    const toggleBodyScroll = (lock) => {
      if (lock) {
        document.body.style.overflow = 'hidden'
        document.body.style.paddingRight = `${window.innerWidth - document.documentElement.clientWidth}px`
      } else {
        document.body.style.overflow = ''
        document.body.style.paddingRight = ''
      }
    }

    // Watcher para manejar la apertura/cierre del modal
    watch(() => props.isOpen, (newValue) => {
      if (newValue) {
        toggleBodyScroll(true)
        setupFocusTrap()
        document.addEventListener('keydown', handleKeydown)
      } else {
        toggleBodyScroll(false)
        document.removeEventListener('keydown', handleKeydown)
      }
    }, { immediate: true })

    // Cleanup al desmontar el componente
    onUnmounted(() => {
      toggleBodyScroll(false)
      document.removeEventListener('keydown', handleKeydown)
    })

    return {
      modalContent,
      titleId,
      modalSizeClasses,
      closeModal,
      handleBackdropClick
    }
  }
}
</script>

<style scoped>
/* Estilos adicionales para las transiciones si son necesarios */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-content-enter-active,
.modal-content-leave-active {
  transition: all 0.3s ease;
}
</style>