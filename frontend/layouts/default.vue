<template>
  <div :class="{ dark: isDark }">
    <div class="min-h-screen bg-lightBg text-black dark:bg-darkBg dark:text-white flex flex-col">

        <!-- Header -->
        <header class="sticky top-0 z-50 bg-white dark:bg-darkBg w-full">
          <div class="container mx-auto max-w-screen-xl px-6 py-4 flex items-center justify-between">
            <NuxtLink to="/" class="font-bold text-xl">IDM</NuxtLink>
            <div class="flex gap-6 items-center">
              <nav class="hidden md:flex gap-6 items-center">
                  <NuxtLink to="/" exact-active-class="nav-link-active" class="hover:text-primary transition-colors">About</NuxtLink>
                  <NuxtLink to="/projects" active-class="nav-link-active" class="hover:text-primary transition-colors">Project</NuxtLink>
                  <NuxtLink to="/contact" active-class="nav-link-active" class="hover:text-primary transition-colors">Contact</NuxtLink>
              </nav>

              <DownloadCV class="hidden md:block" width="40" button-text="Download CV" aos="" />

              <button
                class="flex p-2 rounded-full hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                aria-label="Toggle dark mode"
                @click="toggleDark"
              >
                <ClientOnly> 
                  <Icon v-if="isDark" name="lucide:sun" size="20" />
                  <Icon v-else name="lucide:moon" size="20" />
                  <!-- Provide a fallback for SSR/initial load if desired, though often unnecessary for icons -->
                  <template #fallback>
                    <!-- Optional: You could put a placeholder here, but an empty span is fine -->
                    <span class="inline-block w-[20px] h-[20px]"/> 
                  </template>
                </ClientOnly>
              </button>

              <!--  Mobile menu toggle -->
              <button class="md:hidden flex items-center z-10" @click="showMenu = !showMenu">
                  <Icon v-if="showMenu" :name="'lucide:x'" size="24" />
                  <Icon v-else :name="'lucide:menu'" size="24" />
              </button>
            </div>
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
          <slot /> <!-- NuxtPage content will be rendered here by the layout system -->
      </main>

      <!-- Footer -->
      <AppFooter />

      <!-- Scroll to Top Button -->
      <ScrollToTop />

      <!-- CV Modal -->
      <CVModal />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, provide } from 'vue' // Ensure provide is imported
import { useDarkMode } from '~/composables/useDarkMode'
import MobileMenuDrawer from '~/components/MobileMenuDrawer.vue'
import ScrollToTop from '~/components/ScrollToTop.vue'
import DownloadCV from '~/components/DownloadCV.vue'
import CVModal from '~/components/CVModal.vue'
import AppFooter from '~/components/AppFooter.vue'

// Dark mode composable
const { isDark, toggleDark } = useDarkMode()

const showMenu = ref(false)
const showCVModal = ref(false)

// Make showCVModal available to all components within this layout
provide('showCVModal', showCVModal)
</script>

<style>
html {
  scroll-behavior: smooth;
}
/* Ensure slide transition is defined if used */
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}
.slide-enter-from,
.slide-leave-to {
  transform: translateX(100%);
}
</style>
