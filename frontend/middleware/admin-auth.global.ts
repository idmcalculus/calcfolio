import { defineNuxtRouteMiddleware, navigateTo } from '#app'

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

  } catch (error) {
    console.error('Auth check failed:', error)
    // On any error, redirect to login
    return navigateTo('/admin/login')
  }
})
