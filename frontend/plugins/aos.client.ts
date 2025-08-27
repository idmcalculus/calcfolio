// AOS (Animate On Scroll) plugin
import AOS from 'aos'
import 'aos/dist/aos.css'

export default defineNuxtPlugin((nuxtApp) => {
  if (import.meta.client) {
    // Initialize AOS after the app is fully hydrated
    nuxtApp.hook('app:mounted', () => {
      // Small delay to ensure DOM is ready and hydration is complete
      setTimeout(() => {
        AOS.init({
          duration: 800,
          easing: 'ease-in-out',
          once: false,
          startEvent: 'DOMContentLoaded',
          initClassName: 'aos-init',
          animatedClassName: 'aos-animate',
          useClassNames: false,
          disableMutationObserver: false,
          debounceDelay: 50,
          throttleDelay: 99
        })
        // Force refresh to ensure all elements are properly detected
        AOS.refresh()
      }, 100)
    })

    // Refresh AOS when navigating between pages
    nuxtApp.hook('page:finish', () => {
      if (import.meta.client) {
        setTimeout(() => {
          AOS.refresh()
        }, 100)
      }
    })
  }
})
