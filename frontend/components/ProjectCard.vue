<template>
	<div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow overflow-hidden transition hover:shadow-lg">
	  <img :src="image" :alt="title" class="w-full h-48 object-cover" >
  
	  <div class="p-4">
		<h3 class="text-lg font-semibold text-primary mb-1">{{ title }}</h3>
		<p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
		  {{ description }}
		</p>
  
		<div class="flex flex-wrap gap-2 mb-3">
		  <span
			v-for="(tag, i) in tags"
			:key="i"
			class="text-xs px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded"
		  >
			{{ tag }}
		  </span>
		</div>
  
		<div class="flex gap-4 text-sm">
		  <a
			v-if="link"
			:href="link"
			target="_blank"
			class="hover:underline text-blue-600 dark:text-blue-400"
		  >
			Live
		  </a>
		  <a
			v-if="github"
			:href="github"
			target="_blank"
			class="hover:underline text-blue-600 dark:text-blue-400"
		  >
			Code
		  </a>
		</div>
	  </div>
	</div>
</template>
  
<script setup lang="ts">
  defineProps<{
	title: string
	description: string
	tags: string[]
	image: string
	link?: string
	github?: string
  }>()
</script>