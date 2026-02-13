<template>
  <section
    class="hero-section container mx-auto max-w-(--breakpoint-xl) px-6 my-16 grid grid-cols-1 lg:grid-cols-5 gap-10 items-center"
    data-aos="fade-up"
  >
    <div class="lg:col-span-3 text-center lg:text-left">
      <p class="hero-eyebrow text-sm md:text-base text-gray-500 dark:text-gray-400">
        <span class="typing-text">{{ displayGreeting }}</span>
      </p>
      <p class="mt-2 inline-flex items-center gap-2 rounded-full border border-red-100 dark:border-red-900/40 bg-red-50/70 dark:bg-red-950/30 px-3 py-1 text-xs font-semibold text-red-700 dark:text-red-200">
        <span class="status-dot h-2 w-2 rounded-full bg-primary animate-pulse" />
        Open to senior engineering opportunities
      </p>
      <p class="mt-3 text-2xl md:text-3xl font-bold text-primary">Damilola Michael Ige</p>
      <p class="mt-3 text-sm md:text-base tracking-wide uppercase text-gray-500 dark:text-gray-400">
        Senior Full-Stack &amp; Cloud Engineer
      </p>

      <SectionDivider />

      <h1 class="text-3xl md:text-5xl font-extrabold leading-tight text-gray-900 dark:text-white">
        I engineer dependable products from interface to infrastructure.
      </h1>
      <p class="mt-5 text-base md:text-lg text-gray-700 dark:text-gray-300 leading-relaxed max-w-3xl">
        I partner with teams to ship end-to-end systems across web, backend, cloud, and iOS.
      </p>
      <p class="mt-3 text-base md:text-lg text-gray-700 dark:text-gray-300 leading-relaxed max-w-3xl">
        You get pragmatic architecture, maintainable code, and stable delivery from first release through scale.
      </p>

      <div class="mt-5 flex flex-wrap gap-2.5 justify-center lg:justify-start">
        <span class="hero-pill">Web Platforms</span>
        <span class="hero-pill">Cloud Systems</span>
        <span class="hero-pill">APIs &amp; Services</span>
        <span class="hero-pill">iOS Development</span>
      </div>

      <div class="mt-8 flex flex-wrap gap-3 justify-center lg:justify-start">
        <NuxtLink
          to="/projects"
          class="hero-btn hero-btn-primary w-full sm:w-auto"
          aria-label="View projects"
        >
          View Projects
        </NuxtLink>
        <button
          class="hero-btn hero-btn-secondary w-full sm:w-auto"
          type="button"
          aria-label="Download CV"
          @click="openCVModal"
        >
          Download CV
        </button>
        <NuxtLink
          to="/contact"
          class="hero-btn hero-btn-ghost w-full sm:w-auto"
          aria-label="Contact Damilola"
        >
          Contact
        </NuxtLink>
      </div>

      <a
        href="#experience"
        class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition-colors"
      >
        Explore experience
        <Icon name="lucide:arrow-down" class="w-4 h-4" />
      </a>
    </div>

    <div class="lg:col-span-2 flex justify-center lg:justify-end">
      <div class="hero-profile-card">
        <NuxtImg
          src="/images/profile.svg"
          alt="Damilola Michael Ige"
          class="w-56 md:w-72 object-cover object-center"
        />
        <div class="flex items-center justify-center gap-5 mt-7">
          <a
            href="https://github.com/idmcalculus"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="GitHub"
            class="hero-social-link"
          >
            <Icon name="simple-icons:github" size="22" />
          </a>
          <a
            href="https://x.com/calculus_codes"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="X"
            class="hero-social-link"
          >
            <Icon name="simple-icons:x" size="22" />
          </a>
          <a
            href="https://instagram.com/idmcalculus"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="Instagram"
            class="hero-social-link"
          >
            <Icon name="simple-icons:instagram" size="22" />
          </a>
          <a
            href="https://linkedin.com/in/idmcalculus"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="LinkedIn"
            class="hero-social-link"
          >
            <Icon name="simple-icons:linkedin" size="22" />
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Ref } from 'vue'
import { inject, onMounted, onUnmounted, ref } from 'vue'
import SectionDivider from './SectionDivider.vue'

const greetings = [
  "Hello, I'm",
  'Bonjour, je suis',
  'Hola, soy',
  'Hallo, ich bin',
  'Bawo, emi ni',
  'Sannu, ni ne',
  'Ndewo, ọ bụ m',
  'Jambo, mimi ni',
  '你好，我是',
  'こんにちは、私は',
  'नमस्ते, मैं हूं'
]

const currentGreetingIndex = ref(0)
const displayGreeting = ref('')
const showCVModal = inject<Ref<boolean> | undefined>('showCVModal')

let greetingRotationInterval: ReturnType<typeof setInterval> | undefined
let greetingTypingInterval: ReturnType<typeof setInterval> | undefined

const typeGreeting = () => {
  const currentGreeting = greetings[currentGreetingIndex.value]
  if (!currentGreeting) return

  if (displayGreeting.value.length < currentGreeting.length) {
    displayGreeting.value = currentGreeting.slice(0, displayGreeting.value.length + 1)
    return
  }

  if (greetingTypingInterval) {
    clearInterval(greetingTypingInterval)
    greetingTypingInterval = undefined
  }
}

const startGreetingTyping = () => {
  displayGreeting.value = ''
  if (greetingTypingInterval) clearInterval(greetingTypingInterval)
  greetingTypingInterval = setInterval(typeGreeting, 65)
}

const rotateGreeting = () => {
  currentGreetingIndex.value = (currentGreetingIndex.value + 1) % greetings.length
  startGreetingTyping()
}

const openCVModal = () => {
  if (showCVModal) showCVModal.value = true
}

onMounted(() => {
  startGreetingTyping()
  greetingRotationInterval = setInterval(rotateGreeting, 7000)
})

onUnmounted(() => {
  if (greetingRotationInterval) clearInterval(greetingRotationInterval)
  if (greetingTypingInterval) clearInterval(greetingTypingInterval)
})
</script>

<style scoped>
.hero-profile-card {
  border: 1px solid rgb(229 231 235);
  border-radius: 0.875rem;
  padding: 1.25rem;
  background: rgb(255 255 255 / 0.6);
  backdrop-filter: blur(3px);
}

.dark .hero-profile-card {
  border-color: rgb(63 63 70);
  background: rgb(24 24 27 / 0.8);
}

.hero-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.7rem 1.1rem;
  border-radius: 0.5rem;
  font-weight: 600;
  transition: all 0.2s ease;
}

.hero-btn:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.25);
}

.hero-btn-primary {
  background-color: var(--color-primary);
  border: 1px solid var(--color-primary);
  color: #ffffff;
}

.hero-btn-primary:hover {
  background-color: #b91c1c;
  border-color: #b91c1c;
}

.hero-btn-secondary {
  border: 1px solid rgb(209 213 219);
  color: rgb(17 24 39);
  background: rgb(255 255 255 / 0.8);
}

.dark .hero-btn-secondary {
  border-color: rgb(82 82 91);
  color: rgb(243 244 246);
  background: rgb(24 24 27 / 0.8);
}

.hero-btn-secondary:hover {
  border-color: var(--color-primary);
  color: var(--color-primary);
}

.hero-btn-ghost {
  color: rgb(55 65 81);
  border: 1px solid transparent;
}

.hero-btn-ghost:hover {
  color: var(--color-primary);
  background: rgb(254 242 242);
}

.dark .hero-btn-ghost {
  color: rgb(212 212 216);
}

.dark .hero-btn-ghost:hover {
  background: rgb(63 18 18);
}

.hero-pill {
  display: inline-flex;
  align-items: center;
  border-radius: 9999px;
  border: 1px solid rgb(229 231 235);
  background: rgb(255 255 255 / 0.7);
  color: rgb(75 85 99);
  padding: 0.25rem 0.65rem;
  font-size: 0.75rem;
  font-weight: 700;
}

.dark .hero-pill {
  border-color: rgb(63 63 70);
  background: rgb(24 24 27 / 0.8);
  color: rgb(212 212 216);
}

.hero-social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.4rem;
  height: 2.4rem;
  border-radius: 9999px;
  color: rgb(107 114 128);
  border: 1px solid rgb(229 231 235);
  transition: all 0.2s ease;
}

.hero-social-link:hover {
  color: var(--color-primary);
  border-color: var(--color-primary);
  background: rgb(254 242 242);
}

.hero-social-link:focus-visible {
  outline: none;
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.25);
}

.dark .hero-social-link {
  color: rgb(163 163 163);
  border-color: rgb(63 63 70);
}

.dark .hero-social-link:hover {
  background: rgb(63 18 18);
}

.typing-text {
  display: inline-block;
  border-right: 2px solid currentColor;
  padding-right: 3px;
  animation: blink 0.7s steps(1) infinite;
}

@keyframes blink {
  50% {
    border-color: transparent;
  }
}

@media (prefers-reduced-motion: reduce) {
  .status-dot {
    animation: none;
  }

  .typing-text {
    animation: none;
    border-right: none;
  }
}

@media (max-width: 640px) {
  .hero-btn {
    width: 100%;
  }
}
</style>
