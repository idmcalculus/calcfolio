<template>
  <div class="message-chart-container p-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
      <h3 class="text-lg font-semibold mb-4 sm:mb-0">Messages per Month</h3>

      <!-- Filter Controls -->
      <div class="flex flex-col sm:flex-row gap-3">
        <!-- Email Filter -->
        <div class="flex flex-col">
          <label for="email-filter" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Filter by Email</label>
          <select
            id="email-filter"
            v-model="selectedEmail"
            class="px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:outline-hidden focus:ring-1 focus:ring-primary focus:border-primary text-sm"
          >
            <option :value="null">All Emails</option>
            <option v-for="email in availableEmails" :key="email" :value="email">{{ email }}</option>
          </select>
        </div>

        <!-- Year Filter -->
        <div class="flex flex-col">
          <label for="year-filter" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
          <select
            id="year-filter"
            v-model="selectedYear"
            class="px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:outline-hidden focus:ring-1 focus:ring-primary focus:border-primary text-sm"
          >
            <option :value="null">All Years</option>
            <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
          </select>
        </div>

        <!-- Month Filter -->
        <div class="flex flex-col">
          <label for="month-filter" class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Month</label>
          <select
            id="month-filter"
            v-model="selectedMonth"
            class="px-3 py-2 rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:outline-hidden focus:ring-1 focus:ring-primary focus:border-primary text-sm"
            :disabled="!selectedYear"
          >
            <option :value="null">All Months</option>
            <option v-for="month in months" :key="month.value" :value="month.value">{{ month.label }}</option>
          </select>
        </div>

        <!-- Clear Filters Button -->
        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 rounded hover:bg-gray-300 dark:hover:bg-gray-500 text-sm transition-colors"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Chart Status Messages -->
    <div v-if="props.loading" class="text-center py-6 flex flex-col items-center space-y-4">
      <svg class="animate-spin h-8 w-8 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <div class="text-gray-600 dark:text-gray-400 animate-pulse">Loading chart data...</div>
    </div>
    <div v-else-if="props.error" class="text-center py-6 text-red-500">Error loading chart data: {{ props.error.message }}</div>
    <div v-else-if="chartData.labels.length > 0">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
    <div v-else class="text-center py-6 text-gray-500">
      No message data available for the selected filters.
    </div>

    <!-- Active Filters Display -->
    <div v-if="hasActiveFilters" class="mt-4 text-sm text-gray-600 dark:text-gray-400">
      <span class="font-medium">Active filters:</span>
      <span v-if="selectedEmail">Email: {{ selectedEmail }}</span>
      <span v-if="selectedYear"> | Year: {{ selectedYear }}</span>
      <span v-if="selectedMonth"> | Month: {{ months.find(m => m.value === selectedMonth)?.label }}</span>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
import type { Message } from '~/composables/useApi';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

// Props from parent component
const props = defineProps<{
  messages: Message[]
  loading: boolean
  error: Error | null
}>();

// Filter state
const selectedEmail = ref<string | null>(null);
const selectedYear = ref<number | null>(null);
const selectedMonth = ref<number | null>(null);

// Months for dropdown
const months = ref([
  { value: 1, label: 'January' },
  { value: 2, label: 'February' },
  { value: 3, label: 'March' },
  { value: 4, label: 'April' },
  { value: 5, label: 'May' },
  { value: 6, label: 'June' },
  { value: 7, label: 'July' },
  { value: 8, label: 'August' },
  { value: 9, label: 'September' },
  { value: 10, label: 'October' },
  { value: 11, label: 'November' },
  { value: 12, label: 'December' },
]);

// Available years (current year and previous 5 years)
const currentYear = new Date().getFullYear();
const availableYears = ref(Array.from({ length: 6 }, (_, i) => currentYear - i));

// Computed available emails from shared messages data
const availableEmails = computed(() => {
  return props.messages
    .map(msg => msg.email)
    .filter((email, index, arr) => arr.indexOf(email) === index) // Remove duplicates
    .sort();
});

// Use messages from props instead of API call
const allMessages = computed(() => props.messages);

// Client-side filtering and chart data generation
const chartData = computed(() => {
  let messages = [...allMessages.value];

  // Apply email filter
  if (selectedEmail.value) {
    messages = messages.filter(msg => msg.email === selectedEmail.value);
  }

  // Apply year filter
  if (selectedYear.value) {
    messages = messages.filter(msg => {
      const messageYear = new Date(msg.created_at).getFullYear();
      return messageYear === selectedYear.value;
    });
  }

  // Apply month filter
  if (selectedMonth.value) {
    messages = messages.filter(msg => {
      const messageMonth = new Date(msg.created_at).getMonth() + 1; // getMonth() returns 0-11
      return messageMonth === selectedMonth.value;
    });
  }

  // Group messages by month and count
  const monthlyCounts = new Map<string, number>();

  messages.forEach(msg => {
    const date = new Date(msg.created_at);
    const year = date.getFullYear();
    const month = date.getMonth();
    const monthKey = `${year}-${String(month + 1).padStart(2, '0')}`;

    monthlyCounts.set(monthKey, (monthlyCounts.get(monthKey) || 0) + 1);
  });

  // Sort by date and format labels
  const sortedEntries = Array.from(monthlyCounts.entries()).sort();

  const labels = sortedEntries.map(([monthKey]) => {
    const [year, month] = monthKey.split('-');
    if (!year || !month) return monthKey; // Fallback if split fails
    const date = new Date(parseInt(year), parseInt(month) - 1);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short' });
  });

  const data = sortedEntries.map(([, count]) => count);

  return {
    labels,
    datasets: [
      {
        label: 'Messages Received',
        backgroundColor: 'rgba(59, 130, 246, 0.6)', // Tailwind blue-500 with opacity
        borderColor: 'rgba(59, 130, 246, 1)', // Solid blue border
        borderWidth: 1,
        hoverBackgroundColor: 'rgba(59, 130, 246, 0.8)',
        data,
      },
    ],
  };
});

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

// Computed property to check if any filters are active
const hasActiveFilters = computed(() =>
  selectedEmail.value !== null ||
  selectedYear.value !== null ||
  selectedMonth.value !== null
);

// No need for watchers since chart data is computed reactively from filters

// Clear all filters
const clearFilters = () => {
  selectedEmail.value = null;
  selectedYear.value = null;
  selectedMonth.value = null;
};

</script>

<style scoped>
.message-chart-container {
  height: 300px; /* Example fixed height, adjust as needed */
  position: relative; /* Needed for chart.js responsiveness */
}
</style>
