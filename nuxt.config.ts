// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: false },

  modules: [
    '@nuxt/fonts',
    '@nuxt/eslint',
    '@nuxt/icon',
    '@nuxt/image',
    '@nuxt/ui',
    '@nuxt/test-utils',
    '@nuxt/scripts',
    '@nuxtjs/tailwindcss'
  ],

  css: [
    './assets/css/tailwind.css'
  ],

  fonts: {
    provider: 'google',
    families: [
      {
        name: 'Roboto',
        weights: [400, 500, 700],
        styles: ['normal', 'italic'],
        provider: 'google',
      },
      {
        name: 'Inter',
        weights: [400, 500, 700],
        styles: ['normal', 'italic'],
        provider: 'google',
      },
    ]
  },

  tailwindcss: {
    viewer: false,
  },

  runtimeConfig: {
    public: {
      backendUrl: process.env.BACKEND_URL || 'http://localhost:8080',
    },
  },

  typescript: {
    strict: true
  }
})