<template>
  <section class="relative bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 min-h-screen flex items-center">
    <!-- Background overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-30"></div>
    
    <!-- Hero content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 leading-tight">
          Gestión de Recursos Humanos 
          <span class="text-orange-400">Moderna</span>
        </h1>
        
        <p class="text-xl md:text-2xl text-blue-100 mb-8 leading-relaxed">
          Optimiza, organiza y potencia tu equipo con nuestra plataforma integral de gestión de recursos humanos
        </p>
        
        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <button 
            @click="$emit('navigate', 'employees')"
            class="bg-orange-500 hover:bg-orange-600 text-white font-semibold py-4 px-8 rounded-lg text-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl"
          >
            Comenzar Ahora
          </button>
          
          <button 
            class="border-2 border-white text-white hover:bg-white hover:text-blue-900 font-semibold py-4 px-8 rounded-lg text-lg transition-all duration-300 transform hover:scale-105"
          >
            Ver Demo
          </button>
        </div>
        
        <!-- Trust indicators -->
        <div class="mt-12 text-blue-200">
          <p class="text-sm mb-4">Confiado por más de 500+ empresas</p>
          <div class="flex justify-center items-center space-x-8 opacity-60">
            <div class="w-20 h-8 bg-white bg-opacity-20 rounded flex items-center justify-center">
              <span class="text-xs font-semibold">EMPRESA</span>
            </div>
            <div class="w-20 h-8 bg-white bg-opacity-20 rounded flex items-center justify-center">
              <span class="text-xs font-semibold">CORP</span>
            </div>
            <div class="w-20 h-8 bg-white bg-opacity-20 rounded flex items-center justify-center">
              <span class="text-xs font-semibold">TECH</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Scroll indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
      </svg>
    </div>
  </section>
</template>

<script>
export default {
  name: 'HeroSection',
  emits: ['navigate']
}
</script>