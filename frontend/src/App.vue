<template>
  <div class="vue-component bg-blue-50 p-4 rounded-lg border border-blue-200">
    <h2 class="text-xl font-bold text-blue-800 mb-2">Vue.js Component Test</h2>
    <p class="text-blue-600 mb-4">{{ message }}</p>
    <button 
      @click="updateMessage" 
      class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
    >
      {{ buttonText }}
    </button>
    <p class="text-sm text-gray-600 mt-2">Click count: {{ clickCount }}</p>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      message: 'Hello from Vue.js! Integration is working.',
      buttonText: 'Click me!',
      clickCount: 0
    }
  },
  methods: {
    updateMessage() {
      this.clickCount++
      this.message = `Vue.js is reactive! Clicked ${this.clickCount} times.`
    }
  }
}
</script>
