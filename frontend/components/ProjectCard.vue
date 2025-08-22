<template>
	<div class="p-4 bg-transparent overflow-hidden transition-all hover:shadow-lg hover:scale-105">
	  <NuxtImg :src="image" :alt="title" class="w-full h-64 object-cover rounded-md" />
  
	  <div class="pt-4">
		<div class="flex items-center justify-between mb-2">
			<h3 class="text-lg font-semibold mb-1">{{ title }}</h3>
			<div class="flex gap-4 text-sm">
				<a
					v-if="github"
					:href="github"
					target="_blank"
					class="hover:text-primary"
				>
					<Icon name="simple-icons:github" size="16" />
				</a>
				<a
					v-if="link"
					:href="link"
					target="_blank"
					class="hover:text-primary"
				>
					<Icon name="lucide:external-link" size="16" />
				</a>
			</div>
		</div>

		<p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
		  {{ description }}
		</p>
  
		<div class="flex flex-wrap gap-1 mb-3">
		  <span
			v-for="(tag, i) in tags"
			:key="i"
			class="text-xs py-1 text-primary rounded"
		  >{{ tag }}{{ i < tags.length - 1 ? ' - ' : '' }}</span>
		</div>
	  </div>
	</div>
</template>
  
<script setup lang="ts">
  defineProps<{
	title: string
	description: string
	tags: string[]
	image?: string
	link?: string
	github?: string
  }>()
</script>
