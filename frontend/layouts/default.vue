<template>
  <div :class="{ dark: isDark }">
    <!-- Skip to main content link for accessibility -->
    <a
      href="#main-content"
      class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-primary text-white px-4 py-2 rounded-md z-50 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
    >
      Skip to main content
    </a>

    <div class="min-h-screen bg-light-bg text-black dark:bg-dark-bg dark:text-white flex flex-col">

        <!-- Header -->
        <header
          class="sticky top-0 z-50 w-full border-b border-gray-200/80 dark:border-zinc-800/80 bg-white/90 dark:bg-dark-bg/90 backdrop-blur supports-[backdrop-filter]:bg-white/80 supports-[backdrop-filter]:dark:bg-dark-bg/80"
          role="banner"
        >
          <div class="container mx-auto max-w-(--breakpoint-xl) px-6 py-3 flex items-center justify-between">
            <NuxtLink
              to="/"
              class="font-extrabold text-xl tracking-tight focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-2 py-1"
              aria-label="Damilola Michael Ige - Home"
            >
              IDM
            </NuxtLink>
            <div class="flex items-center gap-3 md:gap-6">
              <nav
                class="hidden md:flex gap-1 items-center"
                role="navigation"
                aria-label="Main navigation"
              >
                  <NuxtLink
                    to="/"
                    exact-active-class="nav-link-active"
                    class="text-sm font-semibold hover:text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-3 py-2"
                    aria-label="About section"
                  >
                    About
                  </NuxtLink>
                  <NuxtLink
                    to="/projects"
                    active-class="nav-link-active"
                    class="text-sm font-semibold hover:text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-3 py-2"
                    aria-label="Projects section"
                  >
                    Projects
                  </NuxtLink>
                  <NuxtLink
                    to="/contact"
                    active-class="nav-link-active"
                    class="text-sm font-semibold hover:text-primary transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-3 py-2"
                    aria-label="Contact section"
                  >
                    Contact
                  </NuxtLink>
              </nav>

              <DownloadCV class="hidden md:block" width="42" button-text="Download CV" aos="" />

              <button
                class="flex p-2 rounded-full border border-gray-200 dark:border-zinc-700 hover:text-primary hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
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
              <button
                class="md:hidden flex items-center z-10 border border-gray-200 dark:border-zinc-700 hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md p-2"
                :aria-expanded="showMenu"
                aria-controls="mobile-menu"
                :aria-label="showMenu ? 'Close mobile menu' : 'Open mobile menu'"
                @click="showMenu = !showMenu"
              >
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
          id="mobile-menu"
          role="navigation"
          aria-label="Mobile navigation"
          @close="showMenu = false"
        />
      </Transition>

      <!-- Main Content -->
      <main
        id="main-content"
        class="flex-1 w-full"
        role="main"
      >
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
import { ref, provide, onMounted } from 'vue' // Ensure provide is imported
import { useDarkMode } from '~/composables/useDarkMode'
import MobileMenuDrawer from '~/components/MobileMenuDrawer.vue'
import ScrollToTop from '~/components/ScrollToTop.vue'
import DownloadCV from '~/components/DownloadCV.vue'
import CVModal from '~/components/CVModal.vue'
import AppFooter from '~/components/AppFooter.vue'

// Dark mode composable
const { isDark, toggleDark, initializeTheme } = useDarkMode()

const showMenu = ref(false)
const showCVModal = ref(false)

// Initialize theme after component is mounted (client-side)
onMounted(() => {
  initializeTheme()
})

// Make showCVModal available to all components within this layout
provide('showCVModal', showCVModal)
</script>

<style>
html {
  scroll-behavior: smooth;
}
</style>
