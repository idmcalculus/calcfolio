<template>
	<section class="my-12 px-4 max-w-5xl mx-auto" data-aos="fade-up">
	  <h2 class="text-2xl md:text-3xl font-bold mb-8 text-center">Some of my work</h2>
  
	  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
		<ProjectCard
		  v-for="(project, i) in projects"
		  :key="i"
		  :title="project.title"
		  :description="project.description"
		  :tags="project.tags"
		  :image="project.image"
		  :github="project.github"
		  :live="project.live"
		/>
	  </div>
	</section>
</template>
  
<script setup lang="ts">
  import { ref, onMounted } from 'vue'
  import AOS from 'aos'
  import ProjectCard from './ProjectCard.vue'
  
  onMounted(() => {
	AOS.init({ duration: 800 })
  })
  
  interface Project {
	title: string
	description: string
	tags: string[]
	image: string
	github?: string
	live?: string
  }
  
  const projects = ref<Project[]>([
	{
	  title: 'Aeeioe’s Website',
	  description: 'A modern, responsive website for Aeeioe, showcasing products and services.',
	  tags: ['Vue.js', 'WordPress', 'Tailwind'],
	  image: '/assets/aeeioe.jpg',
	  github: 'https://github.com/example/aeeioe',
	  live: 'https://aeeioe.com'
	},
	{
	  title: 'Catch The Ghost App',
	  description: 'A productivity app with real-time sync, built with React Native and Firebase.',
	  tags: ['React Native', 'Firebase'],
	  image: '/assets/ghost.jpg',
	  github: 'https://github.com/example/ghost'
	},
	{
	  title: 'Dress Measurement App',
	  description: 'A next-gen measuring app that uses ARKit on iOS to measure body dimensions accurately.',
	  tags: ['Swift', 'iOS', 'ARKit'],
	  image: '/assets/dress.jpg'
	},
	// ... add more projects as needed
  ])
</script>