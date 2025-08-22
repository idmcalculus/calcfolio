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
</script>