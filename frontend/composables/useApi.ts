// composables/useApi.ts
export interface ApiResponse<T = unknown> {
  success: boolean
  message?: string
  data?: T
  error?: string
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

export const useApi = () => {
  const config = useRuntimeConfig()
  const baseURL = config.public.backendUrl

  // Common options for all API calls
  const getOptions = (options: Record<string, unknown> = {}) => ({
    credentials: 'include' as const,
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      ...(options.headers && typeof options.headers === 'object' ? options.headers as Record<string, string> : {}),
    },
    ...options,
  })

  // Auth API calls
  const auth = {
    login: (credentials: LoginRequest) => {
      return $fetch<ApiResponse>('/admin/login', {
        ...getOptions(),
        baseURL,
        method: 'POST',
        body: credentials,
      })
    },

    logout: () => {
      return $fetch<ApiResponse>('/admin/logout', {
        ...getOptions(),
        baseURL,
        method: 'POST',
      })
    },

    checkAuth: (options: Record<string, unknown> = {}) => {
      return useFetch<{ authenticated: boolean }>('/admin/check', {
        ...getOptions(),
        baseURL,
        ...options,
      })
    },
  }

  // Contact form API
  const contact = {
    submit: (formData: ContactFormRequest) => {
      return $fetch<ApiResponse>('/contact', {
        ...getOptions(),
        baseURL,
        method: 'POST',
        body: formData,
      })
    },
  }

  // Admin messages API
  const admin = {
    messages: {
      list: (params: Record<string, unknown> = {}, options: Record<string, unknown> = {}) => {
        // Clean up params - remove null/undefined values
        const cleanParams = Object.fromEntries(
          Object.entries(params).filter(([_, value]) => 
            value !== null && value !== undefined && value !== ''
          )
        )

        return useFetch<PaginatedResponse<Message>>('/admin/messages', {
          ...getOptions(),
          baseURL,
          query: cleanParams,
          ...options,
        })
      },

      stats: (options: Record<string, unknown> = {}) => {
        return useFetch<MessageStatsResponse>('/admin/messages/stats', {
          ...getOptions(),
          baseURL,
          ...options,
        })
      },

      get: (id: number, options: Record<string, unknown> = {}) => {
        return useFetch<Message>(`/admin/messages/${id}`, {
          ...getOptions(),
          baseURL,
          ...options,
        })
      },

      bulkAction: (request: BulkActionRequest) => {
        return $fetch<ApiResponse>('/admin/messages/bulk', {
          ...getOptions(),
          baseURL,
          method: 'PATCH',
          body: request,
        })
      },
    },
  }

  return {
    auth,
    contact,
    admin,
  }
}