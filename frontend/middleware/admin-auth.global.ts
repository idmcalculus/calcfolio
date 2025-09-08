import { defineNuxtRouteMiddleware, navigateTo } from '#app'

/**
 * Type guard to check if an error is authentication-related
 */
function isAuthenticationError(error: unknown): boolean {
  if (!error || typeof error !== 'object') return false

  const errorObj = error as Record<string, unknown>

  // Check status code
  if (errorObj.statusCode === 401) return true

  // Check message content
  if (typeof errorObj.message === 'string') {
    const message = errorObj.message.toLowerCase()
    if (message.includes('authentication') || message.includes('unauthorized')) {
      return true
    }
  }

  // Check server error code
  if (errorObj.serverError && typeof errorObj.serverError === 'object') {
    const serverError = errorObj.serverError as Record<string, unknown>
    if (serverError.code === 'AUTHENTICATION_ERROR') {
      return true
    }
  }

  return false
}

export default defineNuxtRouteMiddleware(async (to, from) => {
  // Skip middleware on server side or if not an admin route
  if (import.meta.server || !to.path.startsWith('/admin')) {
    return
  }

  // Allow access to the login page itself
  if (to.path === '/admin/login') {
    return
  }

  // If coming from login page, skip auth check (user just authenticated)
  if (from.path === '/admin/login') {
    return
  }

  const { auth } = useApi()

  try {
    const authResult = await auth.checkAuth({
      // Add cache buster to ensure fresh request
      query: { t: Date.now() }
    })

    if (!authResult.authenticated) {
      return navigateTo('/admin/login')
    }

    // If authenticated, allow navigation to proceed
    return

  } catch (error: unknown) {
    console.error('Auth check failed:', error)

    // Only redirect on authentication-related errors, not network/server errors
    const isAuthError = isAuthenticationError(error)

    if (isAuthError) {
      return navigateTo('/admin/login')
    }

    // For other errors (network, server issues), allow the page to load
    // The components will handle displaying error states
    console.warn('Non-auth error during auth check, allowing page to load:', error)
    return
  }
})
