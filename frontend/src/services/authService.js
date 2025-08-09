import axios from 'axios'

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api'

class AuthService {
  constructor() {
    this.apiClient = axios.create({
      baseURL: API_BASE_URL,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    })
  }

  /**
   * Login user with email and password
   * @param {string} email - User email
   * @param {string} password - User password
   * @returns {Promise<Object>} Login response with token and user data
   */
  async login(email, password) {
    try {
      const response = await this.apiClient.post('/auth/login', {
        email,
        password
      })
      
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Login failed',
        status: error.response?.status
      }
    }
  }

  /**
   * Logout user
   * @param {string} token - JWT token
   * @returns {Promise<Object>} Logout response
   */
  async logout(token) {
    try {
      const response = await this.apiClient.post('/auth/logout', {}, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Logout failed',
        status: error.response?.status
      }
    }
  }

  /**
   * Validate JWT token
   * @param {string} token - JWT token to validate
   * @returns {Promise<Object>} Validation response
   */
  async validateToken(token) {
    try {
      const response = await this.apiClient.get('/auth/validate', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      
      return {
        success: true,
        data: response.data,
        valid: true
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Token validation failed',
        status: error.response?.status,
        valid: false
      }
    }
  }

  /**
   * Get user profile
   * @param {string} token - JWT token
   * @returns {Promise<Object>} User profile response
   */
  async getUserProfile(token) {
    try {
      const response = await this.apiClient.get('/auth/profile', {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      })
      
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Failed to get user profile',
        status: error.response?.status
      }
    }
  }

  /**
   * Refresh JWT token
   * @param {string} refreshToken - Refresh token
   * @returns {Promise<Object>} Refresh response with new token
   */
  async refreshToken(refreshToken) {
    try {
      const response = await this.apiClient.post('/auth/refresh', {
        refresh_token: refreshToken
      })
      
      return {
        success: true,
        data: response.data
      }
    } catch (error) {
      return {
        success: false,
        error: error.response?.data?.message || 'Token refresh failed',
        status: error.response?.status
      }
    }
  }
}

// Export singleton instance
export default new AuthService()