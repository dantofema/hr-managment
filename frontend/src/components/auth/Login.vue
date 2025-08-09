<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div>
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-indigo-100">
          <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Sign in to your account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Welcome back to HR System
        </p>
      </div>

      <!-- Login Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <input type="hidden" name="remember" value="true" />
        
        <!-- Form Fields -->
        <div class="rounded-md shadow-sm -space-y-px">
          <!-- Email Field -->
          <div>
            <label for="email" class="sr-only">Email address</label>
            <input
              id="email"
              ref="emailInput"
              v-model="form.email"
              name="email"
              type="email"
              autocomplete="email"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              :class="[
                emailError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300',
                isLoading ? 'bg-gray-50' : 'bg-white'
              ]"
              :disabled="isLoading"
              placeholder="Email address"
              @blur="validateEmail"
              @input="clearFieldError('email')"
            />
          </div>

          <!-- Password Field -->
          <div>
            <label for="password" class="sr-only">Password</label>
            <div class="relative">
              <input
                id="password"
                ref="passwordInput"
                v-model="form.password"
                name="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="appearance-none rounded-none relative block w-full px-3 py-2 pr-10 border placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                :class="[
                  passwordError ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300',
                  isLoading ? 'bg-gray-50' : 'bg-white'
                ]"
                :disabled="isLoading"
                placeholder="Password"
                @blur="validatePassword"
                @input="clearFieldError('password')"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                :disabled="isLoading"
                @click="togglePasswordVisibility"
              >
                <svg
                  v-if="showPassword"
                  class="h-5 w-5 text-gray-400 hover:text-gray-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
                <svg
                  v-else
                  class="h-5 w-5 text-gray-400 hover:text-gray-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Field Errors -->
        <div v-if="emailError || passwordError" class="space-y-1">
          <p v-if="emailError" class="text-sm text-red-600" role="alert">
            {{ emailError }}
          </p>
          <p v-if="passwordError" class="text-sm text-red-600" role="alert">
            {{ passwordError }}
          </p>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember-me"
              v-model="form.remember"
              name="remember-me"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              :disabled="isLoading"
            />
            <label for="remember-me" class="ml-2 block text-sm text-gray-900">
              Remember me
            </label>
          </div>

          <div class="text-sm">
            <a href="#" class="font-medium text-indigo-600 hover:text-indigo-500">
              Forgot your password?
            </a>
          </div>
        </div>

        <!-- General Error Message -->
        <div v-if="error" class="rounded-md bg-red-50 p-4" role="alert">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Authentication Error
              </h3>
              <div class="mt-2 text-sm text-red-700">
                {{ error }}
              </div>
            </div>
            <div class="ml-auto pl-3">
              <div class="-mx-1.5 -my-1.5">
                <button
                  type="button"
                  class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600"
                  @click="clearError"
                >
                  <span class="sr-only">Dismiss</span>
                  <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div>
          <button
            type="submit"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
            :class="[
              isLoading 
                ? 'bg-indigo-400 cursor-not-allowed' 
                : 'bg-indigo-600 hover:bg-indigo-700'
            ]"
            :disabled="isLoading || !isFormValid"
          >
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg
                v-if="isLoading"
                class="animate-spin h-5 w-5 text-indigo-300"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg
                v-else
                class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
              </svg>
            </span>
            {{ isLoading ? 'Signing in...' : 'Sign in' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { useAuth } from '@/composables/useAuth'

// Props for navigation callback
const props = defineProps({
  onLoginSuccess: {
    type: Function,
    default: () => {
      // Default redirect behavior - can be overridden by parent
      window.location.href = '/dashboard'
    }
  },
  redirectUrl: {
    type: String,
    default: '/dashboard'
  }
})

// Auth composable
const { login, isLoading, error, clearError } = useAuth()

// Form data
const form = ref({
  email: '',
  password: '',
  remember: false
})

// Form validation
const emailError = ref('')
const passwordError = ref('')
const showPassword = ref(false)

// Template refs
const emailInput = ref(null)
const passwordInput = ref(null)

// Computed properties
const isFormValid = computed(() => {
  return form.value.email.trim() !== '' && 
         form.value.password.trim() !== '' && 
         !emailError.value && 
         !passwordError.value
})

// Validation methods
const validateEmail = () => {
  const email = form.value.email.trim()
  
  if (!email) {
    emailError.value = 'Email is required'
    return false
  }
  
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(email)) {
    emailError.value = 'Please enter a valid email address'
    return false
  }
  
  emailError.value = ''
  return true
}

const validatePassword = () => {
  const password = form.value.password.trim()
  
  if (!password) {
    passwordError.value = 'Password is required'
    return false
  }
  
  if (password.length < 6) {
    passwordError.value = 'Password must be at least 6 characters long'
    return false
  }
  
  passwordError.value = ''
  return true
}

const validateForm = () => {
  const isEmailValid = validateEmail()
  const isPasswordValid = validatePassword()
  
  return isEmailValid && isPasswordValid
}

// Form methods
const clearFieldError = (field) => {
  if (field === 'email') {
    emailError.value = ''
  } else if (field === 'password') {
    passwordError.value = ''
  }
}

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}

const handleSubmit = async () => {
  // Clear any existing errors
  clearError()
  
  // Validate form
  if (!validateForm()) {
    // Focus on first invalid field
    await nextTick()
    if (emailError.value) {
      emailInput.value?.focus()
    } else if (passwordError.value) {
      passwordInput.value?.focus()
    }
    return
  }

  try {
    const result = await login(form.value.email, form.value.password)
    
    if (result.success) {
      // Call the success callback or use default navigation
      if (props.onLoginSuccess) {
        props.onLoginSuccess(result.user, props.redirectUrl)
      } else {
        // Default behavior - redirect to dashboard
        window.location.href = props.redirectUrl
      }
    }
    // Error handling is done by the useAuth composable
  } catch (err) {
    console.error('Login error:', err)
  }
}

// Lifecycle
onMounted(() => {
  // Focus on email input when component mounts
  nextTick(() => {
    emailInput.value?.focus()
  })
})

// Expose for testing
defineExpose({
  form,
  validateForm,
  handleSubmit,
  emailError,
  passwordError,
  isFormValid,
  showPassword,
  togglePasswordVisibility,
  clearFieldError
})
</script>

<style scoped>
/* Additional custom styles if needed */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>