<template>
  <div class="relative">
    <!-- User Menu Button -->
    <button
      @click="toggleMenu"
      @keydown.escape="closeMenu"
      class="flex items-center space-x-2 text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 p-2 hover:bg-gray-100 transition-colors"
      :aria-expanded="isOpen"
      aria-haspopup="true"
    >
      <!-- User Avatar -->
      <div class="h-8 w-8 rounded-full bg-blue-600 flex items-center justify-center">
        <span class="text-white text-sm font-medium">
          {{ userInitials }}
        </span>
      </div>
      
      <!-- User Name (hidden on small screens) -->
      <span class="hidden md:block text-gray-700 font-medium">
        {{ displayName }}
      </span>
      
      <!-- Dropdown Arrow -->
      <svg 
        class="h-4 w-4 text-gray-400 transition-transform duration-200"
        :class="{ 'rotate-180': isOpen }"
        fill="none" 
        viewBox="0 0 24 24" 
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
        role="menu"
        aria-orientation="vertical"
      >
        <div class="py-1">
          <!-- User Info Section -->
          <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-900">{{ user?.name || 'Usuario' }}</p>
            <p class="text-sm text-gray-500 truncate">{{ user?.email }}</p>
            <p v-if="user?.role" class="text-xs text-gray-400 mt-1 capitalize">{{ user.role }}</p>
          </div>

          <!-- Menu Items -->
          <div class="py-1">
            <button
              @click="handleProfile"
              class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
              role="menuitem"
            >
              <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Mi Perfil
            </button>

            <button
              @click="handleSettings"
              class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
              role="menuitem"
            >
              <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Configuración
            </button>

            <!-- Divider -->
            <div class="border-t border-gray-100 my-1"></div>

            <button
              @click="handleLogout"
              class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50 hover:text-red-900 transition-colors"
              role="menuitem"
            >
              <svg class="mr-3 h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
              Cerrar Sesión
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- Backdrop for mobile -->
    <div
      v-if="isOpen"
      @click="closeMenu"
      class="fixed inset-0 z-40 md:hidden"
      aria-hidden="true"
    ></div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'

export default {
  name: 'UserMenu',
  props: {
    user: {
      type: Object,
      required: true
    }
  },
  emits: ['logout', 'profile', 'settings'],
  setup(props, { emit }) {
    const isOpen = ref(false)

    const displayName = computed(() => {
      return props.user?.name || props.user?.email?.split('@')[0] || 'Usuario'
    })

    const userInitials = computed(() => {
      if (props.user?.name) {
        return props.user.name
          .split(' ')
          .map(name => name.charAt(0))
          .join('')
          .toUpperCase()
          .substring(0, 2)
      }
      if (props.user?.email) {
        return props.user.email.charAt(0).toUpperCase()
      }
      return 'U'
    })

    const toggleMenu = () => {
      isOpen.value = !isOpen.value
    }

    const closeMenu = () => {
      isOpen.value = false
    }

    const handleProfile = () => {
      closeMenu()
      emit('profile')
    }

    const handleSettings = () => {
      closeMenu()
      emit('settings')
    }

    const handleLogout = () => {
      closeMenu()
      emit('logout')
    }

    // Close menu when clicking outside
    const handleClickOutside = (event) => {
      if (isOpen.value && !event.target.closest('.relative')) {
        closeMenu()
      }
    }

    // Close menu on escape key
    const handleEscapeKey = (event) => {
      if (event.key === 'Escape' && isOpen.value) {
        closeMenu()
      }
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
      document.addEventListener('keydown', handleEscapeKey)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
      document.removeEventListener('keydown', handleEscapeKey)
    })

    return {
      isOpen,
      displayName,
      userInitials,
      toggleMenu,
      closeMenu,
      handleProfile,
      handleSettings,
      handleLogout
    }
  }
}
</script>