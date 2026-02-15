<template>
  <footer
    class="mt-14 border-t border-gray-200 dark:border-zinc-800 bg-white/70 dark:bg-zinc-950/70"
    role="contentinfo"
    aria-label="Site footer"
  >
    <section class="bg-primary py-3" aria-label="Social media links">
      <div class="container mx-auto max-w-(--breakpoint-xl) px-6 flex flex-col sm:flex-row justify-between items-center gap-3">
        <p class="text-sm font-semibold text-white text-center sm:text-left">
          Open to impactful engineering opportunities and collaborations.
        </p>

        <div class="flex justify-center items-center gap-3">
        <a
          href="https://github.com/idmcalculus"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's GitHub profile"
          class="footer-social-link"
        >
          <Icon name="simple-icons:github" size="20" />
        </a>
        <a
          href="https://x.com/calculus_codes"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's X (Twitter) profile"
          class="footer-social-link"
        >
          <Icon name="simple-icons:x" size="20" />
        </a>
        <a
          href="https://instagram.com/idmcalculus"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's Instagram profile"
          class="footer-social-link"
        >
          <Icon name="simple-icons:instagram" size="20" />
        </a>
        <a
          href="https://linkedin.com/in/idmcalculus"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's LinkedIn profile"
          class="footer-social-link"
        >
          <Icon name="simple-icons:linkedin" size="20" />
        </a>
        </div>
      </div>
    </section>

    <section class="container mx-auto max-w-(--breakpoint-xl) px-6 py-8 grid gap-6 md:grid-cols-3 md:items-end">
      <div class="text-center md:text-left">
        <p class="text-base font-semibold text-gray-900 dark:text-gray-100">Damilola Michael Ige</p>
        <p class="text-sm text-gray-600 dark:text-gray-400">Senior Full-Stack &amp; Cloud Engineer</p>
      </div>

      <nav class="flex items-center justify-center gap-6 text-sm font-semibold text-gray-700 dark:text-gray-300" aria-label="Footer navigation">
        <NuxtLink to="/" class="footer-nav-link">About</NuxtLink>
        <NuxtLink to="/projects" class="footer-nav-link">Projects</NuxtLink>
        <NuxtLink to="/credentials" class="footer-nav-link">Credentials</NuxtLink>
        <NuxtLink to="/contact" class="footer-nav-link">Contact</NuxtLink>
      </nav>

      <div class="text-center md:text-right text-xs text-gray-500 dark:text-gray-400 space-y-1">
        <div class="inline-flex items-center gap-1 justify-center md:justify-end">
          <span>Designed by Ebunoluwa Ige</span>
        <a
          href="https://www.linkedin.com/in/ebunoluwa-ige-ruth/"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Ebunoluwa Ige's LinkedIn profile"
          class="inline-flex hover:text-primary transition focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md p-1"
        >
          <Icon name="simple-icons:linkedin" size="16" />
        </a>
        </div>
        <p>&copy; {{ currentYear }} iDM Portfolio. All rights reserved.</p>
      </div>
    </section>
  </footer>
</template>

<script setup lang="ts">
const currentYear = new Date().getFullYear()
</script>

<style scoped>
.footer-social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.2rem;
  height: 2.2rem;
  border-radius: 9999px;
  color: white;
  border: 1px solid rgb(255 255 255 / 0.35);
  transition: all 0.2s ease;
}

.footer-social-link:hover {
  border-color: white;
  background: rgb(255 255 255 / 0.15);
}

.footer-social-link:focus-visible {
  outline: none;
  box-shadow: 0 0 0 3px rgb(255 255 255 / 0.35);
}

.footer-nav-link {
  border-radius: 0.375rem;
  padding: 0.2rem 0.35rem;
  transition: color 0.2s ease;
}

.footer-nav-link:hover {
  color: var(--color-primary);
}

.footer-nav-link:focus-visible {
  outline: none;
  color: var(--color-primary);
  box-shadow: 0 0 0 3px rgb(245 71 71 / 0.2);
}
</style>
