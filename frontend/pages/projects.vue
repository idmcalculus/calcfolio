<template>
  <div>
    <!-- Header -->
    <section class="my-14 px-6 max-w-(--breakpoint-xl) mx-auto text-center" data-aos="fade-up">
      <p class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Portfolio</p>
      <h1 class="mt-3 text-3xl md:text-5xl font-extrabold text-gray-900 dark:text-white">All Projects</h1>
      <p class="mt-4 text-base md:text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-8 leading-relaxed">
        Explore my complete portfolio of projects, from web applications to mobile apps and everything in between.
      </p>
      <SectionDivider />
    </section>

    <!-- All Projects -->
    <ProjectSection />

    <!-- Back to Home -->
    <section class="text-center my-14" data-aos="fade-up">
      <NuxtLink 
        to="/"
        class="inline-flex items-center px-6 py-3 border border-primary/50 text-primary font-semibold transition-colors hover:bg-primary hover:text-white rounded-lg"
      >
        <Icon name="lucide:arrow-left" class="w-4 h-4 mr-2" />
        Back to Home
      </NuxtLink>
    </section>
  </div>
</template>

<script setup lang="ts">
import ProjectSection from '~/components/ProjectSection.vue'
import SectionDivider from '~/components/SectionDivider.vue'

// Set SEO meta tags for the projects page
useSEO({
  title: 'Projects',
  description: 'Explore my complete portfolio of projects, from web applications to mobile apps and everything in between. See my work in full-stack development, mobile apps, and cloud solutions.',
  keywords: ['projects', 'portfolio', 'web development', 'mobile apps', 'full-stack', 'showcase'],
  url: '/projects'
})
</script>
