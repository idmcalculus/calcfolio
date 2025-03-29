<template>
	<nav class="md:hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 py-4 space-y-2">
	  <NuxtLink to="/" class="block" @click="$emit('close')">About</NuxtLink>
	  <NuxtLink to="/projects" class="block" @click="$emit('close')">Projects</NuxtLink>
	  <NuxtLink to="/contact" class="block" @click="$emit('close')">Contact</NuxtLink>
	  <button @click="toggleDark" class="block mt-2">
		{{ isDark ? '☀️ Light Mode' : '🌙 Dark Mode' }}
	  </button>
	</nav>
</template>
  
<script setup lang="ts">
  import { useDarkMode } from '~/composables/useDarkMode'
  const { isDark, toggleDark } = useDarkMode()
</script>