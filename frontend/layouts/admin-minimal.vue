<template>
  <!-- Apply dark class dynamically -->
  <div :class="{ dark: isDark }">
    <slot /> <!-- Page content will be injected here -->
  </div>
</template>

<script setup lang="ts">
import { useDarkMode } from '~/composables/useDarkMode';

// Get dark mode state
const { isDark } = useDarkMode();

// No toggle needed on this minimal layout, just apply the class
</script>

<style scoped>
/* Add any layout-specific styles if needed */
</style>
