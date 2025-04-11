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
		  :live="project.link"
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
	link?: string
  }
  
  const projects = ref<Project[]>([
	{
		title: "AEEIOE",
		description: "An e-commerce platform for fashion and lifestyle products",
		image: "https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?q=80&w=800&auto=format",
		tags: ["Vue.js", "Node.js", "MongoDB"],
		link: "https://aeeioe.com",
		github: "https://github.com/idmcalculus/aeeioe"
	},
	{
		title: "Ghost CMS Theme",
		description: "A minimal, responsive theme for Ghost blogging platform",
		image: "https://images.unsplash.com/photo-1555066931-4365d14bab8c?q=80&w=800&auto=format",
		tags: ["HTML", "CSS", "JavaScript", "Ghost"],
		link: "https://ghost-theme-demo.com",
		github: "https://github.com/idmcalculus/ghost-theme"
	},
	{
		title: "Dress Store",
		description: "Mobile-first e-commerce application for a dress boutique",
		image: "https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=800&auto=format",
		tags: ["React Native", "Express", "PostgreSQL"],
		link: "https://dressstore.com",
		github: "https://github.com/idmcalculus/dress-store"
	}
	// ... add more projects as needed
  ])
</script>