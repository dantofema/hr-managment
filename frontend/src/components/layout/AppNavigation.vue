<template>
  <nav class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex items-center">
          <h1 class="text-xl font-semibold text-gray-900">HR System</h1>
        </div>
        <div class="flex items-center space-x-4">
          <button
              :class="[
              'px-3 py-2 rounded-md text-sm font-medium transition-colors',
              currentView === 'home'
                ? 'bg-blue-100 text-blue-700'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
              @click="$emit('navigate', 'home')"
          >
            Inicio
          </button>
          <button
              :class="[
              'px-3 py-2 rounded-md text-sm font-medium transition-colors',
              currentView === 'employees'
                ? 'bg-blue-100 text-blue-700'
                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
            ]"
              @click="$emit('navigate', 'employees')"
          >
            Empleados
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'AppNavigation',
  props: {
    currentView: {
      type: String,
      required: true
    }
  },
  emits: ['navigate']
}
</script>