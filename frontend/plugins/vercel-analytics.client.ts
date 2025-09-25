import { inject } from '@vercel/analytics'
import { SpeedInsights } from '@vercel/speed-insights/nuxt'

export default defineNuxtPlugin(() => {
    // Initialize Vercel Analytics
    inject()

    // Initialize Vercel Speed Insights for Web Vitals
    SpeedInsights()
})