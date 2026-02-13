<template>
  <section class="text-center" :data-aos="$nuxt.isHydrating ? undefined : aos">
    <NuxtLink
      to="/projects"
      class="inline-flex w-full sm:w-auto items-center justify-center px-6 py-3 rounded-md bg-primary border border-primary text-white font-semibold transition-colors hover:bg-red-700 hover:border-red-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
      :style="buttonStyle"
    >
      {{ buttonText }}
    </NuxtLink>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  width?: string
  buttonText?: string
  aos?: string
}

const props = withDefaults(defineProps<Props>(), {
  width: '',
  buttonText: 'View All Projects',
  aos: 'fade-up'
})

const buttonStyle = computed(() => {
  if (!props.width) return {}

  const numericWidth = Number(props.width)
  if (!Number.isNaN(numericWidth)) {
    return { width: `${numericWidth / 4}rem` }
  }

  return { width: props.width }
})
</script>
