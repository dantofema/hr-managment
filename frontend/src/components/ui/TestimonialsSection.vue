<template>
  <section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Header -->
      <div class="text-center mb-16">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
          Lo que Dicen Nuestros Clientes
        </h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
          Descubre por qué las empresas líderes eligen nuestra plataforma para transformar su gestión de recursos humanos
        </p>
      </div>
      
      <!-- Testimonials Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
        <div 
          v-for="(testimonial, index) in testimonials" 
          :key="index"
          class="bg-gray-50 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300"
        >
          <!-- Quote -->
          <div class="mb-4">
            <svg class="w-8 h-8 text-blue-500 mb-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
            </svg>
            <p class="text-gray-700 leading-relaxed">
              "{{ testimonial.quote }}"
            </p>
          </div>
          
          <!-- Author -->
          <div class="flex items-center">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-lg mr-4">
              {{ testimonial.initials }}
            </div>
            <div>
              <p class="font-semibold text-gray-900">{{ testimonial.name }}</p>
              <p class="text-sm text-gray-600">{{ testimonial.position }}</p>
              <p class="text-sm text-blue-600">{{ testimonial.company }}</p>
            </div>
          </div>
          
          <!-- Rating -->
          <div class="flex items-center mt-4">
            <div class="flex text-yellow-400">
              <svg v-for="i in 5" :key="i" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
              </svg>
            </div>
            <span class="ml-2 text-sm text-gray-600">5.0</span>
          </div>
        </div>
      </div>
      
      <!-- Trust Logos -->
      <div class="border-t border-gray-200 pt-12">
        <p class="text-center text-gray-600 mb-8 font-medium">
          Empresas que confían en nuestra plataforma
        </p>
        
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-8 items-center justify-items-center opacity-60">
          <div 
            v-for="(logo, index) in trustLogos" 
            :key="index"
            class="bg-gray-100 rounded-lg p-4 w-24 h-16 flex items-center justify-center hover:opacity-100 transition-opacity duration-300"
          >
            <span class="text-xs font-bold text-gray-600 text-center">{{ logo }}</span>
          </div>
        </div>
      </div>
      
      <!-- Certifications/Badges -->
      <div class="mt-12 flex flex-wrap justify-center items-center gap-6">
        <div class="flex items-center bg-green-50 rounded-full px-4 py-2">
          <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
          </svg>
          <span class="text-sm font-medium text-green-700">ISO 27001 Certificado</span>
        </div>
        
        <div class="flex items-center bg-blue-50 rounded-full px-4 py-2">
          <svg class="w-5 h-5 text-blue-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
          </svg>
          <span class="text-sm font-medium text-blue-700">GDPR Compliant</span>
        </div>
        
        <div class="flex items-center bg-purple-50 rounded-full px-4 py-2">
          <svg class="w-5 h-5 text-purple-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
          </svg>
          <span class="text-sm font-medium text-purple-700">99.9% Uptime</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  name: 'TestimonialsSection',
  data() {
    return {
      testimonials: [
        {
          quote: "Esta plataforma transformó completamente nuestra gestión de recursos humanos. La eficiencia aumentó un 300% en el primer mes.",
          name: "María González",
          position: "Directora de RRHH",
          company: "TechCorp Solutions",
          initials: "MG"
        },
        {
          quote: "La interfaz es intuitiva y el soporte técnico excepcional. Nuestros empleados adoptaron el sistema sin problemas.",
          name: "Carlos Rodríguez",
          position: "CEO",
          company: "Innovate Labs",
          initials: "CR"
        },
        {
          quote: "Los reportes automáticos nos ahorran horas de trabajo cada semana. Una inversión que se paga sola.",
          name: "Ana Martínez",
          position: "Gerente de Operaciones",
          company: "Global Enterprises",
          initials: "AM"
        }
      ],
      trustLogos: [
        "TECHCORP",
        "INNOVATE",
        "GLOBAL ENT",
        "STARTUP CO",
        "MEGA CORP",
        "FUTURE TECH"
      ]
    }
  }
}
</script>