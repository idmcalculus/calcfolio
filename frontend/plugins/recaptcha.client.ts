import { VueReCaptcha } from 'vue-recaptcha-v3'

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig()
  // Explicitly cast the site key to string to help TypeScript
  const siteKey = config.public.recaptchaSiteKey as string;

  if (!siteKey) {
    console.warn('reCAPTCHA site key is not configured. Please set NUXT_PUBLIC_RECAPTCHA_SITE_KEY environment variable.')
    // Optionally, you could disable the plugin or handle this case differently
    return;
  }

  // Pass options object directly, including required loaderOptions
  nuxtApp.vueApp.use(VueReCaptcha, {
    siteKey: siteKey,
    loaderOptions: {
      // Add specific loader options here if needed later, e.g.:
      // autoHideBadge: true,
      // explicitRenderParameters: { badge: 'bottomright' }
    } // Provide at least an empty object
  });
})
