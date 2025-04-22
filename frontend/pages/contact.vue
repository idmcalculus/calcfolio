<template>
  <div class="max-w-xl mx-auto p-4 my-12">
    <h2 class="text-2xl md:text-3xl font-bold mb-6 text-center">Contact Me</h2>

    <form class="space-y-4" @submit.prevent="handleSubmit">
      <div class="relative">
        <input
          v-model="form.name"
          type="text"
          id="name"
          required
          class="form-input peer"
          placeholder=" "
        >
        <label for="name" class="form-label">Your Name</label>
      </div>

      <div class="relative">
        <input
          v-model="form.email"
          type="email"
          id="email"
          required
          class="form-input peer"
          placeholder=" "
        >
        <label for="email" class="form-label">Your Email</label>
      </div>

      <div class="relative">
        <input
          v-model="form.subject"
          type="text"
          id="subject"
          required
          class="form-input peer"
          placeholder=" "
        >
        <label for="subject" class="form-label">Subject</label>
      </div>

      <div class="relative">
        <textarea
          v-model="form.message"
          id="message"
          rows="4"
          required
          class="form-input peer"
          placeholder=" "
        />
        <label for="message" class="form-label">Your Message</label>
      </div>

      <button
        type="submit"
        class="bg-primary text-white px-6 py-2 rounded hover:bg-red-600 transition disabled:opacity-50"
        :disabled="loading"
      >
        {{ loading ? 'Sending...' : 'Send Message' }}
      </button>

      <p v-if="responseMsg" :class="success ? 'text-green-600' : 'text-red-500'">
        {{ responseMsg }}
      </p>
    </form>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import { useRuntimeConfig } from '#app'

  const config = useRuntimeConfig()
  const apiUrl = config.public.backendUrl

  const form = ref({
    name: '',
    email: '',
    subject: '',
    message: ''
  })

  const loading = ref(false)
  const responseMsg = ref('')
  const success = ref(false)

  const handleSubmit = async () => {
    loading.value = true
    responseMsg.value = ''

    try {
      const res = await fetch(`${apiUrl}/contact`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(form.value)
      })

      const data = await res.json()
      success.value = data.success
      responseMsg.value = data.message

      if (data.success) {
        form.value = { name: '', email: '', subject: '', message: '' }
      }
    } catch {
      responseMsg.value = 'Something went wrong. Please try again.'
      success.value = false
    } finally {
      loading.value = false
    }
  }
</script>

<style lang="postcss">
.form-input {
  @apply w-full px-4 py-3 rounded bg-white dark:bg-gray-800 text-black dark:text-white outline-none transition-all duration-300 border-2 border-transparent;
  background-clip: padding-box;
}

.form-input:focus {
  border-image: linear-gradient(89.81deg, #9845E8 -1.72%, #33D2FF 54.05%, #DD5789 99.78%, #9845E8 150%) 1;
  animation: gradient 3s ease infinite;
}

.form-label {
  @apply absolute text-gray-500 transition-all duration-300 pointer-events-none px-1;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
}

.form-input:focus ~ .form-label,
.form-input:not(:placeholder-shown) ~ .form-label {
  @apply text-sm text-blue-500 bg-white dark:bg-gray-800;
  top: 0;
  transform: translateY(-50%) scale(0.85);
}

textarea ~ .form-label {
  top: 1.5rem;
}

textarea:focus ~ .form-label,
textarea:not(:placeholder-shown) ~ .form-label {
  top: 0;
}

@keyframes gradient {
  0% { border-image-source: linear-gradient(89.81deg, #9845E8 -1.72%, #33D2FF 54.05%, #DD5789 99.78%, #9845E8 150%); }
  50% { border-image-source: linear-gradient(89.81deg, #33D2FF -1.72%, #DD5789 54.05%, #9845E8 99.78%, #33D2FF 150%); }
  100% { border-image-source: linear-gradient(89.81deg, #9845E8 -1.72%, #33D2FF 54.05%, #DD5789 99.78%, #9845E8 150%); }
}
</style>