<template>
	<button
	  v-if="visible"
	  class="fixed bottom-5 right-6 z-50 w-11 h-11 inline-flex items-center justify-center rounded-full bg-primary text-white shadow-lg border border-primary hover:bg-red-700 hover:border-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
	  aria-label="Scroll to top"
	  @click="scrollToTop"
	>
	  <Icon name="lucide:arrow-up" class="w-5 h-5" />
	</button>
</template>
  
<script setup lang="ts">
  import { ref, onMounted, onUnmounted } from 'vue'
  
  const visible = ref(false)
  
  const toggleVisibility = () => {
	visible.value = window.scrollY > 300
  }
  
  const scrollToTop = () => {
	window.scrollTo({ top: 0, behavior: 'smooth' })
  }
  
  onMounted(() => {
	window.addEventListener('scroll', toggleVisibility)
  })
  onUnmounted(() => {
	window.removeEventListener('scroll', toggleVisibility)
  })
</script>
