import { ref, watchEffect, onMounted } from 'vue'

const isDark = ref(false)

export function useDarkMode() {
  onMounted(() => {
    // Ensure this only runs in the browser
    if (typeof window !== 'undefined') {
      const savedTheme = localStorage.getItem('theme')
      if (savedTheme === 'dark') {
        isDark.value = true
      } else if (savedTheme === 'light') {
        isDark.value = false
      } else {
        // fallback to system preference
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches
      }
    }
  })

  watchEffect(() => {
    if (typeof window !== 'undefined') {
      localStorage.setItem('theme', isDark.value ? 'dark' : 'light')
    }
  })

  const toggleDark = () => {
    isDark.value = !isDark.value
  }

  return {
    isDark,
    toggleDark
  }
}