<template>
  <section class="text-center" :data-aos="$nuxt.isHydrating ? undefined : aos">
    <NuxtLink
      to="/projects"
      :class="[
        'inline-block px-6 py-3 bg-primary text-white transition hover:bg-red-600',
        width ? `w-${width} rounded` : 'w-full'
      ]"
    >
      {{ buttonText }}
    </NuxtLink>
  </section>
</template>

<script setup lang="ts">
interface Props {
  width?: string
  buttonText?: string
  aos?: string
}

const _props = withDefaults(defineProps<Props>(), {
  width: '',
  buttonText: 'View All Projects',
  aos: 'fade-up'
})
</script>