<template>
  <div
    v-if="visible"
    :class="[
      position === 'fixed' ? 'fixed inset-0 z-50' : 'absolute inset-0 z-10 rounded-lg',
      'bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm flex items-center justify-center',
      overlayClass
    ]"
    role="status"
    aria-live="polite"
  >
    <div class="text-center space-y-4">
      <div class="relative">
        <!-- Main spinning ring (tripled size) -->
        <svg
          class="animate-spin h-36 w-36 text-primary mx-auto"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          aria-label="Loading"
        >
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>

        <!-- Enhanced dropping ball animation -->
        <div
          v-if="showCenterDot"
          class="absolute inset-0 flex items-center justify-center"
        >
          <div class="relative w-36 h-36">
            <!-- Ball that drops from center of inner circle -->
            <div
              class="absolute w-6 h-6 rounded-full animate-bounce-ball"
              :class="centerDotClass"
            ></div>
          </div>
        </div>
      </div>
      <div
        v-if="title"
        class="text-lg font-semibold text-gray-700 dark:text-gray-300 animate-pulse"
      >
        {{ title }}
      </div>
      <div
        v-if="subtitle"
        class="text-sm text-gray-500 dark:text-gray-400"
      >
        {{ subtitle }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  visible: boolean
  title?: string
  subtitle?: string
  showCenterDot?: boolean
  centerDotClass?: string
  overlayClass?: string
  position?: 'fixed' | 'absolute'
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const _props = withDefaults(defineProps<Props>(), {
  visible: false,
  title: '',
  subtitle: '',
  showCenterDot: true,
  centerDotClass: 'bg-primary',
  overlayClass: '',
  position: 'absolute'
})
</script>

<style scoped>
/* Enhanced bouncing ball animation with realistic physics */
@keyframes bounce-ball {
  0% {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
  }
  /* First bounce - down to touch inner circle */
  8% {
    top: 65%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.95);
    opacity: 0.98;
  }
  /* Bounce up below center */
  16% {
    top: 58%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    opacity: 0.95;
  }
  /* Second bounce - smaller amplitude */
  24% {
    top: 62%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.85);
    opacity: 0.9;
  }
  32% {
    top: 59%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    opacity: 0.85;
  }
  /* Third bounce - even smaller */
  40% {
    top: 61%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.75);
    opacity: 0.8;
  }
  48% {
    top: 60%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.7);
    opacity: 0.75;
  }
  /* Fourth bounce - minimal movement */
  56% {
    top: 60.5%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.65);
    opacity: 0.7;
  }
  64% {
    top: 60%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.6);
    opacity: 0.65;
  }
  /* Damping phase - very small oscillations */
  72% {
    top: 60.2%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.55);
    opacity: 0.6;
  }
  80% {
    top: 60%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.5);
    opacity: 0.55;
  }
  88% {
    top: 60.1%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.45);
    opacity: 0.5;
  }
  100% {
    top: 60%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.4);
    opacity: 0.45;
  }
}

.animate-bounce-ball {
  animation: bounce-ball 3.5s ease-out infinite;
}
</style>