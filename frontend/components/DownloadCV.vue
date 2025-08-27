<template>
  <section class="text-center" :data-aos="aos">
    <button
      :class="[
        'px-6 py-3 bg-primary text-white transition hover:bg-red-600',
        width ? `w-${width} rounded` : 'w-full'
      ]"
      @click="openModal"
    >
      {{ buttonText }}
    </button>
  </section>
</template>

<script setup lang="ts">
import type { Ref } from 'vue';
import { inject } from 'vue'

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

const openModal = () => {
  if (showCVModal) showCVModal.value = true
}
</script>