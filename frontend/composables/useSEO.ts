export interface SEOData {
  title?: string
  description?: string
  keywords?: string[]
  image?: string
  url?: string
  type?: 'website' | 'article' | 'profile'
  author?: string
  publishedTime?: string
  modifiedTime?: string
  section?: string
  tags?: string[]
}

export const useSEO = (data: SEOData = {}) => {
  const config = useRuntimeConfig()

  // Base URL for canonical URLs and social media
  const baseUrl = config.public.siteUrl || 'https://idmcalculus.cv'

  // Default meta data
  const defaultTitle = 'Damilola Michael Ige - Software Engineer Portfolio'
  const defaultDescription = 'Professional portfolio of Damilola Michael Ige, a skilled software engineer specializing in full-stack development, web applications, and modern technologies.'
  const defaultImage = `${baseUrl}/images/profile.png`
  const defaultKeywords = [
    'software engineer',
    'full-stack developer',
    'web development',
    'portfolio',
    'Damilola Michael Ige',
    'JavaScript',
    'Vue.js',
    'Nuxt.js',
    'PHP',
    'Laravel'
  ]

  // Construct full title
  const fullTitle = data.title ? `${data.title} | ${defaultTitle}` : defaultTitle

  // Construct full description
  const description = data.description || defaultDescription

  // Construct keywords
  const keywords = data.keywords ? [...defaultKeywords, ...data.keywords] : defaultKeywords

  // Construct canonical URL
  const canonicalUrl = data.url ? `${baseUrl}${data.url}` : baseUrl

  // Construct image URL
  const imageUrl = data.image ? (data.image.startsWith('http') ? data.image : `${baseUrl}${data.image}`) : defaultImage

  // Set SEO meta tags using Nuxt's built-in useSeoMeta
  useSeoMeta({
    title: fullTitle,
    description,
    keywords: keywords.join(', '),
    author: data.author || 'Damilola Michael Ige',
    robots: 'index, follow',

    // Open Graph
    ogTitle: fullTitle,
    ogDescription: description,
    ogImage: imageUrl,
    ogType: data.type || 'website',
    ogSiteName: defaultTitle,
    ogLocale: 'en_US',

    // Twitter Card
    twitterCard: 'summary_large_image',
    twitterTitle: fullTitle,
    twitterDescription: description,
    twitterImage: imageUrl,
    twitterCreator: '@calculus_codes',
    twitterSite: '@calculus_codes'
  })

  // Set head tags for links and scripts
  useHead({
    link: [
      { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
      { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' }
    ],
    script: [
      {
        type: 'application/ld+json',
        innerHTML: JSON.stringify({
          '@context': 'https://schema.org',
          '@type': 'Person',
          name: 'Damilola Michael Ige',
          alternateName: 'IDM',
          description: description,
          url: baseUrl,
          image: imageUrl,
          sameAs: [
            'https://github.com/idmcalculus',
            'https://x.com/calculus_codes',
            'https://instagram.com/idmcalculus',
            'https://linkedin.com/in/idmcalculus'
          ],
          jobTitle: 'Software Engineer',
          knowsAbout: keywords
        })
      }
    ]
  })

  // Return SEO utilities
  return {
    title: fullTitle,
    description,
    keywords,
    canonicalUrl,
    imageUrl
  }
}