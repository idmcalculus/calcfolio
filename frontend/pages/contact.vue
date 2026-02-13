<template>
  <div class="max-w-2xl mx-auto px-4 md:px-6 my-14">
    <section class="text-center mb-8">
      <p class="text-xs uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Contact</p>
      <h1 class="mt-3 text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white">Let&apos;s Build Something Reliable</h1>
      <p class="mt-4 text-sm md:text-base text-gray-600 dark:text-gray-300 leading-relaxed">
        Have a role, product, or technical challenge in mind? Send a message and I&apos;ll get back with clear next steps.
      </p>
      <SectionDivider />
    </section>

    <div class="relative bg-white/80 dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm p-5 md:p-7">

      <LoadingOverlay
        :visible="loading"
        title="Sending your message..."
        subtitle="Please wait while we process your request"
        center-dot-class="bg-primary"
        position="absolute"
      />

      <form class="space-y-5" @submit.prevent="handleSubmit">
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
            rows="5"
            required
            class="form-input peer"
            placeholder=" "
          />
          <label for="message" class="form-label">Your Message</label>
        </div>

        <button
          type="submit"
          class="inline-flex items-center justify-center w-full sm:w-auto bg-primary text-white px-6 py-2.5 rounded-lg font-semibold border border-primary hover:bg-red-700 hover:border-red-700 transition-colors disabled:opacity-50"
          :disabled="loading"
        >
          {{ loading ? 'Sending...' : 'Send Message' }}
        </button>

        <p v-if="responseMsg" :class="success ? 'text-green-600' : 'text-red-500'">
          {{ responseMsg }}
        </p>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue'
  import LoadingOverlay from '~/components/LoadingOverlay.vue'
  import SectionDivider from '~/components/SectionDivider.vue'

  const { contact } = useApi()
  const toast = useToast()
  const config = useRuntimeConfig()
  const { isDark } = useDarkMode()

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

  const RECAPTCHA_SCRIPT_ID = 'google-recaptcha-script'

  const loadRecaptchaScript = async () => {
    if (!config.public.recaptchaSiteKey || !import.meta.client) return

    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    if ((window as any).grecaptcha?.execute) return

    const existingScript = document.getElementById(RECAPTCHA_SCRIPT_ID) as HTMLScriptElement | null
    if (existingScript) {
      await new Promise<void>((resolve, reject) => {
        const onLoad = () => resolve()
        const onError = () => reject(new Error('Failed to load reCAPTCHA script'))
        existingScript.addEventListener('load', onLoad, { once: true })
        existingScript.addEventListener('error', onError, { once: true })
      })
      return
    }

    await new Promise<void>((resolve, reject) => {
      const script = document.createElement('script')
      script.id = RECAPTCHA_SCRIPT_ID
      script.src = `https://www.google.com/recaptcha/api.js?render=${config.public.recaptchaSiteKey}`
      script.async = true
      script.defer = true
      script.onload = () => resolve()
      script.onerror = () => reject(new Error('Failed to load reCAPTCHA script'))
      document.head.appendChild(script)
    })
  }

  // --- reCAPTCHA Setup ---
  const getRecaptchaToken = async () => {
    // Skip reCAPTCHA entirely if not configured or not in client
    if (!config.public.recaptchaSiteKey || !import.meta.client) {
      return null
    }

    try {
      await loadRecaptchaScript()

      // Wait for grecaptcha to be available
      let attempts = 0;
      const maxAttempts = 40; // 4 seconds max wait

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
        recaptcha_token: recaptchaToken || '',
        theme_preference: isDark.value ? 'dark' : 'light'
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
  padding: 0.8rem 1rem;
  border-radius: 0.5rem;
  border: 1px solid rgb(209 213 219);
  background-color: rgb(255 255 255);
  color: rgb(17 24 39);
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.dark .form-input {
  background-color: rgb(24 24 27);
  border-color: rgb(63 63 70);
  color: rgb(244 244 245);
}

.form-input:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.2);
  outline: none;
}

.form-label {
  position: absolute;
  color: rgb(75 85 99);
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

.dark .form-label {
  color: rgb(161 161 170);
}

.form-input:focus ~ .form-label,
.form-input:not(:placeholder-shown) ~ .form-label {
  font-size: 0.875rem;
  line-height: 1.25rem;
  color: var(--color-primary);
  background-color: rgb(255 255 255);
  top: 0;
  transform: translateY(-50%) scale(0.85);
}

.dark .form-input:focus ~ .form-label,
.dark .form-input:not(:placeholder-shown) ~ .form-label {
  background-color: rgb(24 24 27);
}

textarea ~ .form-label {
  top: 1.5rem;
}

textarea:focus ~ .form-label,
textarea:not(:placeholder-shown) ~ .form-label {
  top: 0;
}
</style>
