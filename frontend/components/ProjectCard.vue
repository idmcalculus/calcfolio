<template>
	<article class="p-4 bg-transparent overflow-hidden transition-all hover:shadow-lg hover:scale-105 focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 rounded-lg">
	  <NuxtImg
	 :src="imageUrl"
	 :alt="`Screenshot or preview of ${title} project`"
	 class="w-full h-64 object-cover rounded-md"
	 loading="lazy"
	  />

	  <div class="pt-4">
		<div class="flex items-center justify-between mb-2">
			<h3 class="text-lg font-semibold mb-1">{{ title }}</h3>
			<div class="flex gap-4 text-sm">
				<a
					v-if="github"
					:href="github"
					target="_blank"
					rel="noopener noreferrer"
					:aria-label="`View ${title} source code on GitHub`"
					class="hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md p-1"
				>
					<Icon name="simple-icons:github" size="16" />
				</a>
				<a
					v-if="link"
					:href="link"
					target="_blank"
					rel="noopener noreferrer"
					:aria-label="`Visit live demo of ${title}`"
					class="hover:text-primary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md p-1"
				>
					<Icon name="lucide:external-link" size="16" />
				</a>
			</div>
		</div>

		<p class="text-sm text-gray-600 dark:text-gray-300 mb-3">
		  {{ description }}
		</p>

		<div class="flex flex-wrap gap-1 mb-3" role="list" aria-label="Technologies used">
		  <span
			v-for="(tag, i) in tags"
			:key="i"
			role="listitem"
			class="text-xs py-1 text-primary rounded"
			:aria-label="`Technology: ${tag}`"
		  >{{ tag }}{{ i < tags.length - 1 ? ' - ' : '' }}</span>
		</div>
	  </div>
	</article>
</template>
  
<script setup lang="ts">
  defineProps<{
	title: string
	description: string
	tags: string[]
	imageUrl: string
	link?: string
	github?: string
  }>()
</script>
