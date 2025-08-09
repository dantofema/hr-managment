<template>
  <div>
    <!-- Hero Section -->
    <HeroSection @navigate="handleNavigation" />
    
    <!-- Stats Section -->
    <StatsSection />
    
    <!-- Features Section -->
    <section class="py-16 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
            Funcionalidades Principales
          </h2>
          <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Descubre todas las herramientas que necesitas para gestionar tu equipo de manera eficiente
          </p>
        </div>
        
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
          <FeatureCard
            icon="👥"
            title="Gestión de Empleados"
            description="Administra perfiles completos, documentos y historial laboral de todos tus empleados en un solo lugar."
            action-text="Ver empleados"
            status="available"
            @action="handleNavigation('employees')"
          />
          
          <FeatureCard
            icon="📊"
            title="Nóminas Inteligentes"
            description="Automatiza el cálculo de nóminas, deducciones y beneficios con total precisión y cumplimiento legal."
            action-text="Próximamente"
            status="coming-soon"
          />
          
          <FeatureCard
            icon="🏖️"
            title="Gestión de Vacaciones"
            description="Simplifica las solicitudes, aprobaciones y seguimiento de vacaciones y permisos laborales."
            action-text="Próximamente"
            status="coming-soon"
          />
          
          <FeatureCard
            icon="📈"
            title="Reportes y Analytics"
            description="Obtén insights valiosos sobre tu equipo con reportes detallados y métricas de rendimiento."
            action-text="Próximamente"
            status="coming-soon"
          />
          
          <FeatureCard
            icon="🎯"
            title="Evaluaciones de Desempeño"
            description="Implementa procesos de evaluación estructurados para el crecimiento profesional de tu equipo."
            action-text="Próximamente"
            status="coming-soon"
          />
          
          <FeatureCard
            icon="🔐"
            title="Seguridad Avanzada"
            description="Protege la información sensible con encriptación de nivel empresarial y controles de acceso."
            action-text="Próximamente"
            status="coming-soon"
          />
        </div>
      </div>
    </section>
    
    <!-- Testimonials Section -->
    <TestimonialsSection />
    
    <!-- Final CTA Section -->
    <CTASection
      title="¿Listo para Transformar tu Gestión de RRHH?"
      description="Únete a miles de empresas que ya optimizaron sus procesos de recursos humanos con nuestra plataforma integral."
      primary-action="Comenzar Prueba Gratuita"
      secondary-action="Solicitar Demo"
      @primary-action="handleNavigation('employees')"
      @secondary-action="handleDemo"
    />
  </div>
</template>

<script>
import HeroSection from './ui/HeroSection.vue'
import StatsSection from './ui/StatsSection.vue'
import FeatureCard from './ui/FeatureCard.vue'
import TestimonialsSection from './ui/TestimonialsSection.vue'
import CTASection from './ui/CTASection.vue'

export default {
  name: 'Home',
  components: {
    HeroSection,
    StatsSection,
    FeatureCard,
    TestimonialsSection,
    CTASection
  },
  methods: {
    handleNavigation(view) {
      this.$emit('navigate', view)
    },
    handleDemo() {
      // Handle demo request - could open modal, redirect, etc.
      alert('Demo solicitado - Funcionalidad próximamente disponible')
    }
  },
  emits: ['navigate']
}
</script>