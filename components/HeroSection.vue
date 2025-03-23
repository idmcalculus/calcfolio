<template>
	<section class="pt-24 pb-16 text-center px-4 max-w-4xl mx-auto" data-aos="fade-down">
	  <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
		Hi, I’m <span class="text-primary">Damilola</span>
	  </h1>
	  <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
		A creative software engineer with a passion for building responsive web and mobile experiences.
	  </p>
	  <div class="flex flex-wrap justify-center gap-4">
		<NuxtLink
		  to="/contact"
		  class="px-6 py-3 bg-primary text-white rounded hover:bg-red-600 transition"
		>
		  Get In Touch
		</NuxtLink>
		<NuxtLink
		  to="/projects"
		  class="px-6 py-3 border border-primary text-primary rounded hover:bg-primary hover:text-white transition"
		>
		  View Projects
		</NuxtLink>
	  </div>
	</section>
</template>
  
<script setup lang="ts">
	import { onMounted } from 'vue'
	import AOS from 'aos'
	onMounted(() => AOS.init({ duration: 800 }))
</script>