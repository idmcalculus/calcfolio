// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: false },

  build: {
    transpile: ['estree-walker']
  },

  modules: [
    '@nuxt/fonts',
    '@nuxt/eslint',
    '@nuxt/icon',
    '@nuxt/image',
    '@nuxt/test-utils',
    '@nuxt/scripts',
    'nuxt-schema-org',
    '@nuxtjs/robots',
    ['@nuxt/ui', {
      global: false, // Don't apply global styles
      prefix: 'U' // Prefix all UI components
    }]
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
      '@tailwindcss/postcss': {},
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
    },
    optimizeDeps: {
      include: []
    },
    ssr: {
      noExternal: []
    }
  },

  app: {
    head: {
      htmlAttrs: {
        lang: 'en' // Set HTML language for accessibility
      },
      title: 'Damilola Michael Ige - Software Engineer Portfolio',
      meta: [
        {
          name: 'description',
          content: 'Professional portfolio of Damilola Michael Ige, a skilled software engineer specializing in full-stack development, web applications, and modern technologies.'
        },
        {
          name: 'keywords',
          content: 'software engineer, full-stack developer, web development, portfolio, Damilola Michael Ige, JavaScript, Vue.js, Nuxt.js, PHP, Laravel'
        },
        {
          name: 'author',
          content: 'Damilola Michael Ige'
        },
        {
          name: 'robots',
          content: 'index, follow'
        },
        {
          name: 'viewport',
          content: 'width=device-width, initial-scale=1.0'
        },
        {
          name: 'theme-color',
          content: '#000000'
        }
      ],
      link: [
        { rel: 'icon', type: 'image/png', href: '/favicon/favicon-96x96.png', sizes: '96x96' },
        { rel: 'icon', type: 'image/svg+xml', href: '/favicon/favicon.svg' },
        { rel: 'shortcut icon', href: '/favicon/favicon.ico' },
        { rel: 'apple-touch-icon', href: '/favicon/apple-touch-icon.png', sizes: '180x180' },
        { rel: 'manifest', href: '/favicon/site.webmanifest' },
        { rel: 'canonical', href: process.env.NUXT_PUBLIC_SITE_URL || 'https://idmcalculus.cv' }
      ]
    }
  },

  // SEO and Accessibility Configurations
  site: {
    url: process.env.NUXT_PUBLIC_SITE_URL || 'https://idmcalculus.cv',
    name: 'Damilola Michael Ige - Software Engineer Portfolio',
    description: 'Professional portfolio of Damilola Michael Ige, a skilled software engineer specializing in full-stack development, web applications, and modern technologies.',
    defaultLocale: 'en'
  },

  // Schema.org structured data
  schemaOrg: {
    identity: {
      type: 'Person',
      name: 'Damilola Michael Ige',
      alternateName: 'IDM',
      description: 'Software Engineer specializing in full-stack development',
      url: process.env.NUXT_PUBLIC_SITE_URL || 'https://idmcalculus.cv',
      sameAs: [
        'https://github.com/idmcalculus',
        'https://linkedin.com/in/idmcalculus'
      ]
    }
  },

  // Sitemap and Robots will be configured via their respective modules
  // These will be auto-generated based on the site configuration above
})
