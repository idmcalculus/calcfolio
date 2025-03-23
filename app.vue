<template>
  <div :class="{ dark: isDark }">
    <div class="min-h-screen bg-lightBg text-black dark:bg-darkBg dark:text-white flex flex-col">

        <!-- Header -->
        <header class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h1 class="font-bold text-xl">iDM</h1>
          <nav class="hidden md:flex gap-4 items-center">
              <NuxtLink to="/">About</NuxtLink>
              <NuxtLink to="/projects">Projects</NuxtLink>
              <NuxtLink to="/contact">Contact</NuxtLink>
              <button @click="toggleDark">{{ isDark ? '☀️' : '🌙' }}</button>
          </nav>
          <!-- Mobile menu toggle -->
          <button class="md:hidden text-xl" @click="showMenu = !showMenu">
              {{ showMenu ? '✖' : '☰' }}
          </button>
        </header>

      <!-- Mobile Menu Drawer -->
      <MobileMenuDrawer
        v-if="showMenu"
        @close="showMenu = false"
      />

      <!-- Main Content -->
      <main class="flex-1">
        <NuxtPage />
      </main>

      <!-- Footer -->
      <footer class="text-center py-6 border-t border-gray-300 dark:border-gray-700">
        <p>&copy; 2025 iDM Portfolio</p>
      </footer>

      <!-- Scroll to Top Button -->
      <ScrollToTop />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useDarkMode } from '~/composables/useDarkMode'
import MobileMenuDrawer from '~/components/MobileMenuDrawer.vue'
import ScrollToTop from '~/components/ScrollToTop.vue'

// Dark mode composable
const { isDark, toggleDark } = useDarkMode()

const showMenu = ref(false)
</script>

<style>
/* Global or any resets can go here if needed */
</style>