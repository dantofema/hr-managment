<template>
  <div class="counter-component bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-xl border border-purple-200 shadow-lg">
    <h3 class="text-2xl font-bold text-purple-800 mb-4 text-center">Vue.js Counter Test</h3>
    
    <div class="flex items-center justify-center space-x-6 mb-4">
      <button 
        @click="decrement" 
        class="bg-red-500 hover:bg-red-600 text-white font-bold py-3 px-6 rounded-full text-xl transition-colors duration-200 shadow-md hover:shadow-lg"
        :disabled="count <= 0"
        :class="{ 'opacity-50 cursor-not-allowed': count <= 0 }"
      >
        -
      </button>
      
      <div class="text-4xl font-bold text-purple-700 min-w-[80px] text-center">
        {{ count }}
      </div>
      
      <button 
        @click="increment" 
        class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-full text-xl transition-colors duration-200 shadow-md hover:shadow-lg"
      >
        +
      </button>
    </div>
    
    <div class="text-center">
      <p class="text-sm text-gray-600 mb-2">
        Total clicks: {{ totalClicks }}
      </p>
      <button 
        @click="reset" 
        class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded text-sm transition-colors duration-200"
      >
        Reset
      </button>
    </div>
    
    <div class="mt-4 text-center">
      <p class="text-xs text-green-600 font-medium">
        ✅ Vue.js is working correctly!
      </p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Counter',
  data() {
    return {
      count: 0,
      totalClicks: 0
    }
  },
  methods: {
    increment() {
      this.count++
      this.totalClicks++
    },
    decrement() {
      if (this.count > 0) {
        this.count--
        this.totalClicks++
      }
    },
    reset() {
      this.count = 0
      this.totalClicks = 0
    }
  }
}
</script>