/**
 * Debug utilities for API requests and responses
 * Only active in development mode
 */

import type { ServerError } from '~/composables/useApi'

interface RequestOptions {
  method?: string
  baseURL?: string
  body?: unknown
  [key: string]: unknown
}

interface EnhancedError extends Error {
  serverError?: ServerError
  statusCode?: number
  errorType?: string
  errorCode?: string
}

/**
 * Log API request details in development mode
 * @param url Request URL
 * @param options Request options
 */
export const logApiRequest = (url: string, options: RequestOptions): void => {
  if (import.meta.dev) {
    console.group(`🔄 API Request: ${options.method || 'GET'} ${url}`)
    console.log('Options:', options)
    console.log('Timestamp:', new Date().toISOString())
    console.groupEnd()
  }
}

/**
 * Log API response details in development mode
 * @param url Request URL
 * @param response Response data (if successful)
 * @param error Error data (if failed)
 */
export const logApiResponse = (url: string, response?: unknown, error?: unknown): void => {
  if (import.meta.dev) {
    console.group(`${error ? '❌' : '✅'} API Response: ${url}`)
    if (error) {
      console.error('Error:', error)
      if (error && typeof error === 'object' && 'serverError' in error) {
        console.error('Server Error Details:', (error as EnhancedError).serverError)
      }
    } else {
      console.log('Response:', response)
    }
    console.log('Timestamp:', new Date().toISOString())
    console.groupEnd()
  }
}

/**
 * Log detailed error information for debugging
 * @param error The error object to analyze
 */
export const logErrorDetails = (error: unknown): void => {
  if (import.meta.dev) {
    console.group('🚨 API Error Details')
    console.error('Original error:', error)
    console.error('Error type:', typeof error)
    console.error('Error properties:', error && typeof error === 'object' ? Object.keys(error) : 'N/A')
    if (error && typeof error === 'object' && 'data' in error) {
      console.error('Error data:', (error as { data: unknown }).data)
    }
    console.groupEnd()
  }
}

/**
 * Create a debug information object for errors
 * @param error The error to extract debug info from
 * @returns Debug information object
 */
export const getErrorDebugInfo = (error: unknown): Record<string, unknown> => {
  const debugInfo: Record<string, unknown> = {
    timestamp: new Date().toISOString(),
    errorType: typeof error,
    errorName: error instanceof Error ? error.name : 'Unknown',
    errorMessage: error instanceof Error ? error.message : String(error),
  }

  if (error && typeof error === 'object') {
    debugInfo.errorProperties = Object.keys(error)
    
    if ('data' in error) {
      debugInfo.errorData = (error as { data: unknown }).data
    }
    
    if ('statusCode' in error) {
      debugInfo.statusCode = (error as { statusCode: number }).statusCode
    }
    
    if ('serverError' in error) {
      debugInfo.serverError = (error as EnhancedError).serverError
    }
  }

  return debugInfo
}

/**
 * Display error information in a user-friendly toast or alert
 * @param error The error to display
 * @param fallbackMessage Fallback message if error parsing fails
 */
export const displayErrorInfo = (error: unknown, fallbackMessage = 'An unexpected error occurred'): string => {
  // Extract server error if available
  if (error && typeof error === 'object' && 'serverError' in error) {
    const serverError = (error as EnhancedError).serverError
    if (serverError?.message) {
      return serverError.message
    }
  }

  // Extract basic error message
  if (error instanceof Error) {
    return error.message
  }

  // Fallback
  return fallbackMessage
}

/**
 * Check if an error is a network/CORS related error
 * @param error The error to check
 * @returns True if it's a network error
 */
export const isNetworkError = (error: unknown): boolean => {
  const errorMessage = error instanceof Error ? error.message : String(error)
  const errorName = error instanceof Error ? error.name : 'Unknown'
  
  return errorName === 'FetchError' || 
         errorMessage.includes('fetch') || 
         errorMessage.includes('CORS') ||
         errorMessage.includes('Network error')
}

/**
 * Check if an error has structured server error data
 * @param error The error to check
 * @returns True if it has server error structure
 */
export const hasServerErrorData = (error: unknown): boolean => {
  if (!error || typeof error !== 'object' || !('data' in error)) {
    return false
  }
  
  const errorData = (error as { data: unknown }).data
  return errorData !== null &&
         typeof errorData === 'object' &&
         'success' in errorData &&
         !(errorData as { success: boolean }).success &&
         'error' in errorData
}

/**
 * Get HTTP status code from error
 * @param error The error to extract status from
 * @returns HTTP status code or undefined
 */
export const getErrorStatusCode = (error: unknown): number | undefined => {
  if (error && typeof error === 'object' && 'statusCode' in error) {
    return (error as { statusCode: number }).statusCode
  }
  return undefined
}

/**
 * Get user-friendly message for HTTP status codes
 * @param statusCode HTTP status code
 * @returns User-friendly message
 */
export const getStatusMessage = (statusCode: number): string => {
  const statusMessages: Record<number, string> = {
    400: 'Bad request - please check your input',
    401: 'Authentication required - please log in',
    403: 'Access forbidden - insufficient permissions',
    404: 'Resource not found',
    405: 'Method not allowed - invalid request type',
    422: 'Validation error - please check your input',
    429: 'Too many requests - please try again later',
    500: 'Internal server error - please try again later',
    502: 'Bad gateway - server temporarily unavailable',
    503: 'Service unavailable - please try again later'
  }
  
  return statusMessages[statusCode] || 'Unknown error occurred'
}

export default {
  logApiRequest,
  logApiResponse,
  logErrorDetails,
  getErrorDebugInfo,
  displayErrorInfo,
  isNetworkError,
  hasServerErrorData,
  getErrorStatusCode,
  getStatusMessage
}