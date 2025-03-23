<template>
	<section class="text-center my-10" data-aos="fade-up">
	  <button
		class="px-6 py-3 bg-primary text-white rounded hover:bg-red-600 transition"
		@click="showModal = true"
	  >
		Download CV
	  </button>
  
	  <!-- Modal -->
	  <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
		<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-6 w-[90%] max-w-md relative">
		  <!-- Close -->
		  <button class="absolute top-3 right-3 text-xl text-gray-500 hover:text-red-600" @click="close">
			&times;
		  </button>
  
		  <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Download CV</h3>
		  <p class="text-sm text-gray-600 dark:text-gray-300 mb-6">
			Which version would you like to download?
		  </p>
  
		  <div class="flex gap-4 justify-center">
			<a
			  href="/resume.pdf"
			  download
			  class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600 transition"
			  @click="close"
			>
			  PDF
			</a>
			<a
			  href="/resume.docx"
			  download
			  class="border border-primary text-primary px-4 py-2 rounded hover:bg-primary hover:text-white transition"
			  @click="close"
			>
			  DOC
			</a>
		  </div>
		</div>
	  </div>
	</section>
</template>
  
<script setup lang="ts">
  import { ref, onMounted } from 'vue'
  import AOS from 'aos'
  
  const showModal = ref(false)
  
  onMounted(() => {
	AOS.init({ duration: 800 })
  })
  
  const close = () => (showModal.value = false)
</script>