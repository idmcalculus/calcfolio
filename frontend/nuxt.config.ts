// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: false },

  modules: [
    '@nuxt/fonts',
    '@nuxt/eslint',
    '@nuxt/icon',
    '@nuxt/image',
    '@nuxt/test-utils',
    '@nuxt/scripts',
    '@nuxtjs/tailwindcss'
  ],

  css: [
    './assets/css/tailwind.css',
    'aos/dist/aos.css'
  ],

  fonts: {
    provider: 'google',
    families: [
      {
        name: 'Manrope',
        weights: [400, 600, 700],
        provider: 'google',
      },
    ]
  },

  postcss: {
    plugins: {
      'postcss-nesting': {},
      tailwindcss: {},
      autoprefixer: {},
    },
  },

  runtimeConfig: {
    public: {
      backendUrl: process.env.NUXT_PUBLIC_BACKEND_URL || 'http://localhost:8080', 
      recaptchaSiteKey: process.env.NUXT_PUBLIC_RECAPTCHA_SITE_KEY || '',
    },
  },

  typescript: {
    strict: true
  },

  vite: {
    server: {
      hmr: {
        timeout: 5000,
        protocol: 'ws'
      }
    }
  },

  app: {
    head: {
      link: [
        { rel: 'icon', type: 'image/png', href: '/favicon/favicon-96x96.png', sizes: '96x96' },
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon/favicon.svg' },
        { rel: 'shortcut icon', href: '/favicon/favicon.ico' },
        { rel: 'apple-touch-icon', href: '/favicon/apple-touch-icon.png', sizes: '180x180' },
        { rel: 'manifest', href: '/favicon/site.webmanifest' }, 
      ]
    }
  }
})
