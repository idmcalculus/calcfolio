<template>
	<section class="relative my-12 px-4 max-w-5xl mx-auto" data-aos="fade-up">
	  <h2 class="text-2xl md:text-3xl font-bold mb-8 text-center">Experience</h2>
  
	  <div class="border-l-2 border-primary dark:border-primary relative ml-4">
		<div
		  v-for="(job, index) in experience"
		  :key="index"
		  class="mb-10 relative md:flex md:items-center md:justify-between md:gap-6"
		>
		  <!-- Date (left on desktop, above on mobile) -->
		  <div
			class="md:w-1/2 md:text-end md:pr-8"
			:class="{ 'md:order-2': isEven(index), 'md:order-1': !isEven(index) }"
		  >
			<p class="text-sm text-gray-600 dark:text-gray-400 font-semibold">
			  {{ job.date }}
			</p>
		  </div>
  
		  <!-- Timeline dot -->
		  <div
			class="absolute top-2.5 -left-[0.58rem] w-4 h-4 bg-primary rounded-full border-4 border-white dark:border-darkBg z-10"
		  />
  
		  <!-- Job Card -->
		  <div
			class="md:w-1/2 bg-white dark:bg-zinc-800 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-700"
			:class="{ 'md:order-1': isEven(index), 'md:order-2': !isEven(index) }"
		  >
			<h3 class="text-lg font-bold text-primary">{{ job.title }}</h3>
			<p class="text-sm text-gray-700 dark:text-gray-300">{{ job.company }}</p>
			<p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ job.description }}</p>
		  </div>
		</div>
	  </div>
	</section>
</template>
  
<script setup lang="ts">
  import { onMounted, ref } from 'vue'
  import AOS from 'aos'
  
  onMounted(() => AOS.init({ duration: 800 }))
  
  interface Job {
	title: string
	company: string
	date: string
	description: string
  }
  
  const experience = ref<Job[]>([
	{
	  title: 'Software Engineer II',
	  company: 'Aceoie Inc.',
	  date: 'September 2020 – Present',
	  description: 'Lorem ipsum dolor sit amet consectetur. Praesent efficitur eget lacus sed fermentum.'
	},
	{
	  title: 'Senior Fullstack Developer',
	  company: 'MOCC ICT Unit',
	  date: 'December 2020 – December 2022',
	  description: 'Praesent efficitur eget lacus sed fermentum. Nullam pretium ex nec erat luctus.'
	},
	{
	  title: 'Senior Fullstack Developer',
	  company: 'Versa Nigeria',
	  date: 'February 2020 – July 2020',
	  description: 'Vestibulum posuere sapien ut ligula gravida dignissim. Lorem ipsum dolor sit amet.'
	}
  ])
  
  const isEven = (index: number) => index % 2 === 0
</script>