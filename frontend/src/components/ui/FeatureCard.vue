<template>
  <div class="group bg-white rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2 overflow-hidden">
    <!-- Feature Image/Icon -->
    <div class="h-48 bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-transparent to-blue-100 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
      <div class="relative z-10 text-6xl transform group-hover:scale-110 transition-transform duration-300">
        {{ icon }}
      </div>
    </div>
    
    <!-- Card Content -->
    <div class="p-6">
      <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-700 transition-colors duration-300">
        {{ title }}
      </h3>
      
      <p class="text-gray-600 mb-4 leading-relaxed">
        {{ description }}
      </p>
      
      <!-- Action Button -->
      <button 
        v-if="actionText"
        @click="handleAction"
        :class="[
          'font-semibold transition-all duration-300 flex items-center group/btn',
          isDisabled 
            ? 'text-gray-400 cursor-not-allowed' 
            : 'text-blue-600 hover:text-blue-800 group-hover:text-orange-600'
        ]"
        :disabled="isDisabled"
      >
        {{ actionText }}
        <svg 
          v-if="!isDisabled" 
          class="w-4 h-4 ml-2 transform group-hover/btn:translate-x-1 transition-transform duration-300" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
        </svg>
      </button>
      
      <!-- Status Badge -->
      <div v-if="status" class="mt-4">
        <span 
          :class="[
            'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium',
            status === 'available' 
              ? 'bg-green-100 text-green-800' 
              : 'bg-yellow-100 text-yellow-800'
          ]"
        >
          {{ status === 'available' ? 'Disponible' : 'Próximamente' }}
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FeatureCard',
  props: {
    icon: {
      type: String,
      required: true
    },
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      required: true
    },
    actionText: {
      type: String,
      default: null
    },
    status: {
      type: String,
      default: 'available',
      validator: (value) => ['available', 'coming-soon'].includes(value)
    }
  },
  computed: {
    isDisabled() {
      return this.status === 'coming-soon'
    }
  },
  methods: {
    handleAction() {
      if (!this.isDisabled) {
        this.$emit('action')
      }
    }
  },
  emits: ['action']
}
</script>