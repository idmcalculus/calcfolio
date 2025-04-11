import { ref, watch } from 'vue'

export const useDarkMode = () => {
  // Initialize from localStorage or system preference
  const isDark = ref(loadThemePreference())

  // Watch for changes and save to localStorage
  watch(isDark, (newValue) => {
    // Update localStorage
    localStorage.setItem('theme', newValue ? 'dark' : 'light')
    // Update document class for Tailwind
    updateTheme(newValue)
  })

  // Toggle function
  const toggleDark = () => {
    isDark.value = !isDark.value
  }

  // Initialize theme on page load
  if (import.meta.client) {
    updateTheme(isDark.value)
  }

  return {
    isDark,
    toggleDark
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
  // Update document class for Tailwind
  if (isDark) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}