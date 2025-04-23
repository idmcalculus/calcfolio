<template>
  <div class="message-chart-container p-4">
    <h3 class="text-lg font-semibold mb-4">Messages per Month</h3>
    <div v-if="pending" class="text-center py-6">Loading chart data...</div>
    <div v-else-if="error" class="text-center py-6 text-red-500">Error loading chart data: {{ error.message }}</div>
    <div v-else-if="chartData.labels.length > 0">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
     <div v-else class="text-center py-6 text-gray-500">
      No message data available for chart.
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import { useFetch, useRuntimeConfig } from '#app';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

interface ChartApiResponse {
  labels: string[];
  data: number[];
}

const config = useRuntimeConfig();
const apiUrl = config.public.backendUrl;

// Fetch chart data
const { data: apiResponse, pending, error } = await useFetch<ChartApiResponse>(`${apiUrl}/admin/messages/stats`, {
  credentials: 'include', // Send cookies
  lazy: false,
  server: false, // Fetch client-side
});

// Prepare data for the Bar chart component
const chartData = computed(() => ({
  labels: apiResponse.value?.labels ?? [],
  datasets: [
    {
      label: 'Messages Received',
      backgroundColor: 'rgba(59, 130, 246, 0.6)', // Tailwind blue-500 with opacity
      borderColor: 'rgba(59, 130, 246, 1)', // Solid blue border
      borderWidth: 1,
      hoverBackgroundColor: 'rgba(59, 130, 246, 0.8)',
      data: apiResponse.value?.data ?? [],
    },
  ],
}));

// Chart options (customize as needed)
const chartOptions = ref({
  responsive: true,
  maintainAspectRatio: false, // Allow chart to fill container height/width
  plugins: {
    legend: {
      display: false, // Hide legend if only one dataset
    },
    title: {
      display: true,
      text: 'Monthly Message Volume',
    },
    tooltip: {
      mode: 'index' as const, // Use 'as const' for literal type
      intersect: false,
    },
  },
  scales: {
    x: {
      grid: {
        display: false, // Hide vertical grid lines
      }
    },
    y: {
      beginAtZero: true, // Start y-axis at 0
      ticks: {
        precision: 0 // Ensure whole numbers for message counts
      },
      grid: {
        // color: '#e5e7eb', // Light gray grid lines (Tailwind gray-200) - Keep default or adjust
        // borderDash: [5, 5], // Dashed lines
        // drawBorder: false, // Removed to fix type error
      },
      // title: { // Optional Y-axis title
      //   display: true,
      //   text: 'Number of Messages'
      // }
    },
  },
});

</script>

<style scoped>
.message-chart-container {
  height: 300px; /* Example fixed height, adjust as needed */
  position: relative; /* Needed for chart.js responsiveness */
}
</style>
