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
  import { useReCaptcha } from 'vue-recaptcha-v3'
  import { useToast } from 'vue-toastification' // Import useToast

  const { contact } = useApi()

  const form = ref({
    name: '',
    email: '',
    subject: '',
    message: ''
  })

  const loading = ref(false)
  const responseMsg = ref('') // Keep for potential inline messages if needed, or remove
  const success = ref(false)
  const toast = useToast() // Get toast interface

  // --- reCAPTCHA Setup ---
  const recaptchaInstance = useReCaptcha()

  const getRecaptchaToken = async () => {
    if (!recaptchaInstance) {
      console.error('reCAPTCHA instance not available. Check plugin initialization.');
      throw new Error('reCAPTCHA not loaded');
    }
    await recaptchaInstance.recaptchaLoaded() // Wait for the script to load
    const token = await recaptchaInstance.executeRecaptcha('contact_form') // 'contact_form' is the action name
    return token
  }
  // --- End reCAPTCHA Setup ---

  const handleSubmit = async () => {
    loading.value = true
    responseMsg.value = ''
    success.value = false // Reset success state

    try {
      // --- Get reCAPTCHA token ---
      const recaptchaToken = await getRecaptchaToken()
      if (!recaptchaToken) {
        throw new Error('Failed to get reCAPTCHA token.')
      }
      // --- End Get reCAPTCHA token ---

      const responseData = await contact.submit({
        ...form.value,
        recaptcha_token: recaptchaToken
      })
      // Use toast for feedback
      if (responseData.success) {
        toast.success(responseData.message || 'Message sent successfully!');
        form.value = { name: '', email: '', subject: '', message: '' }; // Reset form
        success.value = true;
        responseMsg.value = ''; // Clear any inline message
      } else {
        toast.error(responseData.message || 'Submission failed. Please check your input.');
        success.value = false;
        responseMsg.value = responseData.message || 'Submission failed'; // Optionally keep inline message
      }
    } catch (error: unknown) { // Type error as unknown
      console.error('Contact form submission error:', error);
      const errorMsg = error instanceof Error ? error.message : 'An unexpected error occurred. Please try again.';
      toast.error(errorMsg); // Show error toast
      responseMsg.value = errorMsg; // Optionally show inline message
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
