<template>
  <!-- Apply dark class dynamically -->
  <div :class="{ dark: isDark }" class="min-h-screen flex flex-col bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-white relative">
    <!-- Loading Overlay for Logout -->
    <LoadingOverlay
      :visible="loading"
      title="Logging out..."
      subtitle="Please wait while we securely log you out"
      center-dot-class="bg-red-500"
      overlay-class="bg-gray-100/80 dark:bg-gray-900/80"
      position="fixed"
    />

    <header class="bg-white dark:bg-gray-800 shadow-md p-4 flex justify-between items-center">
      <h1 class="text-xl font-semibold">Admin Dashboard</h1>
      <!-- Right side controls -->
      <div class="flex items-center space-x-4">
         <!-- View Main Site Button -->
         <NuxtLink 
           to="/" 
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition-colors flex items-center space-x-2"
           aria-label="View main site"
         >
           <ClientOnly>
             <Icon name="lucide:external-link" size="16" />
             <template #fallback>
               <span class="inline-block w-[16px] h-[16px]"/>
             </template>
           </ClientOnly>
           <span>View Site</span>
         </NuxtLink>

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
import { ref, onMounted } from 'vue';
import { navigateTo } from '#app';
import { useDarkMode } from '~/composables/useDarkMode';
import LoadingOverlay from '~/components/LoadingOverlay.vue';

// Dark mode state and toggle function
const { isDark, toggleDark, initializeTheme } = useDarkMode();

// Initialize theme after component is mounted (client-side)
onMounted(() => {
  initializeTheme()
})

const { auth } = useApi();
const loading = ref(false);

const handleLogout = async () => {
  loading.value = true;
  try {
    await auth.logout();
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
