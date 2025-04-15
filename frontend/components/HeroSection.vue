<template>
  <section
    class="hero-section container mx-auto max-w-screen-xl px-6 py-16 md:py-24 flex flex-col md:flex-row items-center gap-8 md:gap-16" 
    data-aos="fade-up"
  >
    <!-- Left Column: Text Content -->
    <div class="md:w-2/3 text-center md:text-left">
      <p class="text-lg mb-3 text-gray-600 dark:text-gray-400">
        <span class="typing-text">{{ displayText }}</span>
      </p>
      <h1 class="text-5xl md:text-6xl font-extrabold mb-1">
        <span class="text-gradient">Damilola Michael Ige</span>
      </h1>
      <SectionDivider />
      <p class="text-base md:text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
        I'm a software engineer based in Toronto, Canada and also a communication and journalism student. I enjoy creating things that live on the internet, whether that be websites, applications, or anything in between. I have been freelancing for a year now while studying at the university and I've manage to gain a decent amount of experience and valuable knowledge from all different kinds of fields throughout my projects/work.
      </p>
    </div>

    <!-- Right Column: Image and Social Links -->
    <div class="md:w-1/3 flex justify-end">
      <div class="flex flex-col items-center">
       <!-- Container for image -->
       <div class="relative">
          <img
            src="/images/profile.svg"
            alt="Damilola Michael Ige"
            class="object-cover object-center"
          >
        </div>
        
        <!-- Social links -->
        <div class="flex gap-5 mt-12"> 
          <a href="https://github.com/idmcalculus" target="_blank" rel="noopener noreferrer" aria-label="GitHub" class="text-gray-600 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition">
            <Icon name="simple-icons:github" size="28" />
          </a>
          <a href="https://x.com/calculus_codes" target="_blank" rel="noopener noreferrer" aria-label="X" class="text-gray-600 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition">
            <Icon name="simple-icons:x" size="28" />
          </a>
          <a href="https://instagram.com/idmcalculus" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="text-gray-600 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition">
            <Icon name="simple-icons:instagram" size="28" />
          </a>
          <a href="https://linkedin.com/in/idmcalculus" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="text-gray-600 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition">
            <Icon name="simple-icons:linkedin" size="28" />
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import SectionDivider from './SectionDivider.vue';

const languages = [
  { greeting: "Hello, I'm" },
  { greeting: "Bonjour, je suis" },
  { greeting: "Hola, soy" },
  { greeting: "Hallo, ich bin" },
  { greeting: "Bawo, emi ni" },
  { greeting: "Sannu, ni ne" },
  { greeting: "Ndewo, ọ bụ m" },
  { greeting: "Jambo, mimi ni" },
  { greeting: "你好，我是" },
  { greeting: "こんにちは、私は" },
  { greeting: "नमस्ते, मैं हूं" }
]

const currentIndex = ref(0)
const displayText = ref('')
const targetText = ref(languages[0].greeting)

let intervalId
let typingIntervalId

const typeText = () => {
  if (displayText.value.length < targetText.value.length) {
    displayText.value = targetText.value.slice(0, displayText.value.length + 1)
  }
}

const startNewText = () => {
  displayText.value = ''
  currentIndex.value = (currentIndex.value + 1) % languages.length
  targetText.value = languages[currentIndex.value].greeting
  
  // Clear any existing typing interval
  if (typingIntervalId) clearInterval(typingIntervalId)
  
  // Start typing the new text
  typingIntervalId = setInterval(() => {
    typeText()
  }, 100) // Adjust typing speed here
}

onMounted(() => {
  // Start initial typing
  typingIntervalId = setInterval(() => {
    typeText()
  }, 100)

  // Change language every 20 seconds
  intervalId = setInterval(startNewText, 10000)
})

onUnmounted(() => {
  if (intervalId) clearInterval(intervalId)
  if (typingIntervalId) clearInterval(typingIntervalId)
})
</script>

<style scoped>
.text-gradient {
  background: linear-gradient(
    89.81deg,
    #9845E8 -1.72%,
    #33D2FF 54.05%,
    #DD5789 99.78%,
    #9845E8 150%
  );
  background-size: 200% auto;
  -webkit-background-clip: text;
  background-clip: text;
  color: transparent;
  animation: shine 3s linear infinite;
}

@keyframes shine {
  to {
    background-position: 200% center;
  }
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

.gradient-border {
  position: relative;
  background: linear-gradient(to right, #EC4899, #A855F7);
  padding: 3px;
  border-radius: 9999px;
  width: fit-content;
  height: fit-content;
}

.gradient-border::before {
  content: "";
  position: absolute;
  inset: 0;
  border-radius: 9999px;
  padding: 3px;
  background: linear-gradient(to right, #EC4899, #A855F7);
  -webkit-mask: 
    linear-gradient(#fff 0 0) content-box, 
    linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
}
</style>
