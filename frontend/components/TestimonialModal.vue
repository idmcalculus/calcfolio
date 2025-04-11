<template>
  <TransitionRoot appear :show="isOpen" as="template">
    <Dialog class="relative z-50" as="div" @close="closeModal">
      <TransitionChild
        as="template"
        enter="duration-300 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-200 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/25" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto perspective-1200">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-500 ease-out"

            enter-from="opacity-0 scale-50 rotate-z-180"
            enter-to="opacity-100 scale-100 rotate-z-0"
            leave="duration-300 ease-in"
            leave-from="opacity-100 scale-100 rotate-z-0"
            leave-to="opacity-0 scale-50 rotate-z-180"
          >
            <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-lg bg-white dark:bg-zinc-800 p-6 text-left align-middle shadow-xl transition-all relative">
              <button
                class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                @click="closeModal"
              >
                <Icon name="lucide:x" size="24" />
              </button>

              <!-- Avatar -->
              <img 
                v-if="avatar" 
                :src="avatar" 
                alt="Avatar" 
                class="w-32 h-32 rounded-full mb-4 object-cover"
              >

              <p class="text-base text-gray-700 dark:text-gray-300 mb-6 whitespace-pre-line italic">
                "{{ message }}"
              </p>
              
              <div class="flex items-center justify-between mt-4">
				<div>
					<h3 class="font-bold text-lg text-gray-900 dark:text-gray-100">{{ name }}</h3>
					<p class="text-sm text-gray-600 dark:text-gray-400">{{ role }}</p>
				</div>
				<!-- Source Icon -->
				 <div>
					<a v-if="sourceIcon && url" :href="url" target="_blank" rel="noopener" class="text-gray-400 hover:text-primary">
						<Icon :name="sourceIcon" size="20" />
					</a>
				 </div>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup lang="ts">
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { computed } from 'vue'

interface Props {
  isOpen: boolean
  name: string
  role: string
  message: string
  avatar?: string
  source?: 'linkedin' | 'X' | 'email'
  url?: string
}

const props = defineProps<Props>()
const emit = defineEmits<{
  (e: 'close'): void
}>()

const closeModal = () => {
  emit('close')
}

const sourceIcon = computed(() => {
  switch (props.source) {
    case 'linkedin': return 'simple-icons:linkedin'
    case 'X': return 'simple-icons:x'
    case 'email': return 'lucide:mail'
    default: return null
  }
})
</script>

<style scoped>
.perspective-1200 {
  perspective: 1200px;
}

/* Rotating Door Effect Classes */
.rotate-y-90 {
  transform: rotateY(90deg);
}

.rotate-y-0 {
  transform: rotateY(0deg);
}

/* Zoom Twist Effect Classes */
.rotate-z-180 {
  transform: rotate(180deg);
}

.rotate-z-0 {
  transform: rotate(0deg);
}

/* Flip and Scale Effect Classes */
.rotate-x-180 {
  transform: rotateX(180deg);
}

.rotate-x-0 {
  transform: rotateX(0deg);
}

.scale-75 {
  transform: scale(0.75);
}

/* Apply transform-style to preserve 3D effects */
.transform {
  transform-style: preserve-3d;
  backface-visibility: hidden;
}
</style>