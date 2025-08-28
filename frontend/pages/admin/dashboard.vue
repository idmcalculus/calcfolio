<template>
  <div class="container mx-auto p-6 max-w-(--breakpoint-xl)">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
      <h2 class="text-2xl font-semibold mb-4 sm:mb-0">Messages Dashboard</h2>
      
      <!-- Quick Actions -->
      <div class="flex flex-col sm:flex-row gap-3">
        <NuxtLink 
          to="/" 
          class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition-colors flex items-center justify-center space-x-2"
        >
          <ClientOnly>
            <Icon name="lucide:external-link" size="16" />
            <template #fallback>
              <span class="inline-block w-[16px] h-[16px]"/>
            </template>
          </ClientOnly>
          <span>View Main Site</span>
        </NuxtLink>
        
        <NuxtLink 
          to="/contact" 
          class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm transition-colors flex items-center justify-center space-x-2"
        >
          <ClientOnly>
            <Icon name="lucide:mail" size="16" />
            <template #fallback>
              <span class="inline-block w-[16px] h-[16px]"/>
            </template>
          </ClientOnly>
          <span>Contact Form</span>
        </NuxtLink>
      </div>
    </div>

    <!-- Message Chart -->
    <div class="mb-8 bg-white dark:bg-gray-800 rounded shadow-sm">
      <ClientOnly> <!-- Ensure chart renders only on client -->
         <MessageChart :messages="messages" :loading="status === 'pending'" :error="error || null" />
          <template #fallback>
            <div class="p-4 text-center text-gray-500">Loading chart...</div>
          </template>
      </ClientOnly>
    </div>

    <!-- Message Table -->
    <div class="bg-white dark:bg-gray-800 rounded shadow-sm">
       <ClientOnly> <!-- Ensure table (with client-side fetch) renders only on client -->
         <MessageTable :messages="messages" :loading="status === 'pending'" :error="error || null" :refresh="refresh" />
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

const { admin } = useApi()

// Define page meta to use the admin layout
definePageMeta({
  layout: 'admin'
})

// Single API call to fetch messages for both components
const { data: messagesResponse, status, error, refresh } = await admin.messages.list({
  limit: 1000, // Single call with limit=1000 as requested
  lazy: false,
  server: false,
})

// Extract messages data for child components
const messages = computed(() => messagesResponse.value?.data ?? [])
</script>

<style scoped>
/* Add dashboard-specific styles if needed */
</style>
