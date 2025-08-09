import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import axios from 'axios'
import authService from '@/services/authService'

// Mock axios
vi.mock('axios')
const mockedAxios = vi.mocked(axios)

describe('AuthService', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    
    // Mock axios.create to return a mock instance
    const mockAxiosInstance = {
      post: vi.fn(),
      get: vi.fn()
    }
    mockedAxios.create.mockReturnValue(mockAxiosInstance)
    
    // Set up the mock instance on authService
    authService.apiClient = mockAxiosInstance
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('login', () => {
    it('should return success response on successful login', async () => {
      const mockResponse = {
        data: {
          token: 'mock-jwt-token',
          refresh_token: 'mock-refresh-token',
          user: { id: 1, email: 'test@example.com' }
        }
      }

      authService.apiClient.post.mockResolvedValue(mockResponse)

      const result = await authService.login('test@example.com', 'password123')

      expect(result.success).toBe(true)
      expect(result.data).toEqual(mockResponse.data)
      expect(authService.apiClient.post).toHaveBeenCalledWith('/auth/login', {
        email: 'test@example.com',
        password: 'password123'
      })
    })

    it('should return error response on failed login', async () => {
      const mockError = {
        response: {
          data: { message: 'Invalid credentials' },
          status: 401
        }
      }

      authService.apiClient.post.mockRejectedValue(mockError)

      const result = await authService.login('test@example.com', 'wrongpassword')

      expect(result.success).toBe(false)
      expect(result.error).toBe('Invalid credentials')
      expect(result.status).toBe(401)
    })

    it('should handle network errors gracefully', async () => {
      authService.apiClient.post.mockRejectedValue(new Error('Network error'))

      const result = await authService.login('test@example.com', 'password123')

      expect(result.success).toBe(false)
      expect(result.error).toBe('Login failed')
    })
  })

  describe('logout', () => {
    it('should return success response on successful logout', async () => {
      const mockResponse = { data: { message: 'Logged out successfully' } }
      authService.apiClient.post.mockResolvedValue(mockResponse)

      const result = await authService.logout('mock-token')

      expect(result.success).toBe(true)
      expect(result.data).toEqual(mockResponse.data)
      expect(authService.apiClient.post).toHaveBeenCalledWith('/auth/logout', {}, {
        headers: { 'Authorization': 'Bearer mock-token' }
      })
    })

    it('should return error response on failed logout', async () => {
      const mockError = {
        response: {
          data: { message: 'Token invalid' },
          status: 401
        }
      }

      authService.apiClient.post.mockRejectedValue(mockError)

      const result = await authService.logout('invalid-token')

      expect(result.success).toBe(false)
      expect(result.error).toBe('Token invalid')
      expect(result.status).toBe(401)
    })
  })

  describe('validateToken', () => {
    it('should return valid response for valid token', async () => {
      const mockResponse = { data: { valid: true, user: { id: 1 } } }
      authService.apiClient.get.mockResolvedValue(mockResponse)

      const result = await authService.validateToken('valid-token')

      expect(result.success).toBe(true)
      expect(result.valid).toBe(true)
      expect(result.data).toEqual(mockResponse.data)
      expect(authService.apiClient.get).toHaveBeenCalledWith('/auth/validate', {
        headers: { 'Authorization': 'Bearer valid-token' }
      })
    })

    it('should return invalid response for invalid token', async () => {
      const mockError = {
        response: {
          data: { message: 'Token expired' },
          status: 401
        }
      }

      authService.apiClient.get.mockRejectedValue(mockError)

      const result = await authService.validateToken('expired-token')

      expect(result.success).toBe(false)
      expect(result.valid).toBe(false)
      expect(result.error).toBe('Token expired')
      expect(result.status).toBe(401)
    })
  })

  describe('getUserProfile', () => {
    it('should return user profile on success', async () => {
      const mockResponse = {
        data: { id: 1, email: 'test@example.com', name: 'Test User' }
      }
      authService.apiClient.get.mockResolvedValue(mockResponse)

      const result = await authService.getUserProfile('valid-token')

      expect(result.success).toBe(true)
      expect(result.data).toEqual(mockResponse.data)
      expect(authService.apiClient.get).toHaveBeenCalledWith('/auth/profile', {
        headers: { 'Authorization': 'Bearer valid-token' }
      })
    })

    it('should return error on failed profile fetch', async () => {
      const mockError = {
        response: {
          data: { message: 'Unauthorized' },
          status: 401
        }
      }

      authService.apiClient.get.mockRejectedValue(mockError)

      const result = await authService.getUserProfile('invalid-token')

      expect(result.success).toBe(false)
      expect(result.error).toBe('Unauthorized')
      expect(result.status).toBe(401)
    })
  })

  describe('refreshToken', () => {
    it('should return new token on successful refresh', async () => {
      const mockResponse = {
        data: {
          token: 'new-access-token',
          refresh_token: 'new-refresh-token'
        }
      }
      authService.apiClient.post.mockResolvedValue(mockResponse)

      const result = await authService.refreshToken('valid-refresh-token')

      expect(result.success).toBe(true)
      expect(result.data).toEqual(mockResponse.data)
      expect(authService.apiClient.post).toHaveBeenCalledWith('/auth/refresh', {
        refresh_token: 'valid-refresh-token'
      })
    })

    it('should return error on failed refresh', async () => {
      const mockError = {
        response: {
          data: { message: 'Refresh token expired' },
          status: 401
        }
      }

      authService.apiClient.post.mockRejectedValue(mockError)

      const result = await authService.refreshToken('expired-refresh-token')

      expect(result.success).toBe(false)
      expect(result.error).toBe('Refresh token expired')
      expect(result.status).toBe(401)
    })
  })
})