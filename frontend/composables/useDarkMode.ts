import { ref, watch, nextTick } from 'vue'

export const useDarkMode = () => {
  // Initialize as false to match SSR, will be updated after hydration
  const isDark = ref(false)
  const isHydrated = ref(false)

  // Initialize theme only on client after component is mounted
  const initializeTheme = () => {
    if (import.meta.client && !isHydrated.value) {
      const theme = loadThemePreference()
      isDark.value = theme
      updateTheme(theme)
      isHydrated.value = true
    }
  }

  // Watch for changes and save to localStorage (only after hydration)
  watch(isDark, (newValue) => {
    if (import.meta.client && isHydrated.value) {
      // Update localStorage
      localStorage.setItem('theme', newValue ? 'dark' : 'light')
      // Update document class for Tailwind
      updateTheme(newValue)
    }
  })

  // Toggle function
  const toggleDark = () => {
    isDark.value = !isDark.value
  }

  return {
    isDark,
    toggleDark,
    initializeTheme,
    isHydrated
  }
}

// Helper functions
function loadThemePreference(): boolean {
  if (!import.meta.client) return false
  
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme) {
    return savedTheme === 'dark'
  }
  
  // If no saved preference, use system preference
  return window.matchMedia('(prefers-color-scheme: dark)').matches
}

function updateTheme(isDark: boolean) {
  if (!import.meta.client) return
  
  // Update document class for Tailwind
  nextTick(() => {
    if (isDark) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  })
}