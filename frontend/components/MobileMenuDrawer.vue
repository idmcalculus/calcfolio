<template>
  <div class="fixed inset-0 z-50" @keydown.esc="emit('close')">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px]" @click="emit('close')" />

    <aside
      ref="drawerPanel"
      class="mobile-drawer absolute right-0 top-0 h-full w-[85%] max-w-sm bg-white dark:bg-zinc-900 border-l border-gray-200 dark:border-zinc-700 shadow-2xl p-6 flex flex-col"
      role="dialog"
      aria-modal="true"
      aria-labelledby="mobile-menu-title"
      tabindex="-1"
    >
      <div class="flex items-center justify-between">
        <NuxtLink
          id="mobile-menu-title"
          to="/"
          class="font-bold text-xl tracking-tight focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md px-2 py-1"
          @click="emit('close')"
        >
          IDM
        </NuxtLink>
        <button
          type="button"
          class="p-2 rounded-md text-gray-600 dark:text-gray-300 hover:text-primary hover:bg-gray-100 dark:hover:bg-zinc-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
          aria-label="Close mobile menu"
          @click="emit('close')"
        >
          <Icon name="lucide:x" class="w-5 h-5" />
        </button>
      </div>

      <nav class="mt-8 space-y-2">
        <NuxtLink
          to="/"
          class="drawer-link"
          exact-active-class="drawer-link-active"
          @click="emit('close')"
        >
          About
        </NuxtLink>
        <NuxtLink
          to="/projects"
          class="drawer-link"
          active-class="drawer-link-active"
          @click="emit('close')"
        >
          Projects
        </NuxtLink>
        <NuxtLink
          to="/credentials"
          class="drawer-link"
          active-class="drawer-link-active"
          @click="emit('close')"
        >
          Credentials
        </NuxtLink>
        <NuxtLink
          to="/contact"
          class="drawer-link"
          active-class="drawer-link-active"
          @click="emit('close')"
        >
          Contact
        </NuxtLink>
      </nav>

      <div class="mt-8" @click="emit('close')">
        <DownloadCV width="100%" button-text="Download CV" />
      </div>

      <div class="mt-auto pt-8 border-t border-gray-200 dark:border-zinc-700">
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Connect</p>
        <div class="flex items-center gap-4">
          <a
            href="https://github.com/idmcalculus"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="GitHub"
            class="drawer-social-link"
          >
            <Icon name="simple-icons:github" size="18" />
          </a>
          <a
            href="https://x.com/calculus_codes"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="X"
            class="drawer-social-link"
          >
            <Icon name="simple-icons:x" size="18" />
          </a>
          <a
            href="https://linkedin.com/in/idmcalculus"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="LinkedIn"
            class="drawer-social-link"
          >
            <Icon name="simple-icons:linkedin" size="18" />
          </a>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup lang="ts">
import { nextTick, onMounted, onUnmounted, ref } from 'vue'
import DownloadCV from './DownloadCV.vue'

const emit = defineEmits<{
  close: []
}>()

const drawerPanel = ref<HTMLElement | null>(null)
let previousBodyOverflow = ''

onMounted(async () => {
  previousBodyOverflow = document.body.style.overflow
  document.body.style.overflow = 'hidden'
  await nextTick()
  drawerPanel.value?.focus()
})

onUnmounted(() => {
  document.body.style.overflow = previousBodyOverflow
})
</script>

<style scoped>
.drawer-link {
  display: block;
  padding: 0.75rem 0.9rem;
  border-radius: 0.5rem;
  border: 1px solid rgb(229 231 235);
  color: rgb(31 41 55);
  font-weight: 600;
  transition: all 0.2s ease;
}

.drawer-link:hover {
  border-color: rgb(245 71 71 / 0.5);
  color: var(--color-primary);
  background: rgb(254 242 242);
}

.drawer-link:focus-visible {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.22);
}

.drawer-link-active {
  border-color: rgb(245 71 71 / 0.55);
  color: var(--color-primary);
  background: rgb(254 242 242);
}

.dark .drawer-link {
  border-color: rgb(63 63 70);
  color: rgb(229 231 235);
  background: rgb(24 24 27);
}

.dark .drawer-link:hover {
  border-color: rgb(245 71 71 / 0.6);
  background: rgb(63 18 18);
}

.dark .drawer-link-active {
  border-color: rgb(245 71 71 / 0.7);
  background: rgb(63 18 18);
}

.drawer-social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.1rem;
  height: 2.1rem;
  border-radius: 9999px;
  border: 1px solid rgb(209 213 219);
  color: rgb(107 114 128);
  transition: all 0.2s ease;
}

.drawer-social-link:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.drawer-social-link:focus-visible {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.22);
}

.dark .drawer-social-link {
  border-color: rgb(82 82 91);
  color: rgb(161 161 170);
}

.slide-enter-active,
.slide-leave-active {
  transition: opacity 0.25s ease;
}

.slide-enter-from,
.slide-leave-to {
  opacity: 0;
}

.mobile-drawer {
  transition: transform 0.3s ease;
}

.slide-enter-from .mobile-drawer,
.slide-leave-to .mobile-drawer {
  transform: translateX(100%);
}
</style>
