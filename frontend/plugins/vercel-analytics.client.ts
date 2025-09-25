import { inject } from '@vercel/analytics'
import { SpeedInsights } from '@vercel/speed-insights/nuxt'

export default defineNuxtPlugin(() => {
    // Only initialize analytics when actually deployed to Vercel
    // Check for Vercel-specific environment variables
    if (process.env.VERCEL_ENV || process.env.VERCEL_URL) {
        try {
            // Initialize Vercel Analytics
            inject();

            // Initialize Vercel Speed Insights for Web Vitals
            SpeedInsights();
        } catch (error) {
            console.warn('Failed to initialize Vercel Analytics:', error)
        }
    }
})