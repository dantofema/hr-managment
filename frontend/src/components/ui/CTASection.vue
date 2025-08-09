<template>
  <section class="relative py-16 overflow-hidden">
    <!-- Background with gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900"></div>
    <div class="absolute inset-0 bg-black bg-opacity-20"></div>
    
    <!-- Decorative elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden">
      <div class="absolute -top-10 -right-10 w-40 h-40 bg-orange-500 rounded-full opacity-10 animate-pulse"></div>
      <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-blue-400 rounded-full opacity-10 animate-pulse delay-1000"></div>
    </div>
    
    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <!-- Main Content -->
      <div class="mb-8">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4 leading-tight">
          {{ title }}
        </h2>
        
        <p class="text-xl text-blue-100 mb-8 leading-relaxed max-w-2xl mx-auto">
          {{ description }}
        </p>
      </div>
      
      <!-- CTA Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-8">
        <button 
          v-if="primaryAction"
          @click="$emit('primary-action')"
          class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-4 px-8 rounded-lg text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl min-w-[200px]"
        >
          {{ primaryAction }}
        </button>
        
        <button 
          v-if="secondaryAction"
          @click="$emit('secondary-action')"
          class="border-2 border-white text-white hover:bg-white hover:text-blue-900 font-semibold py-4 px-8 rounded-lg text-lg transition-all duration-300 transform hover:scale-105 min-w-[200px]"
        >
          {{ secondaryAction }}
        </button>
      </div>
      
      <!-- Additional Info -->
      <div v-if="showFeatures" class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
        <div class="flex items-center justify-center text-blue-100">
          <svg class="w-6 h-6 mr-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span class="font-medium">Setup en 5 minutos</span>
        </div>
        
        <div class="flex items-center justify-center text-blue-100">
          <svg class="w-6 h-6 mr-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span class="font-medium">Soporte 24/7</span>
        </div>
        
        <div class="flex items-center justify-center text-blue-100">
          <svg class="w-6 h-6 mr-3 text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
          </svg>
          <span class="font-medium">Garantía 30 días</span>
        </div>
      </div>
      
      <!-- Trust indicators -->
      <div v-if="showTrust" class="mt-8 text-blue-200">
        <p class="text-sm mb-4">{{ trustText || 'Más de 10,000 profesionales HR ya confían en nosotros' }}</p>
        <div class="flex justify-center items-center space-x-2">
          <div v-for="i in 5" :key="i" class="text-yellow-400">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
          </div>
          <span class="ml-2 text-sm">4.9/5 estrellas</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'CTASection',
  props: {
    title: {
      type: String,
      required: true
    },
    description: {
      type: String,
      required: true
    },
    primaryAction: {
      type: String,
      default: null
    },
    secondaryAction: {
      type: String,
      default: null
    },
    showFeatures: {
      type: Boolean,
      default: true
    },
    showTrust: {
      type: Boolean,
      default: true
    },
    trustText: {
      type: String,
      default: null
    }
  },
  emits: ['primary-action', 'secondary-action']
}
</script>