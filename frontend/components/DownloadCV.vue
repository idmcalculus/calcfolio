<template>
  <section class="text-center" :data-aos="$nuxt.isHydrating ? undefined : aos">
    <button
      type="button"
      :aria-label="buttonText"
      class="inline-flex w-full sm:w-auto items-center justify-center px-6 py-3 rounded-md bg-primary border border-primary text-white font-semibold transition-colors hover:bg-red-700 hover:border-red-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
      :style="buttonStyle"
      @click="openModal"
    >
      {{ buttonText }}
    </button>
  </section>
</template>

<script setup lang="ts">
import type { Ref } from 'vue';
import { computed, inject } from 'vue'

interface Props {
  width?: string
  buttonText?: string
  aos?: string
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const props = withDefaults(defineProps<Props>(), {
  width: '',
  buttonText: 'View My Full CV',
  aos: 'fade-up'
})

const showCVModal = inject<Ref<boolean>>('showCVModal')

const buttonStyle = computed(() => {
  if (!props.width) return {}

  const numericWidth = Number(props.width)
  if (!Number.isNaN(numericWidth)) {
    // Preserve existing `w-40` style behavior where 40 maps to 10rem.
    return { width: `${numericWidth / 4}rem` }
  }

  return { width: props.width }
})

const openModal = () => {
  if (showCVModal) showCVModal.value = true
}
</script>
