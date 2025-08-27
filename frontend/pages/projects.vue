<template>
  <div>
    <!-- Header -->
    <section class="my-12 px-6 max-w-(--breakpoint-xl) mx-auto text-center" data-aos="fade-up">
      <h1 class="text-3xl md:text-4xl font-bold mb-4">All Projects</h1>
      <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto mb-8">
        Explore my complete portfolio of projects, from web applications to mobile apps and everything in between.
      </p>
      <SectionDivider />
    </section>

    <!-- All Projects -->
    <ProjectSection />

    <!-- Back to Home -->
    <section class="text-center my-12" data-aos="fade-up">
      <NuxtLink 
        to="/"
        class="inline-flex items-center px-6 py-3 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white transition hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg"
      >
        <Icon name="lucide:arrow-left" class="w-4 h-4 mr-2" />
        Back to Home
      </NuxtLink>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import AOS from 'aos'
import ProjectSection from '~/components/ProjectSection.vue'
import SectionDivider from '~/components/SectionDivider.vue'

// Set page meta
definePageMeta({
  title: 'Projects - Damilola Michael Ige',
  description: 'Explore my complete portfolio of projects, from web applications to mobile apps and everything in between.'
})

onMounted(() => {
  AOS.init({ duration: 800 })
})
</script>