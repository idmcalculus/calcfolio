<template>
  <!-- Apply dark class dynamically -->
  <div :class="{ dark: isDark }" class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white">
    <header class="bg-white dark:bg-gray-800 shadow-md p-4 flex justify-between items-center">
      <h1 class="text-xl font-semibold">Admin Dashboard</h1>
      <!-- Right side controls -->
      <div class="flex items-center space-x-4">
         <!-- Dark Mode Toggle -->
         <button
            class="flex p-2 rounded-full hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
            aria-label="Toggle dark mode"
            @click="toggleDark"
          >
            <ClientOnly> 
              <Icon v-if="isDark" name="lucide:sun" size="20" />
              <Icon v-else name="lucide:moon" size="20" />
              <template #fallback>
                <span class="inline-block w-[20px] h-[20px]"/> 
              </template>
            </ClientOnly>
          </button>

          <!-- Logout Button -->
          <button
            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded text-sm disabled:opacity-50"
            :disabled="loading"
        @click="handleLogout"
          >
            {{ loading ? 'Logging out...' : 'Logout' }}
          </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <div class="flex flex-1 overflow-hidden">
       <main class="flex-1 p-6 lg:px-8 overflow-y-auto">
        <slot /> <!-- Page content goes here -->
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { navigateTo, useRuntimeConfig } from '#app';
import { useDarkMode } from '~/composables/useDarkMode';

// Dark mode state and toggle function
const { isDark, toggleDark } = useDarkMode();

const config = useRuntimeConfig();
const apiUrl = config.public.backendUrl;
const loading = ref(false);

const handleLogout = async () => {
  loading.value = true;
  try {
    const res = await fetch(`${apiUrl}/admin/logout`, {
      method: 'POST',
      credentials: 'include', // Send cookies
       headers: {
        'Accept': 'application/json',
      }
    });

    if (!res.ok) {
      // Attempt to parse error, but proceed with logout navigation regardless
       console.error('Logout request failed:', res.status);
       try {
         const errorData = await res.json();
         console.error('Logout error data:', errorData);
       } catch { /* Ignore parsing error */ }
    }
    // Even if backend logout fails, force redirect to login on client
    await navigateTo('/admin/login');

  } catch (error) {
    console.error('Error during logout:', error);
     // Force redirect to login on any error
    await navigateTo('/admin/login');
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
/* Add specific admin layout styles if needed */
</style>
