export default defineNuxtPlugin((nuxtApp) => {
  if (!import.meta.client) return

  let aos: { init: (options: Record<string, unknown>) => void; refresh: () => void } | null = null
  let aosLoader: Promise<typeof import('aos')> | null = null

  const hasAosTargets = () => Boolean(document.querySelector('[data-aos]'))
  const prefersReducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches

  const loadAOS = async () => {
    if (aos) return aos
    aosLoader ??= import('aos')
    const module = await aosLoader
    aos = module.default
    return aos
  }

  const initOrRefreshAOS = async () => {
    if (prefersReducedMotion() || !hasAosTargets()) return

    const AOS = await loadAOS()
    if (!AOS) return

    if (!document.documentElement.classList.contains('aos-init')) {
      AOS.init({
        duration: 700,
        easing: 'ease-out',
        once: true,
        startEvent: 'DOMContentLoaded',
        initClassName: 'aos-init',
        animatedClassName: 'aos-animate',
        disableMutationObserver: false,
        debounceDelay: 50,
        throttleDelay: 99
      })
    } else {
      AOS.refresh()
    }
  }

  nuxtApp.hook('app:mounted', () => {
    if ('requestIdleCallback' in window) {
      window.requestIdleCallback(() => {
        void initOrRefreshAOS()
      }, { timeout: 1200 })
      return
    }
    setTimeout(() => {
      void initOrRefreshAOS()
    }, 200)
  })

  nuxtApp.hook('page:finish', () => {
    setTimeout(() => {
      void initOrRefreshAOS()
    }, 120)
  })
})
