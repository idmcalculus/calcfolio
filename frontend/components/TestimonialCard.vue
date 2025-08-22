<template>
  <div class="testimonial-card h-full bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow p-6 flex flex-col">
    <!-- Source Icon -->
    <div class="absolute top-6 right-6">
      <a v-if="sourceIcon && url" :href="url" target="_blank" rel="noopener" class="text-gray-400 hover:text-primary">
        <Icon :name="sourceIcon" size="20" />
      </a>
    </div>

    <!-- Avatar -->
    <NuxtImg 
      v-if="avatar" 
      :src="avatar" 
      alt="Avatar" 
      class="w-12 h-12 rounded-full mb-4 object-cover"
    />

    <div class="text-sm text-gray-700 dark:text-gray-300 mb-6 flex-grow">
      <p class="italic">
        "{{ truncatedMessage }}"
        <span v-if="hasLongMessage" class="block mt-2">
          <button 
            class="text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300 not-italic font-medium"
            @click="isModalOpen = true"
          >
            Read more
          </button>
        </span>
      </p>
    </div>
    
    <div class="mt-auto">
      <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100">{{ name }}</h3>
      <p class="text-xs text-gray-600 dark:text-gray-400">{{ role }}</p>
    </div>

    <TestimonialModal
      v-if="hasLongMessage"
      :is-open="isModalOpen"
      v-bind="$props"
      @close="isModalOpen = false"
    />
  </div>
</template>
  
<script setup lang="ts">
import { computed, ref } from 'vue'
import TestimonialModal from './TestimonialModal.vue'

interface Props {
  name: string
  role: string
  message: string
  avatar?: string
  source?: 'linkedin' | 'X' | 'email'
  url?: string
}
  
const props = defineProps<Props>()
const isModalOpen = ref(false)
  
const sourceIcon = computed(() => {
  switch (props.source) {
    case 'linkedin': return 'simple-icons:linkedin'
    case 'X': return 'simple-icons:x'
    case 'email': return 'lucide:mail'
    default: return null
  }
})

const hasLongMessage = computed(() => {
  return props.message.includes('\n') || props.message.length > 280
})

const truncatedMessage = computed(() => {
  if (!hasLongMessage.value) return props.message
  
  if (props.message.includes('\n')) {
    return props.message.split('\n')[0].trim() + '...'
  }
  
  return props.message.slice(0, 280) + '...'
})
</script>