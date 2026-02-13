<template>
  <div class="testimonial-card relative h-full bg-white dark:bg-zinc-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-6 flex flex-col">
    <!-- Source Icon -->
    <div class="absolute top-6 right-6">
      <a
        v-if="sourceIcon && url"
        :href="url"
        target="_blank"
        rel="noopener noreferrer"
        :aria-label="sourceAriaLabel"
        class="text-gray-400 hover:text-primary transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 rounded-md p-1"
      >
        <Icon :name="sourceIcon" size="20" />
      </a>
    </div>

    <!-- Avatar -->
    <NuxtImg 
      v-if="avatarUrl" 
      :src="avatarUrl" 
      :alt="`Profile photo of ${name}`"
      class="w-12 h-12 rounded-full mb-4 object-cover"
    />

    <div class="text-sm text-gray-700 dark:text-gray-300 mb-6 grow">
      <p class="italic">
        "{{ truncatedMessage }}"
        <span v-if="hasLongMessage" class="block mt-2">
          <button 
            type="button"
            class="text-primary hover:text-red-600 not-italic font-medium transition-colors"
            :aria-label="`Read full testimonial from ${name}`"
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

const normalizedMessage = computed(() => {
  return props.message.replace(/\s+/g, ' ').trim()
})

const avatarUrl = computed(() => {
  if (!props.avatar) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.name)}&background=1f2937&color=ffffff&size=128&rounded=true`
  }

  // LinkedIn image URLs in testimonials are time-bound and regularly expire.
  // Use a stable generated avatar to avoid repeated client-side 403 errors.
  if (props.avatar.includes('media.licdn.com')) {
    return `https://ui-avatars.com/api/?name=${encodeURIComponent(props.name)}&background=1f2937&color=ffffff&size=128&rounded=true`
  }

  return props.avatar
})
  
const sourceIcon = computed(() => {
  switch (props.source) {
    case 'linkedin': return 'simple-icons:linkedin'
    case 'X': return 'simple-icons:x'
    case 'email': return 'lucide:mail'
    default: return null
  }
})

const sourceAriaLabel = computed(() => {
  switch (props.source) {
    case 'linkedin':
      return `View ${props.name}'s recommendation on LinkedIn`
    case 'X':
      return `View ${props.name}'s recommendation on X`
    case 'email':
      return `View ${props.name}'s recommendation by email`
    default:
      return `View recommendation from ${props.name}`
  }
})

const hasLongMessage = computed(() => {
  return props.message.includes('\n') || normalizedMessage.value.length > 220
})

const truncatedMessage = computed(() => {
  if (!hasLongMessage.value) return normalizedMessage.value

  return normalizedMessage.value.slice(0, 220).trim() + '...'
})
</script>
