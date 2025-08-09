import { ref, computed, watch } from 'vue'
import authService from '@/services/authService'
import {
  getToken,
  setAuthData,
  clearAuthData,
  getAuthData,
  isAuthenticated as checkIsAuthenticated,
  isTokenExpired
} from '@/utils/tokenStorage'

// Global reactive state
const user = ref(null)
const token = ref(null)
const isLoading = ref(false)
const error = ref(null)

/**
 * Authentication composable
 * Provides reactive authentication state and methods
 */
export function useAuth() {
  // Computed properties
  const isAuthenticated = computed(() => {
    return !!token.value && !!user.value && checkIsAuthenticated()
  })

  const isLoggedIn = computed(() => isAuthenticated.value)

  // Initialize auth state from localStorage
  const initializeAuth = () => {
    const authData = getAuthData()
    if (authData.token && authData.user && !isTokenExpired(authData.token)) {
      token.value = authData.token
      user.value = authData.user
    } else {
      // Clear invalid/expired data
      clearAuthData()
      token.value = null
      user.value = null
    }
  }

  /**
   * Login user with email and password
   * @param {string} email - User email
   * @param {string} password - User password
   * @returns {Promise<Object>} Login result
   */
  const login = async (email, password) => {
    try {
      isLoading.value = true
      error.value = null

      const result = await authService.login(email, password)

      if (result.success) {
        const { token: accessToken, user: userData } = result.data
        
        // Store auth data
        setAuthData({
          token: accessToken,
          refreshToken: result.data.refreshToken || null,
          user: userData
        })

        // Update reactive state
        token.value = accessToken
        user.value = userData

        return {
          success: true,
          user: userData
        }
      } else {
        error.value = result.error
        return {
          success: false,
          error: result.error
        }
      }
    } catch (err) {
      error.value = 'An unexpected error occurred during login'
      return {
        success: false,
        error: error.value
      }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Logout user
   * @returns {Promise<Object>} Logout result
   */
  const logout = async () => {
    try {
      isLoading.value = true
      error.value = null

      // Call logout API if token exists
      if (token.value) {
        await authService.logout(token.value)
      }

      // Clear auth data regardless of API call result
      clearAuthData()
      token.value = null
      user.value = null

      return { success: true }
    } catch (err) {
      // Still clear local data even if API call fails
      clearAuthData()
      token.value = null
      user.value = null
      
      return { success: true } // Consider logout successful even if API fails
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Validate current token
   * @returns {Promise<boolean>} True if token is valid
   */
  const validateToken = async () => {
    if (!token.value) return false

    try {
      const result = await authService.validateToken(token.value)
      
      if (!result.success || !result.valid) {
        // Token is invalid, clear auth data
        await logout()
        return false
      }

      return true
    } catch (err) {
      // On validation error, clear auth data
      await logout()
      return false
    }
  }

  /**
   * Refresh user profile data
   * @returns {Promise<Object>} Profile refresh result
   */
  const refreshProfile = async () => {
    if (!token.value) {
      return { success: false, error: 'No authentication token' }
    }

    try {
      isLoading.value = true
      error.value = null

      const result = await authService.getUserProfile(token.value)

      if (result.success) {
        user.value = result.data
        
        // Update stored user data
        setAuthData({
          token: token.value,
          refreshToken: getAuthData().refreshToken,
          user: result.data
        })

        return { success: true, user: result.data }
      } else {
        error.value = result.error
        return { success: false, error: result.error }
      }
    } catch (err) {
      error.value = 'Failed to refresh profile'
      return { success: false, error: error.value }
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Clear any authentication errors
   */
  const clearError = () => {
    error.value = null
  }

  /**
   * Check if user has specific role
   * @param {string} role - Role to check
   * @returns {boolean} True if user has role
   */
  const hasRole = (role) => {
    return user.value?.roles?.includes(role) || false
  }

  /**
   * Check if user has any of the specified roles
   * @param {Array<string>} roles - Roles to check
   * @returns {boolean} True if user has any of the roles
   */
  const hasAnyRole = (roles) => {
    if (!user.value?.roles) return false
    return roles.some(role => user.value.roles.includes(role))
  }

  /**
   * Check if user has all specified roles
   * @param {Array<string>} roles - Roles to check
   * @returns {boolean} True if user has all roles
   */
  const hasAllRoles = (roles) => {
    if (!user.value?.roles) return false
    return roles.every(role => user.value.roles.includes(role))
  }

  // Watch for token changes to validate periodically
  watch(token, async (newToken) => {
    if (newToken && isTokenExpired(newToken)) {
      await logout()
    }
  })

  // Initialize auth state when composable is first used
  if (!token.value && !user.value) {
    initializeAuth()
  }

  return {
    // State
    user: computed(() => user.value),
    token: computed(() => token.value),
    isAuthenticated,
    isLoggedIn,
    isLoading: computed(() => isLoading.value),
    error: computed(() => error.value),

    // Methods
    login,
    logout,
    validateToken,
    refreshProfile,
    clearError,
    initializeAuth,
    hasRole,
    hasAnyRole,
    hasAllRoles
  }
}

// Export default instance for global use
export default useAuth