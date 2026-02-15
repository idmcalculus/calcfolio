<template>
  <div>
    <CredentialsSection
      :limit="0"
      :show-view-all="false"
      :featured-only="false"
      :show-refine-controls="true"
      :sync-filters-to-query="true"
      :enable-load-more="true"
      :initial-load-count="15"
      :load-more-step="12"
      :large-cards="true"
      title="Credentials"
      description="A curated list of verified certifications and learning milestones from platforms such as Codecademy, Udemy, and other professional programs."
      empty-message="No credentials listed yet."
    />
  </div>
</template>

<script setup lang="ts">
import CredentialsSection from '~/components/CredentialsSection.vue'

useSEO({
  title: 'Credentials',
  description: 'Verified credentials and certifications from professional learning platforms and technical programs.',
  keywords: ['credentials', 'certifications', 'udemy', 'codecademy', 'software engineering'],
  url: '/credentials'
})
</script>
