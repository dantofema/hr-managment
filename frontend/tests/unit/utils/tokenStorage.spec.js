import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest'
import {
  setToken,
  getToken,
  removeToken,
  setRefreshToken,
  getRefreshToken,
  removeRefreshToken,
  setUser,
  getUser,
  removeUser,
  hasToken,
  decodeToken,
  isTokenExpired,
  getTokenExpiration,
  clearAuthData,
  setAuthData,
  getAuthData,
  isAuthenticated
} from '@/utils/tokenStorage'

// Mock localStorage
const localStorageMock = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn()
}

Object.defineProperty(window, 'localStorage', {
  value: localStorageMock
})

describe('TokenStorage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  afterEach(() => {
    vi.restoreAllMocks()
  })

  describe('Token management', () => {
    it('should set token in localStorage', () => {
      setToken('test-token')
      expect(localStorageMock.setItem).toHaveBeenCalledWith('hr_system_token', 'test-token')
    })

    it('should remove token when setting null', () => {
      setToken(null)
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('hr_system_token')
    })

    it('should get token from localStorage', () => {
      localStorageMock.getItem.mockReturnValue('stored-token')
      const token = getToken()
      expect(token).toBe('stored-token')
      expect(localStorageMock.getItem).toHaveBeenCalledWith('hr_system_token')
    })

    it('should remove token from localStorage', () => {
      removeToken()
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('hr_system_token')
    })

    it('should check if token exists', () => {
      localStorageMock.getItem.mockReturnValue('test-token')
      expect(hasToken()).toBe(true)

      localStorageMock.getItem.mockReturnValue(null)
      expect(hasToken()).toBe(false)
    })
  })

  describe('Refresh token management', () => {
    it('should set refresh token in localStorage', () => {
      setRefreshToken('refresh-token')
      expect(localStorageMock.setItem).toHaveBeenCalledWith('hr_system_refresh_token', 'refresh-token')
    })

    it('should get refresh token from localStorage', () => {
      localStorageMock.getItem.mockReturnValue('stored-refresh-token')
      const refreshToken = getRefreshToken()
      expect(refreshToken).toBe('stored-refresh-token')
      expect(localStorageMock.getItem).toHaveBeenCalledWith('hr_system_refresh_token')
    })

    it('should remove refresh token from localStorage', () => {
      removeRefreshToken()
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('hr_system_refresh_token')
    })
  })

  describe('User data management', () => {
    it('should set user data in localStorage', () => {
      const user = { id: 1, email: 'test@example.com' }
      setUser(user)
      expect(localStorageMock.setItem).toHaveBeenCalledWith('hr_system_user', JSON.stringify(user))
    })

    it('should get user data from localStorage', () => {
      const user = { id: 1, email: 'test@example.com' }
      localStorageMock.getItem.mockReturnValue(JSON.stringify(user))
      const retrievedUser = getUser()
      expect(retrievedUser).toEqual(user)
      expect(localStorageMock.getItem).toHaveBeenCalledWith('hr_system_user')
    })

    it('should return null when no user data exists', () => {
      localStorageMock.getItem.mockReturnValue(null)
      const user = getUser()
      expect(user).toBeNull()
    })

    it('should remove user data from localStorage', () => {
      removeUser()
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('hr_system_user')
    })
  })

  describe('JWT token decoding', () => {
    it('should decode valid JWT token', () => {
      // Create a mock JWT token (header.payload.signature)
      const payload = { sub: '1234567890', name: 'John Doe', exp: 1516239022 }
      const encodedPayload = btoa(JSON.stringify(payload))
      const mockToken = `header.${encodedPayload}.signature`

      const decoded = decodeToken(mockToken)
      expect(decoded).toEqual(payload)
    })

    it('should return null for invalid token format', () => {
      const decoded = decodeToken('invalid-token')
      expect(decoded).toBeNull()
    })

    it('should return null for null token', () => {
      const decoded = decodeToken(null)
      expect(decoded).toBeNull()
    })

    it('should handle malformed JSON in token payload', () => {
      const mockToken = 'header.invalid-json.signature'
      const decoded = decodeToken(mockToken)
      expect(decoded).toBeNull()
    })
  })

  describe('Token expiration', () => {
    beforeEach(() => {
      // Mock Date.now() to return a fixed timestamp
      vi.spyOn(Date, 'now').mockReturnValue(1516239022000) // 2018-01-18 00:30:22
    })

    it('should detect expired token', () => {
      const expiredPayload = { exp: 1516239020 } // 2 seconds before current time
      const encodedPayload = btoa(JSON.stringify(expiredPayload))
      const expiredToken = `header.${encodedPayload}.signature`

      expect(isTokenExpired(expiredToken)).toBe(true)
    })

    it('should detect valid token', () => {
      const validPayload = { exp: 1516239025 } // 3 seconds after current time
      const encodedPayload = btoa(JSON.stringify(validPayload))
      const validToken = `header.${encodedPayload}.signature`

      expect(isTokenExpired(validToken)).toBe(false)
    })

    it('should return true for null token', () => {
      expect(isTokenExpired(null)).toBe(true)
    })

    it('should return true for token without exp claim', () => {
      const payloadWithoutExp = { sub: '1234567890' }
      const encodedPayload = btoa(JSON.stringify(payloadWithoutExp))
      const tokenWithoutExp = `header.${encodedPayload}.signature`

      expect(isTokenExpired(tokenWithoutExp)).toBe(true)
    })

    it('should get token expiration date', () => {
      const payload = { exp: 1516239022 }
      const encodedPayload = btoa(JSON.stringify(payload))
      const token = `header.${encodedPayload}.signature`

      const expiration = getTokenExpiration(token)
      expect(expiration).toEqual(new Date(1516239022000))
    })

    it('should return null for token without exp claim', () => {
      const payloadWithoutExp = { sub: '1234567890' }
      const encodedPayload = btoa(JSON.stringify(payloadWithoutExp))
      const tokenWithoutExp = `header.${encodedPayload}.signature`

      const expiration = getTokenExpiration(tokenWithoutExp)
      expect(expiration).toBeNull()
    })
  })

  describe('Authentication data management', () => {
    it('should clear all auth data', () => {
      clearAuthData()
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('hr_system_token')
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('hr_system_refresh_token')
      expect(localStorageMock.removeItem).toHaveBeenCalledWith('hr_system_user')
    })

    it('should set complete auth data', () => {
      const authData = {
        token: 'access-token',
        refreshToken: 'refresh-token',
        user: { id: 1, email: 'test@example.com' }
      }

      setAuthData(authData)

      expect(localStorageMock.setItem).toHaveBeenCalledWith('hr_system_token', 'access-token')
      expect(localStorageMock.setItem).toHaveBeenCalledWith('hr_system_refresh_token', 'refresh-token')
      expect(localStorageMock.setItem).toHaveBeenCalledWith('hr_system_user', JSON.stringify(authData.user))
    })

    it('should get complete auth data', () => {
      const user = { id: 1, email: 'test@example.com' }
      localStorageMock.getItem.mockImplementation((key) => {
        switch (key) {
          case 'hr_system_token':
            return 'stored-token'
          case 'hr_system_refresh_token':
            return 'stored-refresh-token'
          case 'hr_system_user':
            return JSON.stringify(user)
          default:
            return null
        }
      })

      const authData = getAuthData()
      expect(authData).toEqual({
        token: 'stored-token',
        refreshToken: 'stored-refresh-token',
        user
      })
    })
  })

  describe('Authentication status', () => {
    beforeEach(() => {
      vi.spyOn(Date, 'now').mockReturnValue(1516239022000)
    })

    it('should return true for valid authentication', () => {
      const validPayload = { exp: 1516239025 }
      const encodedPayload = btoa(JSON.stringify(validPayload))
      const validToken = `header.${encodedPayload}.signature`
      
      localStorageMock.getItem.mockReturnValue(validToken)
      
      expect(isAuthenticated()).toBe(true)
    })

    it('should return false for expired token', () => {
      const expiredPayload = { exp: 1516239020 }
      const encodedPayload = btoa(JSON.stringify(expiredPayload))
      const expiredToken = `header.${encodedPayload}.signature`
      
      localStorageMock.getItem.mockReturnValue(expiredToken)
      
      expect(isAuthenticated()).toBe(false)
    })

    it('should return false when no token exists', () => {
      localStorageMock.getItem.mockReturnValue(null)
      expect(isAuthenticated()).toBe(false)
    })
  })
})