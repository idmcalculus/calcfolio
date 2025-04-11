<template>
  <section class="text-center my-10" data-aos="fade-up">
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
import { onMounted, inject } from 'vue'
import AOS from 'aos'

interface Props {
  width?: string
  buttonText?: string
}

const props = withDefaults(defineProps<Props>(), {
  width: '',
  buttonText: 'View My Full CV'
})

const showCVModal = inject<Ref<boolean>>('showCVModal')

const openModal = () => {
  if (showCVModal) showCVModal.value = true
}

onMounted(() => {
  AOS.init({ duration: 800 })
})
</script>