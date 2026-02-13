<template>
  <div class="section-divider" aria-hidden="true" />
</template>

<style scoped>
.section-divider {
  display: inline-block;
  height: 0.22rem;
  width: 7.5rem;
  margin-top: 1rem;
  margin-bottom: 1.5rem;
  border-radius: 9999px;
  background: linear-gradient(90deg, var(--color-primary) 0%, rgb(248 113 113) 58%, rgb(248 113 113 / 0) 100%);
}

.dark .section-divider {
  background: linear-gradient(90deg, var(--color-primary) 0%, rgb(248 113 113) 52%, rgb(248 113 113 / 0) 100%);
}
</style>
