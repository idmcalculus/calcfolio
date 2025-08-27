export default defineNuxtPlugin(() => {
  const config = useRuntimeConfig()
  const siteKey = config.public.recaptchaSiteKey as string;

  if (!siteKey) {
    console.warn('reCAPTCHA site key is not configured. Please set NUXT_PUBLIC_RECAPTCHA_SITE_KEY environment variable.')
    return;
  }

  // Load Google reCAPTCHA script manually
  if (typeof window !== 'undefined') {
    const script = document.createElement('script')
    script.src = `https://www.google.com/recaptcha/api.js?render=${siteKey}`
    script.async = true
    script.defer = true
    document.head.appendChild(script)

    // Make grecaptcha available globally
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    ;(window as any).grecaptcha = (window as any).grecaptcha || {}
  }
})
