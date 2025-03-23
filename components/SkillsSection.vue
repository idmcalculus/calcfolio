<template>
	<section class="my-12 px-4 max-w-5xl mx-auto" data-aos="fade-up">
	  <h2 class="text-2xl md:text-3xl font-bold mb-6 text-center">My Skills</h2>
	  <div class="flex flex-wrap gap-3 justify-center">
		<span
		  v-for="(skill, index) in skills"
		  :key="index"
		  class="px-3 py-1 text-sm rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border dark:border-gray-600"
		>
		  {{ skill }}
		</span>
	  </div>
	</section>
</template>
  
<script setup lang="ts">
  import { ref, onMounted } from 'vue'
  import AOS from 'aos'
  
  onMounted(() => AOS.init({ duration: 800 }))
  
  const skills = ref([
	'Vue.js', 'Nuxt.js', 'React', 'TypeScript', 'JavaScript', 'Swift',
	'GraphQL', 'REST APIs', 'PostgreSQL', 'MySQL', 'MongoDB', 'PHP', 'Laravel',
	'Docker', 'Kubernetes', 'AWS', 'GCP', 'CI/CD', 'Tailwind CSS', 'HTML', 'CSS',
	'Jest', 'Mocha', 'GitHub Actions'
  ])
</script>