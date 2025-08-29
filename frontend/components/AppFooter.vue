<template>
  <footer
    class="mt-10 border-t border-gray-300 dark:border-gray-700"
    role="contentinfo"
    aria-label="Site footer"
  >
    <!-- Social Media Section -->
    <section class="bg-red-500 py-3" aria-label="Social media links">
      <div class="container mx-auto max-w-(--breakpoint-xl) px-6 flex justify-center items-center gap-5">
        <a
          href="https://github.com/idmcalculus"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's GitHub profile"
          class="text-white hover:text-gray-200 transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-red-500 rounded-md p-1"
        >
          <Icon name="simple-icons:github" size="24" />
        </a>
        <a
          href="https://x.com/calculus_codes"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's X (Twitter) profile"
          class="text-white hover:text-gray-200 transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-red-500 rounded-md p-1"
        >
          <Icon name="simple-icons:x" size="24" />
        </a>
        <a
          href="https://instagram.com/idmcalculus"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's Instagram profile"
          class="text-white hover:text-gray-200 transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-red-500 rounded-md p-1"
        >
          <Icon name="simple-icons:instagram" size="24" />
        </a>
        <a
          href="https://linkedin.com/in/idmcalculus"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Damilola's LinkedIn profile"
          class="text-white hover:text-gray-200 transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-red-500 rounded-md p-1"
        >
          <Icon name="simple-icons:linkedin" size="24" />
        </a>
      </div>
    </section>

    <!-- Footer Information -->
    <section class="container mx-auto max-w-(--breakpoint-xl) px-6 py-10 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600 dark:text-gray-400 gap-2 sm:gap-0">
      <div class="flex items-center gap-1">
        <span>Designed by Ebunoluwa Ige</span>
        <a
          href="https://www.linkedin.com/in/ebunoluwa-ige-ruth/"
          target="_blank"
          rel="noopener noreferrer"
          aria-label="Visit Ebunoluwa Ige's LinkedIn profile"
          class="flex hover:text-primary dark:hover:text-primary-dark transition focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 rounded-md p-1"
        >
          <Icon name="simple-icons:linkedin" size="18" />
        </a>
      </div>
      <div>
        <p>&copy; 2025 iDM Portfolio. All rights reserved.</p>
      </div>
    </section>
  </footer>
</template>

<script setup>
// No script logic needed for this simple footer yet
</script>

<style scoped>
/* Add any component-specific styles here if needed */
</style>
