<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="w-full max-w-md p-8 space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold text-center text-gray-900 dark:text-white">Admin Login</h2>
      <form class="space-y-4" @submit.prevent="handleLogin">
        <div class="relative">
          <input
            id="username"
            v-model="username"
            type="text"
            required
            class="form-input peer"
            placeholder=" "
          >
          <label for="username" class="form-label">Username</label>
        </div>
        <div class="relative">
          <input
            id="password"
            v-model="password"
            :type="passwordFieldType"
            required
            class="form-input peer pr-10" 
            placeholder=" "
          >
          <label for="password" class="form-label">Password</label>
          <!-- Toggle Button -->
          <button 
            type="button"
            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
            aria-label="Toggle password visibility"
            @click="togglePasswordVisibility"
          >
            <!-- Eye Icon (Conditional) -->
            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L6.228 6.228" />
            </svg>
          </button>
        </div>
        <button
          type="submit"
          class="w-full px-4 py-2 font-semibold text-white bg-primary rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
          :disabled="loading"
        >
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
        <p v-if="errorMsg" class="text-sm text-center text-red-500">
          {{ errorMsg }}
        </p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue' // Import computed
import { navigateTo } from '#app'

// Define page meta to use a different layout if needed, or disable auth middleware for this page
definePageMeta({
  // Example: middleware: false // If you want to explicitly disable global middleware
  layout: 'admin-minimal' // Assuming a minimal layout without navbars etc.
})

const { auth } = useApi()

const username = ref('')
const password = ref('')
const showPassword = ref(false) // State for password visibility
const loading = ref(false)
const errorMsg = ref('') // Keep for potential inline message
// Removed toast for testing - will use console logging instead

// Computed property for password input type
const passwordFieldType = computed(() => (showPassword.value ? 'text' : 'password'));

// Toggle password visibility
const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value;
};

const handleLogin = async () => {
  loading.value = true
  errorMsg.value = ''

  try {
    const data = await auth.login({
      username: username.value,
      password: password.value
    })

    if (!data.success) {
      throw new Error(data.message || 'Login failed. Please check your credentials.')
    }

    // Login successful, navigate to the admin dashboard
    console.log('Login successful!'); // Optional success log
    await navigateTo('/admin/dashboard')

  } catch (error: unknown) {
    console.error('Admin login error:', error)
    const message = error instanceof Error ? error.message : 'An unexpected error occurred.'
    errorMsg.value = message // Show inline error
    console.error('Login error:', message); // Show console error
  } finally {
    loading.value = false
  }
}
</script>

<style scoped lang="postcss">
/* Reuse form styles from contact page or define specific ones */
.form-input {
  @apply w-full px-4 py-3 rounded bg-gray-200 dark:bg-gray-700 text-black dark:text-white outline-none transition-all duration-300 border-2 border-transparent;
  background-clip: padding-box;
}

.form-input:focus {
   border-color: theme('colors.primary'); /* Simple focus border */
}

.form-label {
  @apply absolute text-gray-500 dark:text-gray-400 transition-all duration-300 pointer-events-none px-1;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
}

.form-input:focus ~ .form-label,
.form-input:not(:placeholder-shown) ~ .form-label {
  @apply text-sm text-primary bg-gray-100 dark:bg-gray-800; /* Adjust background to match page */
  top: 0;
  transform: translateY(-50%) scale(0.85);
}
</style>
