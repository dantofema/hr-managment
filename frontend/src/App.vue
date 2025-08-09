<template>
  <div class="vue-app min-h-screen bg-gray-50">
    <!-- Navigation Header -->
    <AppNavigation :current-view="currentView" @navigate="handleNavigation" />

    <!-- Main Content -->
    <main class="flex-1">
      <!-- Home View -->
      <Home v-if="currentView === 'home'" @navigate="handleNavigation" />

      <!-- Employees View -->
      <div v-else-if="currentView === 'employees'">
        <EmployeesList/>
      </div>
    </main>

    <!-- Footer -->
    <AppFooter />
  </div>
</template>

<script>
import {ref} from 'vue'
import Home from './components/Home.vue'
import Counter from './components/Counter.vue'
import EmployeesList from './components/EmployeesList.vue'
import AppNavigation from './components/layout/AppNavigation.vue'
import AppFooter from './components/layout/AppFooter.vue'

export default {
  name: 'App',
  components: {
    Home,
    Counter,
    EmployeesList,
    AppNavigation,
    AppFooter
  },
  setup() {
    // Current view state - simple routing alternative
    const currentView = ref('home')

    const handleNavigation = (view) => {
      currentView.value = view
    }

    return {
      currentView,
      handleNavigation
    }
  }
}
</script>

<style>
/* Global styles if needed */
.vue-app {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
</style>