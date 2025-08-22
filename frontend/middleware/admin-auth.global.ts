import { defineNuxtRouteMiddleware, navigateTo } from '#app'

export default defineNuxtRouteMiddleware(async (to) => {
  // Skip middleware on server side or if not an admin route
  if (import.meta.server || !to.path.startsWith('/admin')) {
    return
  }

  // Allow access to the login page itself
  if (to.path === '/admin/login') {
    return
  }

  const { auth } = useApi()

  try {
    const { data } = await auth.checkAuth({
      server: false, // Client-side only
    })

    if (!data.value?.authenticated) {
      console.log('User not authenticated, redirecting to login.');
      return navigateTo('/admin/login');
    }
    // If authenticated, allow navigation to proceed
    console.log('User authenticated, allowing access to:', to.path);

  } catch (error) {
    console.error('Error during admin authentication check:', error);
    // Redirect to login on any unexpected error during the check
    return navigateTo('/admin/login');
  }
})
