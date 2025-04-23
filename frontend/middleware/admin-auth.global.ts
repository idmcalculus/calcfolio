import { defineNuxtRouteMiddleware, navigateTo, useRuntimeConfig } from '#app'

export default defineNuxtRouteMiddleware(async (to) => {
  // Skip middleware on server side or if not an admin route
  if (import.meta.server || !to.path.startsWith('/admin')) { // Use import.meta.server
    return
  }

  // Allow access to the login page itself
  if (to.path === '/admin/login') {
    return
  }

  const config = useRuntimeConfig()
  const apiUrl = config.public.backendUrl

  try {
    // Check authentication status with the backend
    // Ensure credentials (cookies) are included in the fetch request
    const res = await fetch(`${apiUrl}/admin/check`, {
      credentials: 'include', // Important for sending session cookies
      headers: {
        'Accept': 'application/json',
      }
    });

    if (!res.ok) {
      // Handle network errors or non-JSON responses from backend check
      console.error('Admin auth check failed:', res.status, res.statusText);
      return navigateTo('/admin/login'); // Redirect on error
    }

    const data = await res.json();

    if (!data.authenticated) {
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
