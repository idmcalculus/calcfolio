<template>
	<button
	  v-if="visible"
	  class="fixed bottom-6 right-6 z-50 bg-red-500 text-white p-3 rounded-full shadow-md hover:bg-red-600 transition"
	  aria-label="Scroll to top"
	  @click="scrollToTop"
	>
	  ↑
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