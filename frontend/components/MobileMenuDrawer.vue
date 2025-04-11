<template>
  <div>
    <!-- Dark overlay -->
    <div 
      class="fixed inset-0 bg-black/40 z-40 transition-opacity duration-300"
      @click="$emit('close')"
    />
    
    <!-- Drawer menu -->
    <nav 
      class="fixed top-0 left-0 h-full w-1/2 bg-white dark:bg-gray-900 
             border-r border-gray-200 dark:border-gray-700 z-50 
             transform transition-transform duration-300 ease-in-out
             flex flex-col p-6"
    >
      <!-- Logo or brand -->
      <NuxtLink to="/" class="font-bold text-xl mb-8">IDM</NuxtLink>
      
      <!-- Navigation links -->
      <div class="space-y-6">
        <NuxtLink 
          to="/" 
          class="block hover:text-primary transition-colors" 
          @click="$emit('close')"
        >
          About
        </NuxtLink>
        <NuxtLink 
          to="/projects" 
          class="block hover:text-primary transition-colors" 
          @click="$emit('close')"
        >
          Projects
        </NuxtLink>
        <NuxtLink 
          to="/contact" 
          class="block hover:text-primary transition-colors" 
          @click="$emit('close')"
        >
          Contact
        </NuxtLink>
        
        <div @click="$emit('close')">
          <DownloadCV class="mt-4" width="40" button-text="Download CV" />
        </div>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">

defineEmits(['close'])
</script>

<style scoped>
.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s ease;
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}
</style>