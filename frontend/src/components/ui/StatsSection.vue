<template>
  <section class="py-16 bg-gradient-to-r from-blue-50 to-indigo-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Números que Hablan por Nosotros
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Descubre por qué miles de empresas confían en nuestra plataforma para gestionar su talento humano
        </p>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <div 
          v-for="(stat, index) in stats" 
          :key="index"
          class="text-center group"
        >
          <!-- Icon -->
          <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full mb-4 group-hover:scale-110 transition-transform duration-300">
            <component :is="stat.icon" class="w-8 h-8 text-white" />
          </div>
          
          <!-- Animated Number -->
          <div class="mb-2">
            <span class="text-4xl md:text-5xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors duration-300">
              {{ animatedValues[index] }}
            </span>
            <span class="text-2xl font-bold text-orange-500">{{ stat.suffix }}</span>
          </div>
          
          <!-- Label -->
          <p class="text-lg font-semibold text-gray-700 mb-1">{{ stat.label }}</p>
          <p class="text-sm text-gray-500">{{ stat.description }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import UsersIcon from '../icons/UsersIcon.vue'
import CalendarIcon from '../icons/CalendarIcon.vue'
import StarIcon from '../icons/StarIcon.vue'
import BuildingIcon from '../icons/BuildingIcon.vue'

export default {
  name: 'StatsSection',
  components: {
    UsersIcon,
    CalendarIcon,
    StarIcon,
    BuildingIcon
  },
  setup() {
    const animatedValues = ref([0, 0, 0, 0])
    
    const stats = [
      {
        value: 1250,
        suffix: '+',
        label: 'Empleados Gestionados',
        description: 'Activos en la plataforma',
        icon: UsersIcon
      },
      {
        value: 5,
        suffix: '+',
        label: 'Años de Experiencia',
        description: 'Mejorando procesos HR',
        icon: CalendarIcon
      },
      {
        value: 98,
        suffix: '%',
        label: 'Satisfacción Cliente',
        description: 'Calificación promedio',
        icon: StarIcon
      },
      {
        value: 500,
        suffix: '+',
        label: 'Empresas Confían',
        description: 'En nuestra solución',
        icon: BuildingIcon
      }
    ]
    
    const animateCounters = () => {
      stats.forEach((stat, index) => {
        const duration = 2000 // 2 seconds
        const steps = 60
        const increment = stat.value / steps
        let current = 0
        
        const timer = setInterval(() => {
          current += increment
          if (current >= stat.value) {
            animatedValues.value[index] = stat.value
            clearInterval(timer)
          } else {
            animatedValues.value[index] = Math.floor(current)
          }
        }, duration / steps)
      })
    }
    
    onMounted(() => {
      // Start animation after a short delay
      setTimeout(animateCounters, 500)
    })
    
    return {
      stats,
      animatedValues
    }
  }
}
</script>