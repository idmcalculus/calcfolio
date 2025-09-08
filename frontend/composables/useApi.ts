/**
 * Composable for API operations with consistent patterns for both reactive data fetching
 * and imperative actions following Nuxt 3 best practices.
 *
 * Architecture:
 * - useFetch: For reactive data that components consume (lists, stats, status checks)
 * - $fetch: For imperative actions (login, logout, form submissions, CRUD operations)
 */

// ===== TYPE DEFINITIONS =====

export interface ApiResponse<T = unknown> {
  success: boolean
  message?: string
  data?: T
  error?: string
}

export interface ServerError {
  type: string
  code: string
  message: string
  timestamp: string
  debug?: {
    exception: string
    file: string
    line: number
    trace: string
  }
}

export interface ApiErrorResponse {
  success: false
  error: ServerError
}

export interface LoginRequest {
  username: string
  password: string
}

export interface ContactFormRequest {
  name: string
  email: string
  subject: string
  message: string
  recaptcha_token: string
  theme_preference?: string
}

export interface BulkActionRequest {
  action: 'mark_read' | 'mark_unread' | 'delete'
  ids: number[]
}

export interface MessageStatsResponse {
  labels: string[]
  data: number[]
}

export interface PaginatedResponse<T> {
  data: T[]
  pagination: {
    total: number
    per_page: number
    current_page: number
    last_page: number
    from: number | null
    to: number | null
  }
}

export interface Message {
  id: number
  name: string
  email: string
  subject: string
  message: string
  status: string
  message_id: string
  is_read: boolean
  created_at: string
  updated_at: string
}

// Enhanced type safety for API options
export interface ApiOptions {
  headers?: Record<string, string>
  [key: string]: unknown
}

export interface MessageListParams extends Record<string, unknown> {
  page?: number
  limit?: number
  is_read?: string | number | null
  sort?: string
  order?: string // Allow string to be more flexible with existing code
  search?: string
  status?: string // Add status parameter for filtering by message status
}

// Type for fetch options to avoid 'any'
interface FetchOptions {
  credentials: 'include'
  headers: Record<string, string>
  [key: string]: unknown
}

// Enhanced Error type
interface EnhancedError extends Error {
  serverError?: ServerError
  statusCode?: number
  errorType?: string
  errorCode?: string
}

// Type for request options
interface RequestOptions {
  method?: string
  baseURL?: string
  body?: unknown
  [key: string]: unknown
}

// ===== MAIN COMPOSABLE =====

export const useApi = () => {
  const config = useRuntimeConfig()
  const baseURL = config.public.backendUrl

  // ===== HELPER FUNCTIONS =====

  /**
   * Common options builder with consistent defaults for all API calls
   * @param options - Additional options to merge
   * @returns Merged options with defaults
   */
  const getBaseOptions = (options: ApiOptions = {}): FetchOptions => ({
    credentials: 'include' as const,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...options.headers,
    },
    ...options,
  })

  /**
    * Clean query parameters by removing null/undefined/empty values
    * but keeping valid defaults for pagination
    * @param params - Raw parameters object
    * @returns Cleaned parameters
    */
   const cleanQueryParams = (params: Record<string, unknown>): Record<string, unknown> => {
     const cleaned = Object.fromEntries(
       Object.entries(params).filter(([key, value]) => {
         // Always include pagination parameters with defaults
         if (['page', 'limit', 'sort', 'order'].includes(key)) {
           return true
         }
         // Include other parameters if they have valid values
         return value !== null && value !== undefined && value !== ''
       })
     )

     // Ensure pagination defaults are set and properly typed
     const result = {
       page: 1,
       limit: Math.min(cleaned.limit as number || 15, 10000), // Allow up to 10000 as requested
       sort: 'created_at',
       order: 'desc',
       ...cleaned,
     }

     // Ensure numeric parameters are properly typed
     if (result.page) result.page = Number(result.page)
     if (result.limit) result.limit = Number(result.limit)

     return result
   }

  /**
   * Enhanced error handling utility
   * @param error - The error from fetch operation
   * @throws Enhanced error with server details
   */
  const handleApiError = (error: unknown): never => {
    // Check if it's a server error response with structured error data
    if (error && typeof error === 'object' && 'data' in error) {
      const errorData = (error as { data: unknown }).data
      if (errorData && typeof errorData === 'object' && 'success' in errorData && !errorData.success && 'error' in errorData) {
        const serverError = (errorData as ApiErrorResponse).error

        // Create a descriptive error message
        const errorMessage = serverError.message || 'An error occurred'

        const enhancedError = new Error(errorMessage) as EnhancedError
        enhancedError.serverError = serverError
        enhancedError.statusCode = (error as { statusCode?: number }).statusCode || 500
        enhancedError.errorType = serverError.type
        enhancedError.errorCode = serverError.code

        throw enhancedError
      }
    }

    // Check for network/CORS errors
    const errorMessage = error instanceof Error ? error.message : String(error)
    const errorName = error instanceof Error ? error.name : 'Unknown'

    if (errorName === 'FetchError' || errorMessage.includes('fetch')) {
      const statusCode = error && typeof error === 'object' && 'statusCode' in error
        ? (error as { statusCode: number }).statusCode
        : undefined

      if (statusCode) {
        const statusMessage = getStatusMessage(statusCode)
        throw new Error(`Server error (${statusCode}): ${statusMessage}`)
      }

      throw new Error('Network error: Unable to connect to server. Please check your connection and try again.')
    }

    // Fallback for other types of errors
    throw error instanceof Error ? error : new Error('An unexpected error occurred')
  }

  /**
   * Get user-friendly message for HTTP status codes
   * @param statusCode - HTTP status code
   * @returns User-friendly message
   */
  const getStatusMessage = (statusCode: number): string => {
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

  /**
   * Log API request for debugging
   * @param url - Request URL
   * @param options - Request options
   */
  const logApiRequest = (url: string, options: RequestOptions) => {
    if (import.meta.dev) {
      console.log(`🔄 API Request: ${options.method || 'GET'} ${url}`)
    }
  }

  /**
   * Log API response for debugging
   * @param url - Request URL
   * @param response - Response data
   * @param error - Error if any
   */
  const logApiResponse = (url: string, response?: unknown, error?: unknown) => {
    if (import.meta.dev) {
      if (error) {
        console.error(`❌ API Error: ${url}`, error)
      } else {
        console.log(`✅ API Success: ${url}`)
      }
    }
  }

  // ===== API METHODS =====

  /**
   * Authentication API - Uses $fetch for imperative actions (login/logout)
   * and useFetch for reactive auth state checking
   */
  const auth = {
    /**
     * Login action - Uses $fetch for one-time imperative action
     * @param credentials - Username and password
     * @returns Promise with login response
     */
    login: async (credentials: LoginRequest): Promise<ApiResponse> => {
      const url = '/admin/login'
      const options = {
        ...getBaseOptions(),
        baseURL,
        method: 'POST' as const,
        body: credentials,
      }
      
      logApiRequest(url, options)
      
      try {
        const response = await $fetch<ApiResponse>(url, options)
        logApiResponse(url, response)
        return response
      } catch (error) {
        logApiResponse(url, undefined, error)
        return handleApiError(error)
      }
    },

    /**
     * Logout action - Uses $fetch for one-time imperative action
     * @returns Promise with logout response
     */
    logout: async (): Promise<ApiResponse> => {
      const url = '/admin/logout'
      const options = {
        ...getBaseOptions(),
        baseURL,
        method: 'POST' as const,
      }
      
      logApiRequest(url, options)
      
      try {
        const response = await $fetch<ApiResponse>(url, options)
        logApiResponse(url, response)
        return response
      } catch (error) {
        logApiResponse(url, undefined, error)
        return handleApiError(error)
      }
    },

    /**
     * Check authentication status - Uses useFetch for reactive state
     * @param options - Additional useFetch options
     * @returns Reactive auth status
     */
     checkAuth: async (options: Record<string, unknown> = {}): Promise<{ authenticated: boolean }> => {
       const url = '/admin/check'
       const requestOptions = {
         ...getBaseOptions(),
         baseURL,
         // Add cache buster by default to ensure fresh requests
         query: { t: Date.now(), ...(options.query || {}) },
         ...options,
       }

       logApiRequest(url, requestOptions)

       try {
         const response = await $fetch<{ authenticated: boolean }>(url, requestOptions)
         logApiResponse(url, response)
         return response
       } catch (error) {
         logApiResponse(url, undefined, error)
         return handleApiError(error)
       }
     },

     /**
      * Recover session after resource exhaustion or connection issues
      * @returns Promise with recovery status
      */
     recoverSession: async (): Promise<{ recovered: boolean; authenticated: boolean; message: string }> => {
       const url = '/admin/recover-session'
       const options = {
         ...getBaseOptions(),
         baseURL,
         method: 'POST' as const,
       }

       logApiRequest(url, options)

       try {
         const response = await $fetch<{ recovered: boolean; authenticated: boolean; message: string }>(url, options)
         logApiResponse(url, response)
         return response
       } catch (error) {
         logApiResponse(url, undefined, error)
         // Don't throw error for session recovery - return failed state
         return {
           recovered: false,
           authenticated: false,
           message: 'Session recovery failed'
         }
       }
     },
  }

  /**
   * Contact form API - Uses $fetch for form submission actions
   */
  const contact = {
    /**
     * Submit contact form - Uses $fetch for one-time form submission
     * @param formData - Contact form data with reCAPTCHA token
     * @returns Promise with submission response
     */
    submit: async (formData: ContactFormRequest): Promise<ApiResponse> => {
      const url = '/contact'
      const options = {
        ...getBaseOptions(),
        baseURL,
        method: 'POST' as const,
        body: formData,
      }
      
      logApiRequest(url, options)
      
      try {
        const response = await $fetch<ApiResponse>(url, options)
        logApiResponse(url, response)
        return response
      } catch (error) {
        logApiResponse(url, undefined, error)
        return handleApiError(error)
      }
    },
  }

  /**
   * Admin messages API - Mixed approach:
   * - useFetch for data fetching (list, stats, individual records)
   * - $fetch for actions (bulk operations)
   */
  const admin = {
    messages: {
      /**
        * List messages with pagination/filtering - Uses useFetch for reactive data
        * @param params - Query parameters for filtering and pagination
        * @param options - Additional useFetch options
        * @returns Reactive paginated message list
        */
       list: (params: MessageListParams = {}, options: Record<string, unknown> = {}) => {
         const cleanedParams = cleanQueryParams(params as Record<string, unknown>)

         return useFetch<PaginatedResponse<Message>>('/admin/messages', {
           ...getBaseOptions(),
           baseURL,
           query: cleanedParams,
           // Remove server and lazy options that were causing validation issues
           ...options,
         })
       },

      /**
        * Get individual message - Uses useFetch for reactive record fetching
        * @param id - Message ID
        * @param options - Additional useFetch options
        * @returns Reactive message data wrapped in API response
        */
       get: (id: number, options: Record<string, unknown> = {}) => {
         return useFetch<ApiResponse<Message>>(`/admin/messages/${id}`, {
           ...getBaseOptions(),
           baseURL,
           // Remove server option that was causing issues
           ...options,
         })
       },

      /**
       * Bulk action on messages - Uses $fetch for imperative action
       * @param request - Bulk action request (mark read/unread, delete)
       * @returns Promise with action response
       */
      bulkAction: async (request: BulkActionRequest): Promise<ApiResponse> => {
        const url = '/admin/bulk/messages'
        const options = {
          ...getBaseOptions(),
          baseURL,
          method: 'PATCH' as const,
          body: request,
        }
        
        logApiRequest(url, options)
        
        try {
          const response = await $fetch<ApiResponse>(url, options)
          logApiResponse(url, response)
          return response
        } catch (error) {
          logApiResponse(url, undefined, error)
          return handleApiError(error)
        }
      },
    },
  }

  return {
    auth,
    contact,
    admin,
  }
}