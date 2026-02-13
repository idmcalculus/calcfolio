<template>
	<div>
		<!-- Hero Section -->
		<HeroSection />
	
		<!-- Experience Timeline -->
		<ExperienceTimeline />
	
		<!-- Download CV button below timeline -->
		<DownloadCV class="my-10" />
	
		<!-- Project Section -->
		<ProjectSection :limit="4" />
	
		<!-- View All Projects button -->
		<ViewAllProjects class="my-10" />
	
		<!-- Skills Section -->
		<SkillsSection />
	
		<!-- Testimonials Section -->
		<TestimonialsSection />
	</div>
</template>

<script setup lang="ts">
 	import HeroSection from '~/components/HeroSection.vue'
 	import ExperienceTimeline from '~/components/ExperienceTimeline.vue'
 	import DownloadCV from '~/components/DownloadCV.vue'
 	import ProjectSection from '~/components/ProjectSection.vue'
 	import ViewAllProjects from '~/components/ViewAllProjects.vue'
 	import SkillsSection from '~/components/SkillsSection.vue'
 	import TestimonialsSection from '~/components/TestimonialsSection.vue'

 	// Set SEO meta tags for the homepage
 	useSEO({
 		title: 'Home',
 		description: 'Welcome to my professional portfolio. Discover my journey as a software engineer, explore my projects, and get in touch to discuss opportunities.',
 		keywords: ['home', 'portfolio', 'software engineer', 'projects', 'contact'],
 		type: 'profile',
 		url: '/'
 	})
</script>
