<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="isOpen" 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 md:p-6"
        @click.self="closeModal"
      >
        <div class="fixed inset-0 bg-black/40"/>
        <div class="relative z-50 bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-4 md:p-6 w-full md:w-[90%] max-w-4xl">
          <button 
            class="absolute top-2 right-2 md:top-3 md:right-3 p-2 text-2xl leading-none text-gray-500 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-primary rounded-full" 
            aria-label="Close modal"
            @click="closeModal"
          >
            <Icon name="lucide:x" class="w-5 h-5 hover:text-red-600" />
          </button>

          <h3 class="text-lg md:text-xl font-bold mb-3 md:mb-4 text-gray-900 dark:text-white">My Resume</h3>
          
          <!-- Preview Section -->
          <div class="relative mb-4 md:mb-6 h-[50vh] md:h-[60vh] border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <iframe
              ref="previewFrame"
              :src="'/resumes/Damilola Michael Ige Software Engineer CV.pdf#toolbar=1&view=FitH?embedded=true&'"
              class="w-full h-full"
              title="Resume Preview"
              type="application/pdf"
              :style="{ '-webkit-overflow-scrolling': 'touch' }"
              frameborder="0"
              allow="fullscreen"
            />
            <!-- Fallback for mobile devices -->
            <div v-if="isMobileDevice" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-zinc-900">
              <div class="text-center p-4">
                <p class="text-gray-600 dark:text-gray-300 mb-4">PDF preview may not be available on some mobile devices.</p>
                <a
                  href="/resumes/Damilola Michael Ige Software Engineer CV.pdf?embedded=true&"
                  class="bg-primary text-white px-4 py-2 rounded hover:bg-red-600 transition"
                  target="_blank"
                >
                  Open PDF
                </a>
              </div>
            </div>
          </div>

          <!-- Download Buttons -->
          <div class="flex flex-col sm:flex-row gap-3 justify-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a
              href="/resumes/Damilola Michael Ige Software Engineer CV.pdf?embedded=true&"
              download
              class="bg-primary text-white px-4 py-2 rounded text-center hover:bg-red-600 transition"
            >
              Download PDF
            </a>
            <a
              href="/resumes/Damilola Michael Ige Software Engineer CV.docx?embedded=true&"
              download
              class="border border-primary text-primary px-4 py-2 rounded text-center hover:bg-primary hover:text-white transition"
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
import { inject, ref, onMounted } from 'vue'

const isOpen = inject<Ref<boolean>>('showCVModal')
const previewFrame = ref<HTMLIFrameElement | null>(null)
const isMobileDevice = ref(false)

onMounted(() => {
  // Check if device is mobile
  isMobileDevice.value = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
})

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