// AOS (Animate On Scroll) plugin
import AOS from 'aos'
import 'aos/dist/aos.css'

export default defineNuxtPlugin((nuxtApp) => {
  if (import.meta.client) {
    nuxtApp.hook('app:mounted', () => {
      AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: false
      })
    })
  }
})
