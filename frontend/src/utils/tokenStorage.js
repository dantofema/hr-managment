/**
 * Token Storage Utilities
 * Handles JWT token storage and retrieval from localStorage
 */

const TOKEN_KEY = 'hr_system_token'
const REFRESH_TOKEN_KEY = 'hr_system_refresh_token'
const USER_KEY = 'hr_system_user'

/**
 * Store JWT token in localStorage
 * @param {string} token - JWT access token
 */
export const setToken = (token) => {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token)
  } else {
    localStorage.removeItem(TOKEN_KEY)
  }
}

/**
 * Get JWT token from localStorage
 * @returns {string|null} JWT token or null if not found
 */
export const getToken = () => {
  return localStorage.getItem(TOKEN_KEY)
}

/**
 * Remove JWT token from localStorage
 */
export const removeToken = () => {
  localStorage.removeItem(TOKEN_KEY)
}

/**
 * Store refresh token in localStorage
 * @param {string} refreshToken - JWT refresh token
 */
export const setRefreshToken = (refreshToken) => {
  if (refreshToken) {
    localStorage.setItem(REFRESH_TOKEN_KEY, refreshToken)
  } else {
    localStorage.removeItem(REFRESH_TOKEN_KEY)
  }
}

/**
 * Get refresh token from localStorage
 * @returns {string|null} Refresh token or null if not found
 */
export const getRefreshToken = () => {
  return localStorage.getItem(REFRESH_TOKEN_KEY)
}

/**
 * Remove refresh token from localStorage
 */
export const removeRefreshToken = () => {
  localStorage.removeItem(REFRESH_TOKEN_KEY)
}

/**
 * Store user data in localStorage
 * @param {Object} user - User object
 */
export const setUser = (user) => {
  if (user) {
    localStorage.setItem(USER_KEY, JSON.stringify(user))
  } else {
    localStorage.removeItem(USER_KEY)
  }
}

/**
 * Get user data from localStorage
 * @returns {Object|null} User object or null if not found
 */
export const getUser = () => {
  const user = localStorage.getItem(USER_KEY)
  return user ? JSON.parse(user) : null
}

/**
 * Remove user data from localStorage
 */
export const removeUser = () => {
  localStorage.removeItem(USER_KEY)
}

/**
 * Check if token exists in localStorage
 * @returns {boolean} True if token exists, false otherwise
 */
export const hasToken = () => {
  return !!getToken()
}

/**
 * Decode JWT token payload (without verification)
 * @param {string} token - JWT token
 * @returns {Object|null} Decoded payload or null if invalid
 */
export const decodeToken = (token) => {
  if (!token) return null
  
  try {
    const parts = token.split('.')
    if (parts.length !== 3) return null
    
    const payload = parts[1]
    const decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'))
    return JSON.parse(decoded)
  } catch (error) {
    console.error('Error decoding token:', error)
    return null
  }
}

/**
 * Check if token is expired
 * @param {string} token - JWT token
 * @returns {boolean} True if token is expired, false otherwise
 */
export const isTokenExpired = (token) => {
  if (!token) return true
  
  const decoded = decodeToken(token)
  if (!decoded || !decoded.exp) return true
  
  const currentTime = Math.floor(Date.now() / 1000)
  return decoded.exp < currentTime
}

/**
 * Get token expiration time
 * @param {string} token - JWT token
 * @returns {Date|null} Expiration date or null if invalid
 */
export const getTokenExpiration = (token) => {
  const decoded = decodeToken(token)
  if (!decoded || !decoded.exp) return null
  
  return new Date(decoded.exp * 1000)
}

/**
 * Clear all authentication data from localStorage
 */
export const clearAuthData = () => {
  removeToken()
  removeRefreshToken()
  removeUser()
}

/**
 * Store complete authentication data
 * @param {Object} authData - Authentication data object
 * @param {string} authData.token - JWT access token
 * @param {string} authData.refreshToken - JWT refresh token
 * @param {Object} authData.user - User object
 */
export const setAuthData = ({ token, refreshToken, user }) => {
  setToken(token)
  setRefreshToken(refreshToken)
  setUser(user)
}

/**
 * Get complete authentication data
 * @returns {Object} Authentication data object
 */
export const getAuthData = () => {
  return {
    token: getToken(),
    refreshToken: getRefreshToken(),
    user: getUser()
  }
}

/**
 * Check if user is authenticated (has valid token)
 * @returns {boolean} True if authenticated, false otherwise
 */
export const isAuthenticated = () => {
  const token = getToken()
  return !!(token && !isTokenExpired(token))
}