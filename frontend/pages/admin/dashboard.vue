<template>
  <div class="container mx-auto p-6 max-w-screen-xl">
    <h2 class="text-2xl font-semibold mb-6">Messages Dashboard</h2>

    <!-- Message Chart -->
    <div class="mb-8 bg-white dark:bg-gray-800 rounded shadow">
      <ClientOnly> <!-- Ensure chart renders only on client -->
         <MessageChart />
         <template #fallback>
           <div class="p-4 text-center text-gray-500">Loading chart...</div>
         </template>
      </ClientOnly>
    </div>

    <!-- Message Table -->
    <div class="bg-white dark:bg-gray-800 rounded shadow">
       <ClientOnly> <!-- Ensure table (with client-side fetch) renders only on client -->
         <MessageTable />
          <template #fallback>
           <div class="p-4 text-center text-gray-500">Loading messages...</div>
         </template>
       </ClientOnly>
    </div>

  </div>
</template>

<script setup lang="ts">
import MessageChart from '~/components/admin/MessageChart.vue'
import MessageTable from '~/components/admin/MessageTable.vue'

// Define page meta to use the admin layout
definePageMeta({
  layout: 'admin'
})

// Add logic here later to fetch data if needed directly on the page,
// or rely on child components to fetch their own data.
</script>

<style scoped>
/* Add dashboard-specific styles if needed */
</style>
