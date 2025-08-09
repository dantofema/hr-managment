<template>
  <div class="login-view">
    <Login 
      :on-login-success="handleLoginSuccess"
      :redirect-url="redirectUrl"
    />
  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue'
import { useAuth } from '@/composables/useAuth'
import Login from '@/components/auth/Login.vue'

// Auth composable
const { isAuthenticated } = useAuth()

// Get redirect URL from query params or use default
const redirectUrl = computed(() => {
  const urlParams = new URLSearchParams(window.location.search)
  return urlParams.get('redirect') || '/dashboard'
})

// Handle successful login
const handleLoginSuccess = (user, redirectTo) => {
  // Custom login success logic can be added here
  console.log('Login successful for user:', user)
  
  // Redirect to the intended page
  window.location.href = redirectTo
}

// Redirect if already authenticated
onMounted(() => {
  if (isAuthenticated.value) {
    window.location.href = redirectUrl.value
  }
})

// Set page title
document.title = 'Login - HR System'
</script>

<style scoped>
.login-view {
  min-height: 100vh;
}
</style>