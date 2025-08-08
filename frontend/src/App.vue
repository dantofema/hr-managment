<template>
  <div class="vue-app">
    <div class="max-w-2xl mx-auto p-6 space-y-6">
      <div class="text-center">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">HR System - Vue.js
          Integration (Hot Reload Test)</h1>
        <p class="text-gray-600">Testing Vue.js components with TailwindCSS
          styling - Hot reload test</p>
      </div>
      <Counter/>

    </div>
  </div>
</template>

<script>
import Counter from './components/Counter.vue'

export default {
  name: 'App',
  components: {
    Counter
  }
}
</script>
