<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
		v-if="isOpen" 
		class="fixed inset-0 z-50 flex items-center justify-center"
		@click.self="closeModal"
      >
        <div class="fixed inset-0 bg-black/40"/>
        <div class="relative z-50 bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-6 w-[90%] max-w-4xl">
          <button 
            class="absolute top-3 right-3 text-xl text-gray-500 hover:text-red-600" 
            @click="closeModal"
          >
            ×
          </button>

          <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">My Resume</h3>
          
          <!-- Preview Section -->
          <div class="relative mb-6 h-[80vh] border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <iframe
              ref="previewFrame"
              :src="'/resumes/Damilola_Michael_Ige.pdf#toolbar=1&view=FitH'"
              class="w-full h-full"
              title="Resume Preview"
              allow="fullscreen"
            />
          </div>

          <!-- Download Buttons -->
          <div class="flex gap-4 justify-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a
              href="/resumes/Damilola_Michael_Ige.pdf"
              download
              class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600 transition"
            >
              Download PDF
            </a>
            <a
              href="/resumes/Damilola_Michael_Ige.docx"
              download
              class="border border-primary text-primary px-4 py-2 rounded hover:bg-primary hover:text-white transition"
            >
              Download DOCX
            </a>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import type { Ref } from 'vue'
import { inject, ref } from 'vue'

const isOpen = inject<Ref<boolean>>('showCVModal')
const previewFrame = ref<HTMLIFrameElement | null>(null)

const closeModal = () => {
  if (isOpen) isOpen.value = false
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
</style>