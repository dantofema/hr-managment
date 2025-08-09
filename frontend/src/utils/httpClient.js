import axios from 'axios'
import { getToken, getRefreshToken, setAuthData, clearAuthData, isTokenExpired } from './tokenStorage'
import authService from '@/services/authService'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

/**
 * Create axios instance with base configuration
 */
const httpClient = axios.create({
  baseURL: API_BASE_URL,
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

/**
 * Flag to prevent multiple refresh attempts
 */
let isRefreshing = false
let failedQueue = []

/**
 * Process failed queue after token refresh
 * @param {Error|null} error - Error if refresh failed
 * @param {string|null} token - New token if refresh succeeded
 */
const processQueue = (error, token = null) => {
  failedQueue.forEach(({ resolve, reject }) => {
    if (error) {
      reject(error)
    } else {
      resolve(token)
    }
  })
  
  failedQueue = []
}

/**
 * Request interceptor to add JWT token to headers
 */
httpClient.interceptors.request.use(
  (config) => {
    const token = getToken()
    
    if (token && !isTokenExpired(token)) {
      config.headers.Authorization = `Bearer ${token}`
    }
    
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

/**
 * Response interceptor to handle token expiration and refresh
 */
httpClient.interceptors.response.use(
  (response) => {
    return response
  },
  async (error) => {
    const originalRequest = error.config
    
    // Check if error is due to unauthorized access (401)
    if (error.response?.status === 401 && !originalRequest._retry) {
      const token = getToken()
      const refreshToken = getRefreshToken()
      
      // If no tokens available, reject immediately
      if (!token || !refreshToken) {
        clearAuthData()
        return Promise.reject(error)
      }
      
      // If token is expired, try to refresh
      if (isTokenExpired(token)) {
        if (isRefreshing) {
          // If already refreshing, queue this request
          return new Promise((resolve, reject) => {
            failedQueue.push({ resolve, reject })
          }).then(token => {
            originalRequest.headers.Authorization = `Bearer ${token}`
            return httpClient(originalRequest)
          }).catch(err => {
            return Promise.reject(err)
          })
        }
        
        originalRequest._retry = true
        isRefreshing = true
        
        try {
          const refreshResponse = await authService.refreshToken(refreshToken)
          
          if (refreshResponse.success) {
            const { token: newToken, refresh_token: newRefreshToken, user } = refreshResponse.data
            
            // Update stored tokens
            setAuthData({
              token: newToken,
              refreshToken: newRefreshToken,
              user
            })
            
            // Process queued requests
            processQueue(null, newToken)
            
            // Retry original request with new token
            originalRequest.headers.Authorization = `Bearer ${newToken}`
            return httpClient(originalRequest)
          } else {
            // Refresh failed, clear auth data
            processQueue(error, null)
            clearAuthData()
            return Promise.reject(error)
          }
        } catch (refreshError) {
          // Refresh failed, clear auth data
          processQueue(refreshError, null)
          clearAuthData()
          return Promise.reject(refreshError)
        } finally {
          isRefreshing = false
        }
      }
    }
    
    return Promise.reject(error)
  }
)

/**
 * HTTP client methods with error handling
 */
export const api = {
  /**
   * GET request
   * @param {string} url - Request URL
   * @param {Object} config - Axios config
   * @returns {Promise} Response promise
   */
  get: (url, config = {}) => {
    return httpClient.get(url, config)
  },
  
  /**
   * POST request
   * @param {string} url - Request URL
   * @param {Object} data - Request data
   * @param {Object} config - Axios config
   * @returns {Promise} Response promise
   */
  post: (url, data = {}, config = {}) => {
    return httpClient.post(url, data, config)
  },
  
  /**
   * PUT request
   * @param {string} url - Request URL
   * @param {Object} data - Request data
   * @param {Object} config - Axios config
   * @returns {Promise} Response promise
   */
  put: (url, data = {}, config = {}) => {
    return httpClient.put(url, data, config)
  },
  
  /**
   * PATCH request
   * @param {string} url - Request URL
   * @param {Object} data - Request data
   * @param {Object} config - Axios config
   * @returns {Promise} Response promise
   */
  patch: (url, data = {}, config = {}) => {
    return httpClient.patch(url, data, config)
  },
  
  /**
   * DELETE request
   * @param {string} url - Request URL
   * @param {Object} config - Axios config
   * @returns {Promise} Response promise
   */
  delete: (url, config = {}) => {
    return httpClient.delete(url, config)
  }
}

/**
 * Create authenticated request with automatic token handling
 * @param {Object} config - Axios request config
 * @returns {Promise} Response promise
 */
export const authenticatedRequest = (config) => {
  return httpClient(config)
}

/**
 * Create request without authentication
 * @param {Object} config - Axios request config
 * @returns {Promise} Response promise
 */
export const publicRequest = (config) => {
  const publicClient = axios.create({
    baseURL: API_BASE_URL,
    timeout: 10000,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    }
  })
  
  return publicClient(config)
}

/**
 * Upload file with progress tracking
 * @param {string} url - Upload URL
 * @param {FormData} formData - Form data with file
 * @param {Function} onProgress - Progress callback
 * @returns {Promise} Upload promise
 */
export const uploadFile = (url, formData, onProgress = null) => {
  const config = {
    headers: {
      'Content-Type': 'multipart/form-data'
    }
  }
  
  if (onProgress) {
    config.onUploadProgress = (progressEvent) => {
      const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
      onProgress(percentCompleted)
    }
  }
  
  return httpClient.post(url, formData, config)
}

/**
 * Set global error handler for HTTP requests
 * @param {Function} handler - Error handler function
 */
export const setGlobalErrorHandler = (handler) => {
  httpClient.interceptors.response.use(
    (response) => response,
    (error) => {
      handler(error)
      return Promise.reject(error)
    }
  )
}

export default httpClient