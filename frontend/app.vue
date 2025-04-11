<template>
  <div :class="{ dark: isDark }">
    <div class="min-h-screen bg-lightBg text-black dark:bg-darkBg dark:text-white flex flex-col">

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white dark:bg-darkBg w-full">
          <div class="container mx-auto max-w-screen-xl px-6 py-4 flex items-center justify-between">
            <NuxtLink to="/" class="font-bold text-xl">IDM</NuxtLink>
            <nav class="hidden md:flex gap-6 items-center">
                <NuxtLink to="/" exact-active-class="nav-link-active" class="hover:text-primary transition-colors">About</NuxtLink>
                <NuxtLink to="/projects" active-class="nav-link-active" class="hover:text-primary transition-colors">Project</NuxtLink>
                <NuxtLink to="/contact" active-class="nav-link-active" class="hover:text-primary transition-colors">Contact</NuxtLink>
                <DownloadCV width="40" button-text="Download CV" />
                <button
                  class="flex p-2 rounded-full hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                  aria-label="Toggle dark mode"
                  @click="toggleDark"
                >
                  <Icon v-if="isDark" :name="'lucide:sun'" size="20" />
                  <Icon v-else :name="'lucide:moon'" size="20" />
                </button>
            </nav>

            <!--  Mobile icons -->
            <div class="md:hidden flex items-center absolute top-4 right-6 z-10">
              <button
                  class="mr-4 hover:text-primary transition-colors"
                  aria-label="Toggle dark mode"
                  @click="toggleDark"
                >
                  <Icon v-if="isDark" :name="'lucide:sun'" size="20" />
                  <Icon v-else :name="'lucide:moon'" size="20" />
              </button>

              <!--  Mobile menu toggle -->
              <button @click="showMenu = !showMenu">
                  <Icon v-if="showMenu" :name="'lucide:x'" size="24" />
                  <Icon v-else :name="'lucide:menu'" size="24" />
              </button>
            </div> <!-- Close mobile icons div -->
          </div> <!-- Close container div -->
        </header>

      <!-- Mobile Menu Drawer -->
      <Transition name="slide">
        <MobileMenuDrawer
          v-if="showMenu"
          @close="showMenu = false"
        />
      </Transition>

      <!-- Main Content -->
      <main class="flex-1 w-full">
          <NuxtPage />
      </main>

      <!-- Footer -->
      <footer class="text-center py-6 border-t border-gray-300 dark:border-gray-700">
        <p>&copy; 2025 iDM Portfolio</p>
      </footer>

      <!-- Scroll to Top Button -->
      <ScrollToTop />

      <!-- CV Modal -->
      <CVModal />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue'
import { useDarkMode } from '~/composables/useDarkMode'
import MobileMenuDrawer from '~/components/MobileMenuDrawer.vue'
import ScrollToTop from '~/components/ScrollToTop.vue'
import DownloadCV from '~/components/DownloadCV.vue'
import CVModal from '~/components/CVModal.vue'

// Dark mode composable
const { isDark, toggleDark } = useDarkMode()

const showMenu = ref(false)
const showCVModal = ref(false)

// Make showCVModal available to all components
provide('showCVModal', showCVModal)
</script>
