<template>
  <div class="max-w-xl mx-auto p-4 my-12">
    <h2 class="text-2xl md:text-3xl font-bold mb-6 text-center">Contact Me</h2>

    <form class="space-y-4" @submit.prevent="handleSubmit">
      <div class="relative">
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="form-input peer"
          placeholder=" "
        >
        <label for="name" class="form-label">Your Name</label>
      </div>

      <div class="relative">
        <input
          id="email"
          v-model="form.email"
          type="email"
          required
          class="form-input peer"
          placeholder=" "
        >
        <label for="email" class="form-label">Your Email</label>
      </div>

      <div class="relative">
        <input
          id="subject"
          v-model="form.subject"
          type="text"
          required
          class="form-input peer"
          placeholder=" "
        >
        <label for="subject" class="form-label">Subject</label>
      </div>

      <div class="relative">
        <textarea
          id="message"
          v-model="form.message"
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

  const { contact } = useApi()
  const toast = useToast()
  const config = useRuntimeConfig()

  // Set SEO meta tags for the contact page
  useSEO({
    title: 'Contact',
    description: 'Get in touch with Damilola Michael Ige. I\'m always interested in new opportunities and collaborations. Let\'s discuss how we can work together.',
    keywords: ['contact', 'hire', 'collaboration', 'freelance', 'consulting'],
    url: '/contact'
  })

  const form = ref({
    name: '',
    email: '',
    subject: '',
    message: ''
  })

  const loading = ref(false)
  const responseMsg = ref('') // Keep for potential inline messages if needed, or remove
  const success = ref(false)

  // --- reCAPTCHA Setup ---
  const getRecaptchaToken = async () => {
    // Skip reCAPTCHA entirely if not configured or not in client
    if (!config.public.recaptchaSiteKey || !import.meta.client) {
      console.log('reCAPTCHA not configured or not in client');
      return null;
    }

    try {
      // Wait for grecaptcha to be available
      let attempts = 0;
      const maxAttempts = 50; // 5 seconds max wait

      while (attempts < maxAttempts) {
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        if (typeof window !== 'undefined' && (window as any).grecaptcha && (window as any).grecaptcha.execute) {
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          const token = await (window as any).grecaptcha.execute(config.public.recaptchaSiteKey, { action: 'contact_form' })
          return token
        }
        attempts++
        await new Promise(resolve => setTimeout(resolve, 100)) // Wait 100ms
      }

      throw new Error('reCAPTCHA not loaded within timeout')
    } catch (error) {
      console.warn('reCAPTCHA execution failed:', error)
      return null;
    }
  }
  // --- End reCAPTCHA Setup ---

  const handleSubmit = async () => {
    loading.value = true
    responseMsg.value = ''
    success.value = false // Reset success state

    try {
      // --- Get reCAPTCHA token ---
      const recaptchaToken = await getRecaptchaToken()
      // --- End Get reCAPTCHA token ---

      const responseData = await contact.submit({
        ...form.value,
        recaptcha_token: recaptchaToken || ''
      })
      // Use toast for feedback
      if (responseData.success) {
        toast.add({
          title: 'Success',
          description: responseData.message || 'Message sent successfully!',
          color: 'success'
          
        });
        form.value = { name: '', email: '', subject: '', message: '' }; // Reset form
        success.value = true;
        responseMsg.value = ''; // Clear any inline message
      } else {
        toast.add({
          title: 'Error',
          description: responseData.message || 'Submission failed. Please check your input.',
          color: 'error'
        });
        success.value = false;
        responseMsg.value = responseData.message || 'Submission failed'; // Optionally keep inline message
      }
    } catch (error: unknown) { // Type error as unknown
      console.error('Contact form submission error:', error);
      const errorMsg = error instanceof Error ? error.message : 'An unexpected error occurred. Please try again.';
      toast.add({
        title: 'Error',
        description: errorMsg,
        color: 'error'
      });
      responseMsg.value = errorMsg; // Optionally show inline message
      success.value = false
    } finally {
      loading.value = false
    }
  }
</script>

<style lang="postcss">
.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 0.375rem;
  background-color: rgb(255 255 255);
  color: rgb(0 0 0);
  outline: 2px solid transparent;
  outline-offset: 2px;
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
  border: 2px solid transparent;
  background-clip: padding-box;
}

.dark .form-input {
  background-color: rgb(31 41 55);
  color: rgb(255 255 255);
}

.form-input:focus {
  border-image: linear-gradient(89.81deg, #9845E8 -1.72%, #33D2FF 54.05%, #DD5789 99.78%, #9845E8 150%) 1;
  animation: gradient 3s ease infinite;
}

.form-label {
  position: absolute;
  color: rgb(107 114 128);
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
  pointer-events: none;
  padding-left: 0.25rem;
  padding-right: 0.25rem;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
}

.form-input:focus ~ .form-label,
.form-input:not(:placeholder-shown) ~ .form-label {
  font-size: 0.875rem;
  line-height: 1.25rem;
  color: rgb(59 130 246);
  background-color: rgb(255 255 255);
  top: 0;
  transform: translateY(-50%) scale(0.85);
}

.dark .form-input:focus ~ .form-label,
.dark .form-input:not(:placeholder-shown) ~ .form-label {
  background-color: rgb(31 41 55);
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
