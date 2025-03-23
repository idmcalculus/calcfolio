<template>
	<div class="relative bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-4">
	  <!-- Source Icon -->
	  <div class="absolute top-3 right-3">
		<a v-if="sourceIcon && url" :href="url" target="_blank" rel="noopener" class="text-gray-400 hover:text-primary">
		  <component :is="sourceIcon" class="w-5 h-5" />
		</a>
	  </div>
  
	  <!-- Avatar -->
	  <img v-if="avatar" :src="avatar" alt="Avatar" class="w-12 h-12 rounded-full mb-2" >
  
	  <p class="text-sm text-gray-700 dark:text-gray-300 mb-4 italic">
		"{{ message }}"
	  </p>
	  <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100">{{ name }}</h3>
	  <p class="text-xs text-gray-600 dark:text-gray-400">{{ role }}</p>
	</div>
  </template>
  
<script setup lang="ts">
  import { computed } from 'vue'
  import { Linkedin, Mail, Twitter } from 'lucide-vue-next'
  
  interface Props {
	name: string
	role: string
	message: string
	avatar?: string
	source?: 'linkedin' | 'twitter' | 'email'
	url?: string
  }
  
  const props = defineProps<Props>()
  
  const sourceIcon = computed(() => {
	switch (props.source) {
	  case 'linkedin': return Linkedin
	  case 'email': return Mail
	  case 'twitter': return Twitter
	  default: return null
	}
  })
</script>